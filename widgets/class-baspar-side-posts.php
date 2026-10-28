<?php
/**
 * Sidebar box — latest articles (small thumbnail, title, date).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\blog_query_args;
use function BasparElements\current_blog_post;
use function BasparElements\post_cat_options;
use function BasparElements\render_side_head;
use function BasparElements\fa_num;
use function BasparElements\reading_minutes;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Side_Posts extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-side-posts'; }
	public function get_title() { return __( 'بسپار — کناری: آخرین نوشته‌ها', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-post-list'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'آخرین نوشته‌ها' ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 5, 'min' => 1, 'max' => 12 ) );
		$this->add_control( 'category', array( 'label' => __( 'دسته (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => post_cat_options(), 'default' => '0' ) );
		$this->add_control( 'exclude_current', array( 'label' => __( 'مقاله فعلی نمایش داده نشود', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_time', array( 'label' => __( 'زمان مطالعه', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'more_text', array( 'label' => __( 'متن «همه»', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'همه' ) );
		$this->add_control( 'more_link', array( 'label' => __( 'لینک «همه»', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/blog/' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$args = blog_query_args( array( 'source' => ! empty( $s['category'] ) && '0' !== (string) $s['category'] ? 'category' : 'latest', 'count' => $s['count'], 'category' => $s['category'] ) );
		$cur  = ( 'yes' === $s['exclude_current'] ) ? current_blog_post() : null;
		if ( $cur ) {
			$args['post__not_in'] = array( $cur->ID );
		}
		$args['no_found_rows'] = true;
		$posts = get_posts( $args + array( 'suppress_filters' => false ) );
		if ( ! $posts ) {
			return;
		}
		?>
		<div class="baspar-scope">
			<section class="sbx">
				<?php render_side_head( 'layers', $s['title'], $s['more_link']['url'] ?? '', $s['more_text'] ); ?>
				<ul class="sp-list">
					<?php foreach ( $posts as $p ) :
						$thumb = get_the_post_thumbnail( $p->ID, 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) );
						?>
						<li class="sp-item">
							<a class="sp-thumb" href="<?php echo esc_url( get_permalink( $p ) ); ?>" tabindex="-1" aria-hidden="true"><?php echo $thumb ? $thumb : icon_svg( 'beaker', 22 ); // phpcs:ignore ?></a>
							<div class="sp-body">
								<a class="sp-title" href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a>
								<span class="sp-meta"><time datetime="<?php echo esc_attr( get_the_date( 'c', $p ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y', $p ) ); ?></time><?php if ( 'yes' === $s['show_time'] ) : ?><i aria-hidden="true"></i><?php echo esc_html( sprintf( '%s دقیقه', fa_num( reading_minutes( $p ) ) ) ); ?><?php endif; ?></span>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		</div>
		<?php
	}
}
