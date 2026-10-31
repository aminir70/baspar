#!/usr/bin/env python3
"""Split, clean and minify the Baspar Elements stylesheet.

Usage:  python3 tools/build-css.py [--report]

Input:  assets/css/baspar-elements.css   (the full, hand-edited stylesheet)
Output: assets/css/src/<bundle>.css       (readable bundles)
        assets/css/dist/<bundle>.min.css  (what the site loads)
        assets/css/dist/all.min.css       (everything; editor / fallback)
        assets/css/dist/critical-product.min.css (inlined on product pages)
        includes/css-map.php              (widget -> bundle map for PHP)

How a rule is placed: every class in its selector is looked up in the PHP/JS
sources; a class printed only by widgets of one bundle sends the rule to that
bundle; classes used by several bundles (or by shared helpers / the script)
send it to "core", which every page with a Baspar widget loads first.
Original rule order is kept inside each bundle.

Cleaning (nothing else is dropped):
  * a rule whose selector + media query repeats later with all of its
    properties (the later one always wins) is removed;
  * @keyframes never referenced by an animation are removed;
  * rules whose every class appears nowhere in the plugin are removed only
    when their names are in the reviewed DEAD_OK list below.
"""
import json
import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, 'assets/css/baspar-elements.css')

BUNDLES = ['core', 'home', 'product', 'blog', 'side', 'shop', 'pages']
WIDGET_BUNDLE = {
    'core': ['topbar', 'header', 'footer', 'float-whatsapp'],
    'home': ['hero', 'categories', 'trust', 'products', 'product-tabs', 'promo', 'brands', 'about-box',
             'blog-posts', 'featured-line', 'hero-v2', 'cat-tiles', 'image-banners', 'cta-strip',
             'product-rail', 'about-v2', 'home-blog'],
    'product': ['product-gallery', 'product-info', 'product-description', 'product-reviews', 'pd-hero',
                'pd-anchors', 'pd-features', 'pd-specs', 'pd-description', 'pd-reviews', 'pd-related',
                'pd-contact', 'pd-bottom-text'],
    'blog': ['blog-sidebar', 'post-header', 'post-share', 'author-box', 'blog-hero', 'post-featured',
             'post-grid', 'blog-topics', 'post-hero', 'post-content', 'post-toc', 'related-posts',
             'post-comments'],
    # Sidebar widgets: used on single posts and on the product page.
    'side': ['side-cta', 'side-posts', 'side-cats', 'side-products', 'side-banner', 'side-links'],
    'shop': ['shop-sidebar', 'shop-toolbar', 'term-description'],
}
# Shared include files -> bundles that call them.
INCLUDE_BUNDLE = {
    'includes/helpers.php': {'core'},
    'includes/blog.php': {'blog', 'home', 'side'},
    'includes/product.php': {'product'},
    'includes/home.php': {'home'},
    'widgets/class-baspar-widget-base.php': {'core'},
}
# Classes the script only reads or toggles; counted only when nothing else
# prints the class (e.g. the lightbox the script builds).
JS_SOURCE = 'assets/js/baspar-elements.js'
# Selectors that must stay in core even if only one bundle prints them
# (generic utilities that any widget may use in the future).
GENERIC = {'baspar-scope', 'is-fa', 'mono', 'ltr', 'dim', 'small', 'screen-reader-text', 'bspr-sr',
           'baspar-reveal', 'reveal', 'in', 'container', 'baspar-section'}

# Critical (above the fold) product-page rules: selectors made only of these
# classes (plus elements/pseudo) are copied into critical-product.css.
CRITICAL_PRODUCT = {'baspar-scope', 'pdh', 'pdh-crumbs', 'pdh-grid', 'pdh-gallery', 'pdg', 'pdg-main',
                    'pdg-img', 'pdg-zoom', 'pdh-info', 'pdh-chips', 'pdh-chip', 'pdh-title', 'pdh-sku',
                    'is-fa', 'mono', 'ltr'}
CRITICAL_WIDGETS = ['topbar', 'header', 'float-whatsapp', 'pd-hero']
CRITICAL_CHROME = {'baspar-scope', 'bspr-topbar', 'topbar', 'topbar-inner', 'topbar-left', 'topbar-right',
                   'header', 'header-inner', 'brand', 'brand-text', 'bspr-bleed', 'bspr-header-root'}


# ---------------------------------------------------------------- parsing --
def strip_comments(css):
    out, i, n = [], 0, len(css)
    while i < n:
        if css.startswith('/*', i):
            j = css.find('*/', i + 2)
            i = n if j < 0 else j + 2
            continue
        c = css[i]
        if c in '"\'':
            j = i + 1
            while j < n and css[j] != c:
                j += 2 if css[j] == '\\' else 1
            out.append(css[i:j + 1])
            i = j + 1
            continue
        out.append(c)
        i += 1
    return ''.join(out)


