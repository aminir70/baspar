<?php
/**
 * Sidebar banner — an uploaded image (with link) or, without an image, a
 * designed gradient banner with eyebrow, title, text and button.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;
use function BasparElements\slash_url;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Side_Banner extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-side-banner'; }
	public function get_title() { return __( 'بسپار — کناری: بنر', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-banner'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'بنر', 'baspar-elements' ) ) );
		$this->add_control( 'image', array( 'label' => __( 'تصویر بنر (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA, 'description' => __( 'اگر تصویر بگذارید همان نمایش داده می‌شود؛ وگرنه بنر طراحی‌شده با متن زیر.', 'baspar-elements' ) ) );
		$this->add_control( 'alt', array( 'label' => __( 'متن جایگزین تصویر', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'image[url]!' => '' ) ) );
		$this->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/shop/' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'برچسب کوچک', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'SHOP', 'condition' => array( 'image[url]' => '' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'فروشگاه مواد اولیه شیمیایی', 'condition' => array( 'image[url]' => '' ) ) );
		$this->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => 'بیش از ۱۰۰ گرید موجود در انبار تهران؛ قیمت روز با استعلام.', 'condition' => array( 'image[url]' => '' ) ) );
		$this->add_control( 'btn', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده محصولات', 'condition' => array( 'image[url]' => '' ) ) );
		$this->add_control( 'tone', array( 'label' => __( 'رنگ بنر', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'purple', 'options' => array( 'purple' => __( 'بنفش', 'baspar-elements' ), 'dark' => __( 'تیره', 'baspar-elements' ), 'amber' => __( 'کهربایی', 'baspar-elements' ) ), 'condition' => array( 'image[url]' => '' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$url  = ! empty( $s['link']['url'] ) ? slash_url( $s['link']['url'] ) : '';
		$ext  = ! empty( $s['link']['is_external'] ) ? ' target="_blank" rel="noopener"' : '';
		$tag  = $url ? 'a' : 'div';
		$href = $url ? ' href="' . esc_url( $url ) . '"' . $ext : '';
		$img  = ! empty( $s['image']['url'] ) ? $s['image'] : null;
		$tone = in_array( $s['tone'] ?? 'purple', array( 'purple', 'dark', 'amber' ), true ) ? $s['tone'] : 'purple';
		?>
		<div class="baspar-scope">
			<?php if ( $img ) : ?>
				<<?php echo $tag; // phpcs:ignore ?> class="sbn sbn-img"<?php echo $href; // phpcs:ignore ?>>
					<img src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $s['alt'] ); ?>" loading="lazy">
				</<?php echo $tag; // phpcs:ignore ?>>
			<?php else : ?>
				<<?php echo $tag; // phpcs:ignore ?> class="sbn sbn-<?php echo esc_attr( $tone ); ?>"<?php echo $href; // phpcs:ignore ?>>
					<span class="sbn-deco" aria-hidden="true"></span>
					<?php if ( $s['eyebrow'] ) : ?><span class="sbn-eyebrow mono"><?php echo esc_html( $s['eyebrow'] ); ?></span><?php endif; ?>
					<?php if ( $s['title'] ) : ?><strong class="sbn-title"><?php echo esc_html( $s['title'] ); ?></strong><?php endif; ?>
					<?php if ( $s['text'] ) : ?><span class="sbn-text"><?php echo esc_html( $s['text'] ); ?></span><?php endif; ?>
					<?php if ( $s['btn'] ) : ?><span class="sbn-btn"><?php echo esc_html( $s['btn'] ); ?><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></span><?php endif; ?>
				</<?php echo $tag; // phpcs:ignore ?>>
			<?php endif; ?>
		</div>
		<?php
	}
}
