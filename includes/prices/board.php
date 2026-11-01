<?php
/**
 * Petrochemical base prices — board data (what the price page shows) and the
 * public REST API used by its charts.
 *
 * @package BasparElements
 */

namespace BasparElements\Prices;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Short label of a group name ("پلی اتیلن سبک خطی - LLDPE" → "LLDPE").
 *
 * @param string $name Group name.
 * @return string
 */
function group_short( $name ) {
	if ( preg_match( '/\(\s*([A-Za-z][A-Za-z0-9\- ]*)\s*\)\s*$/u', $name, $m ) || preg_match( '/-\s*([A-Za-z][A-Za-z0-9 ]+)\s*$/u', $name, $m ) ) {
		return trim( $m[1] );
	}
	return $name;
}

/**
 * Everything the board needs for one week (cached for the latest week).
 *
 * @param int $week_id Week id (0 = latest).
 * @return array|null
 */
function board( $week_id = 0 ) {
	if ( ! $week_id ) {
		$cached = get_transient( 'baspar_prices_board' );
		if ( is_array( $cached ) ) {
			return $cached;
		}
	}
	$w = week( $week_id );
	if ( ! $w ) {
		return null;
	}
	$prev_w   = previous_week( $w );
	$prev_map = array();
	if ( $prev_w ) {
		foreach ( rows( $prev_w->id ) as $r ) {
			$prev_map[ $r->gkey ] = (int) $r->price;
		}
	}
	$spark_all = $week_id ? array() : sparks( 12 );
	$groups    = array();
	$producers = array();
	$out_rows  = array();
	$up        = 0;
	$down      = 0;
	$pcts      = array();
	foreach ( rows( $w->id ) as $r ) {
		$price = (int) $r->price;
		$prev  = null !== $r->prev_price ? (int) $r->prev_price : ( $prev_map[ $r->gkey ] ?? null );
		$pct   = ( $prev && $prev > 0 ) ? round( ( $price - $prev ) * 100 / $prev, 2 ) : null;
		$gid   = (int) $r->group_id ? (int) $r->group_id : crc32( $r->group_name ) % 100000;
		if ( ! isset( $groups[ $gid ] ) ) {
			$groups[ $gid ] = array( 'id' => $gid, 'name' => $r->group_name, 'short' => group_short( $r->group_name ), 'n' => 0, 'up' => 0, 'down' => 0, 'pcts' => array() );
		}
		$groups[ $gid ]['n']++;
		$pid = (int) $r->producer_id ? (int) $r->producer_id : crc32( $r->producer ) % 100000;
		if ( ! isset( $producers[ $pid ] ) ) {
			$producers[ $pid ] = array( 'id' => $pid, 'name' => $r->producer, 'n' => 0 );
		}
		$producers[ $pid ]['n']++;
		if ( null !== $pct ) {
			$pcts[]                   = $pct;
			$groups[ $gid ]['pcts'][] = $pct;
			if ( $pct > 0 ) {
				$up++;
				$groups[ $gid ]['up']++;
			} elseif ( $pct < 0 ) {
				$down++;
				$groups[ $gid ]['down']++;
			}
		}
		$spark = $spark_all[ $r->gkey ] ?? array();
		// Trim leading empty weeks (grade added recently).
		while ( $spark && null === $spark[0] ) {
			array_shift( $spark );
		}
		$out_rows[] = array(
			'k'  => $r->gkey,
			'g'  => $gid,
			'gr' => $r->grade,
			'p'  => $pid,
			'v'  => $price,
			'pv' => $prev,
			'c'  => $pct,
			's'  => $spark,
		);
	}
	foreach ( $groups as &$g ) {
		$g['avg'] = $g['pcts'] ? round( array_sum( $g['pcts'] ) / count( $g['pcts'] ), 2 ) : null;
		unset( $g['pcts'] );
	}
	unset( $g );
	usort( $producers, static function ( $a, $b ) {
		return strcmp( $a['name'], $b['name'] );
	} );
	$movers = array_filter( $out_rows, static function ( $r ) {
		return null !== $r['c'];
	} );
	usort( $movers, static function ( $a, $b ) {
		return $b['c'] <=> $a['c'];
	} );
	$movers = array_values( $movers );
	$data   = array(
		'week'      => array(
			'id'          => (int) $w->id,
			'date'        => jdate( $w->published_at ),
			'date_short'  => jdate( $w->published_at, true ),
			'iso'         => mysql2date( 'c', $w->published_at, false ),
			'valid_until' => $w->valid_until ? \BasparElements\fa_num( $w->valid_until ) : '',
			'dollar'      => (int) $w->dollar,
			'dollar_prev' => $prev_w ? (int) $prev_w->dollar : 0,
			'prev_date'   => $prev_w ? jdate( $prev_w->published_at ) : '',
			'url'         => (string) $w->url,
			'pdf'         => (string) $w->pdf_url,
			'source'      => (string) $w->source,
			'count'       => count( $out_rows ),
		),
		'stats'     => array(
			'up'      => $up,
			'down'    => $down,
			'flat'    => count( $pcts ) - $up - $down,
			'avg'     => $pcts ? round( array_sum( $pcts ) / count( $pcts ), 2 ) : null,
			'top_up'  => array_slice( array_column( array_filter( $movers, static function ( $r ) { return $r['c'] > 0; } ), 'k' ), 0, 5 ),
			'top_down'=> array_slice( array_reverse( array_column( array_filter( $movers, static function ( $r ) { return $r['c'] < 0; } ), 'k' ) ), 0, 5 ),
		),
		'groups'    => array_values( $groups ),
		'producers' => array_values( $producers ),
		'rows'      => $out_rows,
		'weeks'     => array_map( static function ( $x ) {
			return array( 'id' => (int) $x->id, 'date' => jdate( $x->published_at ) );
		}, weeks( 60 ) ),
		'dollars'   => dollar_series( 26 ),
	);
	if ( ! $week_id ) {
		set_transient( 'baspar_prices_board', $data, 12 * HOUR_IN_SECONDS );
	}
	return $data;
}

