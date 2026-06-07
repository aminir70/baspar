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

		// Frontend + editor assets.
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_styles' ) );

		// Load Google Fonts (Vazirmatn + JetBrains Mono).
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_fonts' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_fonts' ) );

		// Helpers shared by widgets.
		require_once BASPAR_ELEMENTS_PATH . 'includes/helpers.php';
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
	 * Enqueue the design stylesheet.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'baspar-elements',
			BASPAR_ELEMENTS_ASSETS . 'css/baspar-elements.css',
			array(),
			BASPAR_ELEMENTS_VERSION
		);
	}

	/**
	 * Register the interaction script (reveal, tabs, faq, counters, float wa).
	 */
	public function register_scripts() {
		wp_register_script(
			'baspar-elements',
			BASPAR_ELEMENTS_ASSETS . 'js/baspar-elements.js',
			array(),
			BASPAR_ELEMENTS_VERSION,
			true
		);
	}

	/**
	 * Enqueue interaction script on the frontend.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'baspar-elements' );
	}

	/**
	 * Enqueue the webfonts used by the design.
	 */
	public function enqueue_fonts() {
		wp_enqueue_style(
			'baspar-elements-fonts',
			'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap',
			array(),
			BASPAR_ELEMENTS_VERSION
		);
	}
}
