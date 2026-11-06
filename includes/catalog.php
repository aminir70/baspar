<?php
/**
 * Product lists without numbered pages (1.11.0).
 *
 * The shop and every product category is one URL: the products widget shows
 * the first batch and a «مشاهده محصولات بیشتر» button loads the rest from the
 * REST route below. Old /page/N/ addresses redirect (301) to the first page
 * and the rel="next"/"prev" links of SEO plugins are removed, so search
 * engines see a single page per category.
 *
 * @package BasparElements
 */

namespace BasparElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether product archives are kept on a single URL.
 * Turn off with add_filter( 'baspar_elements_single_page_archives', '__return_false' ).
 *
 * @return bool
 */
function single_page_archives() {
	return (bool) apply_filters( 'baspar_elements_single_page_archives', true );
}

/**
 * Shop / product category / product tag / product attribute archive.
 *
 * @return bool
 */
function is_product_archive() {
	return ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() );
}

// /product-category/x/page/3/  ->  /product-category/x/  (query string kept).
add_action(
	'template_redirect',
	static function () {
		if ( is_admin() || wp_doing_ajax() || ! single_page_archives() || ! is_product_archive() ) {
			return;
		}
		if ( (int) get_query_var( 'paged' ) < 2 ) {
			return;
		}
		$url = remove_query_arg( array( 'paged', 'product-page' ), get_pagenum_link( 1, false ) );
		wp_safe_redirect( $url, 301, 'Baspar Elements' );
		exit;
	},
	1
);

// No rel="next" / rel="prev" on product archives (Rank Math, Yoast).
add_filter(
	'rank_math/frontend/disable_adjacent_rel_links',
	static function ( $off ) {
		return ( single_page_archives() && is_product_archive() ) ? true : $off;
	}
);
foreach ( array( 'wpseo_next_rel_link', 'wpseo_prev_rel_link' ) as $baspar_hook ) {
	add_filter(
		$baspar_hook,
		static function ( $link ) {
			return ( single_page_archives() && is_product_archive() ) ? '' : $link;
		}
	);
}
unset( $baspar_hook );

/**
 * One product card (the same markup the products widget prints).
 *
 * @param int   $id Product ID.
 * @param array $o  show_cat (bool), foot (string), class (extra classes), delay (ms).
 * @return string
 */
