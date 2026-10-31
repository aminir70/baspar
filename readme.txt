=== Baspar Elements ===
Contributors: basparmarket
Tags: elementor, woocommerce, widgets, rtl, persian
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.9.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

مجموعه ویجت‌های اختصاصی المنتور برای سایت بسپارمارکت؛ هر بخش یک ویجت جدا، کاملاً قابل تغییر و با داده‌ی پویا از وردپرس/ووکامرس.

== Description ==

Baspar Elements adds a set of Elementor widgets that implement the Baspar Market
design system (industrial purple, Vazirmatn). Every section is a separate widget,
fully customizable from Elementor (typography, colors, logo, images, spacing), and
dynamic data (menu, posts, WooCommerce products & categories) is read live from
WordPress.

Includes 97 widgets (header/menu, footer, hero, categories, trust, products,
product tabs, promo, brands, about, blog, breadcrumb, stats, mission/vision/values,
timeline, team, process, industries, FAQ, cities, mid/final CTA, compare table,
contact methods, social showcase, contact map, city quick-facts, shipment route,
city zones, city industries, and more) plus 15 importable templates (home, blog magazine, single post,
about, contact, pillar, importer, product, shop, blog, and one per city: Shiraz,
Isfahan, Mashhad, Tabriz, Tehran).

Requires Elementor. WooCommerce is required only for the product/category widgets.

== Installation ==

1. Upload the `baspar-elements` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Edit any page with Elementor and find the widgets under the "بسپارمارکت" category.
4. Optionally import the page templates from the `templates/` folder via
   Templates → Saved Templates → Import Templates.

== Changelog ==

= 1.9.1 =
* Fix: product pages printed five Baspar stylesheets (page file + core +
  product + side + all.min.css). Elementor saves each page's widget style
  dependencies once (_elementor_page_assets) and reuses them: lists saved by
  1.8.x named the full stylesheet, and theme templates had saved the bundle
  handles before the page plan existed. Now:
  - the page plan is decided once per request, available to any caller;
  - widgets always declare the same handles (their bundle);
  - a print_styles_array filter prints only the planned file, whoever
    enqueued Baspar handles (head and footer);
  - saved Elementor lists from older versions are cleared once after update.
  Result: one Baspar stylesheet per page (all.min.css only with
  BASPAR_ELEMENTS_CSS_ALL).
* Webfonts self-hosted (Vazirmatn arabic/latin/latin-ext, JetBrains Mono
  latin/latin-ext — variable WOFF2, OFL) with inline @font-face and
  font-display:swap: no render-blocking Google Fonts stylesheet; the Persian
  font is preloaded; JetBrains Mono downloads only where it is shown.
* WP Rocket: uploads/baspar-elements/ excluded from CSS minify (already
  minified, versioned by file name).

= 1.9.0 =
* Speed (mobile LCP / CLS). Details and test notes: docs/perf-1.9.0.md.
  - CSS is loaded only where needed: the stylesheet is split into bundles
    (core, home, product, blog, side, shop, pages); each page gets one
    minified file with just its bundles, in the original rule order
    (built once into uploads/baspar-elements/). Pages with no Baspar widget
    load no Baspar CSS, script or webfont. Product page: 55 KB -> 15 KB gzip.
  - Critical CSS inline on product pages (header, gallery, title): no layout
    shift even when CSS is loaded asynchronously.
  - Product gallery: first image as WebP 360/480/600 (made once, quality 75)
    with a sizes matching the column, preloaded; the PNG is no longer in
    srcset. Falls back to the old markup when WebP can't be written.
  - Removed 269 overridden duplicate rules, 142 rules of unused prototype
    classes and 5 unused @keyframes.
  - Script: minified, deferred, only on pages with Baspar widgets; no
    forced layout on load (measurements wait for the first paint /
    ResizeObserver / IntersectionObserver, reads before writes).
  - WP Rocket: script-driven classes added to the Remove Unused CSS safelist.
  - Kill switch: define( 'BASPAR_ELEMENTS_CSS_ALL', true ) in wp-config.php.