def find_block_end(css, i):
    """css[i] == '{' -> index of the matching '}'."""
    depth, n = 0, len(css)
    while i < n:
        c = css[i]
        if c in '"\'':
            j = i + 1
            while j < n and css[j] != c:
                j += 2 if css[j] == '\\' else 1
            i = j + 1
            continue
        if c == '{':
            depth += 1
        elif c == '}':
            depth -= 1
            if depth == 0:
                return i
        i += 1
    raise ValueError('unbalanced braces')


def parse(css, media=()):
    """-> list of dicts: rule {sel, body, media} | keyframes {name, text} | other {text}."""
    items, i, n = [], 0, len(css)
    while i < n:
        while i < n and css[i] in ' \t\r\n;':
            i += 1
        if i >= n:
            break
        b = css.find('{', i)
        if b < 0:
            break
        prelude = css[i:b].strip()
        e = find_block_end(css, b)
        body = css[b + 1:e]
        if prelude.startswith('@media') or prelude.startswith('@supports') or prelude.startswith('@container'):
            items.extend(parse(body, media + (norm_ws(prelude),)))
        elif prelude.startswith('@keyframes') or prelude.startswith('@-webkit-keyframes'):
            items.append({'type': 'keyframes', 'name': prelude.split()[1], 'text': prelude + '{' + body + '}', 'media': media})
        elif prelude.startswith('@'):
            items.append({'type': 'other', 'text': prelude + '{' + body + '}', 'media': media})
        else:
            items.append({'type': 'rule', 'sel': norm_ws(prelude), 'body': body.strip(), 'media': media})
        i = e + 1
    return items


def norm_ws(s):
    return re.sub(r'\s+', ' ', s).strip()


def split_selectors(sel):
    parts, depth, cur = [], 0, ''
    for c in sel:
        if c in '([':
            depth += 1
        elif c in ')]':
            depth -= 1
        if c == ',' and depth == 0:
            parts.append(cur.strip())
            cur = ''
        else:
            cur += c
    if cur.strip():
        parts.append(cur.strip())
    return parts


def classes_of(sel):
    return set(re.findall(r'\.(-?[A-Za-z_][A-Za-z0-9_-]*)', re.sub(r'\[[^\]]*\]', '', sel)))


def decl_props(body):
    props, important = [], False
    for d in split_decls(body):
        if ':' not in d:
            continue
        p, v = d.split(':', 1)
        props.append(p.strip().lower())
        if '!important' in v:
            important = True
    return props, important


def split_decls(body):
    out, depth, cur, q = [], 0, '', None
    for c in body:
        if q:
            cur += c
            if c == q:
                q = None
            continue
        if c in '"\'':
            q = c
        elif c == '(':
            depth += 1
        elif c == ')':
            depth -= 1
        if c == ';' and depth == 0:
            out.append(cur.strip())
            cur = ''
        else:
            cur += c
    if cur.strip():
        out.append(cur.strip())
    return out


# ---------------------------------------------------------- source scan ---
def scan_sources():
    """class token -> set(bundles); prefixes (dynamic classes) -> set(bundles)."""
    files = {}
    for b, slugs in WIDGET_BUNDLE.items():
        for s in slugs:
            files['widgets/class-baspar-%s.php' % s] = {b}
    for f in os.listdir(os.path.join(ROOT, 'widgets')):
        rel = 'widgets/' + f
        if f.endswith('.php') and rel not in files and rel not in INCLUDE_BUNDLE:
            files[rel] = {'pages'}
    files.update(INCLUDE_BUNDLE)
    tokens, prefixes = {}, {}
    for rel, bset in files.items():
        p = os.path.join(ROOT, rel)
        if not os.path.exists(p):
            continue
        txt = open(p, encoding='utf-8').read()
        for t in set(re.findall(r'[A-Za-z_][A-Za-z0-9_-]*', txt)):
            tokens.setdefault(t, set()).update(bset)
        for t in set(re.findall(r'([A-Za-z_][A-Za-z0-9_]*(?:-[A-Za-z0-9_]+)*-)(?=[\'"{]|<\?php)', txt)):
            prefixes.setdefault(t, set()).update(bset)
    js = open(os.path.join(ROOT, JS_SOURCE), encoding='utf-8').read()
    for t in set(re.findall(r'[A-Za-z_][A-Za-z0-9_-]*', js)):
        if t not in tokens:
            tokens[t] = {'js'}  # only the script mentions it; placed in core unless a widget class decides
    return tokens, prefixes, files