function product_card_html( $id, $o = array() ) {
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;
	if ( ! $product ) {
		return '';
	}
	$o        = array_merge( array( 'show_cat' => true, 'foot' => 'استعلام قیمت', 'class' => '', 'delay' => 0 ), $o );
	$cat_name = $o['show_cat'] ? primary_cat_name( $id ) : '';
	$sku      = $product->get_sku();
	$thumb    = get_the_post_thumbnail( $id, 'medium', array( 'loading' => 'lazy' ) );
	ob_start();
	?>
	<a href="<?php echo esc_url( get_permalink( $id ) ); ?>" class="pcard <?php echo esc_attr( $o['class'] ); ?>"<?php echo $o['delay'] ? ' data-reveal-delay="' . (int) $o['delay'] . '"' : ''; ?>>
		<div class="pcard-img <?php echo $thumb ? 'has-image' : ''; ?>">
			<?php if ( $sku ) : ?><span class="pcard-tag"><?php echo esc_html( $sku ); ?></span><?php endif; ?>
			<?php echo $thumb ? $thumb : icon_svg( 'package', 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<div class="pcard-body">
			<?php if ( $cat_name ) : ?><span class="pcard-cat"><?php echo esc_html( $cat_name ); ?></span><?php endif; ?>
			<div class="pcard-name"><?php echo esc_html( get_the_title( $id ) ); ?></div>
		</div>
		<div class="pcard-foot">
			<span><?php echo esc_html( $o['foot'] ); ?></span>
			<?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</a>
	<?php
	return (string) ob_get_clean();
}

/**
 * Query args the "load more" button may repeat: the widget's first-page
 * query without paging keys and empty values.
 *
 * @param array $args WP_Query args.
 * @return array
 */
function more_query_args( $args ) {
	$drop = array( 'paged', 'page', 'posts_per_page', 'nopaging', 'offset', 'fields', 'no_found_rows', 'suppress_filters', 'cache_results', 'update_post_term_cache', 'update_post_meta_cache', 'lazy_load_term_meta', 'comments_per_page', 'error', 'm', 'p', 'page_id', 'pagename', 'name', 'attachment', 'attachment_id', 'subpost', 'subpost_id', 'preview', 'static', 'feed', 'tb', 'embed', 'search_terms', 'search_terms_count', 'search_orderby_title' );
	$out  = array();
	foreach ( (array) $args as $k => $v ) {
		if ( in_array( $k, $drop, true ) || ( '' === $v || array() === $v || null === $v || false === $v || 0 === $v ) ) {
			continue;
		}
		$out[ $k ] = $v;
	}
	return $out;
}

/**
 * Signed, compact token holding the query args (so the endpoint cannot be
 * asked for anything but the list the page itself shows).
 *
 * @param array $args Args from more_query_args().
 * @return string
 */
function more_token( $args ) {
	$data = base64_encode( (string) wp_json_encode( $args ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	return $data . '.' . substr( wp_hash( 'baspar-more|' . $data, 'nonce' ), 0, 20 );
}

/**
 * Read a token back; null when it was altered.
 *
 * @param string $token Token.
 * @return array|null
 */
function more_token_args( $token ) {
	$parts = explode( '.', (string) $token );
	if ( 2 !== count( $parts ) || ! hash_equals( substr( wp_hash( 'baspar-more|' . $parts[0], 'nonce' ), 0, 20 ), $parts[1] ) ) {
		return null;
	}
	$args = json_decode( (string) base64_decode( $parts[0] ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	return is_array( $args ) ? $args : null;
}

/**
 * Products with the same sort value (same date, same sales…) keep one fixed
 * order, so a batch never repeats or skips a product. Queries opt in with
 * 'baspar_stable' => true.
 */
add_filter(
	'posts_orderby',
	static function ( $orderby, $query ) {
		if ( ! $query->get( 'baspar_stable' ) ) {
			return $orderby;
		}
		global $wpdb;
		$id = "{$wpdb->posts}.ID";
		if ( '' === trim( (string) $orderby ) ) {
			return "{$id} DESC";
		}
		return false === stripos( $orderby, 'RAND(' ) && false === strpos( $orderby, $id ) ? $orderby . ", {$id} DESC" : $orderby;
	},
	10,
	2
);

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'baspar/v1',
			'/products',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'args'                => array(
					'q'      => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'offset' => array( 'sanitize_callback' => 'absint' ),
					'limit'  => array( 'sanitize_callback' => 'absint' ),
					'cat'    => array( 'sanitize_callback' => 'absint' ),
					'foot'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
				),
				'callback'            => static function ( $req ) {
					$args = more_token_args( $req['q'] );
					if ( null === $args || ! function_exists( 'wc_get_product' ) ) {
						return new \WP_Error( 'baspar_bad_query', 'Invalid list.', array( 'status' => 400 ) );
					}
					$limit                  = min( 200, max( 1, (int) $req['limit'] ) );
					$args['post_type']      = 'product';
					$args['post_status']    = 'publish';
					$args['posts_per_page'] = $limit;
					$args['offset']         = (int) $req['offset'];
					$args['fields']         = 'ids';
					$args['has_password']   = false;
					$args['baspar_stable']  = true;
					$q    = new \WP_Query( $args );
					$o    = array(
						'show_cat' => (bool) $req['cat'],
						'foot'     => mb_substr( (string) $req['foot'], 0, 40 ),
						'class'    => 'is-new',
					);
					$html = '';
					foreach ( $q->posts as $id ) {
						$html .= product_card_html( (int) $id, $o );
					}
					$res = rest_ensure_response(
						array(
							'html'  => $html,
							'count' => count( $q->posts ),
							'total' => (int) $q->found_posts,
						)
					);
					$res->header( 'Cache-Control', 'public, max-age=600' );
					return $res;
				},
			)
		);
	}
);
