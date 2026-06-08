<?php
/**
 * Shared helper functions for Baspar Elements widgets.
 *
 * @package BasparElements
 */

namespace BasparElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the built-in line SVG icon set used across the design.
 *
 * Keeping the icons here (instead of relying on the Elementor icon library only)
 * lets dynamic widgets render the exact same line-icons as the original mockup.
 * Widgets that expose an icon picker still use Elementor's Icons_Manager; this
 * is the fallback / default set.
 *
 * @param string $name  Icon key.
 * @param int    $size  Width/height in px.
 * @return string SVG markup (already escaped/safe — static strings).
 */
function icon_svg( $name, $size = 24 ) {
	$s = (int) $size;
	$icons = array(
		'whatsapp'  => '<svg viewBox="0 0 24 24" fill="currentColor" width="%1$d" height="%1$d"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>',
		'phone'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.37 1.9.72 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.35 1.85.59 2.81.72A2 2 0 0 1 22 16.92z"/></svg>',
		'mail'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>',
		'pin'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M12 22s8-7.5 8-13a8 8 0 1 0-16 0c0 5.5 8 13 8 13z"/><circle cx="12" cy="9" r="3"/></svg>',
		'clock'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".75" fill="currentColor"/></svg>',
		'telegram'  => '<svg viewBox="0 0 24 24" fill="currentColor" width="%1$d" height="%1$d"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/></svg>',
		'arrow'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M14 6l-6 6 6 6"/></svg>',
		'arrow-down'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M6 10l6 6 6-6"/></svg>',
		'check'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M20 6L9 17l-5-5"/></svg>',
		'plus'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M12 5v14M5 12h14"/></svg>',
		'cart'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><circle cx="9" cy="21" r="1.5"/><circle cx="18" cy="21" r="1.5"/><path d="M3 3h2l3 13h12l2-8H7"/></svg>',
		'truck'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M2 17V6a1 1 0 0 1 1-1h12v12"/><path d="M15 9h4l3 4v4h-3"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/><path d="M9 18h6"/></svg>',
		'shield'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/></svg>',
		'chat'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M21 12a8 8 0 1 1-3.2-6.4L21 4l-1 3.5A8 8 0 0 1 21 12z"/></svg>',
		'refresh'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg>',
		'layers'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M12 3L2 8l10 5 10-5-10-5z"/><path d="M2 13l10 5 10-5"/><path d="M2 18l10 5 10-5"/></svg>',
		'palette'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M12 3a9 9 0 0 0 0 18c1.5 0 2-1 2-2 0-1.5 1-2 2-2h2a4 4 0 0 0 4-4 9 9 0 0 0-10-10z"/><circle cx="7.5" cy="11" r="1" fill="currentColor"/><circle cx="10" cy="7" r="1" fill="currentColor"/><circle cx="15" cy="7" r="1" fill="currentColor"/><circle cx="17.5" cy="11" r="1" fill="currentColor"/></svg>',
		'beaker'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M8 3h8"/><path d="M9 3v6.5L5 18a2 2 0 0 0 1.8 3h10.4a2 2 0 0 0 1.8-3L15 9.5V3"/><path d="M6.5 14h11"/></svg>',
		'flask'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M9 3h6"/><path d="M10 3v6L4.5 18A2 2 0 0 0 6.2 21h11.6a2 2 0 0 0 1.7-3L14 9V3"/><path d="M7 15h10"/></svg>',
		'droplet'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M12 3s6 7 6 11a6 6 0 1 1-12 0c0-4 6-11 6-11z"/></svg>',
		'package'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M3 7l9-4 9 4v10l-9 4-9-4V7z"/><path d="M3 7l9 4 9-4"/><path d="M12 11v10"/></svg>',
		'target'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>',
		'eye'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
		'award'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><circle cx="12" cy="9" r="6"/><path d="M8.2 13.4L7 21l5-3 5 3-1.2-7.6"/></svg>',
		'users'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
		'building'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 9v.01M9 13v.01M9 17v.01M15 9v.01M15 13v.01M15 17v.01"/></svg>',
		'info'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v5h1"/></svg>',
		'factory'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M3 21h18V10l-6 4V10l-6 4V5H3v16z"/><path d="M9 17h2M13 17h2M17 17h2"/></svg>',
		'globe'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>',
		'bolt'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/></svg>',
		'star'      => '<svg viewBox="0 0 24 24" fill="currentColor" width="%1$d" height="%1$d"><path d="M12 2l3 7 7.5.6-5.7 5 1.8 7.4L12 18l-6.6 4 1.8-7.4-5.7-5L9 9z"/></svg>',
		'search'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>',
		'menu'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M3 6h18M3 12h18M3 18h18"/></svg>',
		'x'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="%1$d" height="%1$d"><path d="M18 6L6 18M6 6l12 12"/></svg>',
	);

	$key = isset( $icons[ $name ] ) ? $name : 'package';
	return sprintf( $icons[ $key ], $s );
}

