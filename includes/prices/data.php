<?php
/**
 * Petrochemical base prices — storage and shared helpers.
 *
 * Weekly base prices of petrochemical products (Iran Commodity Exchange,
 * announced by مدیریت توسعه صنایع پایین‌دستی پتروشیمی) are kept as weekly
 * snapshots so every grade has a price history:
 *   {prefix}baspar_price_weeks  one row per announcement (date, dollar rate, source)
 *   {prefix}baspar_price_rows   one row per grade per week (rial / kg)
 *
 * A grade keeps the same `gkey` across weeks: "w<chart id>" for rows
 * imported from Wikiplast (its chart id is stable), otherwise "n<hash>" of
 * the normalised grade + producer names.
 *
 * @package BasparElements
 */

namespace BasparElements\Prices;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DB_VERSION = '1';
const OPTION     = 'baspar_prices_settings';
const STATE      = 'baspar_prices_state';

/**
 * Table names.
 *
 * @return array{weeks:string,rows:string}
 */
function tables() {
	global $wpdb;
	return array(
		'weeks' => $wpdb->prefix . 'baspar_price_weeks',
		'rows'  => $wpdb->prefix . 'baspar_price_rows',
	);
}

/**
 * Create / upgrade tables (runs once per DB_VERSION).
 */
function maybe_install() {
	if ( get_option( 'baspar_prices_db' ) === DB_VERSION ) {
		return;
	}
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t       = tables();
	$charset = $wpdb->get_charset_collate();
	dbDelta(
		"CREATE TABLE {$t['weeks']} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  src_id bigint(20) unsigned NOT NULL DEFAULT 0,
  source varchar(20) NOT NULL DEFAULT 'wikiplast',
  url text NULL,
  title varchar(255) NOT NULL DEFAULT '',
  published_at datetime NOT NULL,
  valid_until varchar(40) NOT NULL DEFAULT '',
  dollar bigint(20) unsigned NOT NULL DEFAULT 0,
  pdf_url text NULL,
  row_count int(11) NOT NULL DEFAULT 0,
  imported_at datetime NOT NULL,
  PRIMARY KEY  (id),
  KEY src_id (src_id),
  KEY published_at (published_at)
) $charset;"
	);
	dbDelta(
		"CREATE TABLE {$t['rows']} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  week_id bigint(20) unsigned NOT NULL,
  gkey varchar(40) NOT NULL,
  grade varchar(191) NOT NULL,
  group_id int(11) NOT NULL DEFAULT 0,
  group_name varchar(191) NOT NULL DEFAULT '',
  producer_id int(11) NOT NULL DEFAULT 0,
  producer varchar(191) NOT NULL DEFAULT '',
  price bigint(20) unsigned NOT NULL DEFAULT 0,
  prev_price bigint(20) unsigned NULL,
  pos int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  KEY week_id (week_id),
  KEY gkey_week (gkey,week_id)
) $charset;"
	);
	update_option( 'baspar_prices_db', DB_VERSION, false );
}

/**
 * Settings with defaults.
 *
 * @return array
 */
function settings() {
	$s = get_option( OPTION, array() );
	return array_merge(
		array(
			'auto'        => 1,          // Daily check for a new announcement.
			'reader'      => 1,          // Fall back to the r.jina.ai reader when the direct request fails.
			'archive_url' => 'https://wikiplast.ir/archive-news/88/1',
			'attribution' => 'منبع: مدیریت توسعه صنایع پایین‌دستی پتروشیمی (قیمت پایه بورس کالا)، به نقل از ویکی‌پلاست',
			'whatsapp'    => '',         // Price inquiry number; empty = default sales experts.
			'vat'         => 10,         // VAT % for the calculator.
		),
		is_array( $s ) ? $s : array()
	);
}

/* ------------------------------------------------------------------------
 * Text helpers
 * --------------------------------------------------------------------- */

/**
 * Persian/Arabic digits → Latin, remove thousands separators.
 *
 * @param string $s Text.
 * @return string
 */
function latin( $s ) {
	return strtr(
		(string) $s,
		array(
			'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
			'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
			'٬' => ',', '،' => ',',
		)
	);
}

/**
 * Parse a price ("1,849,350", "۱٬۸۴۹٬۳۵۰") → int, or null.
 *
 * @param string $s Text.
 * @return int|null
 */
function to_int( $s ) {
	$d = preg_replace( '/[^0-9]/', '', latin( $s ) );
	return '' === $d ? null : (int) $d;
}

/**
 * Clean a name: trim, collapse spaces, drop bidi marks.
 *
 * @param string $s Text.
 * @return string
 */
