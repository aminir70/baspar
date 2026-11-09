<?php
/**
 * Article list — grid or row cards with optional pagination. Can follow the
 * URL (?bq= keyword, ?bcat= category slug) so the blog hero search and topic
 * links filter it in place.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\blog_query_args;
use function BasparElements\blog_shown_ids;
use function BasparElements\post_cat_options;
use function BasparElements\render_post_card;
use function BasparElements\render_blog_pagination;
use function BasparElements\icon_svg;
use function BasparElements\fa_num;
use function BasparElements\slash_url;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Post_Grid extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-post-grid'; }
	public function get_title() { return __( 'بسپار — لیست مقالات', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-posts-grid'; }

	protected function register_controls() {
		$this->start_controls_section( 'query', array( 'label' => __( 'منبع', 'baspar-elements' ) ) );
		$this->add_control( 'source', array(
			'label'   => __( 'کدام مقالات؟', 'baspar-elements' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'latest',
			'options' => array(
				'latest'   => __( 'جدیدترین', 'baspar-elements' ),
				'category' => __( 'یک دسته', 'baspar-elements' ),
				'keyword'  => __( 'موضوع (کلیدواژه)', 'baspar-elements' ),
				'popular'  => __( 'پربحث‌ترین', 'baspar-elements' ),
				'manual'   => __( 'انتخاب دستی (شناسه‌ها)', 'baspar-elements' ),
			),
		) );
		$this->add_control( 'category', array( 'label' => __( 'دسته', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => post_cat_options(), 'default' => '0', 'condition' => array( 'source' => 'category' ) ) );
		$this->add_control( 'keyword', array( 'label' => __( 'کلیدواژه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'placeholder' => 'رزین', 'condition' => array( 'source' => 'keyword' ) ) );
		$this->add_control( 'ids', array( 'label' => __( 'شناسه مقاله‌ها', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد در هر صفحه', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 9, 'min' => 1 ) );
		$this->add_control( 'exclude_shown', array( 'label' => __( 'حذف مقالاتی که بالاتر در صفحه آمده‌اند', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'follow_url', array( 'label' => __( 'فیلتر با جستجو / موضوع صفحه', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'description' => __( 'روشن: پارامترهای ?bq= (جستجو) و ?bcat= (نامک دسته) روی این لیست اعمال می‌شود. فقط برای لیست اصلی صفحه روشن کنید.', 'baspar-elements' ) ) );
		$this->add_control( 'pagination', array( 'label' => __( 'صفحه‌بندی', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'anchor', array( 'label' => __( 'شناسه بخش (anchor)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'layout', array( 'label' => __( 'چیدمان', 'baspar-elements' ) ) );
		$this->add_control( 'layout', array( 'label' => __( 'نوع کارت', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'card', 'options' => array( 'card' => __( 'کارت عمودی', 'baspar-elements' ), 'row' => __( 'ردیفی (تصویر کنار متن)', 'baspar-elements' ) ) ) );
		$this->add_control( 'columns', array( 'label' => __( 'ستون‌ها', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => '3', 'options' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ) ) );
		$this->add_control( 'excerpt', array( 'label' => __( 'تعداد کلمات خلاصه (۰ = بدون خلاصه)', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 18, 'min' => 0 ) );
		$this->add_control( 'show_cat', array( 'label' => __( 'دسته', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_date', array( 'label' => __( 'تاریخ', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_time', array( 'label' => __( 'زمان مطالعه', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'more_text', array( 'label' => __( 'دکمه «همه مقالات» (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'more_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان کارت', 'baspar-elements' ), '.pc-title' );
		$this->end_controls_section();
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$follow  = 'yes' === $s['follow_url'];
		$paged   = ( 'yes' === $s['pagination'] && isset( $_GET['bp'] ) ) ? max( 1, (int) $_GET['bp'] ) : 1; // phpcs:ignore
		$keyword = $follow && isset( $_GET['bq'] ) ? sanitize_text_field( wp_unslash( $_GET['bq'] ) ) : ''; // phpcs:ignore
		$catslug = $follow && isset( $_GET['bcat'] ) ? sanitize_title( wp_unslash( $_GET['bcat'] ) ) : ''; // phpcs:ignore
		$a       = array( 'source' => $s['source'], 'count' => $s['count'], 'category' => $s['category'] ?? 0, 'keyword' => $s['keyword'] ?? '', 'ids' => $s['ids'] ?? '', 'paged' => $paged );
		$filtered = false;
		if ( '' !== $keyword ) {
			$a['source']  = 'keyword';
			$a['keyword'] = $keyword;
			$filtered     = true;
		} elseif ( '' !== $catslug && ( $term = get_category_by_slug( $catslug ) ) ) { // phpcs:ignore
			$a['source']   = 'category';
			$a['category'] = $term->term_id;
			$filtered      = true;
		}
		if ( 'yes' === $s['exclude_shown'] && ! $filtered ) {
			$a['exclude'] = blog_shown_ids();
		}
		$q      = new \WP_Query( blog_query_args( $a ) );
		$anchor = sanitize_html_class( $s['anchor'] );
		$cols   = in_array( (string) $s['columns'], array( '1', '2', '3', '4' ), true ) ? $s['columns'] : '3';
		?>
		<div class="baspar-scope"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<?php if ( $filtered ) : ?>
				<div class="pg-filter">
					<span><?php echo icon_svg( 'search', 16 ); // phpcs:ignore ?>
						<?php
						if ( '' !== $keyword ) {
							/* translators: 1: keyword, 2: count */
							printf( esc_html__( 'نتایج «%1$s»: %2$s مقاله', 'baspar-elements' ), esc_html( $keyword ), esc_html( fa_num( $q->found_posts ) ) );
						} else {
							/* translators: 1: category, 2: count */
							printf( esc_html__( 'دسته «%1$s»: %2$s مقاله', 'baspar-elements' ), esc_html( $term->name ), esc_html( fa_num( $q->found_posts ) ) );
						}
						?>
					</span>
					<a href="<?php echo esc_url( remove_query_arg( array( 'bq', 'bcat', 'bp' ) ) . ( $anchor ? '#' . $anchor : '' ) ); ?>"><?php echo icon_svg( 'x', 14 ); // phpcs:ignore ?><?php esc_html_e( 'نمایش همه', 'baspar-elements' ); ?></a>
				</div>
			<?php endif; ?>

			<?php if ( $q->have_posts() ) : ?>
				<div class="pg pg-<?php echo esc_attr( $s['layout'] ); ?> pg-cols-<?php echo esc_attr( $cols ); ?> <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
					<?php foreach ( $q->posts as $p ) {
						render_post_card( $p, array(
							'layout'    => 'row' === $s['layout'] ? 'row' : 'card',
							'excerpt'   => (int) $s['excerpt'],
							'show_cat'  => 'yes' === $s['show_cat'],
							'show_date' => 'yes' === $s['show_date'],
							'show_time' => 'yes' === $s['show_time'],
						) );
					} ?>
				</div>
				<?php
				blog_shown_ids( wp_list_pluck( $q->posts, 'ID' ) );
				if ( 'yes' === $s['pagination'] ) {
					render_blog_pagination( (int) $q->max_num_pages, $paged, 'bp', $anchor );
				} elseif ( ! empty( $s['more_text'] ) && ! empty( $s['more_link']['url'] ) ) {
					echo '<div class="pg-more"><a class="btn btn-ghost" href="' . esc_url( slash_url( $s['more_link']['url'] ) ) . '">' . esc_html( $s['more_text'] ) . icon_svg( 'arrow', 14 ) . '</a></div>'; // phpcs:ignore
				}
				?>
			<?php else : ?>
				<div class="pg-empty"><?php echo icon_svg( 'search', 28 ); // phpcs:ignore ?><p><?php esc_html_e( 'مقاله‌ای با این مشخصات پیدا نشد. کلیدواژه دیگری را امتحان کنید.', 'baspar-elements' ); ?></p></div>
			<?php endif; ?>
		</div>
		<?php
		wp_reset_postdata();
	}
}
