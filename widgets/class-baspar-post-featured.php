<?php
/**
 * Featured articles — magazine layout: one large overlay card plus a list of
 * smaller cards. Source: sticky posts (falls back to most discussed),
 * latest, most discussed, a category or hand-picked IDs.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\blog_query_args;
use function BasparElements\blog_shown_ids;
use function BasparElements\post_cat_options;
use function BasparElements\post_primary_cat;
use function BasparElements\render_post_card;
use function BasparElements\reading_minutes;
use function BasparElements\fa_num;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Post_Featured extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-post-featured'; }
	public function get_title() { return __( 'بسپار — مقالات برگزیده (مجله‌ای)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-posts-masonry'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'منبع', 'baspar-elements' ) ) );
		$this->add_control( 'source', array(
			'label'       => __( 'کدام مقالات؟', 'baspar-elements' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'sticky',
			'options'     => array(
				'sticky'   => __( 'سنجاق‌شده‌ها (مهم)', 'baspar-elements' ),
				'latest'   => __( 'جدیدترین', 'baspar-elements' ),
				'popular'  => __( 'پربحث‌ترین (بیشترین دیدگاه)', 'baspar-elements' ),
				'category' => __( 'یک دسته', 'baspar-elements' ),
				'manual'   => __( 'انتخاب دستی (شناسه‌ها)', 'baspar-elements' ),
			),
			'description' => __( 'برای «مهم»، مقاله را در ویرایشگر وردپرس «سنجاق به بالای وبلاگ» کنید. اگر هیچ مقاله سنجاق‌شده‌ای نباشد، پربحث‌ترین‌ها نمایش داده می‌شوند.', 'baspar-elements' ),
		) );
		$this->add_control( 'category', array( 'label' => __( 'دسته', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => post_cat_options(), 'default' => '0', 'condition' => array( 'source' => 'category' ) ) );
		$this->add_control( 'ids', array( 'label' => __( 'شناسه مقاله‌ها (با ویرگول)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'placeholder' => '6636, 6649, 6664', 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 4, 'min' => 1, 'max' => 7 ) );
		$this->add_control( 'big_excerpt', array( 'label' => __( 'خلاصه مقاله اصلی', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'badge', array( 'label' => __( 'برچسب مقاله اصلی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'برگزیده' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$count = max( 1, min( 7, (int) $s['count'] ) );
		$args  = blog_query_args( array( 'source' => $s['source'], 'count' => $count, 'category' => $s['category'] ?? 0, 'ids' => $s['ids'] ?? '' ) );
		if ( 'sticky' === $s['source'] && empty( $args['post__in'] ) ) {
			$args = blog_query_args( array( 'source' => 'popular', 'count' => $count ) );
		}
		$args['no_found_rows'] = true;
		$posts = get_posts( $args + array( 'suppress_filters' => false ) );
		if ( ! $posts ) {
			return;
		}
		blog_shown_ids( wp_list_pluck( $posts, 'ID' ) );
		$big  = array_shift( $posts );
		$cat  = post_primary_cat( $big->ID );
		$img  = get_the_post_thumbnail_url( $big->ID, 'large' );
		?>
		<div class="baspar-scope">
			<div class="pf<?php echo $posts ? '' : ' pf-single'; ?> <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<article class="pf-big">
					<a class="pf-big-link" href="<?php echo esc_url( get_permalink( $big ) ); ?>">
						<span class="pf-big-media"><?php echo $img ? '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( get_the_title( $big ) ) . '" loading="lazy">' : ''; ?></span>
						<span class="pf-big-shade" aria-hidden="true"></span>
						<span class="pf-big-body">
							<span class="pf-tags">
								<?php if ( $s['badge'] ) : ?><span class="pf-badge"><?php echo icon_svg( 'star', 12 ); // phpcs:ignore ?><?php echo esc_html( $s['badge'] ); ?></span><?php endif; ?>
								<?php if ( $cat ) : ?><span class="pf-cat"><?php echo esc_html( $cat->name ); ?></span><?php endif; ?>
							</span>
							<span class="pf-big-title"><?php echo esc_html( get_the_title( $big ) ); ?></span>
							<?php if ( 'yes' === $s['big_excerpt'] ) : ?>
								<span class="pf-big-excerpt"><?php echo esc_html( wp_trim_words( has_excerpt( $big ) ? get_the_excerpt( $big ) : wp_strip_all_tags( strip_shortcodes( $big->post_content ) ), 26, '…' ) ); ?></span>
							<?php endif; ?>
							<span class="pf-big-meta">
								<span><?php echo icon_svg( 'clock', 14 ); // phpcs:ignore ?><?php echo esc_html( get_the_date( 'j F Y', $big ) ); ?></span>
								<span><?php echo icon_svg( 'eye', 14 ); // phpcs:ignore ?><?php echo esc_html( sprintf( '%s دقیقه مطالعه', fa_num( reading_minutes( $big ) ) ) ); ?></span>
								<span class="pf-big-go"><?php esc_html_e( 'خواندن مقاله', 'baspar-elements' ); ?><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></span>
							</span>
						</span>
					</a>
				</article>
				<?php if ( $posts ) : ?>
					<div class="pf-list">
						<?php foreach ( $posts as $p ) {
							render_post_card( $p, array( 'layout' => 'row', 'excerpt' => 0, 'img_size' => 'medium', 'show_time' => false ) );
						} ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
