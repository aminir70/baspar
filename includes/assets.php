<?php
/**
 * Front-end assets: load only the CSS bundles, script and webfonts the
 * current page needs, inline the critical product CSS, and preload the
 * product LCP image.
 *
 * Which Baspar widgets a page shows is read from the Elementor data of the
 * page itself and of the Elementor Pro theme-builder templates that apply to
 * it (header, footer, single, archive), following nested template / global
 * widgets. Widgets also declare their bundles to Elementor
 * (get_style_depends), so anything this scan can't see still gets its CSS.
 *
 * Kill switch: define( 'BASPAR_ELEMENTS_CSS_ALL', true ) in wp-config.php
 * loads the single full stylesheet and the script on every page, like 1.8.x.
 *
 * @package BasparElements
 */

namespace BasparElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BUNDLE_ORDER = array( 'core', 'home', 'product', 'blog', 'side', 'shop', 'pages' );

/**
 * Widget slug (without "baspar-") => bundle.
 *
 * @return array<string,string>
 */
function css_map() {
	static $map = null;
	if ( null === $map ) {
		$map = include BASPAR_ELEMENTS_PATH . 'includes/css-map.php';
		$map = is_array( $map ) ? $map : array();
	}
	return $map;
}

/**
 * Bundle of one widget slug (without "baspar-").
 *
 * @param string $slug Widget slug.
 * @return string
 */
function widget_bundle( $slug ) {
	$map = css_map();
	return isset( $map[ $slug ] ) ? $map[ $slug ] : 'pages';
}

/**
 * Use readable sources instead of the minified files (SCRIPT_DEBUG).
 *
 * @return bool
 */
function assets_debug() {
	return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
}

/**
 * Load everything everywhere (kill switch, editor, preview).
 *
 * @return bool
 */
