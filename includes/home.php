<?php
/**
 * Shared helpers for the home page (v2) widgets: section heading block,
 * category images/counts and cached product queries.
 *
 * @package BasparElements
 */

namespace BasparElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the common "section heading" controls on a widget.
 *
 * @param \Elementor\Widget_Base $w        Widget.
 * @param array                  $defaults kicker, title, highlight, sub, link_text, link.
 */
function home_head_controls( $w, $defaults ) {
	$t = \Elementor\Controls_Manager::TEXT;
	$w->add_control( 'h_kicker', array( 'label' => __( 'برچسب لاتین بالای عنوان', 'baspar-elements' ), 'type' => $t, 'default' => $defaults['kicker'] ?? '' ) );
	$w->add_control( 'h_title', array( 'label' => __( 'عنوان بخش', 'baspar-elements' ), 'type' => $t, 'default' => $defaults['title'] ?? '' ) );
	$w->add_control( 'h_highlight', array( 'label' => __( 'بخش رنگی عنوان (ادامه عنوان)', 'baspar-elements' ), 'type' => $t, 'default' => $defaults['highlight'] ?? '' ) );
	$w->add_control( 'h_sub', array( 'label' => __( 'توضیح کوتاه', 'baspar-elements' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => $defaults['sub'] ?? '' ) );
	$w->add_control( 'h_tag', array(
		'label'   => __( 'تگ عنوان', 'baspar-elements' ),
		'type'    => \Elementor\Controls_Manager::SELECT,
		'default' => 'h2',
		'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'div' => 'div' ),
	) );
	$w->add_control( 'h_link_text', array( 'label' => __( 'لینک کنار عنوان — متن', 'baspar-elements' ), 'type' => $t, 'default' => $defaults['link_text'] ?? '' ) );
	$w->add_control( 'h_link', array( 'label' => __( 'لینک کنار عنوان — آدرس', 'baspar-elements' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => $defaults['link'] ?? '' ) ) );
}

/**
 * Render the section heading from settings registered by home_head_controls().
 *
 * @param array  $s     Settings.
 * @param string $extra Extra HTML placed at the end (e.g. tabs).
 * @param string $tone  '' or 'dark'.
 */
function render_home_head( $s, $extra = '', $tone = '' ) {
	if ( empty( $s['h_title'] ) && empty( $s['h_kicker'] ) && '' === $extra ) {
		return;
	}
	$tag = in_array( $s['h_tag'] ?? 'h2', array( 'h2', 'h3', 'div' ), true ) ? $s['h_tag'] : 'h2';
	?>
	<div class="hm-head<?php echo $tone ? ' is-' . esc_attr( $tone ) : ''; ?>" data-anim="up">
		<div class="hm-head-t">
			<?php if ( ! empty( $s['h_kicker'] ) ) : ?><span class="hm-kicker"><i aria-hidden="true"></i><span class="mono"><?php echo esc_html( $s['h_kicker'] ); ?></span></span><?php endif; ?>
			<?php if ( ! empty( $s['h_title'] ) ) : ?>
				<<?php echo $tag; // phpcs:ignore ?> class="hm-title"><?php echo esc_html( $s['h_title'] ); ?><?php if ( ! empty( $s['h_highlight'] ) ) : ?> <span class="hm-hl"><?php echo esc_html( $s['h_highlight'] ); ?></span><?php endif; ?></<?php echo $tag; // phpcs:ignore ?>>
			<?php endif; ?>
			<?php if ( ! empty( $s['h_sub'] ) ) : ?><p class="hm-sub"><?php echo esc_html( $s['h_sub'] ); ?></p><?php endif; ?>
		</div>
		<?php echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by the widget. ?>
		<?php if ( ! empty( $s['h_link_text'] ) && ! empty( $s['h_link']['url'] ) ) : ?>
			<a class="hm-more" href="<?php echo esc_url( slash_url( $s['h_link']['url'] ) ); ?>"><span><?php echo esc_html( $s['h_link_text'] ); ?></span><i aria-hidden="true"><?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?></i></a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * A product category term (object) or null.
 *
 * @param int $term_id Term id.
 * @return object|null
 */
function home_term( $term_id ) {
	$term_id = (int) $term_id;
	if ( ! $term_id || ! function_exists( 'get_term' ) ) {
		return null;
	}
	$t = get_term( $term_id, 'product_cat' );
	return ( $t && ! is_wp_error( $t ) ) ? $t : null;
}

/**
 * Thumbnail URL of a product category (WooCommerce "thumbnail_id" meta).
 *
 * @param int    $term_id Term id.
 * @param string $size    Image size.
 * @return string
 */
function home_term_image( $term_id, $size = 'medium' ) {
	$term_id = (int) $term_id;
	if ( ! $term_id ) {
		return '';
	}
	$thumb = (int) get_term_meta( $term_id, 'thumbnail_id', true );
	return $thumb ? (string) wp_get_attachment_image_url( $thumb, $size ) : '';
}

/**
 * Number of products in a category, including its sub-categories.
 *
 * @param int $term_id Term id.
 * @return int
 */
function home_term_count( $term_id ) {
	$t = home_term( $term_id );
	if ( ! $t ) {
		return 0;
	}
	// WooCommerce stores a recursive count (children included) in term meta.
	$rec = (int) get_term_meta( (int) $term_id, 'product_count_product_cat', true );
	return max( (int) $t->count, $rec );
}

/**
 * Latest (or random) published product ids of a category, cached for 6h.
 *
 * @param int    $term_id Term id (0 = all products).
 * @param int    $count   How many.
 * @param string $order   latest|random|popular.
 * @return int[]
 */
function home_product_ids( $term_id, $count, $order = 'latest' ) {
	$count = max( 1, min( 24, (int) $count ) );
	$key   = 'baspar_hp_' . (int) $term_id . '_' . $count . '_' . $order;
	$ids   = function_exists( 'get_transient' ) ? get_transient( $key ) : false;
	if ( is_array( $ids ) ) {
		return $ids;
	}
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'numberposts'    => $count,
		'fields'         => 'ids',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'suppress_filters' => false,
	);
	if ( 'random' === $order ) {
		$args['orderby'] = 'rand';
	} elseif ( 'popular' === $order ) {
		$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$args['orderby']  = 'meta_value_num';
	}
	if ( $term_id ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'product_cat', 'terms' => (int) $term_id, 'include_children' => true ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}
	$ids = array_map( 'intval', (array) get_posts( $args ) );
	if ( function_exists( 'set_transient' ) ) {
		set_transient( $key, $ids, 'random' === $order ? HOUR_IN_SECONDS : 6 * HOUR_IN_SECONDS );
	}
	return $ids;
}

/**
 * One product card for the home rails.
 *
 * @param int   $id Product id.
 * @param array $o  cta, cat (bool).
 */
function render_home_product_card( $id, $o = array() ) {
	$o     = array_merge( array( 'cta' => __( 'استعلام قیمت', 'baspar-elements' ), 'cat' => true, 'i' => 0 ), $o );
	$title = get_the_title( $id );
	$cat   = $o['cat'] ? primary_cat_name( $id ) : '';
	$thumb = get_the_post_thumbnail( $id, 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'alt' => esc_attr( $title ) ) );
	?>
	<a class="hpc" href="<?php echo esc_url( get_permalink( $id ) ); ?>" style="--i:<?php echo (int) $o['i']; ?>">
		<span class="hpc-img"><?php echo $thumb ? $thumb : icon_svg( 'package', 44 ); // phpcs:ignore ?><span class="hpc-glow" aria-hidden="true"></span></span>
		<span class="hpc-body">
			<?php if ( $cat ) : ?><span class="hpc-cat"><?php echo esc_html( $cat ); ?></span><?php endif; ?>
			<strong class="hpc-t"><?php echo esc_html( $title ); ?></strong>
			<span class="hpc-cta"><span><?php echo esc_html( $o['cta'] ); ?></span><i aria-hidden="true"><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></i></span>
		</span>
	</a>
	<?php
}
