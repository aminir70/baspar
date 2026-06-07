<?php
/**
 * Blog sidebar — search + categories list + popular posts + newsletter.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Blog_Sidebar extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-blog-sidebar'; }
	public function get_title() { return __( 'بسپار — سایدبار وبلاگ', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-sidebar'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'show_search', array( 'label' => __( 'جستجو', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_cats', array( 'label' => __( 'دسته‌بندی‌ها', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_popular', array( 'label' => __( 'پربازدیدترین', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_newsletter', array( 'label' => __( 'خبرنامه', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'popular_count', array( 'label' => __( 'تعداد پربازدید', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 4 ) );
		$this->add_control( 'newsletter_title', array( 'label' => __( 'عنوان خبرنامه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'عضو خبرنامه شوید' ) );
		$this->add_control( 'newsletter_desc', array( 'label' => __( 'متن خبرنامه', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => 'مقالات و تخفیف‌ها را در ایمیل دریافت کنید.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$cats = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'number' => 8 ) );
		$popular = new \WP_Query( array(
			'post_type' => 'post', 'posts_per_page' => max( 1, (int) $s['popular_count'] ),
			'orderby' => 'comment_count', 'order' => 'DESC',
		) );
		?>
		<div class="baspar-scope">
			<aside class="blog-side">
				<?php if ( 'yes' === $s['show_search'] ) : ?>
					<div class="blog-side-box">
						<h4><?php echo icon_svg( 'search', 16 ); // phpcs:ignore ?> جستجو</h4>
						<form class="blog-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
							<input type="search" name="s" placeholder="در مقالات جستجو کنید..." />
							<?php echo icon_svg( 'search', 16 ); // phpcs:ignore ?>
						</form>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_cats'] && ! is_wp_error( $cats ) && $cats ) : ?>
					<div class="blog-side-box">
						<h4><?php echo icon_svg( 'layers', 16 ); // phpcs:ignore ?> دسته‌بندی‌ها</h4>
						<ul class="blog-categories">
							<?php foreach ( $cats as $c ) : ?>
								<li><a href="<?php echo esc_url( get_term_link( $c ) ); ?>"><span><?php echo esc_html( $c->name ); ?></span><span class="count"><?php echo esc_html( $c->count ); ?></span></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_popular'] && $popular->have_posts() ) : ?>
					<div class="blog-side-box">
						<h4><?php echo icon_svg( 'eye', 16 ); // phpcs:ignore ?> پربازدیدترین</h4>
						<div class="blog-pop-list">
							<?php while ( $popular->have_posts() ) : $popular->the_post(); ?>
								<a href="<?php the_permalink(); ?>" class="bpop">
									<div class="bpop-thumb"><?php echo icon_svg( 'doc', 24 ); // phpcs:ignore ?></div>
									<div class="bpop-body">
										<strong><?php echo esc_html( wp_trim_words( get_the_title(), 8 ) ); ?></strong>
										<span><?php echo esc_html( get_the_date( 'j F' ) ); ?></span>
									</div>
								</a>
							<?php endwhile; wp_reset_postdata(); ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_newsletter'] ) : ?>
					<div class="blog-side-box" style="background:linear-gradient(135deg,var(--brand) 0%,var(--brand-dk) 100%);color:#fff;border:0">
						<strong style="font-size:16px;color:#fff;display:block;margin-bottom:8px"><?php echo esc_html( $s['newsletter_title'] ); ?></strong>
						<p style="font-size:12.5px;color:#E5D4FF;line-height:1.7;margin:0 0 14px"><?php echo esc_html( $s['newsletter_desc'] ); ?></p>
						<input type="email" placeholder="email@example.com" style="width:100%;padding:11px 14px;border:0;border-radius:8px;font-family:inherit;font-size:13px;margin-bottom:8px" />
						<button type="button" style="width:100%;padding:11px;background:#fff;color:var(--brand);border:0;border-radius:8px;font-weight:700;cursor:pointer">عضویت</button>
					</div>
				<?php endif; ?>
			</aside>
		</div>
		<?php
	}
}
