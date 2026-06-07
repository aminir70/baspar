<?php
/**
 * Dynamic blog posts grid. Reads posts live via WP_Query, optionally filtered
 * by category. Card shows thumbnail (or icon), category pill, date badge and
 * excerpt.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Blog_Posts
 */
class Baspar_Blog_Posts extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-blog-posts';
	}

	public function get_title() {
		return __( 'بسپار — مقالات وبلاگ (پویا)', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	private function cat_options() {
		$opts = array( '0' => __( 'همه دسته‌ها', 'baspar-elements' ) );
		$terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$opts[ $t->term_id ] = $t->name;
			}
		}
		return $opts;
	}

	protected function register_controls() {
		$this->start_controls_section( 'query', array( 'label' => __( 'منبع مقالات', 'baspar-elements' ) ) );
		$this->add_control( 'category', array( 'label' => __( 'دسته‌بندی', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => $this->cat_options(), 'default' => '0' ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 3 ) );
		$this->add_control(
			'columns',
			array(
				'label'   => __( 'ستون‌ها', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '3',
				'options' => array( '2' => '2', '3' => '3' ),
			)
		);
		$this->add_control( 'read_more', array( 'label' => __( 'متن «ادامه»', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مطالعه مقاله' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.blog-body h3' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $s['count'] ),
			'ignore_sticky_posts' => true,
		);
		if ( ! empty( $s['category'] ) && '0' !== $s['category'] ) {
			$args['cat'] = (int) $s['category'];
		}
		$q = new \WP_Query( $args );

		if ( ! $q->have_posts() ) {
			echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'مقاله‌ای یافت نشد.', 'baspar-elements' ) . '</div>';
			return;
		}
		?>
		<div class="baspar-scope">
			<div class="blog-grid cols-<?php echo esc_attr( $s['columns'] ); ?>">
				<?php
				$i = 0;
				while ( $q->have_posts() ) :
					$q->the_post();
					$cats     = get_the_category();
					$cat_name = ( $cats && ! is_wp_error( $cats ) ) ? strtoupper( $cats[0]->slug ) : '';
					$day      = get_the_date( 'j' );
					$month    = get_the_date( 'F' );
					$thumb    = get_the_post_thumbnail( get_the_ID(), 'medium_large', array( 'loading' => 'lazy' ) );
					?>
					<a href="<?php the_permalink(); ?>" class="blog-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 60 ); ?>">
						<div class="blog-thumb">
							<?php if ( $cat_name ) : ?><span class="blog-cat-pill"><?php echo esc_html( $cat_name ); ?></span><?php endif; ?>
							<?php echo $thumb ? $thumb : icon_svg( 'layers', 42 ); // phpcs:ignore ?>
							<div class="blog-date">
								<strong><?php echo esc_html( $day ); ?></strong>
								<span><?php echo esc_html( $month ); ?></span>
							</div>
						</div>
						<div class="blog-body">
							<h3><?php the_title(); ?></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							<div class="blog-meta">
								<span><?php echo esc_html( get_the_author() ); ?></span>
								<span class="read-more"><?php echo esc_html( $s['read_more'] ); ?><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></span>
							</div>
						</div>
					</a>
					<?php
					++$i;
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
		<?php
	}
}
