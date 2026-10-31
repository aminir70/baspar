<?php
/**
 * Responsive WebP copies of product images (360 / 480 / 600 px).
 *
 * The files are written once next to the original upload
 * (photo-bspr480.webp …) with the WordPress image editor and remembered in
 * the attachment meta `_baspar_webp`. They are made the first time a product
 * page shows the image, and when a product is saved. If the server can't
 * write WebP, callers fall back to the normal WordPress markup.
 *
 * @package BasparElements
 */

namespace BasparElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const WEBP_WIDTHS  = array( 360, 480, 600 );
const WEBP_QUALITY = 75; // ≈30% smaller than WebP Express's copy; no visible loss on product photos.

/**
 * `sizes` for the product gallery's main image (matches .pdh-grid columns:
 * one column up to 820px, two up to 1200px, three above).
 *
 * @return string
 */
function gallery_sizes() {
	return (string) apply_filters( 'baspar_elements_gallery_sizes', '(max-width: 820px) calc(100vw - 32px), (max-width: 1200px) 46vw, 480px' );
}

/**
 * Can this server write WebP? (cached for a day).
 *
 * @return bool
 */
function webp_supported() {
	$c = get_transient( 'baspar_webp_ok' );
	if ( false === $c ) {
		$c = function_exists( 'wp_image_editor_supports' ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ? 'yes' : 'no';
		set_transient( 'baspar_webp_ok', $c, DAY_IN_SECONDS );
	}
	return 'yes' === $c;
}

/**
 * WebP set for an attachment: [ src, srcset, width, height ] or null.
 *
 * @param int  $id       Attachment id.
 * @param bool $generate Create missing files now.
 * @return array|null
 */
function webp_image_set( $id, $generate = true ) {
	static $cache = array();
	$id = (int) $id;
	if ( isset( $cache[ $id ] ) ) {
		return $cache[ $id ];
	}
	$cache[ $id ] = null;
	$file = $id ? get_attached_file( $id ) : '';
	if ( ! $file || ! is_readable( $file ) || ! preg_match( '/\.(png|jpe?g)$/i', $file ) ) {
		return null;
	}
	$stamp = filemtime( $file ) . '-' . filesize( $file );
	$data  = get_post_meta( $id, '_baspar_webp', true );
	$ok    = is_array( $data ) && ( $data['stamp'] ?? '' ) === $stamp && ! empty( $data['files'] );
	if ( $ok ) {
		foreach ( $data['files'] as $f ) {
			if ( ! is_readable( dirname( $file ) . '/' . $f['file'] ) ) {
				$ok = false;
				break;
			}
		}
	}
	if ( ! $ok ) {
		if ( ! $generate ) {
			return null;
		}
		$data = make_webp_set( $id, $file, $stamp );
		if ( ! $data ) {
			return null;
		}
	}
	$base   = trailingslashit( dirname( wp_get_attachment_url( $id ) ) );
	$srcset = array();
	$big    = null;
	foreach ( $data['files'] as $f ) {
		$srcset[] = $base . rawurlencode( $f['file'] ) . ' ' . (int) $f['w'] . 'w';
		if ( ! $big || $f['w'] > $big['w'] ) {
			$big = $f;
		}
	}
	$cache[ $id ] = array(
		'src'    => $base . rawurlencode( $big['file'] ),
		'srcset' => implode( ', ', $srcset ),
		'width'  => (int) $big['w'],
		'height' => (int) $big['h'],
	);
	return $cache[ $id ];
}

/**
 * Write the WebP files for one attachment.
 *
 * @param int    $id    Attachment id.
 * @param string $file  Original file path.
 * @param string $stamp mtime-size of the original.
 * @return array|null Stored meta.
 */
function make_webp_set( $id, $file, $stamp ) {
	if ( ! webp_supported() ) {
		return null;
	}
	$lock = 'baspar_webp_lock_' . $id;
	if ( get_transient( $lock ) ) {
		return null; // Another request is making them; use the old markup this time.
	}
	set_transient( $lock, 1, MINUTE_IN_SECONDS );
	$size = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $file ) : getimagesize( $file );
	if ( ! $size ) {
		delete_transient( $lock );
		return null;
	}
	list( $ow, $oh ) = $size;
	$name   = pathinfo( $file, PATHINFO_FILENAME );
	$dir    = dirname( $file );
	$files  = array();
	$widths = array_unique( array_map( static function ( $w ) use ( $ow ) {
		return (int) min( $w, $ow );
	}, WEBP_WIDTHS ) );
	rsort( $widths );
	foreach ( $widths as $w ) {
		$ed = wp_get_image_editor( $file );
		if ( is_wp_error( $ed ) ) {
			break;
		}
		$ed->set_quality( (int) apply_filters( 'baspar_elements_webp_quality', WEBP_QUALITY ) );
		if ( $w < $ow ) {
			$r = $ed->resize( $w, null, false );
			if ( is_wp_error( $r ) ) {
				continue;
			}
		}
		$target = $dir . '/' . $name . '-bspr' . $w . '.webp';
		$saved  = $ed->save( $target, 'image/webp' );
		if ( is_wp_error( $saved ) || empty( $saved['path'] ) ) {
			continue;
		}
		$files[] = array( 'file' => basename( $saved['path'] ), 'w' => (int) $saved['width'], 'h' => (int) $saved['height'] );
	}
	delete_transient( $lock );
	if ( ! $files ) {
		return null;
	}
	$data = array( 'stamp' => $stamp, 'files' => array_reverse( $files ) );
	update_post_meta( $id, '_baspar_webp', $data );
	return $data;
}

/**
 * <img> markup for the product gallery's main image (WebP set), or '' to
 * let the caller use wp_get_attachment_image().
 *
 * @param int    $id  Attachment id.
 * @param string $alt Alt text.
 * @return string
 */
function gallery_main_image( $id, $alt ) {
	$set = webp_image_set( $id );
	if ( ! $set ) {
		return '';
	}
	// "webpexpress-processed" tells WebP Express the tag is already WebP.
	return sprintf(
		'<img src="%1$s" srcset="%2$s" sizes="%3$s" width="%4$d" height="%5$d" class="pdg-img webpexpress-processed" alt="%6$s" fetchpriority="high" loading="eager" decoding="async" data-pdg-img="1">',
		esc_url( $set['src'] ),
		esc_attr( $set['srcset'] ),
		esc_attr( gallery_sizes() ),
		(int) $set['width'],
		(int) $set['height'],
		esc_attr( $alt )
	);
}

/**
 * Make the WebP files when a product is saved (so visitors never wait).
 *
 * @param int $post_id Product id.
 */
function webp_on_product_save( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
		return;
	}
	$ids = array( (int) get_post_thumbnail_id( $post_id ) );
	$gal = get_post_meta( $post_id, '_product_image_gallery', true );
	if ( $gal ) {
		$ids = array_merge( $ids, array_map( 'intval', explode( ',', $gal ) ) );
	}
	foreach ( array_slice( array_filter( array_unique( $ids ) ), 0, 8 ) as $id ) {
		webp_image_set( $id );
	}
}

/**
 * Remove our WebP files when the attachment is deleted.
 *
 * @param int $id Attachment id.
 */
function webp_on_delete( $id ) {
	$data = get_post_meta( $id, '_baspar_webp', true );
	$file = get_attached_file( $id );
	if ( ! is_array( $data ) || empty( $data['files'] ) || ! $file ) {
		return;
	}
	foreach ( $data['files'] as $f ) {
		$path = dirname( $file ) . '/' . basename( $f['file'] );
		if ( is_file( $path ) ) {
			wp_delete_file( $path );
		}
	}
}