WP_BODY = {'admin-bar', 'logged-in', 'rtl', 'home', 'single-product', 'single-post'}


def bundles_of_class(c, tokens, prefixes):
    m = re.match(r'elementor-widget-baspar-(.+)$', c)
    if m:
        return {widget_map().get(m.group(1), 'pages')}
    if c in tokens:
        return tokens[c]
    out = set()
    for p, b in prefixes.items():
        if c.startswith(p) and len(c) > len(p):
            out |= b
    return out


def widget_map():
    m = {}
    for b, slugs in WIDGET_BUNDLE.items():
        for s in slugs:
            m[s] = b
    for f in os.listdir(os.path.join(ROOT, 'widgets')):
        mm = re.match(r'class-baspar-(.+)\.php$', f)
        if mm and mm.group(1) != 'widget-base' and mm.group(1) not in m:
            m[mm.group(1)] = 'pages'
    return m


# ------------------------------------------------------------- minify -----
def minify_body(body):
    decls = [norm_ws(d) for d in split_decls(body) if d.strip()]
    out = []
    for d in decls:
        if ':' in d:
            p, v = d.split(':', 1)
            v = v.strip()
            v = re.sub(r'\s*!\s*important', '!important', v)
            v = re.sub(r'\s*,\s*', ',', v) if 'url(' not in v and '"' not in v and "'" not in v else v
            out.append(p.strip() + ':' + v)
        else:
            out.append(d)
    return ';'.join(out)


def minify_sel(sel):
    sel = norm_ws(sel)
    sel = re.sub(r'\s*([>+~,])\s*', r'\1', sel)
    return sel


def minify_text(t):
    t = norm_ws(t)
    t = re.sub(r'\s*([{};:,>])\s*', r'\1', t)
    t = t.replace(';}', '}')
    return t


def emit(items, minify):
    """Group consecutive items with the same media chain."""
    out, cur_media, buf = [], None, []

    def flush():
        if not buf:
            return
        inner = ('' if minify else '\n').join(buf)
        if cur_media:
            text = inner
            for m in reversed(cur_media):
                text = (m + '{' + text + '}') if minify else (m + '{\n' + text + '\n}')
            out.append(text)
        else:
            out.append(inner)

    for it in items:
        media = it['media']
        if media != cur_media:
            flush()
            buf = []
            cur_media = media
        if it['type'] == 'rule':
            if minify:
                buf.append(minify_sel(it['sel']) + '{' + minify_body(it['body']) + '}')
            else:
                buf.append(it['sel'] + '{' + it['body'] + '}')
        else:
            buf.append(minify_text(it['text']) if minify else it['text'])
    flush()
    return ('' if minify else '\n').join(out) + '\n'


# --------------------------------------------------------------- main -----
# Reviewed list of class names that appear nowhere in the plugin and are not
# printed by WordPress, WooCommerce or another plugin (prototype leftovers).
# Filled from the --report output after checking each name.
DEAD_OK = {
    'b2b-badge', 'blog-layout', 'bs-card', 'bs-code', 'bs-foot', 'bs-img', 'bs-name', 'bs-num',
    'bs-stripes', 'bs-top', 'cat-grid', 'cat-layout', 'cat-list', 'cat-main', 'cat-range',
    'cat-range-values', 'cmp-tick', 'contact-grid', 'foot', 'foot-brand', 'foot-inner',
    'foot-meta', 'h2t', 'hero-art-frame', 'hero-art-img', 'hero-art-mark', 'hero-art-tag',
    'hero-b2b', 'hero-b2b-ctas', 'hero-b2b-inner', 'hero-b2b-sub', 'hero-b2b-trust',
    'hero-b2b-visual', 'hero-stat-card', 'lcard', 'lcard-body', 'lcard-cta', 'midcta-b',
    'midcta-b-btns', 'midcta-b-inner', 'nav-dd', 'nav-dd-menu', 'origin-head', 'origin-head-text',
    'origin-pill', 'origin-pills', 'origin-tagline', 'post-body', 'post-figure', 'post-hero',
    'post-wrap', 'prod-layout', 'prod-tabs', 'ptab', 'ptab-panel', 'ptabs-bar', 'pulse',
    'rfq-body', 'rfq-card-float', 'rfq-dots', 'rfq-foot', 'rfq-head', 'rfq-head-l', 'rfq-panel',
    'rfq-row', 'rfq-tag', 'scc-followers', 'section-alt', 'tb-tag', 'team-tag',
}


