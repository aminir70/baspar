<?php
/**
 * Petrochemical base prices — importer.
 *
 * Source: Wikiplast "اعلام قیمت‌ها" archive (weekly post "قیمت پایه محصولات
 * پتروشیمی تا …" with the full table as HTML: table#pricetbl).
 * The site may refuse connections from servers outside Iran, so when the
 * direct request fails the page is read through the r.jina.ai reader
 * (setting "reader"). Manual fallbacks: post URL, saved HTML file, pasted
 * table text, CSV.
 *
 * @package BasparElements
 */

namespace BasparElements\Prices;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36';

/* ------------------------------------------------------------------------
 * Fetching
 * --------------------------------------------------------------------- */

/**
 * Fetch a Wikiplast page as HTML.
 *
 * @param string $url    Page URL.
 * @param string $expect Marker the HTML must contain to count as valid.
 * @return array|\WP_Error [ 'body' => html, 'via' => 'direct'|'reader' ]
 */
function fetch( $url, $expect = '' ) {
	$errors = array();
	// When the direct connection failed recently (server outside Iran), go
	// straight to the reader instead of waiting for another timeout.
	if ( ! ( settings()['reader'] && get_transient( 'baspar_prices_direct_down' ) ) ) {
		$r = wp_remote_get( $url, array( 'timeout' => 20, 'user-agent' => UA, 'headers' => array( 'Accept-Language' => 'fa-IR,fa;q=0.9' ) ) );
		if ( ! is_wp_error( $r ) && 200 === (int) wp_remote_retrieve_response_code( $r ) ) {
			$body = (string) wp_remote_retrieve_body( $r );
			if ( '' === $expect || false !== strpos( $body, $expect ) ) {
				delete_transient( 'baspar_prices_direct_down' );
				return array( 'body' => $body, 'via' => 'direct' );
			}
			$errors[] = 'direct: unexpected page';
		} else {
			$errors[] = 'direct: ' . ( is_wp_error( $r ) ? $r->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $r ) );
			if ( is_wp_error( $r ) ) {
				set_transient( 'baspar_prices_direct_down', 1, 6 * HOUR_IN_SECONDS );
			}
		}
	}
	if ( settings()['reader'] ) {
		$r = wp_remote_get(
			'https://r.jina.ai/' . $url,
			array( 'timeout' => 60, 'headers' => array( 'X-Return-Format' => 'html', 'Accept' => 'text/html' ) )
		);
		if ( ! is_wp_error( $r ) && 200 === (int) wp_remote_retrieve_response_code( $r ) ) {
			$body = (string) wp_remote_retrieve_body( $r );
			if ( '' === $expect || false !== strpos( $body, $expect ) ) {
				return array( 'body' => $body, 'via' => 'reader' );
			}
			$errors[] = 'reader: unexpected page';
		} else {
			$errors[] = 'reader: ' . ( is_wp_error( $r ) ? $r->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $r ) );
		}
	}
	return new \WP_Error( 'baspar_prices_fetch', implode( ' | ', $errors ) );
}

/* ------------------------------------------------------------------------
 * Parsing
 * --------------------------------------------------------------------- */

/**
 * Load HTML into a DOMXPath (UTF-8 safe, quiet).
 *
 * @param string $html HTML.
 * @return \DOMXPath
 */
function xpath( $html ) {
	$doc = new \DOMDocument();
	$old = libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?>' . $html, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR );
	libxml_clear_errors();
	libxml_use_internal_errors( $old );
	return new \DOMXPath( $doc );
}

/**
 * Absolute Wikiplast URL.
 *
 * @param string $href Link.
 * @return string
 */
function abs_url( $href ) {
	$href = (string) $href;
	if ( 0 === strpos( $href, '//' ) ) {
		return 'https:' . $href;
	}
	if ( 0 === strpos( $href, '/' ) ) {
		return 'https://wikiplast.ir' . $href;
	}
	return $href;
}

/**
 * Price posts listed on an archive page.
 *
 * @param string $html Archive HTML.
 * @return array[] [ id, url, title ] newest first.
 */