= 1.8.1 =
* Fix: mega menu category hover and the mobile menu drawer did nothing on the
  live site. WP Rocket kept serving an old minified copy of the plugin script
  (made before the mega menu existed). The plugin's CSS/JS are now excluded
  from WP Rocket minify/combine and "delay JavaScript", so they always load
  the version that is installed.

= 1.8.0 =
* Hello Elementor support. The theme can be switched from Woodmart to Hello:
  - Topbar, header and footer get "تمام عرض صفحه" (on by default): they
    span the whole window even inside a boxed section.
  - Sticky header without Elementor Pro's sticky: the header area sticks to
    the top, the topbar scrolls away, a soft shadow appears once scrolled.
  - Hardening against Hello's reset (pink button borders/hover, table zebra
    rows and padding, list/heading margins) inside every Baspar widget.
* New home page (7 widgets, all with dynamic, reduced-motion aware motion):
  hero-v2 (aurora background, drifting molecules, rotating industry word,
  animated counters, live catalogue card), cat-tiles (bento categories with
  live counts and cursor spotlight), image-banners (3D tilt + light sweep),
  cta-strip (moving gradient promo), product-rail (tabbed or category
  showcase slider, autoplay, arrows, progress), about-v2 (orbiting icons,
  counters), home-blog (bento of latest posts). Every current home section
  is kept, Woodmart elements replaced.
* Templates: baspar-home-v2.json (page), baspar-header-hello.json (header),
  baspar-footer-hello.json (footer).

= 1.7.0 =
* Header: new mega menu (default "نوع منو: مگا منو"; "کشویی ساده" keeps the
  old menu). Built from the same WordPress menu:
  - Desktop: items whose sub-menu has two levels (e.g. محصولات) open a full
    panel — category tabs with images and product counts, the active
    category's sub-categories as columns (or image cards when there is one
    level), its newest products (cached 6h), a "view all" link and a promo
    card (title, text, WhatsApp button, second link). Other items open a
    styled dropdown. Opens on hover with a small delay and on keyboard focus;
    Escape closes; menu items with "#" become buttons (no href="#").
  - Mobile: a drawer from the right with product search, drill-down views
    (back button, "view all" link, category tiles with images), WhatsApp and
    call buttons and working hours. Moved to <body> so the sticky header
    cannot clip it; Escape / overlay / link tap closes it.
  - CSS classes "mega" / "no-mega" on a menu item force the panel on or off.

= 1.6.5 =
* Product hero for products with little data (none of the 163 products has a
  short description or SKU; 68 have no attributes), e.g. MEKO, where the
  middle column was just the title:
  - Automatic summary when the short description is empty: Rank Math meta
    description, else the excerpt, else the first real paragraph of the
    description (40 words).
  - "Buy in 3 steps" block (editable lines); by default shown only when the
    product has no attributes, so spec-rich pages do not get longer.

= 1.6.4 =
* Product hero: removed the empty block under the product photo. The three
  columns now end at (almost) the same height: wider photo column, share
  buttons under the photo, highlights at the bottom of the info column, the
  gallery is no longer sticky, and the expert cards are more compact (role
  text limited to two lines, smaller paddings) so the quote card is not much
  taller than the photo.

= 1.6.3 =
* SEO / markup audit of the new product page:
  - Structured data: on single products the Rank Math graph is completed with
    a BreadcrumbList (linked from the page node), the Product's brand (name +
    logo) and SKU — values come from the product, nothing is invented; nodes
    Rank Math already emitted are untouched.
  - Headings: the description widget's title is no longer a generic H2 before
    the article's own H2s (new tag control, default div); sidebar box titles
    are no longer H3s (they used to appear inside the reviews outline); the
    comment form title no longer contains the "cancel reply" link text;
    footer column titles are H3 (were H5 straight after H2).
  - Header and footer logos have width/height (layout shift); footer and
    top-bar social links are upgraded from http:// to https://.

