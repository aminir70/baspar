=== Baspar Elements ===
Contributors: basparmarket
Tags: elementor, woocommerce, widgets, rtl, persian
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

مجموعه ویجت‌های اختصاصی المنتور برای سایت بسپارمارکت؛ هر بخش یک ویجت جدا، کاملاً قابل تغییر و با داده‌ی پویا از وردپرس/ووکامرس.

== Description ==

Baspar Elements adds a set of Elementor widgets that implement the Baspar Market
design system (industrial purple, Vazirmatn). Every section is a separate widget,
fully customizable from Elementor (typography, colors, logo, images, spacing), and
dynamic data (menu, posts, WooCommerce products & categories) is read live from
WordPress.

Includes 29 widgets (header/menu, footer, hero, categories, trust, products,
product tabs, promo, brands, about, blog, breadcrumb, stats, mission/vision/values,
timeline, team, process, industries, FAQ, cities, mid/final CTA, compare table,
contact methods, social showcase, contact map) plus 4 importable page templates
(home, about, contact, pillar).

Requires Elementor. WooCommerce is required only for the product/category widgets.

== Installation ==

1. Upload the `baspar-elements` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Edit any page with Elementor and find the widgets under the "بسپارمارکت" category.
4. Optionally import the page templates from the `templates/` folder via
   Templates → Saved Templates → Import Templates.

== Changelog ==

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
