<?php
/**
 * Article body — the post content with the design's reading typography
 * (headings, tables, quotes, lists, images), stable heading ids for the
 * table of contents, optional reading-progress bar and tag list.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_blog_post;
use function BasparElements\rendered_post_content;
use function BasparElements\content_with_heading_ids;
use function BasparElements\normalize_content_html;
use function BasparElements\wrap_content_tables;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Post_Content extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-post-content'; }
	public function get_title() { return __( 'بسپار — متن مقاله (پویا)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-post-content'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'progress', array( 'label' => __( 'نوار پیشرفت مطالعه (بالای صفحه)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'hide_plugin_toc', array( 'label' => __( 'مخفی کردن فهرست مطالب افزونه‌ها داخل متن', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'description' => __( 'وقتی ویجت «فهرست مطالب» بسپار در کنار متن هست، فهرست تکراری LuckyWP / Easy TOC داخل متن پنهان می‌شود.', 'baspar-elements' ) ) );
		$this->add_control( 'normalize', array( 'label' => __( 'یکدست کردن ظاهر متن', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'description' => __( 'رنگ، اندازه و پس‌زمینه‌های درون‌خطی که هنگام کپی متن آمده‌اند (لینک آبی، تیتر ۱۸pt، ردیف زرد جدول و...) حذف می‌شوند تا متن با طراحی سایت هماهنگ شود. پاراگراف‌های خالی هم حذف می‌شوند. چیزی در خود نوشته تغییر نمی‌کند.', 'baspar-elements' ) ) );
		$this->add_control( 'show_tags', array( 'label' => __( 'برچسب‌ها در انتهای مقاله', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'body_typo', __( 'تایپوگرافی متن', 'baspar-elements' ), '.bspr-article' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$post = current_blog_post();
		if ( ! $post ) {
			return;
		}
		list( $html ) = content_with_heading_ids( $post->ID, rendered_post_content( $post ) );
		if ( 'yes' === ( $s['normalize'] ?? 'yes' ) ) {
			$html = normalize_content_html( $html );
		}
		$html = wrap_content_tables( $html );
		$cls = 'bspr-article' . ( 'yes' === $s['hide_plugin_toc'] ? ' hide-plugin-toc' : '' );
		?>
		<div class="baspar-scope">
			<?php if ( 'yes' === $s['progress'] ) : ?><div class="bspr-progress" aria-hidden="true"><span></span></div><?php endif; ?>
			<div class="<?php echo esc_attr( $cls ); ?>" data-article>
				<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- post content after the_content filters. ?>
			</div>
			<?php
			$tags = 'yes' === $s['show_tags'] ? get_the_tags( $post->ID ) : false;
			if ( $tags && ! is_wp_error( $tags ) ) :
				?>
				<div class="bspr-tags"><span><?php esc_html_e( 'برچسب‌ها:', 'baspar-elements' ); ?></span>
					<?php foreach ( $tags as $t ) : ?><a href="<?php echo esc_url( get_tag_link( $t ) ); ?>">#<?php echo esc_html( $t->name ); ?></a><?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
