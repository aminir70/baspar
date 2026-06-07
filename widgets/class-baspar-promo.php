<?php
/**
 * Promo banner (yellow gradient) with title, text and a button.
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
 * Class Baspar_Promo
 */
class Baspar_Promo extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-promo';
	}

	public function get_title() {
		return __( 'بسپار — بنر تبلیغ', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'نیک بیاندیش، نیک بیاموز، نیک برگزین.' ) );
		$this->add_control( 'text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => 'راهنمای رایگان انتخاب گرید مناسب برای فرمولاسیون شما — تماس با کارشناس فنی.' ) );
		$this->add_control( 'btn_text', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'دریافت مشاوره' ) );
		$this->add_control( 'btn_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$this->add_control( 'btn_icon', array( 'label' => __( 'آیکون واتس‌اپ', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_background( 'bg', '.promo-banner' );
		$this->add_color( 'title_color', __( 'رنگ عنوان', 'baspar-elements' ), '.promo-banner h3', 'color' );
		$this->add_radius( 'radius', __( 'گردی گوشه', 'baspar-elements' ), '.promo-banner' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="promo-banner <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<div>
					<h3><?php echo esc_html( $s['title'] ); ?></h3>
					<p><?php echo esc_html( $s['text'] ); ?></p>
				</div>
				<?php if ( $s['btn_text'] ) : ?>
					<a href="<?php echo esc_url( $s['btn_link']['url'] ?? '#' ); ?>" class="promo-btn">
						<?php if ( 'yes' === $s['btn_icon'] ) { echo icon_svg( 'whatsapp', 16 ); /* phpcs:ignore */ } ?>
						<?php echo esc_html( $s['btn_text'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
