<?php
/**
 * Shared helpers for the blog / single-post widgets: post queries, the post
 * card markup, reading time and table-of-contents support.
 *
 * @package BasparElements
 */

namespace BasparElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The post the single-post widgets should describe: the queried post on a
 * single post, otherwise (Elementor editor / theme-builder preview) the most
 * recent published post so the widgets show real data while editing.
 *
 * @return \WP_Post|null
 */
function current_blog_post() {
	if ( is_singular( 'post' ) ) {
		return get_post();
	}
	$post = get_post();
	if ( $post instanceof \WP_Post && 'post' === $post->post_type ) {
		return $post;
	}
	$latest = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish' ) );
	return $latest ? $latest[0] : null;
}

/**
 * Estimated reading time in minutes (Persian text ~200 words / minute).
 *
 * @param \WP_Post|int $post Post.
 * @return int
 */
function reading_minutes( $post ) {
	$post  = get_post( $post );
	$text  = $post ? wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) : '';
	$words = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Primary category of a post (Rank Math / Yoast primary first).
 *
 * @param int $post_id Post ID.
 * @return \WP_Term|null
 */
function post_primary_cat( $post_id ) {
	$cats = get_the_category( $post_id );
	if ( ! $cats ) {
		return null;
	}
	foreach ( array( 'rank_math_primary_category', '_yoast_wpseo_primary_category' ) as $key ) {
		$pid = (int) get_post_meta( $post_id, $key, true );
		foreach ( $cats as $c ) {
			if ( $pid && (int) $c->term_id === $pid ) {
				return $c;
			}
		}
	}
	return $cats[0];
}

/**
 * Category options for a select control (id => name).
 *
 * @return array<string,string>
 */
function post_cat_options() {
	$opts  = array( '0' => __( 'همه دسته‌ها', 'baspar-elements' ) );
	$terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$opts[ (string) $t->term_id ] = $t->name;
		}
	}
	return $opts;
}

/**
 * Build WP_Query args for the blog widgets.
 *
 * @param array $a {
 *     @type string $source   latest|category|keyword|sticky|popular|manual.
 *     @type int    $count    Posts per page.
 *     @type int    $category Category ID.
 *     @type string $keyword  Search keyword.
 *     @type string $ids      Comma separated post IDs (manual).
 *     @type int    $offset   Skip N posts.
 *     @type int    $paged    Page number.
 *     @type int[]  $exclude  Post IDs to exclude.
 * }
 * @return array
 */
function blog_query_args( $a ) {
	$a    = wp_parse_args( $a, array( 'source' => 'latest', 'count' => 6, 'category' => 0, 'keyword' => '', 'ids' => '', 'offset' => 0, 'paged' => 1, 'exclude' => array() ) );
	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, (int) $a['count'] ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => false,
	);
	switch ( $a['source'] ) {
		case 'category':
			if ( (int) $a['category'] ) {
				$args['cat'] = (int) $a['category'];
			}
			break;
		case 'keyword':
			if ( '' !== trim( (string) $a['keyword'] ) ) {
				$args['s'] = trim( (string) $a['keyword'] );
			}
			break;
		case 'sticky':
			$sticky = get_option( 'sticky_posts' );
			if ( $sticky ) {
				$args['post__in'] = array_map( 'intval', $sticky );
			}
			break;
		case 'popular':
			$args['orderby'] = array( 'comment_count' => 'DESC', 'date' => 'DESC' );
			break;
		case 'manual':
			$ids = array_filter( array_map( 'intval', preg_split( '/[\s,،]+/u', (string) $a['ids'] ) ) );
			if ( $ids ) {
				$args['post__in'] = $ids;
				$args['orderby']  = 'post__in';
			}
			break;
	}
	if ( (int) $a['offset'] > 0 ) {
		$args['offset'] = (int) $a['offset'] + ( max( 1, (int) $a['paged'] ) - 1 ) * $args['posts_per_page'];
	} else {
		$args['paged'] = max( 1, (int) $a['paged'] );
	}
	if ( $a['exclude'] ) {
		$args['post__not_in'] = array_map( 'intval', (array) $a['exclude'] );
	}
	return $args;
}

/**
 * Render one post card.
 *
 * @param \WP_Post $post Post.
 * @param array    $o    Options: layout (card|row|mini|overlay), excerpt (int words,
 *                       0 = none), show_cat, show_date, show_time, heading (tag),
 *                       img_size, class.
 */
