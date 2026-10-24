=== Baspar Elements ===
Contributors: basparmarket
Tags: elementor, woocommerce, widgets, rtl, persian
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.4.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

مجموعه ویجت‌های اختصاصی المنتور برای سایت بسپارمارکت؛ هر بخش یک ویجت جدا، کاملاً قابل تغییر و با داده‌ی پویا از وردپرس/ووکامرس.

== Description ==

Baspar Elements adds a set of Elementor widgets that implement the Baspar Market
design system (industrial purple, Vazirmatn). Every section is a separate widget,
fully customizable from Elementor (typography, colors, logo, images, spacing), and
dynamic data (menu, posts, WooCommerce products & categories) is read live from
WordPress.

Includes 66 widgets (header/menu, footer, hero, categories, trust, products,
product tabs, promo, brands, about, blog, breadcrumb, stats, mission/vision/values,
timeline, team, process, industries, FAQ, cities, mid/final CTA, compare table,
contact methods, social showcase, contact map, city quick-facts, shipment route,
city zones, city industries, and more) plus 13 importable page templates (home,
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
