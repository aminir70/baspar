<?php
/**
 * Product description (the product's long text) with the article
 * typography of the blog, optional table of contents, cleaned-up pasted
 * styling and scrollable tables.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_product;
use function BasparElements\rendered_post_content;
use function BasparElements\content_with_heading_ids;
use function BasparElements\normalize_content_html;
use function BasparElements\wrap_content_tables;
use function BasparElements\fa_num;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Pd_Description extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-pd-description'; }
	public function get_title() { return __( 'بسپار — محصول: توضیحات کامل', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-post-content'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان بخش', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'معرفی و توضیحات محصول' ) );
		$this->add_control( 'title_tag', array( 'label' => __( 'تگ عنوان بخش', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'div', 'options' => array( 'div' => 'div', 'h2' => 'H2', 'h3' => 'H3' ), 'description' => __( 'متن محصول خودش H2 دارد؛ برای سئو بهتر است این عنوان تکراری H2 نباشد.', 'baspar-elements' ) ) );
		$this->add_control( 'anchor', array( 'label' => __( 'شناسه بخش', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'pd-desc' ) );
		$this->add_control( 'toc', array( 'label' => __( 'فهرست مطالب بالای متن', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'normalize', array( 'label' => __( 'یکدست کردن ظاهر متن', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'description' => __( 'رنگ‌ها و پس‌زمینه‌های درون‌خطی کپی‌شده حذف می‌شوند؛ چیزی در خود محصول تغییر نمی‌کند.', 'baspar-elements' ) ) );
		$this->add_control( 'collapse', array( 'label' => __( 'جمع کردن متن طولانی («ادامه مطلب»)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'body_typo', __( 'تایپوگرافی متن', 'baspar-elements' ), '.bspr-article' );
		$this->end_controls_section();
	}

	protected function render() {
		$product = current_product();
		if ( ! $product ) {
			return;
		}
		$post = get_post( $product->get_id() );
		if ( ! $post || '' === trim( $post->post_content ) ) {
			return;
		}
		$s = $this->get_settings_for_display();
		list( $html, $items ) = content_with_heading_ids( $post->ID, rendered_post_content( $post ) );
		if ( 'yes' === $s['normalize'] ) {
			$html = normalize_content_html( $html );
		}
		$html   = wrap_content_tables( $html );
		$anchor = sanitize_html_class( $s['anchor'] );
		$h2     = array_values( array_filter( $items, static function ( $it ) {
			return 2 === $it['level'];
		} ) );
		$tid    = 'pdd-' . $this->get_id();
		?>
		<div class="baspar-scope"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<section class="pdd">
				<?php if ( $s['title'] ) : $tt = in_array( $s['title_tag'] ?? 'div', array( 'div', 'h2', 'h3' ), true ) ? $s['title_tag'] : 'div'; ?><<?php echo $tt; // phpcs:ignore ?> class="pd-h"><span class="pd-h-ic"><?php echo icon_svg( 'doc', 18 ); // phpcs:ignore ?></span><?php echo esc_html( $s['title'] ); ?></<?php echo $tt; // phpcs:ignore ?>><?php endif; ?>
				<?php if ( 'yes' === $s['toc'] && count( $h2 ) > 2 ) : ?>
					<details class="bspr-toc pdd-toc" data-toc>
						<summary><span class="bspr-toc-ic"><?php echo icon_svg( 'menu', 16 ); // phpcs:ignore ?></span><span class="bspr-toc-title"><?php esc_html_e( 'فهرست مطالب', 'baspar-elements' ); ?></span><span class="bspr-toc-count"><?php echo esc_html( fa_num( count( $h2 ) ) ); ?></span><span class="bspr-toc-caret"><?php echo icon_svg( 'arrow-down', 14 ); // phpcs:ignore ?></span></summary>
						<ol><?php foreach ( $h2 as $k => $it ) : ?><li class="lvl-2"><a href="#<?php echo esc_attr( $it['id'] ); ?>"><span class="n"><?php echo esc_html( fa_num( $k + 1 ) ); ?></span><span class="t"><?php echo esc_html( $it['text'] ); ?></span></a></li><?php endforeach; ?></ol>
					</details>
				<?php endif; ?>
				<?php if ( 'yes' === $s['collapse'] ) : ?><input type="checkbox" class="pdd-toggle" id="<?php echo esc_attr( $tid ); ?>"><?php endif; ?>
				<div class="bspr-article hide-plugin-toc<?php echo 'yes' === $s['collapse'] ? ' pdd-clamp' : ''; ?>" data-article><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- product content after the_content filters. ?></div>
				<?php if ( 'yes' === $s['collapse'] ) : ?><label class="pdd-more" for="<?php echo esc_attr( $tid ); ?>"><span data-more><?php esc_html_e( 'ادامه مطلب', 'baspar-elements' ); ?></span><span data-less><?php esc_html_e( 'بستن', 'baspar-elements' ); ?></span></label><?php endif; ?>
			</section>
		</div>
		<?php
	}
}