/**
 * Price inquiry contacts: setting, else the default sales experts.
 *
 * @return array{wa:string,bale:string,tel:string}
 */
function inquiry_contacts() {
	$s   = settings();
	$num = trim( (string) $s['whatsapp'] );
	$out = array( 'wa' => '', 'bale' => '', 'tel' => '' );
	$exp = function_exists( '\\BasparElements\\experts_settings' ) ? \BasparElements\experts_settings() : array();
	if ( '' === $num && ! empty( $exp['experts'] ) ) {
		foreach ( (array) $exp['experts'] as $e ) {
			if ( ! empty( $e['active'] ) && ! empty( $e['is_default'] ) && ! empty( $e['whatsapp'] ) ) {
				$num        = $e['whatsapp'];
				$out['tel'] = (string) ( $e['phone'] ?? '' );
				break;
			}
		}
	}
	if ( '' !== $num && function_exists( '\\BasparElements\\intl_number' ) ) {
		$out['wa'] = 'https://wa.me/' . \BasparElements\intl_number( $num );
	}
	if ( ! empty( $exp['bale'] ) && function_exists( '\\BasparElements\\bale_link' ) ) {
		$out['bale'] = (string) \BasparElements\bale_link( $exp['bale'] );
	}
	return $out;
}

/* ------------------------------------------------------------------------
 * REST API (public, read-only)
 *   GET /wp-json/baspar/v1/prices?week=ID
 *   GET /wp-json/baspar/v1/prices/history?keys=w113,w115&weeks=52
 * --------------------------------------------------------------------- */

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'baspar/v1',
			'/prices',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'args'                => array( 'week' => array( 'sanitize_callback' => 'absint' ) ),
				'callback'            => static function ( $req ) {
					$b = board( (int) $req['week'] );
					if ( ! $b ) {
						return new \WP_Error( 'baspar_prices_empty', 'No prices yet.', array( 'status' => 404 ) );
					}
					$res = rest_ensure_response( $b );
					$res->header( 'Cache-Control', 'public, max-age=1800' );
					return $res;
				},
			)
		);
		register_rest_route(
			'baspar/v1',
			'/prices/history',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'args'                => array(
					'keys'  => array( 'sanitize_callback' => 'sanitize_text_field' ),
					'weeks' => array( 'sanitize_callback' => 'absint' ),
				),
				'callback'            => static function ( $req ) {
					$keys = array_slice( array_filter( explode( ',', (string) $req['keys'] ) ), 0, 6 );
					$res  = rest_ensure_response(
						array(
							'series'  => history( $keys, $req['weeks'] ? (int) $req['weeks'] : 52 ),
							'dollars' => dollar_series( $req['weeks'] ? (int) $req['weeks'] : 52 ),
						)
					);
					$res->header( 'Cache-Control', 'public, max-age=1800' );
					return $res;
				},
			)
		);
	}
);