= 1.6.2 =
* Bale: the button now uses Bale's real logo (speech bubble with a check mark,
  drawn as an inline SVG) and its brand colour #0DCA9B, in the expert cards and
  in the mobile contact bar.

= 1.6.1 =
* Bale messenger: new "بله شرکت" field on the experts page; experts without
  their own Bale ID use the company account, so the Bale button shows for
  everyone once it is set. Bale button added to the mobile contact bar.
  Bale links are built only from a username or a ble.ir link (phone-number
  links are not supported by Bale).

= 1.6.0 =
* New product page (no online purchase — price on request), 9 widgets:
  - Product Hero: gallery with thumbnails + lightbox, category and brand
    chips (with brand logo), H1, SKU, short description, key specs from the
    product attributes, highlights, share buttons, stock status, "price on
    request" box and the category's sales experts (WhatsApp / Bale / call),
    plus a fixed contact bar on mobile.
  - Section nav (sticky), trust strip, full specifications table (product
    attributes + category, brand, SKU, weight/dimensions), full description
    with table of contents and cleaned-up pasted styling, reviews with rating
    summary/distribution and a no-JS star rating form, related products,
    sidebar contact card, and a per-product bottom text.
* Sales experts: wp-admin › بسپار › کارشناسان فروش. Each expert has name,
  role, photo, WhatsApp, Bale and phone and a list of product categories
  (many-to-many; sub-categories inherit from their parents; "default" experts
  for categories without anyone). Editable prefilled WhatsApp message and
  working hours. A summary table shows who answers each category.
* Per-product bottom text: meta box "بسپار — متن پایین صفحه محصول" on the
  product edit screen.
* Templates: templates/baspar-single-product.json (Theme Builder, Single
  Product) and templates/baspar-single-product-section.json (page type, for
  a Woodmart single-product layout).

= 1.5.5 =
* Author Box: the "ادامه معرفی" button did not react (it depended on the
  plugin script binding at the right moment, which fails inside the Elementor
  editor and with delayed/cached scripts). It is now a pure-CSS toggle
  (checkbox + label), so it works everywhere without JavaScript.

= 1.5.4 =
* Author Box redesigned and fixed: a biography containing HTML (<strong>) and
  ✓ marks was printed as raw tags; it is now decoded and sanitized (bold, line
  breaks, links; every ✓ becomes its own checklist line, bold labels like
  "سابقه کار:" start a new line). The avatar no longer collapses to a sliver
  (fixed 84px circle), long biographies are collapsed behind an
  "ادامه معرفی" button, and there is a link to the author's articles.

= 1.5.3 =
* Sidebar "latest products": default is now 10 products in random order
  (new "ترتیب" control: random / latest). Random mode renders a pool of up to
  3x the count and the script shows a fresh random selection on every page
  view, so it keeps changing even on cached pages (WP Rocket).

= 1.5.2 =
* Single post: the sidebar column is now sticky for the whole article. When
  the sidebar is taller than the screen it scrolls until its bottom edge is
  visible and then stays pinned until the article ends (pure CSS sticky with a
  computed negative `top`; desktop > 1024px only). Applied automatically to the
  column that holds the sidebar widgets next to the article; add the CSS class
  `bspr-no-sticky` to the column to turn it off.

= 1.5.1 =
* Single-post sidebar: five new widgets — Latest Posts, Main Product
  Categories (top-level, with category image/icon and live counts), Latest
  Products, Banner (uploaded image + link, or a designed gradient banner)
  and Link Cards (default: "خرید مواد اولیه شیمیایی" and
  "وارد کننده مواد اولیه شیمیایی").
* templates/baspar-single-post.json updated: sidebar = CTA → link cards →
  banner → latest posts → product categories → banner → latest products.
  The table of contents moved to a collapsed bar at the top of the article
  (a sticky TOC cannot share a column with a tall stack of widgets without
  overlapping them).

