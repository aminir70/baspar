<?php
/**
 * Single post hero — breadcrumb, category, H1, lead, author/date/reading
 * meta and the featured image. Reads the current post (or the latest post
 * while editing a theme-builder template).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_blog_post;
use function BasparElements\post_primary_cat;
use function BasparElements\reading_minutes;
use function BasparElements\fa_num;
use function BasparElements\icon_svg;
use function BasparElements\slash_url;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Post_Hero extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-post-hero'; }
	public function get_title() { return __( 'بسپار — هیرو مقاله (پویا)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-post-title'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'blog_label', array( 'label' => __( 'نام وبلاگ در مسیر', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'وبلاگ' ) );
		$this->add_control( 'blog_link', array( 'label' => __( 'لینک وبلاگ', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/blog/' ) ) );
		$this->add_control( 'show_lead', array( 'label' => __( 'خلاصه زیر عنوان', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'description' => __( 'از «چکیده» نوشته؛ اگر چکیده خالی باشد نمایش داده نمی‌شود.', 'baspar-elements' ) ) );
		$this->add_control( 'show_image', array( 'label' => __( 'تصویر شاخص', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_updated', array( 'label' => __( 'تاریخ به‌روزرسانی', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.ph-title' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$post = current_blog_post();
		if ( ! $post ) {
			return;
		}
		$id       = $post->ID;
		$cat      = post_primary_cat( $id );
		$author   = (int) $post->post_author;
		$pub      = get_the_date( 'j F Y', $id );
		$mod_ts   = get_post_modified_time( 'U', false, $id );
		$pub_ts   = get_post_time( 'U', false, $id );
		$updated  = ( 'yes' === $s['show_updated'] && $mod_ts - $pub_ts > DAY_IN_SECONDS ) ? get_the_modified_date( 'j F Y', $id ) : '';
		$comments = (int) get_comments_number( $id );
		$lead     = ( 'yes' === $s['show_lead'] && has_excerpt( $id ) ) ? get_the_excerpt( $id ) : '';
		$blog_url = ! empty( $s['blog_link']['url'] ) ? slash_url( $s['blog_link']['url'] ) : home_url( '/' );
		?>
		<div class="baspar-scope">
			<header class="ph">
				<div class="ph-bg" aria-hidden="true"></div>
				<div class="ph-inner">
					<nav class="ph-crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'baspar-elements' ); ?>">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'baspar-elements' ); ?></a>
						<span aria-hidden="true">/</span>
						<a href="<?php echo esc_url( $blog_url ); ?>"><?php echo esc_html( $s['blog_label'] ); ?></a>
						<?php if ( $cat ) : ?>
							<span aria-hidden="true">/</span>
							<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
						<?php endif; ?>
					</nav>
					<?php if ( $cat ) : ?><a class="ph-cat" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a><?php endif; ?>
					<h1 class="ph-title"><?php echo esc_html( get_the_title( $id ) ); ?></h1>
					<?php if ( $lead ) : ?><p class="ph-lead"><?php echo esc_html( $lead ); ?></p><?php endif; ?>
					<div class="ph-meta">
						<span class="ph-author">
							<?php echo get_avatar( $author, 44, '', '', array( 'class' => 'ph-avatar' ) ); // phpcs:ignore ?>
							<span><small><?php esc_html_e( 'نویسنده', 'baspar-elements' ); ?></small><strong><?php echo esc_html( get_the_author_meta( 'display_name', $author ) ); ?></strong></span>
						</span>
						<span class="ph-m"><?php echo icon_svg( 'clock', 16 ); // phpcs:ignore ?><span><small><?php esc_html_e( 'انتشار', 'baspar-elements' ); ?></small><time datetime="<?php echo esc_attr( get_the_date( 'c', $id ) ); ?>"><?php echo esc_html( $pub ); ?></time></span></span>
						<?php if ( $updated ) : ?><span class="ph-m"><?php echo icon_svg( 'refresh', 16 ); // phpcs:ignore ?><span><small><?php esc_html_e( 'به‌روزرسانی', 'baspar-elements' ); ?></small><time datetime="<?php echo esc_attr( get_the_modified_date( 'c', $id ) ); ?>"><?php echo esc_html( $updated ); ?></time></span></span><?php endif; ?>
						<span class="ph-m"><?php echo icon_svg( 'eye', 16 ); // phpcs:ignore ?><span><small><?php esc_html_e( 'زمان مطالعه', 'baspar-elements' ); ?></small><strong><?php echo esc_html( sprintf( '%s دقیقه', fa_num( reading_minutes( $post ) ) ) ); ?></strong></span></span>
						<?php if ( $comments ) : ?><a class="ph-m" href="#comments"><?php echo icon_svg( 'chat', 16 ); // phpcs:ignore ?><span><small><?php esc_html_e( 'دیدگاه', 'baspar-elements' ); ?></small><strong><?php echo esc_html( fa_num( $comments ) ); ?></strong></span></a><?php endif; ?>
					</div>
					<?php if ( 'yes' === $s['show_image'] && has_post_thumbnail( $id ) ) : ?>
						<figure class="ph-figure"><?php echo get_the_post_thumbnail( $id, 'full', array( 'class' => 'ph-img', 'fetchpriority' => 'high', 'loading' => 'eager' ) ); // phpcs:ignore ?></figure>
					<?php endif; ?>
				</div>
			</header>
		</div>
		<?php
	}
}
