# Baspar Elements 1.9.0 — speed plan (LCP / CLS on mobile)

Written before the change. Each item: what changes, why, risk, how the risk is handled.

## 1. Split the stylesheet and load only what a page uses
- `assets/css/baspar-elements.css` (341 KB raw, 56 KB gzip, every page) is
  split by `tools/build-css.py` into bundles: `core` (shared rules, topbar,
  header/mega menu, footer, WhatsApp button), `home`, `product`, `blog`,
  `side` (sidebar widgets, used by posts and products), `shop`, `pages`
  (about / contact / pillar / importer / city). A rule goes to the bundle of
  the widgets that print its class names; rules used by several bundles go
  to `core`.
- Which bundles a page needs is read in `<head>` from the Elementor data of
  the page and of the Elementor Pro theme-builder header / footer / single /
  archive templates (nested template and global widgets included). Pages
  without any Baspar widget get no Baspar CSS, JS or webfont.
- The page gets ONE stylesheet: the needed bundles joined in the original
  rule order (from `assets/css/dist/parts.json`), written once to
  `wp-content/uploads/baspar-elements/page-<version>-<hash>.css`. Keeping the
  original order matters: when the bundles were loaded as separate files,
  three pages showed differences (a shared rule that used to come after a
  page rule now came first). If uploads isn't writable, the separate bundle
  files are used.
- Safety net: every widget declares its bundle to Elementor
  (`get_style_depends`), so a widget the scan can't see (popup, shortcode)
  still gets its CSS.
- Elementor editor / preview: the full stylesheet, as before.

Risk: medium. Handled by: loading 12 live pages and comparing the computed
style of every element (old full file vs. the new page file) at 1366 px and
390 px. Kill switch: `define( 'BASPAR_ELEMENTS_CSS_ALL', true );` in
wp-config.php restores the 1.8.x loading.

## 2. Critical CSS for the product page (inline)
- A CSS block for everything above the fold on product pages (topbar,
  header with its menu panels/drawer kept hidden, floating buttons, the whole
  product hero) is printed inline in `<head>`: layout properties and colours
  only, 29 KB raw / 5.5 KB gzip. Turn off with
  `add_filter( 'baspar_elements_critical_css', '__return_false' );`.
  `.pdg-main` keeps `aspect-ratio:1/1; width:100%` and the image has
  `width`/`height`, so the photo's space is reserved even if the rest of the
  CSS arrives late (WP Rocket "load CSS asynchronously").

Risk: low. It repeats rules that already exist in the stylesheet (same
values), so the page looks identical; the cost is ~5.5 KB gzip of HTML.
Tested by loading the live DOP page with the Baspar CSS made asynchronous and
2.5 s late: without the block the header first paints as a long unstyled
link list (layout shift 0.91 in the test); with it, layout shift is 0 and the
product photo is the LCP element.

## 3. Responsive WebP gallery image
- The first gallery image is printed as WebP at 360, 480 and 600 px with a
  `sizes` that matches the real column width (full width on phones). The
  original PNG is no longer in `srcset`. `fetchpriority="high"`,
  `loading="eager"`, `width`/`height` stay. A `<link rel="preload">` for it is
  added to `<head>`.
- The WebP files are made once per image (WordPress image editor, quality 75)
  next to the original (`name-bspr480.webp`) the first time the product is
  viewed or saved. If the server can't write WebP, the old markup is used.
- The image keeps the `webpexpress-processed` class so WebP Express doesn't
  wrap it in a second `<picture>`. Thumbnail clicks switch to the WebP 600.

Risk: low–medium. First view of each product creates up to 3 small files
(~0.1–0.3 s once). A lock prevents two requests doing it at the same time.
WebP Express settings are not changed.

## 4. Dead / duplicate CSS
- Removed: rules repeated with the same selector and declarations, rules
  fully overridden later by the same selector in the same media query,
  `@keyframes` never used, and rules for class names that exist nowhere in the
  plugin (old prototype leftovers). Class names that come from WordPress,
  WooCommerce or other plugins' HTML are kept.
- Fonts: Google Fonts link already has `display=swap` (kept); it is now only
  loaded on pages with Baspar widgets.

Risk: low. The computed-style comparison in item 1 covers this too.

## 5. JavaScript
- Loaded only when a Baspar widget is on the page (Elementor's script
  dependency), with `defer`, minified copy in `assets/js/dist/`.
- No forced layout on load: scroll-reveal/motion use the IntersectionObserver
  result instead of `getBoundingClientRect()`; sliders, tab indicator, sticky
  header and sticky sidebar measure inside `ResizeObserver` / after the first
  paint, and reads and writes are batched.

Risk: low–medium (timing). Checked by running the menus, drawer, tabs,
sliders, counters, TOC, gallery/lightbox in a browser.

## 6. WP Rocket helpers
- Adds Baspar's JavaScript-state classes (`is-open`, `is-on`, `in`, …) to the
  "Remove Unused CSS" safelist, so RUCSS can't strip the open state of the
  mobile menu, tabs or animations.

Risk: none (only adds to a list).

## 7. Release
- Version 1.9.0 (new `?ver=` on every file). The 1.8.1 CSS/JS are kept in
  `backup/1.8.1/` in the repository (not shipped in the zip).