function load_all_assets() {
	if ( defined( 'BASPAR_ELEMENTS_CSS_ALL' ) && BASPAR_ELEMENTS_CSS_ALL ) {
		return true;
	}
	if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['elementor_library'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}
	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance ) ) {
		$el = \Elementor\Plugin::$instance;
		if ( ( isset( $el->preview ) && method_exists( $el->preview, 'is_preview_mode' ) && $el->preview->is_preview_mode() )
			|| ( isset( $el->editor ) && method_exists( $el->editor, 'is_edit_mode' ) && $el->editor->is_edit_mode() ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Register every style/script handle (cheap; enqueue decides what prints).
 */
function register_assets() {
	$v    = BASPAR_ELEMENTS_VERSION;
	$base = BASPAR_ELEMENTS_ASSETS;
	$dbg  = assets_debug();
	// Full stylesheet: editor, preview, kill switch.
	wp_register_style( 'baspar-elements', $base . ( $dbg ? 'css/baspar-elements.css' : 'css/dist/all.min.css' ), array(), $v );
	foreach ( BUNDLE_ORDER as $b ) {
		$deps = 'core' === $b ? array() : array( 'baspar-css-core' );
		wp_register_style( 'baspar-css-' . $b, $base . ( $dbg ? 'css/src/' . $b . '.css' : 'css/dist/' . $b . '.min.css' ), $deps, $v );
	}
	// Webfonts: self-hosted, declared inline (no render-blocking font
	// stylesheet). A browser only downloads a file when text on the page
	// uses that font and range, e.g. JetBrains Mono only where it's shown.
	wp_register_style( 'baspar-elements-fonts', false, array(), $v );
	wp_add_inline_style( 'baspar-elements-fonts', font_face_css() );
	$script_args = array( 'in_footer' => true, 'strategy' => 'defer' );
	wp_register_script( 'baspar-elements', $base . ( $dbg ? 'js/baspar-elements.js' : 'js/dist/baspar-elements.min.js' ), array(), $v, $script_args );
}

/**
 * @font-face rules for the self-hosted fonts.
 *
 * @return string
 */
function font_face_css() {
	$rows = include BASPAR_ELEMENTS_PATH . 'includes/fonts.php';
	$base = BASPAR_ELEMENTS_ASSETS . 'fonts/';
	$css  = '';
	foreach ( (array) $rows as $r ) {
		$css .= sprintf(
			"@font-face{font-family:'%s';font-style:normal;font-weight:%s;font-display:swap;src:url(%s) format('woff2');unicode-range:%s}",
			$r[0],
			$r[2],
			esc_url( $base . $r[1] . '?ver=' . BASPAR_ELEMENTS_VERSION ),
			$r[3]
		);
	}
	return $css;
}

/**
 * Preload the Persian text font (used above the fold on every Baspar page).
 */
function preload_font() {
	if ( ! page_css_plan()['mode'] ) {
		return;
	}
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( BASPAR_ELEMENTS_ASSETS . 'fonts/vazirmatn-arabic.woff2?ver=' . BASPAR_ELEMENTS_VERSION )
	);
}

/**
 * Elementor data (raw JSON string) of a document / template.
 *
 * @param int $id Post id.
 * @return string
 */
function elementor_raw( $id ) {
	$raw = get_post_meta( (int) $id, '_elementor_data', true );
	if ( is_array( $raw ) ) {
		$raw = wp_json_encode( $raw );
	}
	return is_string( $raw ) ? $raw : '';
}

/**
 * Baspar widget slugs used by documents (with nested templates / globals).
 *
 * @param int[] $ids Document ids.
 * @return string[] Slugs without the "baspar-" prefix.
 */
function widgets_in_documents( $ids ) {
	$found = array();
	$seen  = array();
	$queue = array_map( 'intval', $ids );
	$depth = 0;
	while ( $queue && $depth < 4 ) {
		$next = array();
		foreach ( $queue as $id ) {
			if ( ! $id || isset( $seen[ $id ] ) ) {
				continue;
			}
			$seen[ $id ] = true;
			$raw         = elementor_raw( $id );
			if ( '' === $raw ) {
				continue;
			}
			if ( preg_match_all( '/"widgetType":"baspar-([a-z0-9-]+)"/', $raw, $m ) ) {
				foreach ( $m[1] as $slug ) {
					$found[ $slug ] = true;
				}
			}
			// Template widget / shortcode and global widgets.
			if ( preg_match_all( '/"(?:template_id|templateID)":"?(\d+)"?/', $raw, $t ) ) {
				foreach ( $t[1] as $tid ) {
					$next[] = (int) $tid;
				}
			}
			if ( preg_match_all( '/\[elementor-template id=\\\\?"?(\d+)/', $raw, $t ) ) {
				foreach ( $t[1] as $tid ) {
					$next[] = (int) $tid;
				}
			}
		}
		$queue = $next;
		$depth++;
	}
	return array_keys( $found );
}

/**
 * Elementor documents rendered on the current request.
 *
 * @return int[]
 */
function current_documents() {
	$ids = array();
	if ( is_singular() ) {
		$ids[] = (int) get_queried_object_id();
	} elseif ( function_exists( 'is_shop' ) && is_shop() && function_exists( 'wc_get_page_id' ) ) {
		$ids[] = (int) wc_get_page_id( 'shop' );
	} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
		$ids[] = (int) get_option( 'page_for_posts' );
	}
	// Elementor Pro theme builder.
	if ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
		try {
			$conditions = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager();
			foreach ( array( 'header', 'footer', 'single', 'archive' ) as $location ) {
				$docs = $conditions->get_documents_for_location( $location );
				if ( is_array( $docs ) ) {
					foreach ( array_keys( $docs ) as $doc_id ) {
						$ids[] = (int) $doc_id;
					}
				}
			}
		} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Fall back to the per-widget dependencies.
		}
	}
	/**
	 * Filter the Elementor document ids scanned for Baspar widgets.
	 *
	 * @param int[] $ids Document ids.
	 */
	return array_values( array_unique( array_filter( (array) apply_filters( 'baspar_elements_documents', $ids ) ) ) );
}

/**
 * Baspar widgets on the current page (cached for the request).
 *
 * @return string[]
 */
function page_widgets() {
	static $widgets = null;
	if ( null === $widgets ) {
		$widgets = widgets_in_documents( current_documents() );
		/**
		 * Filter the Baspar widget slugs (without "baspar-") detected on the page.
		 *
		 * @param string[] $widgets Slugs.
		 */
		$widgets = (array) apply_filters( 'baspar_elements_page_widgets', $widgets );
	}
	return $widgets;
}

/**
 * Bundles the current page needs, in load order.
 *
 * @return string[]
 */
function page_bundles() {
	$widgets = page_widgets();
	if ( ! $widgets ) {
		return array();
	}
	$need = array( 'core' => true );
	foreach ( $widgets as $w ) {
		$need[ widget_bundle( $w ) ] = true;
	}
	return array_values( array_filter( BUNDLE_ORDER, static function ( $b ) use ( $need ) {
		return isset( $need[ $b ] );
	} ) );
}

/**
 * One stylesheet for a set of bundles, built from the ordered parts so every
 * rule keeps its original place in the cascade. Written once per plugin
 * version and bundle set to uploads/baspar-elements/ (a few KB each).
 *
 * @param string[] $bundles Bundles in load order.
 * @return string URL, or '' when the file can't be written.
 */
