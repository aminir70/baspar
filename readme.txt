=== Baspar Elements ===
Contributors: basparmarket
Tags: elementor, woocommerce, widgets, rtl, persian
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

مجموعه ویجت‌های اختصاصی المنتور برای سایت بسپارمارکت؛ هر بخش یک ویجت جدا، کاملاً قابل تغییر و با داده‌ی پویا از وردپرس/ووکامرس.

== Description ==

Baspar Elements adds a set of Elementor widgets that implement the Baspar Market
design system (industrial purple, Vazirmatn). Every section is a separate widget,
fully customizable from Elementor (typography, colors, logo, images, spacing), and
dynamic data (menu, posts, WooCommerce products & categories) is read live from
WordPress.

Includes 65 widgets (header/menu, footer, hero, categories, trust, products,
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
