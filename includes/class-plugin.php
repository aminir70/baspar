<?php
/**
 * Main plugin bootstrap: registers category, widgets, assets.
 *
 * @package BasparElements
 */

namespace BasparElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * List of widget class file slugs (without `class-baspar-` prefix / `.php`).
	 *
	 * The order here is the order widgets appear in the Elementor panel.
	 *
	 * @var string[]
	 */
	private $widgets = array(
		// Shared chrome.
		'topbar',
		'header',
		'footer',
		'float-whatsapp',
		// Home / generic sections.
		'hero',
		'categories',
		'trust',
		'products',
		'product-tabs',
		'promo',
		'brands',
		'about-box',
		'blog-posts',
		'featured-line',
		// Pillar page sections.
		'hero-pillar',
		'brands-strip',
		'categories-tabs',
		'buying-guide',
		// Importer (chemical-importer) page sections.
		'importer-hero',
		'origin-countries',
		'cred-bar',
		'why-direct',
		'product-cats-codes',
		'b2b-services',
		'import-process',
		'credentials',
		'industries-text',
		'seller-compare',
		// Inner-page sections (about / contact / pillar).
		'breadcrumb',
		'section-heading',
		'stats',
		'mvv',
		'timeline',
		'team',
		'process',
		'industries',
		'faq',
		'cities',
		'mid-cta',
		'final-cta',
		'compare-table',
		'contact-methods',
		'social-showcase',
		'contact-map',
		'about-intro',
		'principles',
		// Contact page extras.
		'addr-cards',
		'phone-stack',
		'hours-banner',
		'contact-form',
		// Product page (single product).
		'product-gallery',
		'product-info',
		'product-description',
		'product-reviews',
		// Blog / single post.
		'blog-sidebar',
		'post-header',
		'post-share',
		'author-box',
		// Shop / category page.
		'shop-sidebar',
		'shop-toolbar',
		'term-description',
		// Blog landing page + single post (1.5.0).
		'blog-hero',
		'post-featured',
		'post-grid',
		'blog-topics',
		'post-hero',
		'post-content',
		'post-toc',
		'related-posts',
		'post-comments',
		'side-cta',
		'side-posts',
		'side-cats',
		'side-products',
		'side-banner',
		'side-links',
		// Product page (1.6.0).
		'pd-hero',
		'pd-anchors',
		'pd-features',
		'pd-specs',
		'pd-description',
		'pd-reviews',
		'pd-related',
		'pd-contact',
		'pd-bottom-text',
		// Home page v2 (1.8.0) — Hello Elementor ready, motion-rich.
		'hero-v2',
		'cat-tiles',
		'image-banners',
		'cta-strip',
		'product-rail',
		'about-v2',
		'home-blog',
		// City landing pages (buy-chemical-raw-materials-{city}).
		'city-facts',
		'prose',
		'shipment-route',
		'city-zones',
		'city-industries',
	);

	/**
	 * Get the singleton.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor: wire up hooks.
	 */
	private function __construct() {
		// i18n.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Register a dedicated widget category.
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );

		// Register widgets.
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );

		// Helpers shared by widgets.
		require_once BASPAR_ELEMENTS_PATH . 'includes/helpers.php';
		require_once BASPAR_ELEMENTS_PATH . 'includes/blog.php';
		require_once BASPAR_ELEMENTS_PATH . 'includes/product.php';
		require_once BASPAR_ELEMENTS_PATH . 'includes/home.php';
		require_once BASPAR_ELEMENTS_PATH . 'includes/assets.php';
		require_once BASPAR_ELEMENTS_PATH . 'includes/images.php';

		// Assets (1.9.0): only the CSS bundles / script / webfont a page uses
		// (see includes/assets.php). Handles are registered early so widget
		// dependencies always resolve.
		add_action( 'init', '\BasparElements\register_assets', 20 );
		add_action( 'wp_enqueue_scripts', '\BasparElements\enqueue_page_assets', 20 );
		add_action( 'wp_head', '\BasparElements\preload_product_image', 2 );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );

		// Responsive WebP copies of product images.
		add_action( 'save_post_product', '\BasparElements\webp_on_product_save', 30 );
		add_action( 'delete_attachment', '\BasparElements\webp_on_delete' );

		// WP Rocket: keep our CSS/JS out of minify/combine and "delay JS".
		// Rocket's minified copies are only rebuilt when its cache is cleared,
		// so after a plugin update the site kept serving the old script (the
		// mega menu and mobile drawer had no JavaScript). Unminified, the
		// files load with ?ver=<plugin version> and refresh on every update;
		// not delayed, the menu works on the first tap.
		add_filter( 'rocket_exclude_js', array( $this, 'rocket_exclude_assets' ) );
		add_filter( 'rocket_exclude_css', array( $this, 'rocket_exclude_assets' ) );
		add_filter( 'rocket_delay_js_exclusions', array( $this, 'rocket_exclude_delay' ) );
		// WP Rocket "Remove unused CSS": keep the script-driven states.
		add_filter( 'rocket_rucss_safelist', '\BasparElements\rocket_rucss_safelist' );
	}

	/**
	 * WP Rocket minify/combine exclusions (path patterns).
	 *
	 * @param array $list Patterns.
	 * @return array
	 */
	public function rocket_exclude_assets( $list ) {
		$list   = (array) $list;
		$list[] = '/wp-content/plugins/baspar-elements/assets/(.*)';
		return $list;
	}

	/**
	 * WP Rocket delay-JS exclusions.
	 *
	 * @param array $list Patterns.
	 * @return array
	 */
	public function rocket_exclude_delay( $list ) {
		$list   = (array) $list;
		$list[] = 'baspar-elements/assets/js/';
		return $list;
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'baspar-elements', false, dirname( plugin_basename( BASPAR_ELEMENTS_FILE ) ) . '/languages' );
	}

	/**
	 * Register the "بسپارمارکت" widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'baspar',
			array(
				'title' => __( 'بسپارمارکت', 'baspar-elements' ),
				'icon'  => 'eicon-flash',
			)
		);
	}

	/**
	 * Register every widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		// Base abstract widget shared by all. Wrapped so a problem loading it
		// can never take down an Elementor-rendered page.
		try {
			require_once BASPAR_ELEMENTS_PATH . 'widgets/class-baspar-widget-base.php';
		} catch ( \Throwable $e ) {
			if ( function_exists( 'baspar_elements_log_fatal' ) ) {
				baspar_elements_log_fatal( $e );
			}
			return;
		}

		foreach ( $this->widgets as $slug ) {
			$file = BASPAR_ELEMENTS_PATH . 'widgets/class-baspar-' . $slug . '.php';
			if ( ! file_exists( $file ) ) {
				continue;
			}

			// Isolate each widget: a parse error or exception in one widget
			// (catchable as ParseError/Error since PHP 7) must not break the
			// editor or take down the site — log it and skip that widget.
			try {
				require_once $file;

				// Build class name: e.g. "float-whatsapp" -> "Baspar_Float_Whatsapp".
				$class = '\\BasparElements\\Widgets\\Baspar_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $slug ) ) );
				if ( class_exists( $class ) ) {
					$widgets_manager->register( new $class() );
				}
			} catch ( \Throwable $e ) {
				if ( function_exists( 'baspar_elements_log_fatal' ) ) {
					baspar_elements_log_fatal( $e );
				}
			}
		}
	}

	/**
	 * Elementor editor panel: the full stylesheet and webfonts.
	 */
	public function enqueue_editor_styles() {
		\BasparElements\register_assets();
		wp_enqueue_style( 'baspar-elements' );
		wp_enqueue_style( 'baspar-elements-fonts' );
	}
}