function combined_css_url( $bundles ) {
	$up = wp_upload_dir( null, false );
	if ( ! empty( $up['error'] ) || empty( $up['basedir'] ) ) {
		return '';
	}
	$dir  = trailingslashit( $up['basedir'] ) . 'baspar-elements';
	$name = 'page-' . sanitize_file_name( BASPAR_ELEMENTS_VERSION ) . '-' . substr( md5( implode( ',', $bundles ) ), 0, 10 ) . '.css';
	$path = $dir . '/' . $name;
	$url  = trailingslashit( $up['baseurl'] ) . 'baspar-elements/' . $name;
	if ( is_readable( $path ) && filesize( $path ) > 0 ) {
		return $url;
	}
	$src = BASPAR_ELEMENTS_PATH . 'assets/css/dist/parts.json';
	$parts = is_readable( $src ) ? json_decode( (string) file_get_contents( $src ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $parts ) ) {
		return '';
	}
	$want = array_flip( $bundles );
	$css  = '';
	foreach ( $parts as $part ) {
		if ( isset( $want[ $part[0] ] ) ) {
			$css .= $part[1];
		}
	}
	if ( '' === $css || ! wp_mkdir_p( $dir ) ) {
		return '';
	}
	// Remove files of older plugin versions.
	foreach ( (array) glob( $dir . '/page-*.css' ) as $old ) {
		if ( $old && false === strpos( basename( $old ), 'page-' . sanitize_file_name( BASPAR_ELEMENTS_VERSION ) . '-' ) ) {
			wp_delete_file( $old );
		}
	}
	$tmp = $path . '.' . wp_rand() . '.tmp';
	$css = '/* Baspar Elements ' . BASPAR_ELEMENTS_VERSION . ': ' . implode( '+', $bundles ) . " */\n" . $css . "\n";
	if ( false === file_put_contents( $tmp, $css ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		return '';
	}
	if ( ! @rename( $tmp, $path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.rename_rename
		wp_delete_file( $tmp );
		return is_readable( $path ) ? $url : '';
	}
	return $url;
}

/**
 * The page's CSS plan, decided once per request (after the main query):
 *   mode    'all'   full stylesheet (editor, preview, kill switch)
 *           'page'  one combined file (normal)
 *           'split' separate bundle files (uploads not writable / SCRIPT_DEBUG)
 *           ''      no Baspar widget found (widgets' own dependencies apply)
 *   bundles bundles in the page file, url its address.
 *
 * Anyone may ask (Elementor reads widget dependencies before our enqueue
 * runs); the answer is the same every time.
 *
 * @return array{mode:string,bundles:string[],url:string}
 */
function page_css_plan() {
	static $plan = null;
	$none = array( 'mode' => '', 'bundles' => array(), 'url' => '' );
	if ( null !== $plan ) {
		return $plan;
	}
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $none;
	}
	if ( load_all_assets() ) {
		$plan = array( 'mode' => 'all', 'bundles' => array(), 'url' => '' );
		return $plan;
	}
	if ( ! did_action( 'wp' ) ) {
		return $none; // Query not ready yet: decide later, don't cache.
	}
	$bundles = page_bundles();
	if ( ! $bundles ) {
		$plan = $none;
		return $plan;
	}
	$url  = assets_debug() ? '' : combined_css_url( $bundles );
	$plan = array( 'mode' => $url ? 'page' : 'split', 'bundles' => $bundles, 'url' => $url );
	return $plan;
}

/**
 * Last word on what prints (head and footer): whatever enqueued Baspar
 * handles — widget dependencies, Elementor's saved per-page asset lists
 * (`_elementor_page_assets`, which may still hold 1.8.x handles), Elementor
 * Pro's theme templates — only the planned stylesheet remains.
 *
 * @param string[] $todo Handles about to print.
 * @return string[]
 */
function filter_print_styles( $todo ) {
	if ( ! is_array( $todo ) || ( is_admin() && ! wp_doing_ajax() ) ) {
		return $todo;
	}
	$plan = page_css_plan();
	$mode = $plan['mode'];
	if ( '' === $mode ) {
		return $todo; // Nothing planned: keep what widgets asked for.
	}
	$out = array();
	foreach ( $todo as $h ) {
		if ( 'baspar-elements' === $h && 'all' !== $mode ) {
			continue;
		}
		if ( 0 === strpos( (string) $h, 'baspar-css-' ) && 'baspar-css-page' !== $h ) {
			$b = substr( $h, 11 );
			if ( 'all' === $mode ) {
				continue;
			}
			if ( 'page' === $mode && ( 'core' === $b || in_array( $b, $plan['bundles'], true ) ) ) {
				continue;
			}
		}
		$out[] = $h;
	}
	return $out;
}

/**
 * Elementor saves each document's style dependencies once
 * (`_elementor_page_assets`) and reuses them. Lists saved by older versions
 * name handles that no longer fit (1.8.x: the full stylesheet); clear them
 * once after an update so Elementor saves fresh ones.
 */
function maybe_reset_elementor_assets() {
	if ( get_option( 'baspar_elements_assets_ver' ) === BASPAR_ELEMENTS_VERSION ) {
		return;
	}
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_page_assets' AND meta_value LIKE '%baspar-%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	wp_cache_flush_group( 'post_meta' );
	update_option( 'baspar_elements_assets_ver', BASPAR_ELEMENTS_VERSION, true );
}

/**
 * Enqueue what the page needs (wp_enqueue_scripts).
 */
function enqueue_page_assets() {
	if ( is_admin() ) {
		return;
	}
	$plan = page_css_plan();
	if ( 'all' === $plan['mode'] ) {
		wp_enqueue_style( 'baspar-elements' );
		wp_enqueue_style( 'baspar-elements-fonts' );
		wp_enqueue_script( 'baspar-elements' );
		return;
	}
	if ( ! $plan['mode'] ) {
		return; // No Baspar widget here: no CSS, JS or webfont.
	}
	if ( is_product_page_with_hero() && apply_filters( 'baspar_elements_critical_css', true ) ) {
		$crit = critical_css();
		if ( '' !== $crit ) {
			wp_register_style( 'baspar-critical', false, array(), BASPAR_ELEMENTS_VERSION );
			wp_enqueue_style( 'baspar-critical' );
			wp_add_inline_style( 'baspar-critical', $crit );
		}
	}
	if ( 'page' === $plan['mode'] ) {
		wp_enqueue_style( 'baspar-css-page', $plan['url'], array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- version is in the file name.
	} else {
		foreach ( $plan['bundles'] as $b ) {
			wp_enqueue_style( 'baspar-css-' . $b );
		}
	}
	wp_enqueue_style( 'baspar-elements-fonts' );
	wp_enqueue_script( 'baspar-elements' );
}

/**
 * Product page that shows the Baspar product hero (gallery + title).
 *
 * @return bool
 */
function is_product_page_with_hero() {
	return function_exists( 'is_product' ) && is_product() && in_array( 'pd-hero', page_widgets(), true );
}

/**
 * Critical CSS for the product page (layout of the header box, breadcrumbs,
 * gallery and title — the same values as the product bundle).
 *
 * @return string
 */
function critical_css() {
	$file = BASPAR_ELEMENTS_PATH . 'assets/css/dist/critical-product.min.css';
	if ( ! is_readable( $file ) ) {
		return '';
	}
	$css = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	return trim( str_replace( '</', '<\/', $css ) );
}

/**
 * Preload the first product image (the LCP element) on product pages.
 */
function preload_product_image() {
	if ( load_all_assets() || ! is_product_page_with_hero() ) {
		return;
	}
	$product = current_product();
	if ( ! $product ) {
		return;
	}
	$id = (int) $product->get_image_id();
	if ( ! $id ) {
		$gal = $product->get_gallery_image_ids();
		$id  = $gal ? (int) $gal[0] : 0;
	}
	$img = $id ? webp_image_set( $id ) : null;
	if ( ! $img ) {
		return;
	}
	printf(
		'<link rel="preload" as="image" href="%1$s" imagesrcset="%2$s" imagesizes="%3$s" type="image/webp" fetchpriority="high">' . "\n",
		esc_url( $img['src'] ),
		esc_attr( $img['srcset'] ),
		esc_attr( gallery_sizes() )
	);
}

/**
 * Widget style handles (used by Baspar_Widget_Base::get_style_depends()).
 *
 * @param string $slug Widget slug without "baspar-".
 * @return string[]
 */
function widget_style_handles( $slug ) {
	// Always the same answer for a widget (Elementor saves it per page);
	// filter_print_styles() drops what the page stylesheet already holds.
	if ( 'all' === page_css_plan()['mode'] ) {
		return array( 'baspar-elements', 'baspar-elements-fonts' );
	}
	return array( 'baspar-css-' . widget_bundle( $slug ), 'baspar-elements-fonts' );
}

/**
 * WP Rocket "Remove unused CSS": selectors that only match after the script
 * runs (open menus, active tabs, scroll-in animations) must be kept.
 *
 * @param array $list Safelist.
 * @return array
 */
function rocket_rucss_safelist( $list ) {
	$keep = array(
		'.baspar-js', '.baspar-motion', '.bspr-sticky-host', '.bspr-sticky-col', '.baspar-lightbox',
		'.is-open', '.is-on', '.is-in', '.in', '.is-active', '.is-stuck', '.is-ready', '.is-enter',
		'.is-copied', '.is-static', '.at-start', '.at-end', '.spr-extra', '.is-current',
		'.mmd-root(.*)', '.mmd(.*)', '.mm-(.*)', '.prl-(.*)', '.h2v-(.*)',
	);
	return array_values( array_unique( array_merge( (array) $list, $keep ) ) );
}