/**
 * Map of available built-in icons for icon-select controls (key => label).
 *
 * @return array<string,string>
 */
function icon_options() {
	return array(
		''          => __( '— بدون آیکون —', 'baspar-elements' ),
		'layers'    => __( 'لایه‌ها (پلیمر)', 'baspar-elements' ),
		'palette'   => __( 'پالت (رنگ)', 'baspar-elements' ),
		'beaker'    => __( 'بشر (شیمیایی)', 'baspar-elements' ),
		'flask'     => __( 'ارلن (کامپوزیت)', 'baspar-elements' ),
		'droplet'   => __( 'قطره (چسب)', 'baspar-elements' ),
		'refresh'   => __( 'چرخه (لاستیک)', 'baspar-elements' ),
		'package'   => __( 'بسته', 'baspar-elements' ),
		'truck'     => __( 'کامیون', 'baspar-elements' ),
		'shield'    => __( 'سپر', 'baspar-elements' ),
		'chat'      => __( 'گفتگو', 'baspar-elements' ),
		'award'     => __( 'مدال', 'baspar-elements' ),
		'target'    => __( 'هدف', 'baspar-elements' ),
		'eye'       => __( 'چشم', 'baspar-elements' ),
		'users'     => __( 'تیم', 'baspar-elements' ),
		'building'  => __( 'ساختمان', 'baspar-elements' ),
		'factory'   => __( 'کارخانه', 'baspar-elements' ),
		'globe'     => __( 'کره زمین', 'baspar-elements' ),
		'bolt'      => __( 'برق', 'baspar-elements' ),
		'clock'     => __( 'ساعت', 'baspar-elements' ),
		'shield_check' => __( 'سپر', 'baspar-elements' ),
		'info'      => __( 'اطلاعات', 'baspar-elements' ),
		'phone'     => __( 'تلفن', 'baspar-elements' ),
		'mail'      => __( 'ایمیل', 'baspar-elements' ),
		'pin'       => __( 'موقعیت', 'baspar-elements' ),
		'whatsapp'  => __( 'واتس‌اپ', 'baspar-elements' ),
		'telegram'  => __( 'تلگرام', 'baspar-elements' ),
		'instagram' => __( 'اینستاگرام', 'baspar-elements' ),
		'cart'      => __( 'سبد خرید', 'baspar-elements' ),
		'star'      => __( 'ستاره', 'baspar-elements' ),
	);
}

/**
 * Simple inline SVG flags for the importer page (origin countries / supply map).
 *
 * @param string $name Country key: china|germany|turkey|iran.
 * @return string SVG markup.
 */
function flag_svg( $name ) {
	$flags = array(
		'china'   => '<svg viewBox="0 0 60 40"><rect width="60" height="40" fill="#DE2910"/><g fill="#FFDE00"><path d="M10 6l1.8 5.5H17l-4.6 3.4 1.8 5.5L10 16.9 5.8 20.4l1.8-5.5L3 11.5h5.2z"/><circle cx="22" cy="5" r="1.6"/><circle cx="26" cy="9" r="1.6"/><circle cx="26" cy="14" r="1.6"/><circle cx="22" cy="18" r="1.6"/></g></svg>',
		'germany' => '<svg viewBox="0 0 60 40"><rect width="60" height="13.33" y="0" fill="#000"/><rect width="60" height="13.33" y="13.33" fill="#DD0000"/><rect width="60" height="13.34" y="26.66" fill="#FFCE00"/></svg>',
		'turkey'  => '<svg viewBox="0 0 60 40"><rect width="60" height="40" fill="#E30A17"/><circle cx="26" cy="20" r="10" fill="#fff"/><circle cx="29" cy="20" r="8" fill="#E30A17"/><path fill="#fff" d="M38 20l7.5-2.4-4.6 6.4v-8l4.6 6.4z"/></svg>',
		'iran'    => '<svg viewBox="0 0 60 40"><rect width="60" height="13.33" y="0" fill="#239F40"/><rect width="60" height="13.33" y="13.33" fill="#fff"/><rect width="60" height="13.34" y="26.66" fill="#DA0000"/></svg>',
	);
	return isset( $flags[ $name ] ) ? $flags[ $name ] : $flags['iran'];
}