= 1.5.0 =
* New blog design (magazine landing + single post), built from 10 new widgets:
  - Blog Hero (H1, article search, topic chips, live article/category counts)
  - Featured Articles (magazine layout; sticky posts → "important", falls back
    to most discussed; or latest / category / hand-picked IDs)
  - Article List (grid or row cards, numbered pagination, follows the hero
    search ?bq= and ?bcat= so the page filters in place, skips posts already
    shown above)
  - Blog Topics (tiles with live article counts that filter the list)
  - Post Hero (breadcrumb, category, H1, lead, author/date/updated/reading
    time/comments, featured image)
  - Post Content (reading typography, styled tables with horizontal scroll,
    quotes, images; optional clean-up of pasted inline colours/sizes and empty
    paragraphs; reading-progress bar; hides the duplicate LuckyWP/Easy TOC box)
  - Table of Contents (from the article's H2/H3, sticky, highlights the
    section being read, collapsible on phones)
  - Related Articles (title keyword + category, filled with latest)
  - Comments, Sidebar CTA card
* Templates: templates/baspar-blog-magazine.json (page) and
  templates/baspar-single-post.json (Elementor Pro theme builder, Single Post).
* Author Box no longer prints the author's WordPress login name as the role;
  shows a configurable role label, real avatar and a fallback bio.

= 1.4.4 =
* Contact Map: the embed field now accepts the full <iframe> code copied
  from Google Maps / Neshan (previously only the bare src URL worked, so a
  pasted iframe showed an empty box). The real map is shown in a framed,
  animated card: loading skeleton, fade-in, soft colour until hover, and a
  floating info card (title, optional address, pulsing pin) with a
  "مسیریابی" button whose directions link is built from the map's
  coordinates (or set manually). Respects reduced-motion.

= 1.4.3 =
* Shop toolbar redesigned: search, sort and view toggle share one 42px
  height (the theme was stretching the search box and squashing the view
  buttons), search icon on the reading side, custom sort chevron, focus
  ring, real grid/list toggle; inline styles moved to the stylesheet;
  stacks cleanly on tablet and phone. Accessible labels for all controls.

= 1.4.2 =
* Shop sidebar category tree redesigned: rows with hover/open tint, counts
  as Persian-digit pills, small round expand/collapse button (no more tall
  theme-styled boxes), aligned columns for rows without children, indented
  sub-categories with a soft guide line instead of "·" prefixes, and the
  current category highlighted. Styles moved from an inline <style> block
  into the stylesheet; toggle buttons expose aria-expanded.

= 1.4.1 =
* Header menu: items are aligned on one line again. The theme gives every
  list item except the last a 10px bottom margin, so the last item
  ("تماس با ما") sat ~5px lower; menu list items now have no margin.

= 1.4.0 =
* Restored the complete design stylesheet. An earlier series of interrupted
  pushes (1.2.2) had replaced the 188 KB design system with a partial
  58 KB file, so many sections lost their styling (home hero console and
  floating cards, process steps, cities, FAQ list, CTA button groups,
  product-card labels, pagination, and more). The full file is back, with
  every selector now scoped to `.baspar-scope` (some prototype rules such as
  `.btn`, `.header`, `.section` used to leak into the theme) and duplicate
  prototype rules removed so the refined/mobile rules win. All 1.3.x fixes
  are re-applied on top.
* Persian text no longer renders in the letter-spaced monospace label font
  (it broke the letter joins, e.g. «گ ر ی د»): widget output marks
  Persian-text elements with `is-fa`, which resets font and tracking.
* Scroll-reveal animation can no longer hide content: elements stay visible
  until the script runs (safe with WP Rocket "delay JS"), and anything
  already on screen is shown without animating.
