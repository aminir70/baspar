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

/* ------------------------------------------------------------------------
 * Sidebar banner (widget «کناری: قیمت پایه» + automatic insert)
 * --------------------------------------------------------------------- */

/**
 * URL of the price page: the setting, else the first published page that
 * holds the price board widget (looked up once, then remembered).
 *
 * @return string
 */
function page_url() {
	$s = settings();
	if ( ! empty( $s['page_url'] ) ) {
		return $s['page_url'];
	}
	$id = (int) get_option( 'baspar_prices_page_id' );
	if ( ( ! $id || 'publish' !== get_post_status( $id ) ) && ! get_transient( 'baspar_prices_page_none' ) ) {
		global $wpdb;
		$id = (int) $wpdb->get_var( "SELECT p.ID FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_elementor_data' WHERE p.post_type = 'page' AND p.post_status = 'publish' AND m.meta_value LIKE '%baspar-price-board%' ORDER BY p.ID ASC LIMIT 1" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		update_option( 'baspar_prices_page_id', $id, false );
		if ( ! $id ) {
			set_transient( 'baspar_prices_page_none', 1, 12 * HOUR_IN_SECONDS ); // Look again later.
		}
	}
	return $id ? (string) get_permalink( $id ) : '';
}

/**
 * Sidebar banner markup ('' when there are no prices yet or no page).
 *
 * @param array $o title, cta, url, groups (how many).
 * @return string
 */
function sidebar_banner( $o = array() ) {
	$b   = board();
	$url = ! empty( $o['url'] ) ? $o['url'] : page_url();
	if ( ! $b || ! $url ) {
		return '';
	}
	$o   = array_merge( array( 'title' => 'قیمت پایه محصولات پتروشیمی', 'cta' => 'مشاهده جدول قیمت‌ها', 'groups' => 3 ), array_filter( $o ) );
	$num = static function ( $n, $d = 0 ) {
		return strtr( \BasparElements\fa_num( number_format( (float) $n, $d, '.', ',' ) ), array( ',' => '٬', '.' => '٫' ) );
	};
	$pct = static function ( $v ) use ( $num ) {
		return null === $v ? '—' : ( $v > 0 ? '+' : ( $v < 0 ? '−' : '' ) ) . $num( abs( $v ), 2 ) . '٪';
	};
	$cls = static function ( $v ) {
		return $v > 0 ? 'is-up' : ( $v < 0 ? 'is-down' : '' );
	};
	$w      = $b['week'];
	$groups = $b['groups'];
	usort( $groups, static function ( $a, $c ) {
		return $c['n'] <=> $a['n'];
	} );
	$groups = array_slice( $groups, 0, max( 0, (int) $o['groups'] ) );
	// Dollar trend as a tiny sparkline.
	$spark = '';
	$ds    = array_column( (array) $b['dollars'], 1 );
	if ( count( $ds ) > 2 ) {
		$min = min( $ds );
		$max = max( $ds );
		$n   = count( $ds );
		$pts = array();
		foreach ( $ds as $i => $v ) {
			$pts[] = round( 2 + 116 * $i / ( $n - 1 ), 1 ) . ',' . round( $max === $min ? 14 : 2 + 24 * ( 1 - ( $v - $min ) / ( $max - $min ) ), 1 );
		}
		$spark = '<span class="spb-trend"><small>' . esc_html__( 'روند دلار پایه', 'baspar-elements' ) . '</small><svg class="spb-spark" viewBox="0 0 120 28" preserveAspectRatio="none" aria-hidden="true"><polyline points="' . esc_attr( implode( ' ', $pts ) ) . '"/></svg></span>';
	}
	ob_start();
	?>
	<div class="baspar-scope spb-wrap">
		<a class="spb" href="<?php echo esc_url( $url ); ?>">
			<span class="spb-top"><span class="spb-live"><i aria-hidden="true"></i><?php echo esc_html( 'هفته ' . $w['date'] ); ?></span><?php if ( null !== $b['stats']['avg'] ) : ?><span class="spb-chg <?php echo esc_attr( $cls( $b['stats']['avg'] ) ); ?>" title="<?php esc_attr_e( 'میانگین تغییر هفته', 'baspar-elements' ); ?>"><?php echo esc_html( $pct( $b['stats']['avg'] ) ); ?></span><?php endif; ?></span>
			<strong class="spb-t"><?php echo esc_html( $o['title'] ); ?></strong>
			<span class="spb-sub"><?php echo esc_html( $num( $w['count'] ) . ' گرید' . ( $w['dollar'] ? ' · دلار پایه ' . $num( round( $w['dollar'] / 10 ) ) . ' تومان' : '' ) ); ?></span>
			<?php echo $spark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above from numbers. ?>
			<?php if ( $groups ) : ?>
				<span class="spb-rows">
					<?php foreach ( $groups as $g ) : ?>
						<span class="spb-row"><b><?php echo esc_html( $g['short'] ); ?></b><em class="<?php echo esc_attr( $cls( $g['avg'] ) ); ?>"><?php echo esc_html( $pct( $g['avg'] ) ); ?></em></span>
					<?php endforeach; ?>
				</span>
			<?php endif; ?>
			<span class="spb-btn"><?php echo esc_html( $o['cta'] ); ?><?php echo \BasparElements\icon_svg( 'arrow', 16 ); // phpcs:ignore ?></span>
		</a>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Automatic insert into the sidebars of posts and products: once per page,
 * right before the first of these sidebar widgets (after the contact box /
 * table of contents). Skipped when the page already has the banner widget.
 */
add_filter(
	'elementor/widget/render_content',
	static function ( $content, $widget ) {
		static $done = false;
		if ( $done || is_admin() || ! is_singular( array( 'post', 'product' ) ) || ! settings()['sidebar'] ) {
			return $content;
		}
		$name = $widget->get_name();
		if ( 'baspar-side-prices' === $name ) {
			$done = true; // Placed by hand: no automatic copy.
			return $content;
		}
		if ( ! in_array( $name, array( 'baspar-side-posts', 'baspar-side-cats', 'baspar-side-links', 'baspar-side-products' ), true ) ) {
			return $content;
		}
		if ( function_exists( '\\BasparElements\\page_widgets' ) && in_array( 'side-prices', \BasparElements\page_widgets(), true ) ) {
			$done = true;
			return $content;
		}
		$done = true;
		return sidebar_banner() . $content;
	},
	10,
	2
);