/**
 * Flag options for selects.
 *
 * @return array<string,string>
 */
function flag_options() {
	return array(
		'china'   => __( 'چین', 'baspar-elements' ),
		'germany' => __( 'آلمان', 'baspar-elements' ),
		'turkey'  => __( 'ترکیه', 'baspar-elements' ),
		'iran'    => __( 'ایران', 'baspar-elements' ),
	);
}

/**
 * Convert Latin digits in a string to Persian digits.
 *
 * @param string|int|float $value Value to convert.
 * @return string
 */
function fa_num( $value ) {
	$latin   = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$persian = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	return str_replace( $latin, $persian, (string) $value );
}

/**
 * Resolve the WooCommerce product for the current context.
 *
 * Order of resolution: the global $product (set on single product pages and in
 * the Woo loop) → the queried post → in the Elementor editor (or any preview),
 * the most recent published product so the widgets show real data while editing
 * a Theme Builder template. Returns null when WooCommerce is inactive or no
 * product can be resolved.
 *
 * @return \WC_Product|null
 */
function current_product() {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return null;
	}

	global $product;
	if ( $product instanceof \WC_Product ) {
		return $product;
	}

	$id = get_the_ID();
	if ( $id && 'product' === get_post_type( $id ) ) {
		$resolved = wc_get_product( $id );
		if ( $resolved instanceof \WC_Product ) {
			return $resolved;
		}
	}

	// Editor / preview fallback: pick the latest product so the design shows
	// real data instead of an empty widget while building a template.
	$is_preview = is_admin();
	if ( ! $is_preview && isset( \Elementor\Plugin::$instance->editor ) ) {
		$is_preview = \Elementor\Plugin::$instance->editor->is_edit_mode();
	}
	if ( ! $is_preview && function_exists( 'is_preview' ) ) {
		$is_preview = is_preview();
	}
	if ( $is_preview ) {
		$latest = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);
		if ( ! empty( $latest ) ) {
			$resolved = wc_get_product( $latest[0] );
			if ( $resolved instanceof \WC_Product ) {
				return $resolved;
			}
		}
	}

	return null;
}

/**
 * Best-effort brand lookup for a product.
 *
 * WooCommerce has no single universal brand field, so we probe the common brand
 * taxonomies (native WC 9.6+ `product_brand`, Perfect Brands `pwb-brand`, YITH,
 * etc.) and fall back to a product attribute whose name looks like "brand".
 *
 * @param \WC_Product $product Product object.
 * @return string Brand name(s), comma separated, or empty string.
 */
function product_brand( $product ) {
	if ( ! ( $product instanceof \WC_Product ) ) {
		return '';
	}
	$pid = $product->get_id();

	// 1) Known brand taxonomies.
	$taxonomies = array( 'product_brand', 'pwb-brand', 'yith_product_brand', 'pa_brand', 'berocket_brand_collection' );
	foreach ( $taxonomies as $tax ) {
		if ( ! taxonomy_exists( $tax ) ) {
			continue;
		}
		$names = wp_get_post_terms( $pid, $tax, array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $names ) && ! empty( $names ) ) {
			return implode( '، ', $names );
		}
	}

	// 2) A product attribute that looks like a brand.
	foreach ( $product->get_attributes() as $attribute ) {
		$raw   = $attribute->get_name();
		$label = wc_attribute_label( $raw );
		$hay   = strtolower( $raw . ' ' . $label );
		if ( false === strpos( $hay, 'brand' ) && false === strpos( $label, 'برند' ) ) {
			continue;
		}
		if ( $attribute->is_taxonomy() ) {
			$names = wc_get_product_terms( $pid, $raw, array( 'fields' => 'names' ) );
			if ( ! empty( $names ) ) {
				return implode( '، ', $names );
			}
		} else {
			$options = $attribute->get_options();
			if ( ! empty( $options ) ) {
				return implode( '، ', $options );
			}
		}
	}

	return '';
}

/**
 * Return a product's visible attributes as label => value(s) pairs.
 *
 * @param \WC_Product $product Product object.
 * @return array<string,string>
 */
function product_specs( $product ) {
	$rows = array();
	if ( ! ( $product instanceof \WC_Product ) ) {
		return $rows;
	}
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->get_visible() ) {
			continue;
		}
		$label = wc_attribute_label( $attribute->get_name() );
		if ( $attribute->is_taxonomy() ) {
			$values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
			$value  = is_array( $values ) ? implode( '، ', $values ) : '';
		} else {
			$value = implode( '، ', $attribute->get_options() );
		}
		if ( '' !== $value ) {
			$rows[ $label ] = $value;
		}
	}
	return $rows;
}