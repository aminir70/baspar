<?php
/**
 * Table of contents for the current post — built server-side from the
 * article's h2/h3 headings (same ids as the content widget), sticky in the
 * sidebar, highlights the section being read. Collapsible on phones.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_blog_post;
use function BasparElements\rendered_post_content;
use function BasparElements\content_with_heading_ids;
use function BasparElements\fa_num;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Post_Toc extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-post-toc'; }
	public function get_title() { return __( 'بسپار — فهرست مطالب مقاله', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-table-of-contents'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'فهرست مطالب' ) );
		$this->add_control( 'levels', array( 'label' => __( 'سطح سرفصل‌ها', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => '2', 'options' => array( '2' => 'H2', '3' => 'H2 + H3' ) ) );
		$this->add_control( 'collapsed', array( 'label' => __( 'در ابتدا بسته باشد', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'description' => __( 'برای نسخه موبایلِ بالای متن مناسب است.', 'baspar-elements' ) ) );
		$this->add_control( 'sticky', array( 'label' => __( 'چسبان هنگام اسکرول (دسکتاپ)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'prefix_class' => 'bspr-sticky-' ) );
		$this->add_control( 'offset', array( 'label' => __( 'فاصله از بالا (px)', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 100, 'condition' => array( 'sticky' => 'yes' ), 'selectors' => array( '{{WRAPPER}}' => '--sticky-top: {{VALUE}}px;' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$post = current_blog_post();
		if ( ! $post ) {
			return;
		}
		list( , $items ) = content_with_heading_ids( $post->ID, rendered_post_content( $post ) );
		$max   = '3' === (string) $s['levels'] ? 3 : 2;
		$items = array_values( array_filter( $items, static function ( $it ) use ( $max ) {
			return $it['level'] <= $max;
		} ) );
		if ( ! $items ) {
			return;
		}
		?>
		<div class="baspar-scope">
			<details class="bspr-toc"<?php echo 'yes' === ( $s['collapsed'] ?? '' ) ? '' : ' open'; ?> data-toc>
				<summary>
					<span class="bspr-toc-ic"><?php echo icon_svg( 'menu', 16 ); // phpcs:ignore ?></span>
					<span class="bspr-toc-title"><?php echo esc_html( $s['title'] ); ?></span>
					<span class="bspr-toc-count"><?php echo esc_html( fa_num( count( $items ) ) ); ?></span>
					<span class="bspr-toc-caret"><?php echo icon_svg( 'arrow-down', 14 ); // phpcs:ignore ?></span>
				</summary>
				<ol>
					<?php $n = 0; foreach ( $items as $it ) :
						if ( 2 === $it['level'] ) {
							++$n;
						}
						?>
						<li class="lvl-<?php echo esc_attr( $it['level'] ); ?>"><a href="#<?php echo esc_attr( $it['id'] ); ?>"><?php if ( 2 === $it['level'] ) : ?><span class="n"><?php echo esc_html( fa_num( $n ) ); ?></span><?php endif; ?><span class="t"><?php echo esc_html( $it['text'] ); ?></span></a></li>
					<?php endforeach; ?>
				</ol>
			</details>
		</div>
		<?php
	}
}
