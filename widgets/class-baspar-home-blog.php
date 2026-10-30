<?php
/**
 * Home blog — section heading plus the latest articles as a bento: one large
 * cover card and a stack of compact cards, with staggered entrance.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\home_head_controls;
use function BasparElements\render_home_head;
use function BasparElements\render_post_card;
use function BasparElements\blog_shown_ids;
use function BasparElements\post_cat_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Home_Blog extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-home-blog'; }
	public function get_title() { return __( 'بسپار — مطالب وبلاگ (صفحه اصلی ۲)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-posts-masonry'; }

	protected function register_controls() {
		$this->start_controls_section( 'head', array( 'label' => __( 'عنوان بخش', 'baspar-elements' ) ) );
		home_head_controls( $this, array(
			'kicker'    => 'BASPAR JOURNAL',
			'title'     => 'مطالب',
			'highlight' => 'وبلاگ',
			'sub'       => 'آموزش، مقایسه و راهنمای خرید مواد اولیه؛ هر هفته یک مطلب تازه از کارشناسان بسپارمارکت.',
			'link_text' => 'همه مقالات',
			'link'      => '/blog/',
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'query', array( 'label' => __( 'مقالات', 'baspar-elements' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 4, 'min' => 1, 'max' => 7 ) );
		$this->add_control( 'category', array( 'label' => __( 'دسته', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => post_cat_options(), 'default' => '0' ) );
		$this->add_control( 'excerpt', array( 'label' => __( 'تعداد کلمات خلاصه کارت بزرگ', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 26, 'min' => 0 ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$args  = array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => max( 1, min( 7, (int) $s['count'] ) ), 'ignore_sticky_posts' => true, 'no_found_rows' => true );
		if ( (int) $s['category'] ) {
			$args['cat'] = (int) $s['category'];
		}
		$q = new \WP_Query( $args );
		if ( ! $q->posts ) {
			return;
		}
		blog_shown_ids( wp_list_pluck( $q->posts, 'ID' ) );
		$first = array_shift( $q->posts );
		?>
		<div class="baspar-scope">
			<section class="hm-sec hbl" data-anim-group>
				<?php render_home_head( $s ); ?>
				<div class="hbl-grid<?php echo $q->posts ? '' : ' is-single'; ?>">
					<div class="hbl-main" data-anim="left"><?php render_post_card( $first, array( 'layout' => 'overlay', 'excerpt' => (int) $s['excerpt'], 'heading' => 'h3', 'img_size' => 'large' ) ); ?></div>
					<?php if ( $q->posts ) : ?>
						<div class="hbl-list">
							<?php foreach ( $q->posts as $k => $p ) : ?>
								<div class="hbl-item" data-anim="up" style="--i:<?php echo (int) $k + 1; ?>"><?php render_post_card( $p, array( 'layout' => 'row', 'excerpt' => 0, 'heading' => 'h3', 'img_size' => 'medium', 'show_time' => false ) ); ?></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</div>
		<?php
		wp_reset_postdata();
	}
}