function render_post_card( $post, $o = array() ) {
	$o = wp_parse_args( $o, array( 'layout' => 'card', 'excerpt' => 20, 'show_cat' => true, 'show_date' => true, 'show_time' => true, 'heading' => 'h3', 'img_size' => 'medium_large', 'class' => '' ) );
	$id      = $post->ID;
	$url     = get_permalink( $id );
	$title   = get_the_title( $id );
	$cat     = $o['show_cat'] ? post_primary_cat( $id ) : null;
	$tag     = in_array( $o['heading'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $o['heading'] : 'h3';
	$thumb   = get_the_post_thumbnail( $id, $o['img_size'], array( 'loading' => 'lazy', 'alt' => esc_attr( $title ) ) );
	$excerpt = '';
	if ( $o['excerpt'] > 0 ) {
		$raw     = has_excerpt( $id ) ? get_the_excerpt( $id ) : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		$excerpt = wp_trim_words( $raw, (int) $o['excerpt'], '…' );
	}
	$cls = 'pc pc-' . $o['layout'] . ( $o['class'] ? ' ' . $o['class'] : '' );
	?>
	<article class="<?php echo esc_attr( $cls ); ?>">
		<a class="pc-media" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true">
			<?php echo $thumb ? $thumb : '<span class="pc-ph">' . icon_svg( 'beaker', 36 ) . '</span>'; // phpcs:ignore ?>
		</a>
		<div class="pc-body">
			<?php if ( $cat ) : ?>
				<a class="pc-cat" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
			<?php endif; ?>
			<<?php echo $tag; // phpcs:ignore ?> class="pc-title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></<?php echo $tag; // phpcs:ignore ?>>
			<?php if ( $excerpt ) : ?><p class="pc-excerpt"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
			<?php if ( $o['show_date'] || $o['show_time'] ) : ?>
				<div class="pc-meta">
					<?php if ( $o['show_date'] ) : ?><span><?php echo icon_svg( 'clock', 13 ); // phpcs:ignore ?><time datetime="<?php echo esc_attr( get_the_date( 'c', $id ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y', $id ) ); ?></time></span><?php endif; ?>
					<?php if ( $o['show_time'] ) : ?><span><?php echo icon_svg( 'eye', 13 ); // phpcs:ignore ?><?php echo esc_html( sprintf( '%s دقیقه مطالعه', fa_num( reading_minutes( $post ) ) ) ); ?></span><?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * Numbered pagination links for a custom query, using a dedicated query
 * var so it works on static pages too.
 *
 * @param int    $total   Total pages.
 * @param int    $current Current page.
 * @param string $var     Query var name.
 * @param string $anchor  Fragment to jump back to the list.
 */
function render_blog_pagination( $total, $current, $var = 'bp', $anchor = '' ) {
	if ( $total < 2 ) {
		return;
	}
	$link = static function ( $n ) use ( $var, $anchor ) {
		$url = 1 === $n ? remove_query_arg( $var ) : add_query_arg( $var, $n );
		return esc_url( $url . ( $anchor ? '#' . $anchor : '' ) );
	};
	$pages = array_unique( array_filter( array( 1, $current - 1, $current, $current + 1, $total ), static function ( $n ) use ( $total ) {
		return $n >= 1 && $n <= $total;
	} ) );
	sort( $pages );
	echo '<nav class="pagination" aria-label="' . esc_attr__( 'صفحه‌بندی مقالات', 'baspar-elements' ) . '">';
	if ( $current > 1 ) {
		echo '<a href="' . $link( $current - 1 ) . '" rel="prev" aria-label="' . esc_attr__( 'صفحه قبل', 'baspar-elements' ) . '">' . icon_svg( 'arrow-right', 14 ) . '</a>'; // phpcs:ignore
	}
	$prev = 0;
	foreach ( $pages as $n ) {
		if ( $prev && $n > $prev + 1 ) {
			echo '<span class="ellipsis">…</span>';
		}
		if ( $n === $current ) {
			echo '<span class="is-on" aria-current="page">' . esc_html( fa_num( $n ) ) . '</span>';
		} else {
			echo '<a href="' . $link( $n ) . '">' . esc_html( fa_num( $n ) ) . '</a>'; // phpcs:ignore
		}
		$prev = $n;
	}
	if ( $current < $total ) {
		echo '<a href="' . $link( $current + 1 ) . '" rel="next" aria-label="' . esc_attr__( 'صفحه بعد', 'baspar-elements' ) . '">' . icon_svg( 'arrow', 14 ) . '</a>'; // phpcs:ignore
	}
	echo '</nav>';
}

/**
 * Give every h2/h3 in the content a stable id and return [html, toc items].
 * The id is reused when the heading (or a span inside it, as LuckyWP TOC
 * does) already has one. Results are cached per post for the request so
 * the content and TOC widgets agree.
 *
 * @param int    $post_id Post ID.
 * @param string $html    Rendered content.
 * @return array{0:string,1:array<int,array{id:string,text:string,level:int}>}
 */
function content_with_heading_ids( $post_id, $html ) {
	static $cache = array();
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}
	$items = array();
	$used  = array();
	$n     = 0;
	$html  = preg_replace_callback(
		'#<h([23])(\s[^>]*)?>(.*?)</h\1>#is',
		static function ( $m ) use ( &$items, &$used, &$n ) {
			$level = (int) $m[1];
			$attrs = isset( $m[2] ) ? $m[2] : '';
			$inner = $m[3];
			$text  = trim( html_entity_decode( wp_strip_all_tags( $inner ), ENT_QUOTES ) );
			if ( '' === $text ) {
				return $m[0];
			}
			++$n;
			if ( preg_match( '#\sid=(["\'])(.*?)\1#', $attrs, $idm ) ) {
				$id = $idm[2];
			} elseif ( preg_match( '#\sid=(["\'])(.*?)\1#', $inner, $idm ) ) {
				$id = $idm[2];
			} else {
				$id    = 'h-' . $n;
				$attrs = ' id="' . esc_attr( $id ) . '"' . $attrs;
			}
			if ( isset( $used[ $id ] ) ) {
				$id    = $id . '-' . $n;
				$attrs = preg_replace( '#\sid=(["\'])(.*?)\1#', '', $attrs );
				$attrs = ' id="' . esc_attr( $id ) . '"' . $attrs;
			}
			$used[ $id ] = true;
			$items[]     = array( 'id' => $id, 'text' => $text, 'level' => $level );
			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		},
		(string) $html
	);
	$cache[ $post_id ] = array( (string) $html, $items );
	return $cache[ $post_id ];
}

/**
 * Rendered content of a post (the_content filters applied once per request).
 *
 * @param \WP_Post $post Post.
 * @return string
 */
function rendered_post_content( $post ) {
	static $cache = array();
	static $busy  = false;
	if ( $busy ) {
		return ''; // Never re-enter (the_content inside the_content).
	}
	if ( ! isset( $cache[ $post->ID ] ) ) {
		$busy = true;
		$prev            = $GLOBALS['post'] ?? null;
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		$cache[ $post->ID ] = apply_filters( 'the_content', $post->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$GLOBALS['post']    = $prev; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( $prev instanceof \WP_Post ) {
			setup_postdata( $prev );
		}
		$busy = false;
	}
	return $cache[ $post->ID ];
}

/**
 * Posts already shown by blog widgets earlier on the page, so a later list
 * can skip them (e.g. "latest" below "featured").
 *
 * @param int[] $add IDs to register.
 * @return int[]
 */
function blog_shown_ids( $add = array() ) {
	static $ids = array();
	if ( $add ) {
		$ids = array_values( array_unique( array_merge( $ids, array_map( 'intval', (array) $add ) ) ) );
	}
	return $ids;
}

/**
 * Header row of a sidebar box (icon chip, title, optional "all" link).
 *
 * @param string $icon      Icon key (icon_svg).
 * @param string $title     Box title.
 * @param string $more_url  Optional "view all" URL.
 * @param string $more_text Optional "view all" label.
 */
function render_side_head( $icon, $title, $more_url = '', $more_text = '' ) {
	if ( '' === (string) $title ) {
		return;
	}
	echo '<div class="sbx-h"><span class="sbx-ic" aria-hidden="true">' . icon_svg( $icon ? $icon : 'layers', 18 ) . '</span><h3 class="sbx-t">' . esc_html( $title ) . '</h3>'; // phpcs:ignore
	if ( $more_url && $more_text ) {
		echo '<a class="sbx-more" href="' . esc_url( slash_url( $more_url ) ) . '">' . esc_html( $more_text ) . '</a>';
	}
	echo '</div>';
}