function clean( $s ) {
	$s = html_entity_decode( wp_strip_all_tags( (string) $s ), ENT_QUOTES, 'UTF-8' );
	$s = preg_replace( '/[\x{200E}\x{200F}\x{202A}-\x{202E}\x{00A0}]/u', ' ', $s );
	return trim( preg_replace( '/\s+/u', ' ', $s ) );
}

/**
 * Comparable form of a name (for matching grades across sources).
 *
 * @param string $s Text.
 * @return string
 */
function norm( $s ) {
	$s = mb_strtolower( latin( clean( $s ) ), 'UTF-8' );
	$s = strtr( $s, array( 'ي' => 'ی', 'ك' => 'ک', 'ة' => 'ه', '‌' => '' ) );
	return preg_replace( '/[\s\-_()\[\]\.]+/u', '', $s );
}

/* ------------------------------------------------------------------------
 * Jalali calendar
 * --------------------------------------------------------------------- */

/**
 * Gregorian → Jalali.
 *
 * @param int $gy Year.
 * @param int $gm Month.
 * @param int $gd Day.
 * @return int[] [y, m, d]
 */
function g2j( $gy, $gm, $gd ) {
	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + intdiv( $gy2 + 3, 4 ) - intdiv( $gy2 + 99, 100 ) + intdiv( $gy2 + 399, 400 ) + $gd + $g_d_m[ $gm - 1 ];
	$jy    = -1595 + ( 33 * intdiv( $days, 12053 ) );
	$days %= 12053;
	$jy   += 4 * intdiv( $days, 1461 );
	$days %= 1461;
	if ( $days > 365 ) {
		$jy  += intdiv( $days - 1, 365 );
		$days = ( $days - 1 ) % 365;
	}
	if ( $days < 186 ) {
		$jm = 1 + intdiv( $days, 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + intdiv( $days - 186, 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}
	return array( $jy, $jm, $jd );
}

/**
 * Jalali → Gregorian.
 *
 * @param int $jy Year.
 * @param int $jm Month.
 * @param int $jd Day.
 * @return int[] [y, m, d]
 */
function j2g( $jy, $jm, $jd ) {
	$jy  += 1595;
	$days = -355668 + ( 365 * $jy ) + ( intdiv( $jy, 33 ) * 8 ) + intdiv( ( $jy % 33 ) + 3, 4 ) + $jd + ( ( $jm < 7 ) ? ( $jm - 1 ) * 31 : ( ( $jm - 7 ) * 30 ) + 186 );
	$gy   = 400 * intdiv( $days, 146097 );
	$days %= 146097;
	if ( $days > 36524 ) {
		$gy  += 100 * intdiv( --$days, 36524 );
		$days %= 36524;
		if ( $days >= 365 ) {
			$days++;
		}
	}
	$gy  += 4 * intdiv( $days, 1461 );
	$days %= 1461;
	if ( $days > 365 ) {
		$gy  += intdiv( $days - 1, 365 );
		$days = ( $days - 1 ) % 365;
	}
	$gd   = $days + 1;
	$leap = ( ( 0 === $gy % 4 && 0 !== $gy % 100 ) || 0 === $gy % 400 );
	$sal  = array( 0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
	for ( $gm = 0; $gm < 13 && $gd > $sal[ $gm ]; $gm++ ) {
		$gd -= $sal[ $gm ];
	}
	return array( $gy, $gm, $gd );
}

/**
 * "1405/08/10" (Persian or Latin digits) → "2026-11-01 12:00:00", or ''.
 *
 * @param string $s Jalali date.
 * @return string
 */
function jalali_to_mysql( $s ) {
	if ( ! preg_match( '#^\s*(\d{4})[/\-.](\d{1,2})[/\-.](\d{1,2})\s*$#', latin( $s ), $m ) ) {
		return '';
	}
	list( $y, $mo, $d ) = j2g( (int) $m[1], (int) $m[2], (int) $m[3] );
	return sprintf( '%04d-%02d-%02d 12:00:00', $y, $mo, $d );
}

/**
 * Jalali month names.
 *
 * @return string[]
 */
function jmonths() {
	return array( 1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
}

/**
 * MySQL datetime → "۱۰ آبان ۱۴۰۵" (or short "1405/08/10").
 *
 * @param string $datetime Datetime (site time).
 * @param bool   $short    Numeric form.
 * @return string
 */
function jdate( $datetime, $short = false ) {
	$ts = strtotime( (string) $datetime );
	if ( ! $ts ) {
		return '';
	}
	list( $y, $m, $d ) = g2j( (int) gmdate( 'Y', $ts ), (int) gmdate( 'n', $ts ), (int) gmdate( 'j', $ts ) );
	if ( $short ) {
		return sprintf( '%04d/%02d/%02d', $y, $m, $d );
	}
	return \BasparElements\fa_num( $d . ' ' . jmonths()[ $m ] . ' ' . $y );
}

/* ------------------------------------------------------------------------
 * Queries
 * --------------------------------------------------------------------- */

/**
 * Weeks, newest first.
 *
 * @param int $limit Max.
 * @return object[]
 */
function weeks( $limit = 60 ) {
	global $wpdb;
	$t = tables();
	return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t['weeks']} ORDER BY published_at DESC, id DESC LIMIT %d", $limit ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

/**
 * One week (latest when $id = 0).
 *
 * @param int $id Week id.
 * @return object|null
 */
function week( $id = 0 ) {
	global $wpdb;
	$t = tables();
	if ( $id ) {
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t['weeks']} WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
	return $wpdb->get_row( "SELECT * FROM {$t['weeks']} ORDER BY published_at DESC, id DESC LIMIT 1" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

/**
 * Week just before another one.
 *
 * @param object $week Week.
 * @return object|null
 */
function previous_week( $week ) {
	global $wpdb;
	$t = tables();
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t['weeks']} WHERE published_at < %s ORDER BY published_at DESC, id DESC LIMIT 1", $week->published_at ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

/**
 * Rows of a week in source order.
 *
 * @param int $week_id Week id.
 * @return object[]
 */
function rows( $week_id ) {
	global $wpdb;
	$t = tables();
	return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t['rows']} WHERE week_id = %d ORDER BY pos ASC", $week_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

/**
 * Price history per grade: gkey => [ [ 'YYYY/MM/DD' jalali, price ], ... ] oldest first.
 *
 * @param string[] $keys  Grade keys.
 * @param int      $weeks How many recent weeks.
 * @return array
 */
function history( $keys, $weeks = 26 ) {
	global $wpdb;
	$keys = array_values( array_filter( array_map( 'strval', (array) $keys ), static function ( $k ) {
		return (bool) preg_match( '/^[wn][a-z0-9]{1,38}$/', $k );
	} ) );
	if ( ! $keys ) {
		return array();
	}
	$t      = tables();
	$wk     = weeks( max( 1, min( 260, (int) $weeks ) ) );
	if ( ! $wk ) {
		return array();
	}
	$ids    = implode( ',', array_map( 'intval', wp_list_pluck( $wk, 'id' ) ) );
	$in     = implode( ',', array_fill( 0, count( $keys ), '%s' ) );
	$sql    = "SELECT r.gkey, r.price, w.published_at FROM {$t['rows']} r JOIN {$t['weeks']} w ON w.id = r.week_id WHERE r.week_id IN ($ids) AND r.gkey IN ($in) ORDER BY w.published_at ASC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$res    = (array) $wpdb->get_results( $wpdb->prepare( $sql, $keys ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$out    = array();
	foreach ( $res as $r ) {
		$out[ $r->gkey ][] = array( jdate( $r->published_at, true ), (int) $r->price );
	}
	return $out;
}

/**
 * Last N prices of every grade (sparklines), gkey => [p1, p2, ...] oldest first.
 *
 * @param int $n Weeks.
 * @return array
 */
function sparks( $n = 8 ) {
	global $wpdb;
	$t  = tables();
	$wk = weeks( max( 2, (int) $n ) );
	if ( ! $wk ) {
		return array();
	}
	$wk   = array_reverse( $wk );
	$pos  = array();
	foreach ( $wk as $i => $w ) {
		$pos[ (int) $w->id ] = $i;
	}
	$ids  = implode( ',', array_keys( $pos ) );
	$res  = (array) $wpdb->get_results( "SELECT week_id, gkey, price FROM {$t['rows']} WHERE week_id IN ($ids)" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$out  = array();
	foreach ( $res as $r ) {
		if ( ! isset( $out[ $r->gkey ] ) ) {
			$out[ $r->gkey ] = array_fill( 0, count( $wk ), null );
		}
		$out[ $r->gkey ][ $pos[ (int) $r->week_id ] ] = (int) $r->price;
	}
	return $out;
}

/**
 * Dollar rate per week (oldest first) for the header trend.
 *
 * @param int $n Weeks.
 * @return array[] [ [ jalali date, rial ], ... ]
 */
function dollar_series( $n = 26 ) {
	$out = array();
	foreach ( array_reverse( weeks( $n ) ) as $w ) {
		if ( (int) $w->dollar ) {
			$out[] = array( jdate( $w->published_at, true ), (int) $w->dollar );
		}
	}
	return $out;
}

/**
 * Drop cached board data (after an import or a delete).
 */
function flush_cache() {
	delete_transient( 'baspar_prices_board' );
	delete_transient( 'baspar_prices_sparks' );
	// One page-cache purge a minute after the last change (a history
	// backfill saves many weeks in a row).
	wp_clear_scheduled_hook( 'baspar_prices_purge' );
	wp_schedule_single_event( time() + 60, 'baspar_prices_purge' );
}
