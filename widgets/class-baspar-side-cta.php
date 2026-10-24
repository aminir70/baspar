<?php
/**
 * Sidebar CTA card — short title, text and a button (WhatsApp by default).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;
use function BasparElements\slash_url;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Side_Cta extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-side-cta'; }
	public function get_title() { return __( 'بسپار — کارت CTA کناری', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-call-to-action'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'NEED A QUOTE?' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'این ماده را برای تولید لازم دارید؟' ) );
		$this->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => 'نام محصول، گرید و مقدار را بفرستید؛ پیش‌فاکتور با قیمت روز برایتان می‌آید.' ) );
		$this->add_control( 'btn', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام در واتس‌اپ' ) );
		$this->add_control( 'link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$this->add_control( 'icon', array( 'label' => __( 'آیکون دکمه', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'whatsapp', 'options' => array( 'whatsapp' => 'واتس‌اپ', 'phone' => 'تلفن', 'cart' => 'سبد', 'mail' => 'ایمیل' ) ) );
		$this->add_control( 'sticky', array( 'label' => __( 'چسبان هنگام اسکرول (دسکتاپ)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'prefix_class' => 'bspr-sticky-' ) );
		$this->add_control( 'btn2', array( 'label' => __( 'لینک دوم (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده فروشگاه' ) );
		$this->add_control( 'link2', array( 'label' => __( 'آدرس لینک دوم', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/shop/' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_background( 'bg', '.scta' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<aside class="scta">
				<span class="scta-glow" aria-hidden="true"></span>
				<?php if ( $s['kicker'] ) : ?><span class="scta-kicker mono"><?php echo esc_html( $s['kicker'] ); ?></span><?php endif; ?>
				<?php if ( $s['title'] ) : ?><strong class="scta-title"><?php echo esc_html( $s['title'] ); ?></strong><?php endif; ?>
				<?php if ( $s['text'] ) : ?><p><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
				<?php if ( $s['btn'] && ! empty( $s['link']['url'] ) ) : ?>
					<a class="btn btn-white scta-btn" href="<?php echo esc_url( slash_url( $s['link']['url'] ) ); ?>"><?php echo icon_svg( $s['icon'], 18 ); // phpcs:ignore ?><?php echo esc_html( $s['btn'] ); ?></a>
				<?php endif; ?>
				<?php if ( $s['btn2'] && ! empty( $s['link2']['url'] ) ) : ?>
					<a class="scta-link" href="<?php echo esc_url( slash_url( $s['link2']['url'] ) ); ?>"><?php echo esc_html( $s['btn2'] ); ?><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></a>
				<?php endif; ?>
			</aside>
		</div>
		<?php
	}
}