* Products pagination: the "next" arrow was rendered as a dead button with an
  empty icon (the SVG's `currentColor` was mistaken for the current page);
  now real prev/next links with proper icons, styles moved to CSS.
* Shop toolbar: real grid/list icons (both showed a box icon); toolbar wraps
  cleanly on phones.
* Team / Principles widgets: column count no longer set inline, so the grid
  collapses properly on tablet/phone; team photos use a 4:5 frame.
* Home and pillar hero floating cards no longer cover the console/product
  details; hidden on small phones. Header CTA hidden on mobile again.
* Focus outlines, selection colour and link styling in rich text.

= 1.3.7 =
* Pillar page (buy-chemical-raw-materials) update:
  - New widget: Seller Compare (مقایسه انواع فروشنده) — header + real
    `<table>` with `<thead>`/`<th>`, rows from a repeater, horizontal scroll
    on small screens, optional highlighted row and closing paragraph.
  - Hero Pillar: the title is now sanitized on output (h1–h6/p/span wrappers
    and inline styles are stripped) and the H1 is printed only when it has
    text — fixes the empty `<h1>` + second H1 caused by a heading pasted
    inside the title field. New default title/subtitle.
  - Products: the card label now shows the primary category *name*
    (Rank Math primary category first) instead of the raw upper-cased slug.
  - Trust cards: new optional per-card number and link (+ link label) fields.
  - Brands Strip: new switcher to hide the label + title above the strip.
  - Mid CTA: new "title tag" select (h2/h3/div/p), default h2.
  - Process: new optional per-step check-list (one item per line).
  - Cities: cards without a link are now a plain `<span>` (no `href="#"`,
    no arrow); optional link per city; default links for 4 cities.
  - FAQ: answers may contain links/bold (kses-filtered); new default Q&A list.
  - Final CTA: new default texts, shop button links to `/shop/`; removed the
    inline style on ghost buttons (moved to CSS).
  - Buying Guide: new default 5 questions.
  - Internal links produced by these widgets now always end with `/`
    (new `slash_url()` / `slash_links()` helpers).
  - CSS: restored rules for the cities, process steps, FAQ list and CTA
    button groups that had been dropped by an earlier stylesheet rewrite.

= 1.3.6 =
* Fixed the About Box widget's counter/stats block (`.aboutbox-stats`):
  it had zero CSS, so the two counters (number, title, subtitle) just
  stacked as plain unstyled text instead of showing side-by-side with a
  separator. Added a flex layout matching the widget's existing design
  language.

= 1.3.5 =
* Fixed the footer logo: `.footer-brand img` had no explicit size, so the
  logo (a 2000x2000 source file) rendered at whatever width its flex
  container allowed (~300px+), stretching the brand column's row height
  and pushing the "بسپارمارکت / BASPARMARKET.COM" text out of its own
  column into the neighboring "لینک‌های مهم" column — on both desktop and
  mobile. Constrained it to 48x48px, matching the header logo's sizing.

= 1.3.4 =
* Fixed the Float WhatsApp widget: its render() output was never wrapped in
  the shared `.baspar-scope` div that every other widget uses, so none of
  its CSS (fixed position, 60px circle, brand color, pulse animation)
  ever matched — it rendered as a plain unstyled inline link sitting
  wherever it was placed in the page instead of a floating button pinned
  to the corner. This also explains the odd oversized box it showed as in
  the Elementor editor canvas. Verified the fix renders the button
  correctly (position:fixed, circular, green) against the plugin's own
  stylesheet.

= 1.3.3 =
* Fixed three more mobile bugs on the homepage/header, found and confirmed
  the same way (headless mobile rendering of the live site):
  - Header: on mobile, with the nav and CTA hidden, the logo and hamburger
    button had no space between them, leaving the hamburger stranded next
    to the logo instead of anchored to the edge of the screen. Added
    `justify-content:space-between` on mobile.
  - Mobile menu: items were missing `display:block` on the link, so a
    plain link's row height (~28px) didn't match a dropdown item's row
    height (~54px) — menu rows looked uneven when opened.
  - Home hero "live console" mockup (product rows): the row's grid could
    not shrink below its text's natural minimum width, forcing the whole
    hero to overflow horizontally on phones. Restructured the row to a
    2-column layout on narrow screens (icon beside code+name, stock badge
    and arrow hidden) instead of stretching off-screen.

= 1.3.2 =
* Fixed two real mobile layout bugs, confirmed by rendering the live site in
  a headless mobile browser:
  - Topbar: the phone number and email could break mid-character onto
    3+ lines on narrow screens because the links had no `white-space:nowrap`
    and the row had no `flex-wrap`. Now each link wraps as a whole unit.
  - Chemical Importer page hero ("imp-hero-inner"): this two-column grid
    was missing a mobile breakpoint entirely (unlike every sibling hero
    layout), so on phones it stayed at a squeezed ~90px column, causing the
    supply-network visual to overlap the heading text. Added the same
    920px stacking rule used by the other hero widgets.

= 1.3.1 =
* City page CSS fixes: the intro text under "Local Supply" now spans the
  full section width (removed a leftover max-width), the Categories tab
  panel now has proper spacing between its title/description and the
  "View all" button, and the 4-step process grid uses a 2-column layout on
  tablet widths instead of an uneven 3+1 wrap.
* Version bump only, to force the browser/host cache to fetch the updated
  stylesheet (the 1.3.0 CSS fixes were shipped under the same version
  number as an earlier commit, so cached copies of the file could still be
  served under that version string).

= 1.3.0 =
* Added 5 city landing pages (buy-chemical-raw-materials-{city}) with 5 new
  widgets — city quick-facts, prose, shipment route, city zones, city
  industries — plus importable templates for Shiraz, Isfahan, Mashhad,
  Tabriz and Tehran. The bestsellers section on each page uses a live
  WooCommerce products query.
* Added 7 icons (food, pill, chip, leather, textile, steel, car) used by
  the new city-industries widget.
* Fixed the main stylesheet (assets/css/baspar-elements.css), which had
  been left truncated by an interrupted push.

= 1.2.2 =
* Category page layout: on mobile, the products grid now shows before the
  sidebar (filters/widgets) instead of after it — the sidebar column used
  to stack first below the page title.

= 1.2.1 =
* Brands Strip (marquee) widget: added typography and color controls for
  the section title and the brand-name pills — the font was previously
  fixed and not customizable.

= 1.2.0 =
* All widget description/subtitle fields under a title (hero subtitles, section
  descriptions, CTA text, footer about text, etc.) are now rich-text editors so
  links (and bold/italic) can be inserted — previously plain text only.
* Section Heading widget: the description text now spans the full width of the
  section instead of being limited to roughly half.

= 1.1.9 =
* Section Heading widget: added heading tag selector (H1–H6, default H2).

= 1.1.8 =
* Single product widgets are now fully dynamic. Product Gallery reads the
  featured image + gallery, Product Info reads the title, SKU, brand,
  categories, short description and attribute spec table, and Product
  Reviews reads the rating summary and approved reviews — all from the
  current WooCommerce product, with nothing entered by hand.
* New "Product Description" widget that renders the product's long
  description dynamically.
* Header: fixed the always-open mobile menu, added a working mobile
  accordion for sub-menus (any depth), desktop dropdown carets, and made
  the CTA button customizable. TopBar is now mobile-friendly.

= 1.0.4 =
* Added the chemical-importer page: new "Importer Hero (supply network map)"
  and "Origin Countries" widgets, plus importer / blog / shop page templates.
  31 widgets and 7 importable templates total.

= 1.0.3 =
* Fix: renamed an internal method in the Categories widget that collided with
  Elementor's final `Base_Object::get_items()` and caused a fatal error.

= 1.0.2 =
* Bulletproof loading: every code path (bootstrap, widget registration, base
  class load, per-widget render) is wrapped so a single error can never
  white-screen the site; errors are written to the WordPress debug log.

= 1.0.0 =
* First release: 29 widgets + 4 page templates.
