<?php
/**
 * Related articles — posts from the current post's category (filled up with
 * the latest posts when the category is small), excluding the current one.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_blog_post;
use function BasparElements\post_primary_cat;
use function BasparElements\render_post_card;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Related_Posts extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-related-posts'; }
	public function get_title() { return __( 'بسپار — مقالات مرتبط', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-post-list'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 12 ) );
		$this->add_control( 'columns', array( 'label' => __( 'ستون‌ها', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => '3', 'options' => array( '2' => '2', '3' => '3', '4' => '4' ) ) );
		$this->add_control( 'match', array( 'label' => __( 'مرتبط بر اساس', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'title', 'options' => array( 'title' => __( 'کلمات عنوان + دسته', 'baspar-elements' ), 'category' => __( 'فقط دسته', 'baspar-elements' ) ) ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	/**
	 * Most significant word of the title (longest, ignoring short/stop words)
	 * — most articles here are "X چیست" so one keyword finds real siblings.
	 */
	private function title_keyword( $title ) {
		$stop  = array( 'چیست', 'چگونه', 'انواع', 'کاربرد', 'کاربردها', 'راهنمای', 'خرید', 'برای', 'تفاوت', 'مقایسه', 'کدام', 'انتخاب', 'بهترین', 'درباره' );
		$words = preg_split( '/[\s\-–—:،؟?!()«»]+/u', wp_strip_all_tags( $title ), -1, PREG_SPLIT_NO_EMPTY );
		$words = array_filter( $words, static function ( $w ) use ( $stop ) {
			return mb_strlen( $w, 'UTF-8' ) > 3 && ! in_array( $w, $stop, true );
		} );
		usort( $words, static function ( $a, $b ) {
			return mb_strlen( $b, 'UTF-8' ) - mb_strlen( $a, 'UTF-8' );
		} );
		return $words ? reset( $words ) : '';
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$post  = current_blog_post();
		if ( ! $post ) {
			return;
		}
		$count = max( 1, (int) $s['count'] );
		$seen  = array( $post->ID );
		$found = array();
		$base  = array( 'post_type' => 'post', 'post_status' => 'publish', 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'suppress_filters' => false );
		if ( 'title' === $s['match'] ) {
			$kw = $this->title_keyword( get_the_title( $post ) );
			if ( $kw ) {
				$found = get_posts( $base + array( 's' => $kw, 'posts_per_page' => $count, 'post__not_in' => $seen ) );
			}
		}
		$cat = post_primary_cat( $post->ID );
		if ( count( $found ) < $count && $cat ) {
			$seen  = array_merge( $seen, wp_list_pluck( $found, 'ID' ) );
			$found = array_merge( $found, get_posts( $base + array( 'cat' => $cat->term_id, 'posts_per_page' => $count - count( $found ), 'post__not_in' => $seen ) ) );
		}
		if ( count( $found ) < $count ) {
			$seen  = array_merge( $seen, wp_list_pluck( $found, 'ID' ) );
			$found = array_merge( $found, get_posts( $base + array( 'posts_per_page' => $count - count( $found ), 'post__not_in' => $seen ) ) );
		}
		if ( ! $found ) {
			return;
		}
		$cols = in_array( (string) $s['columns'], array( '2', '3', '4' ), true ) ? $s['columns'] : '3';
		?>
		<div class="baspar-scope">
			<div class="pg pg-card pg-cols-<?php echo esc_attr( $cols ); ?> <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php foreach ( $found as $p ) {
					render_post_card( $p, array( 'excerpt' => 14 ) );
				} ?>
			</div>
		</div>
		<?php
	}
}