def main():
    report = '--report' in sys.argv
    css = strip_comments(open(SRC, encoding='utf-8').read())
    items = parse(css)
    tokens, prefixes, _ = scan_sources()

    # 1) overridden duplicates
    rules = [it for it in items if it['type'] == 'rule']
    later = {}
    drop = set()
    for idx in range(len(items) - 1, -1, -1):
        it = items[idx]
        if it['type'] != 'rule':
            continue
        key = (it['sel'], it['media'])
        props, imp = decl_props(it['body'])
        if key in later:
            lp, limp = later[key]
            if set(props) <= lp and (not imp or limp):
                drop.add(idx)
                continue
            later[key] = (lp | set(props), limp or imp)
        else:
            later[key] = (set(props), imp)
    dup_removed = len(drop)

    # 2) classify
    unknown = {}
    for idx, it in enumerate(items):
        if idx in drop:
            continue
        if it['type'] != 'rule':
            continue
        sels = split_selectors(it['sel'])
        bset, all_unknown, unknown_names = set(), True, set()
        for s in sels:
            cls = classes_of(s) - {'baspar-scope'} - WP_BODY
            if not cls:
                bset.add('core')
                all_unknown = False
                continue
            cands = []
            for c in sorted(cls):
                b = bundles_of_class(c, tokens, prefixes)
                if b:
                    cands.append(b)
                else:
                    unknown_names.add(c)
            if not cands:
                continue
            all_unknown = False
            if cls & GENERIC and len(cands) == len([c for c in cls if c in GENERIC]):
                bset.add('core')
                continue
            # The most specific class decides; if several classes tie, take all
            # their bundles (a class toggled only by the script counts as core
            # and gives way to a real bundle).
            n = min(len(c) for c in cands)
            best = set()
            for c in cands:
                if len(c) == n:
                    best |= c
            if len(best) > 1:
                best.discard('js')
            bset |= {'core' if b == 'js' else b for b in best}
        if all_unknown:
            it['bundle'] = 'dead' if unknown_names <= DEAD_OK else 'core'
            if it['bundle'] != 'dead':
                for c in unknown_names:
                    unknown.setdefault(c, 0)
                    unknown[c] += 1
        else:
            it['bundle'] = sorted(bset)[0] if len(bset) == 1 else 'core'

    # Safety check: a rule placed in a page bundle must, in every selector,
    # contain a class printed only by that bundle's widgets (or toggled by the
    # script) — otherwise a page without that bundle could miss it.
    moved = 0
    for it in items:
        if it['type'] != 'rule' or it.get('bundle') in (None, 'dead', 'core'):
            continue
        for sel in split_selectors(it['sel']):
            cls = classes_of(sel) - {'baspar-scope'} - WP_BODY
            if not any(bundles_of_class(c, tokens, prefixes) and bundles_of_class(c, tokens, prefixes) <= {it['bundle'], 'js'} for c in cls):
                it['bundle'] = 'core'
                moved += 1
                break

    # 3) keyframes placement
    anim_use = {}
    for it in items:
        if it['type'] == 'rule' and it.get('bundle') not in (None, 'dead'):
            for d in split_decls(it['body']):
                if ':' in d:
                    p, v = d.split(':', 1)
                    if p.strip().lower() in ('animation', 'animation-name'):
                        for name in re.findall(r'[A-Za-z_][A-Za-z0-9_-]*', v):
                            anim_use.setdefault(name, set()).add(it['bundle'])
    kf_removed = 0
    for it in items:
        if it['type'] == 'keyframes':
            b = anim_use.get(it['name'])
            if not b:
                it['bundle'] = 'dead'
                kf_removed += 1
            else:
                it['bundle'] = next(iter(b)) if len(b) == 1 else 'core'
        elif it['type'] == 'other':
            it['bundle'] = 'core'

    keep = [it for i, it in enumerate(items) if i not in drop and it.get('bundle') != 'dead']
    dead_removed = len([it for i, it in enumerate(items) if i not in drop and it.get('bundle') == 'dead' and it['type'] == 'rule'])

    os.makedirs(os.path.join(ROOT, 'assets/css/src'), exist_ok=True)
    os.makedirs(os.path.join(ROOT, 'assets/css/dist'), exist_ok=True)
    sizes = {}
    header = '/* Baspar Elements — %s bundle. Generated by tools/build-css.py from assets/css/baspar-elements.css; edit that file, then rebuild. */\n'
    for b in BUNDLES:
        part = [it for it in keep if it['bundle'] == b]
        open(os.path.join(ROOT, 'assets/css/src/%s.css' % b), 'w', encoding='utf-8').write(header % b + emit(part, False))
        mini = emit(part, True).strip() + '\n'
        open(os.path.join(ROOT, 'assets/css/dist/%s.min.css' % b), 'w', encoding='utf-8').write(mini)
        sizes[b] = (len(part), len(mini.encode()))
    allmin = emit(keep, True).strip() + '\n'

    # Ordered parts: consecutive rules of one bundle, in the original file
    # order. PHP joins the parts of the bundles a page needs into one file,
    # so every rule keeps its original position in the cascade.
    parts, run, run_b = [], [], None
    for it in keep:
        if it['bundle'] != run_b and run:
            parts.append([run_b, emit(run, True).strip()])
            run = []
        run_b = it['bundle']
        run.append(it)
    if run:
        parts.append([run_b, emit(run, True).strip()])
    json.dump(parts, open(os.path.join(ROOT, 'assets/css/dist/parts.json'), 'w', encoding='utf-8'), ensure_ascii=False, separators=(',', ':'))
    open(os.path.join(ROOT, 'assets/css/dist/all.min.css'), 'w', encoding='utf-8').write(allmin)

    # 4) critical CSS for the product page: everything above the fold —
    # topbar, header (menu panels and drawer stay hidden), floating buttons
    # and the whole product hero — layout properties only.
    crit_set = CRITICAL_PRODUCT | CRITICAL_CHROME
    for w in CRITICAL_WIDGETS:
        txt = open(os.path.join(ROOT, 'widgets/class-baspar-%s.php' % w), encoding='utf-8').read()
        crit_set |= set(re.findall(r'[A-Za-z_][A-Za-z0-9_-]*', txt))
    crit_set -= {'is-open', 'is-stuck'}
    crit = []
    for it in keep:
        if it['type'] != 'rule':
            continue
        sels = [s for s in split_selectors(it['sel']) if classes_of(s) and classes_of(s) <= crit_set
                and not re.search(r':(hover|focus|active|focus-visible|focus-within)|::?(before|after)', s)]
        if not sels:
            continue
        if it['sel'] == '.baspar-scope *,.baspar-scope *::before,.baspar-scope *::after':
            pass
        crit.append(dict(it, sel=','.join(sels)))
    # keep only layout-relevant declarations
    LAYOUT = re.compile(r'^(--[a-z0-9-]+|display|position|top|right|bottom|left|inset.*|width|height|min-.*|max-.*|aspect-ratio|margin.*|padding.*|grid.*|flex.*|gap|row-gap|column-gap|align.*|justify.*|order|box-sizing|font.*|line-height|letter-spacing|direction|overflow.*|border-radius|border|border-width|white-space|text-overflow|object-fit|visibility|vertical-align|text-align|transform|translate|opacity|pointer-events|z-index|list-style.*|clip|clip-path|content|-webkit-line-clamp|-webkit-box-orient|color|background|background-color|border-color|text-decoration)$')
    crit_items = []
    for it in crit:
        ds = [d for d in split_decls(it['body']) if ':' in d and LAYOUT.match(d.split(':', 1)[0].strip().lower())]
        if ds:
            crit_items.append(dict(it, body=';'.join(ds)))
    crit_css = emit(crit_items, True).strip() + '\n'
    open(os.path.join(ROOT, 'assets/css/dist/critical-product.min.css'), 'w', encoding='utf-8').write(crit_css)

    # 5) PHP map
    wm = widget_map()
    php = "<?php\n/**\n * Generated by tools/build-css.py — widget => CSS bundle.\n *\n * @package BasparElements\n */\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\nreturn " + php_array(wm) + ";\n"
    open(os.path.join(ROOT, 'includes/css-map.php'), 'w', encoding='utf-8').write(php)

    print('rules in: %d | overridden dups removed: %d | unused keyframes removed: %d | dead rules removed: %d | safety moves to core: %d'
          % (len(rules), dup_removed, kf_removed, dead_removed, moved))
    for b in BUNDLES:
        print('  %-8s %4d items  %7d bytes min' % (b, sizes[b][0], sizes[b][1]))
    print('  all      %7d bytes min | critical %d bytes | %d ordered parts' % (len(allmin.encode()), len(crit_css.encode()), len(parts)))
    if report:
        print('unknown classes kept in core (count):')
        for c, n in sorted(unknown.items(), key=lambda x: -x[1]):
            print('  ', c, n)


def php_array(d):
    rows = ["\t'%s' => '%s'," % (k, v) for k, v in sorted(d.items())]
    return 'array(\n' + '\n'.join(rows) + '\n)'


if __name__ == '__main__':
    main()
