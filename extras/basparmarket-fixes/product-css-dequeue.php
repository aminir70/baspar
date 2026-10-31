<?php
/**
 * Basparmarket Fixes — product page: drop stylesheets the product page
 * doesn't use, to cut render-blocking CSS requests on mobile.
 *
 * Measured on /product/dop/ (rules used / rules in file):
 *   mpp_gutenberg (profile picture blocks)        0 / 188
 *   hello-elementor-header-footer (Hello theme)   0 / 75   (site uses Elementor Pro header/footer)
 *   elementor-gf-local-roboto / -robotoslab       only an extra request, no font is downloaded
 *   yith-wcbr (brands)                            0 / 9
 *   RMFA (Rank Math FAQ accordion)                only when the product has no Rank Math FAQ block
 *   woo-variation-swatches (+ its inline CSS)     only when the product isn't variable
 *
 * Applies only to single product pages for visitors: never in wp-admin, the
 * Elementor editor/preview, the Customizer, or for a logged-in user who sees
 * the admin bar.
 *
 * Turn off:  define( 'BASPARMARKET_FIXES_DEQUEUE', false );  in wp-config.php
 *       or:  update_option( 'basparmarket_fixes_dequeue', 'no' );
 * Change the list:
 *   add_filter( 'basparmarket_fixes_dequeue_styles', function ( $handles, $product ) {
 *       return array_diff( $handles, array( 'yith-wcbr' ) ); // keep one
 *   }, 10, 2 );
 *
 * @package BasparmarketFixes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'basparmarket_fixes_dequeue_applies' ) ) {

	/**
	 * Should the product-page dequeue run on this request?
	 *
	 * @return bool
	 */
	function basparmarket_fixes_dequeue_applies() {
		static $applies = null;
		if ( null !== $applies ) {
			return $applies;
		}
		$applies = false;
		if ( defined( 'BASPARMARKET_FIXES_DEQUEUE' ) && ! BASPARMARKET_FIXES_DEQUEUE ) {
			return false;
		}
		if ( 'no' === get_option( 'basparmarket_fixes_dequeue', 'yes' ) ) {
			return false;
		}
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}
		if ( ! did_action( 'wp' ) || ! function_exists( 'is_product' ) || ! is_product() ) {
			return false;
		}
		if ( is_user_logged_in() && is_admin_bar_showing() ) {
			return false;
		}
		if ( is_customize_preview() || is_preview() ) {
			return false;
		}
		// Elementor editor / preview iframe.
		if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['elementor_library'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance ) ) {
			$el = \Elementor\Plugin::$instance;
			if ( ( isset( $el->preview ) && method_exists( $el->preview, 'is_preview_mode' ) && $el->preview->is_preview_mode() )
				|| ( isset( $el->editor ) && method_exists( $el->editor, 'is_edit_mode' ) && $el->editor->is_edit_mode() ) ) {
				return false;
			}
		}
		$applies = true;
		return true;
	}

	/**
	 * Does the product's content use a Rank Math FAQ block?
	 *
	 * @param int $post_id Product id.
	 * @return bool
	 */
	function basparmarket_fixes_has_rank_math_faq( $post_id ) {
		$content = (string) get_post_field( 'post_content', $post_id );
		if ( function_exists( 'has_block' ) && has_block( 'rank-math/faq-block', $content ) ) {
			return true;
		}
		$haystack = $content . ' ' . (string) get_post_field( 'post_excerpt', $post_id );
		$data     = get_post_meta( $post_id, '_elementor_data', true );
		if ( is_string( $data ) ) {
			$haystack .= ' ' . $data;
		}
		return (bool) preg_match( '/rank-math-faq|rank-math\/faq-block|rank_math_faq|wp-block-rank-math-faq/i', $haystack );
	}

	/**
	 * Handles to drop on this product page.
	 *
	 * @return string[]
	 */
	function basparmarket_fixes_dequeue_handles() {
		static $handles = null;
		if ( null !== $handles ) {
			return $handles;
		}
		$id      = (int) get_queried_object_id();
		$product = ( $id && function_exists( 'wc_get_product' ) ) ? wc_get_product( $id ) : null;
		$list    = array(
			'mpp_gutenberg',
			'hello-elementor-header-footer',
			'elementor-gf-local-roboto',
			'elementor-gf-local-robotoslab',
			'yith-wcbr',
		);
		if ( $id && ! basparmarket_fixes_has_rank_math_faq( $id ) ) {
			$list[] = 'RMFA';
		}
		if ( $product && ! $product->is_type( 'variable' ) ) {
			$list[] = 'woo-variation-swatches'; // Its inline CSS goes with it.
		}
		/**
		 * Filter the stylesheet handles removed on single product pages.
		 *
		 * @param string[]         $list    Handles.
		 * @param WC_Product|null  $product Current product.
		 */
		$handles = array_values( array_unique( array_filter( (array) apply_filters( 'basparmarket_fixes_dequeue_styles', $list, $product ), 'is_string' ) ) );
		return $handles;
	}

	/**
	 * Dequeue the handles (runs late on several hooks, because Elementor and
	 * some plugins enqueue late).
	 */
	function basparmarket_fixes_product_dequeue_styles() {
		if ( ! basparmarket_fixes_dequeue_applies() ) {
			return;
		}
		foreach ( basparmarket_fixes_dequeue_handles() as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) || wp_style_is( $handle, 'enqueued' ) ) {
				wp_dequeue_style( $handle );
			}
		}
	}
	add_action( 'wp_enqueue_scripts', 'basparmarket_fixes_product_dequeue_styles', 9999 );
	add_action( 'wp_print_styles', 'basparmarket_fixes_product_dequeue_styles', 9999 );
	add_action( 'wp_print_footer_scripts', 'basparmarket_fixes_product_dequeue_styles', 1 );

	/**
	 * Last safety net: a dequeued handle can come back as another style's
	 * dependency; drop it from the print list too.
	 *
	 * @param string[] $todo Handles about to print.
	 * @return string[]
	 */
	function basparmarket_fixes_filter_print_styles( $todo ) {
		if ( ! is_array( $todo ) || ! basparmarket_fixes_dequeue_applies() ) {
			return $todo;
		}
		return array_values( array_diff( $todo, basparmarket_fixes_dequeue_handles() ) );
	}
	add_filter( 'print_styles_array', 'basparmarket_fixes_filter_print_styles', 9999 );
}