function parse_archive( $html ) {
	$xp  = xpath( $html );
	$out = array();
	foreach ( $xp->query( '//a[contains(@href, "/news/")]' ) as $a ) {
		$href  = $a->getAttribute( 'href' );
		$title = clean( $a->textContent );
		// Only the regular weekly table ("… قیمت پایه محصولات پتروشیمی تا 17 آبان 1405 …"),
		// not side announcements such as "پایه‌ها با قیمت‌های جهانی اعلام شد".
		if ( ! preg_match( '#/news/(\d+)/#', $href, $m ) || ! preg_match( '/قیمت\s*پایه\s*محصولات\s*پتروشیمی\s*تا\s/u', $title ) ) {
			continue;
		}
		$id = (int) $m[1];
		if ( ! isset( $out[ $id ] ) ) {
			$out[ $id ] = array( 'id' => $id, 'url' => abs_url( $href ), 'title' => $title );
		}
	}
	krsort( $out );
	return array_values( $out );
}

/**
 * A price post → [ meta, rows ].
 *
 * @param string $html Post HTML.
 * @param string $url  Post URL (for its id).
 * @return array|\WP_Error
 */
function parse_post( $html, $url = '' ) {
	$xp   = xpath( $html );
	$meta = array(
		'src_id'       => preg_match( '#/news/(\d+)#', (string) $url, $m ) ? (int) $m[1] : 0,
		'url'          => $url,
		'source'       => 'wikiplast',
		'title'        => '',
		'published_at' => '',
		'valid_until'  => '',
		'dollar'       => 0,
		'pdf_url'      => '',
	);
	$t = $xp->query( '//title' )->item( 0 );
	if ( $t ) {
		$meta['title'] = trim( preg_replace( '/\s*\|.*$/u', '', clean( $t->textContent ) ) );
	}
	if ( ! $meta['src_id'] ) {
		$can = $xp->query( '//link[@rel="canonical"]/@href|//meta[@property="og:url"]/@content' )->item( 0 );
		if ( $can && preg_match( '#/news/(\d+)#', $can->nodeValue, $m ) ) {
			$meta['src_id'] = (int) $m[1];
			$meta['url']    = abs_url( $can->nodeValue );
		}
	}
	$pub = $xp->query( '//meta[@property="article:published_time"]/@content' )->item( 0 );
	if ( $pub ) {
		try {
			$dt = new \DateTime( $pub->nodeValue );
			$dt->setTimezone( wp_timezone() );
			$meta['published_at'] = $dt->format( 'Y-m-d H:i:s' );
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}
	$desc = $xp->query( '//meta[@name="description"]/@content' )->item( 0 );
	$text = ( $desc ? $desc->nodeValue : '' ) . ' ' . $meta['title'];
	if ( preg_match( '/دلار[^0-9۰-۹]{0,40}([0-9۰-۹,٬]{5,})\s*تومان/u', $text, $m ) ) {
		$meta['dollar'] = (int) to_int( $m[1] ) * 10; // Toman → rial.
	}
	if ( preg_match( '/تا\s+([0-9۰-۹]{1,2}\s+\S+\s+[0-9۰-۹]{4})/u', $meta['title'], $m ) ) {
		$meta['valid_until'] = latin( $m[1] );
	}
	foreach ( $xp->query( '//a[contains(@href, ".pdf")]' ) as $a ) {
		if ( false !== mb_strpos( $a->textContent, 'پایه' ) ) {
			$meta['pdf_url'] = abs_url( $a->getAttribute( 'href' ) );
			break;
		}
	}

	$rows   = array();
	$names  = array(); // group id => name, from heading rows that link to /catNN.
	$group  = array( 'id' => 0, 'name' => '' );
	$trs    = $xp->query( '//table[@id="pricetbl"]//tr' );
	if ( ! $trs->length ) {
		$trs = $xp->query( '//tr[contains(@class, "maintr") or contains(@class, "trgroup")]' );
	}
	foreach ( $trs as $tr ) {
		$cls = ' ' . $tr->getAttribute( 'class' ) . ' ';
		if ( false !== strpos( $cls, ' maincell ' ) ) {
			continue; // Column header row (گرید / قیمت / …).
		}
		if ( false !== strpos( $cls, ' trgroup ' ) ) {
			$a = $xp->query( './/a[contains(@href, "/cat")]', $tr )->item( 0 );
			if ( $a && preg_match( '#/cat(\d+)#', $a->getAttribute( 'href' ), $m ) ) {
				$group             = array( 'id' => (int) $m[1], 'name' => clean( $a->textContent ) );
				$names[ $group['id'] ] = $group['name'];
			}
			continue;
		}
		if ( false === strpos( $cls, ' maintr ' ) ) {
			continue;
		}
		// The row's own class names its group (e.g. "maintr group86").
		$gid = preg_match( '/\bgroup(\d+)\b/', $cls, $m ) ? (int) $m[1] : $group['id'];
		$td  = $xp->query( './td', $tr );
		if ( $td->length < 5 ) {
			continue;
		}
		$price = to_int( $td->item( 1 )->textContent );
		if ( ! $price ) {
			continue;
		}
		$prod_a = $xp->query( './/a[contains(@href, "/petros/")]', $td->item( 4 ) )->item( 0 );
		$chart  = $xp->query( './/a[contains(@href, "/chart/")]', $tr )->item( 0 );
		$rows[] = array(
			'grade'       => clean( $td->item( 0 )->textContent ),
			'price'       => $price,
			'prev_price'  => to_int( $td->item( 2 )->textContent ),
			'producer'    => clean( $td->item( 4 )->textContent ),
			'producer_id' => ( $prod_a && preg_match( '#/petros/(\d+)#', $prod_a->getAttribute( 'href' ), $m ) ) ? (int) $m[1] : 0,
			'group_id'    => $gid,
			'group_name'  => '',
			'chart_id'    => ( $chart && preg_match( '#/chart/\d+/(\d+)#', $chart->getAttribute( 'href' ), $m ) ) ? (int) $m[1] : 0,
		);
	}
	foreach ( $rows as &$r ) {
		$r['group_name'] = $names[ $r['group_id'] ] ?? '';
	}
	unset( $r );
	if ( ! $rows ) {
		return new \WP_Error( 'baspar_prices_parse', __( 'جدول قیمت در صفحه پیدا نشد.', 'baspar-elements' ) );
	}
	return array( $meta, $rows );
}

/**
 * Table text pasted from the browser (copy of the Wikiplast table) or any
 * "grade  price  [previous]  [change]  producer" lines; a line without
 * numbers starts a new group.
 *
 * @param string $text Text.
 * @return array[] Rows.
 */
function parse_text( $text ) {
	$rows  = array();
	$group = '';
	foreach ( preg_split( '/\R/u', (string) $text ) as $line ) {
		$line = trim( latin( $line ) );
		if ( '' === $line ) {
			continue;
		}
		if ( ! preg_match_all( '/(?<![\w.])-?\d{1,3}(?:,\d{3})+(?![\d,])|(?<![\w.,])\d{5,}(?![\d,])/u', $line, $nums, PREG_OFFSET_CAPTURE ) ) {
			// No price on the line: a group heading (skip table header rows).
			if ( ! preg_match( '/^(گرید|grade)\b/ui', $line ) && mb_strlen( $line ) < 120 ) {
				$group = clean( $line );
			}
			continue;
		}
		$first = $nums[0][0];
		$last  = end( $nums[0] );
		$grade = clean( substr( $line, 0, $first[1] ) );
		$prod  = clean( substr( $line, $last[1] + strlen( $last[0] ) ) );
		$price = to_int( $first[0] );
		if ( '' === $grade || ! $price ) {
			continue;
		}
		$rows[] = array(
			'grade'       => $grade,
			'price'       => $price,
			'prev_price'  => isset( $nums[0][1] ) && count( $nums[0] ) >= 2 ? to_int( $nums[0][1][0] ) : null,
			'producer'    => trim( preg_replace( array( '/^[\s\-–—]+/u', '/\s*(نمودار|chart)\s*$/ui' ), '', $prod ) ),
			'producer_id' => 0,
			'group_id'    => 0,
			'group_name'  => $group,
			'chart_id'    => 0,
		);
	}
	return $rows;
}

/**
 * CSV: group, grade, producer, price[, prev_price] (header row optional,
 * Persian or English headers).
 *
 * @param string $text CSV text.
 * @return array[] Rows.
 */
function parse_csv( $text ) {
	$rows  = array();
	$lines = preg_split( '/\R/u', trim( (string) $text ) );
	$map   = array( 'group' => 0, 'grade' => 1, 'producer' => 2, 'price' => 3, 'prev' => 4 );
	foreach ( $lines as $i => $line ) {
		$cells = str_getcsv( $line, false !== strpos( $line, "\t" ) ? "\t" : ',' );
		if ( 0 === $i && ! to_int( implode( '', array_slice( $cells, 3, 1 ) ) ) ) {
			foreach ( $cells as $k => $h ) {
				$h = norm( $h );
				if ( in_array( $h, array( 'group', 'گروه' ), true ) ) { $map['group'] = $k; }
				if ( in_array( $h, array( 'grade', 'گرید' ), true ) ) { $map['grade'] = $k; }
				if ( in_array( $h, array( 'producer', 'تولیدکننده', 'پتروشیمی', 'مجتمع' ), true ) ) { $map['producer'] = $k; }
				if ( in_array( $h, array( 'price', 'قیمت' ), true ) ) { $map['price'] = $k; }
				if ( in_array( $h, array( 'prevprice', 'prev', 'قیمتقبل' ), true ) ) { $map['prev'] = $k; }
			}
			continue;
		}
		$price = to_int( $cells[ $map['price'] ] ?? '' );
		$grade = clean( $cells[ $map['grade'] ] ?? '' );
		if ( ! $price || '' === $grade ) {
			continue;
		}
		$rows[] = array(
			'grade'       => $grade,
			'price'       => $price,
			'prev_price'  => to_int( $cells[ $map['prev'] ] ?? '' ),
			'producer'    => clean( $cells[ $map['producer'] ] ?? '' ),
			'producer_id' => 0,
			'group_id'    => 0,
			'group_name'  => clean( $cells[ $map['group'] ] ?? '' ),
			'chart_id'    => 0,
		);
	}
	return $rows;
}

/* ------------------------------------------------------------------------
 * Saving
 * --------------------------------------------------------------------- */

/**
 * Save one week (replaces an earlier import of the same post).
 *
 * @param array   $meta Week meta.
 * @param array[] $rows Rows.
 * @return int|\WP_Error Week id.
 */
function store_week( $meta, $rows ) {
	global $wpdb;
	maybe_install();
	$t = tables();
	if ( empty( $meta['published_at'] ) ) {
		$meta['published_at'] = current_time( 'mysql' );
	}
	// Names of the latest stored week, to keep the same grade key for rows
	// that come without a Wikiplast chart id (manual imports).
	$known  = array();
	$latest = week();
	if ( $latest ) {
		foreach ( rows( $latest->id ) as $r ) {
			$known[ norm( $r->grade ) . '|' . norm( $r->producer ) ] = $r->gkey;
			$known[ norm( $r->grade ) ]                              = $known[ norm( $r->grade ) ] ?? $r->gkey;
		}
	}
	$week_id = 0;
	if ( ! empty( $meta['src_id'] ) ) {
		$week_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t['weeks']} WHERE src_id = %d AND source = %s", $meta['src_id'], $meta['source'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
	$data = array(
		'src_id'       => (int) ( $meta['src_id'] ?? 0 ),
		'source'       => substr( (string) ( $meta['source'] ?? 'manual' ), 0, 20 ),
		'url'          => (string) ( $meta['url'] ?? '' ),
		'title'        => mb_substr( (string) ( $meta['title'] ?? '' ), 0, 250 ),
		'published_at' => $meta['published_at'],
		'valid_until'  => (string) ( $meta['valid_until'] ?? '' ),
		'dollar'       => (int) ( $meta['dollar'] ?? 0 ),
		'pdf_url'      => (string) ( $meta['pdf_url'] ?? '' ),
		'row_count'    => count( $rows ),
		'imported_at'  => current_time( 'mysql' ),
	);
	if ( $week_id ) {
		$wpdb->update( $t['weeks'], $data, array( 'id' => $week_id ) );
		$wpdb->delete( $t['rows'], array( 'week_id' => $week_id ) );
	} else {
		if ( false === $wpdb->insert( $t['weeks'], $data ) ) {
			return new \WP_Error( 'baspar_prices_db', $wpdb->last_error );
		}
		$week_id = (int) $wpdb->insert_id;
	}
	$values = array();
	$seen   = array();
	foreach ( array_values( $rows ) as $pos => $r ) {
		if ( ! empty( $r['chart_id'] ) ) {
			$gkey = 'w' . (int) $r['chart_id'];
		} else {
			$k    = norm( $r['grade'] ) . '|' . norm( $r['producer'] );
			$gkey = $known[ $k ] ?? ( $known[ norm( $r['grade'] ) ] ?? 'n' . substr( md5( $k ), 0, 16 ) );
		}
		if ( isset( $seen[ $gkey ] ) ) {
			continue; // Same grade twice in one table: keep the first.
		}
		$seen[ $gkey ] = true;
		$values[]      = $wpdb->prepare(
			'(%d,%s,%s,%d,%s,%d,%s,%d,' . ( null === $r['prev_price'] ? 'NULL' : '%d' ) . ',%d)',
			array_merge(
				array( $week_id, $gkey, mb_substr( $r['grade'], 0, 190 ), (int) $r['group_id'], mb_substr( $r['group_name'], 0, 190 ), (int) $r['producer_id'], mb_substr( $r['producer'], 0, 190 ), (int) $r['price'] ),
				null === $r['prev_price'] ? array() : array( (int) $r['prev_price'] ),
				array( $pos )
			)
		);
	}
	foreach ( array_chunk( $values, 150 ) as $chunk ) {
		$wpdb->query( "INSERT INTO {$t['rows']} (week_id,gkey,grade,group_id,group_name,producer_id,producer,price,prev_price,pos) VALUES " . implode( ',', $chunk ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
	$wpdb->update( $t['weeks'], array( 'row_count' => count( $values ) ), array( 'id' => $week_id ) );
	flush_cache();
	return $week_id;
}

/**
 * Import one Wikiplast post by URL.
 *
 * @param string $url Post URL.
 * @return int|\WP_Error Week id.
 */
function import_url( $url ) {
	$url = esc_url_raw( trim( (string) $url ) );
	if ( ! preg_match( '#^https?://(www\.)?wikiplast\.ir/news/\d+#', $url ) ) {
		return new \WP_Error( 'baspar_prices_url', __( 'آدرس باید یک خبر «قیمت پایه» در wikiplast.ir باشد.', 'baspar-elements' ) );
	}
	$page = fetch( $url, 'pricetbl' );
	if ( is_wp_error( $page ) ) {
		return $page;
	}
	$parsed = parse_post( $page['body'], $url );
	if ( is_wp_error( $parsed ) ) {
		return $parsed;
	}
	list( $meta, $rows ) = $parsed;
	$id = store_week( $meta, $rows );
	if ( ! is_wp_error( $id ) ) {
		update_state( array( 'last_ok' => current_time( 'mysql' ), 'last_via' => $page['via'], 'last_error' => '' ) );
	}
	return $id;
}

/**
 * Is a Wikiplast post already stored?
 *
 * @param int $src_id Post id.
 * @return bool
 */
function has_post( $src_id ) {
	global $wpdb;
	$t = tables();
	return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t['weeks']} WHERE src_id = %d AND source = 'wikiplast'", $src_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

/* ------------------------------------------------------------------------
 * Automatic checks and history backfill
 * --------------------------------------------------------------------- */

/**
 * Importer state (last run, errors, backfill queue).
 *
 * @return array
 */
function state() {
	$s = get_option( STATE, array() );
	return is_array( $s ) ? $s : array();
}

/**
 * Merge into the state.
 *
 * @param array $data Values.
 */
function update_state( $data ) {
	update_option( STATE, array_merge( state(), $data ), false );
}

/**
 * Look for a new announcement and import it (cron + "check now").
 *
 * @return array Result: imported week ids, message.
 */
function check_latest() {
	update_state( array( 'last_check' => current_time( 'mysql' ) ) );
	$s    = settings();
	$page = fetch( $s['archive_url'], '/news/' );
	if ( is_wp_error( $page ) ) {
		update_state( array( 'last_error' => $page->get_error_message() ) );
		return array( 'ok' => false, 'message' => $page->get_error_message() );
	}
	$posts = parse_archive( $page['body'] );
	if ( ! $posts ) {
		$msg = __( 'هیچ خبر «قیمت پایه» در صفحه آرشیو پیدا نشد (ممکن است ساختار سایت عوض شده باشد).', 'baspar-elements' );
		update_state( array( 'last_error' => $msg ) );
		return array( 'ok' => false, 'message' => $msg );
	}
	$new = array();
	foreach ( array_slice( $posts, 0, 3 ) as $p ) { // The newest few, in case a week was missed.
		if ( has_post( $p['id'] ) ) {
			continue;
		}
		$id = import_url( $p['url'] );
		if ( is_wp_error( $id ) ) {
			update_state( array( 'last_error' => $id->get_error_message() ) );
			return array( 'ok' => false, 'message' => $id->get_error_message() );
		}
		$new[] = $id;
	}
	update_state( array( 'last_ok' => current_time( 'mysql' ), 'last_error' => '', 'last_via' => $page['via'] ) );
	return array( 'ok' => true, 'imported' => $new, 'message' => $new ? sprintf( '%d هفته جدید وارد شد.', count( $new ) ) : __( 'قیمت جدیدی منتشر نشده است.', 'baspar-elements' ) );
}

/**
 * Queue older weeks for import (history), processed in small cron batches.
 *
 * @param int $weeks How many weeks back.
 * @return array Result.
 */
function queue_backfill( $weeks = 52 ) {
	$weeks = max( 1, min( 260, (int) $weeks ) );
	$s     = settings();
	$found = array();
	for ( $page = 1; $page <= 40 && count( $found ) < $weeks; $page++ ) {
		$url = preg_replace( '#/\d+/?$#', '/' . $page, $s['archive_url'] );
		$res = fetch( $url, '/news/' );
		if ( is_wp_error( $res ) ) {
			if ( ! $found ) {
				return array( 'ok' => false, 'message' => $res->get_error_message() );
			}
			break;
		}
		$posts = parse_archive( $res['body'] );
		if ( ! $posts ) {
			break;
		}
		foreach ( $posts as $p ) {
			$found[ $p['id'] ] = $p['url'];
		}
	}
	krsort( $found );
	$found = array_slice( $found, 0, $weeks, true );
	$queue = array();
	foreach ( $found as $id => $url ) {
		if ( ! has_post( $id ) ) {
			$queue[] = $url;
		}
	}
	update_state( array( 'queue' => $queue, 'queue_total' => count( $queue ), 'queue_errors' => array(), 'queue_tries' => array() ) );
	if ( $queue && ! wp_next_scheduled( 'baspar_prices_backfill' ) ) {
		wp_schedule_single_event( time() + 5, 'baspar_prices_backfill' );
	}
	return array( 'ok' => true, 'message' => sprintf( '%d هفته در صف ورود قرار گرفت.', count( $queue ) ) );
}

/**
 * Cron: import a few queued weeks, then reschedule until the queue is empty.
 */
function run_backfill() {
	$st    = state();
	$queue = isset( $st['queue'] ) ? (array) $st['queue'] : array();
	$errs  = isset( $st['queue_errors'] ) ? (array) $st['queue_errors'] : array();
	$start = time();
	$tries = isset( $st['queue_tries'] ) ? (array) $st['queue_tries'] : array();
	while ( $queue && time() - $start < 40 ) {
		$url = array_shift( $queue );
		$id  = import_url( $url );
		if ( is_wp_error( $id ) ) {
			$n             = ( $tries[ md5( $url ) ] ?? 0 ) + 1;
			$tries[ md5( $url ) ] = $n;
			if ( $n < 3 ) {
				$queue[] = $url; // Retry later (the reader sometimes refuses a request).
			} else {
				$errs[] = $url . ' — ' . $id->get_error_message();
			}
		}
		update_state( array( 'queue' => $queue, 'queue_tries' => $tries, 'queue_errors' => array_slice( $errs, -20 ) ) );
	}
	if ( $queue ) {
		wp_schedule_single_event( time() + 30, 'baspar_prices_backfill' );
	}
}

/**
 * Cron schedule for the automatic check.
 */
function schedule() {
	$auto = (bool) settings()['auto'];
	$next = wp_next_scheduled( 'baspar_prices_check' );
	if ( $auto && ! $next ) {
		wp_schedule_event( time() + 120, 'twicedaily', 'baspar_prices_check' );
	} elseif ( ! $auto && $next ) {
		wp_clear_scheduled_hook( 'baspar_prices_check' );
	}
}

add_action( 'init', __NAMESPACE__ . '\\maybe_install', 5 );
add_action( 'init', __NAMESPACE__ . '\\schedule', 30 );
add_action( 'baspar_prices_check', __NAMESPACE__ . '\\check_latest' );
add_action( 'baspar_prices_backfill', __NAMESPACE__ . '\\run_backfill' );
// Cached pages show the new prices right away.
add_action(
	'baspar_prices_purge',
	__NAMESPACE__ . '\\purge_all_pages'
);
