<?php
/**
 * Contact map. Either an animated graphic pin (design style) or a real
 * embedded iframe (Google Maps / Neshan) via the embed URL.
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
 * Class Baspar_Contact_Map
 */
class Baspar_Contact_Map extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-contact-map';
	}

	public function get_title() {
		return __( 'بسپار — نقشه تماس', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-google-maps';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'نقشه', 'baspar-elements' ) ) );
		$this->add_control(
			'mode',
			array(
				'label'   => __( 'حالت', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'graphic',
				'options' => array(
					'graphic' => __( 'گرافیکی متحرک (پین پالس‌دار)', 'baspar-elements' ),
					'embed'   => __( 'نقشه واقعی (iframe)', 'baspar-elements' ),
				),
			)
		);
		$this->add_control( 'label', array( 'label' => __( 'برچسب پین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'دفتر مرکزی بسپارمارکت', 'condition' => array( 'mode' => 'graphic' ) ) );
		$this->add_control(
			'embed',
			array(
				'label'       => __( 'کد embed نقشه (آدرس src)', 'baspar-elements' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'description' => __( 'فقط آدرس src نقشه (Google Maps / نشان) را وارد کنید.', 'baspar-elements' ),
				'condition'   => array( 'mode' => 'embed' ),
			)
		);
		$this->add_control( 'height', array( 'label' => __( 'ارتفاع (px)', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 360 ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$h = (int) $s['height'];
		?>
		<div class="baspar-scope">
			<div class="contact-map" style="height:<?php echo esc_attr( $h ); ?>px">
				<?php if ( 'embed' === $s['mode'] && ! empty( $s['embed'] ) ) : ?>
					<iframe src="<?php echo esc_url( trim( $s['embed'] ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
				<?php else : ?>
					<div class="cmap-grid" aria-hidden="true"></div>
					<div class="cmap-pulse" aria-hidden="true"></div>
					<div class="cmap-pin">
						<div class="cmap-pin-mark"><?php echo icon_svg( 'pin', 26 ); // phpcs:ignore ?></div>
						<div class="cmap-pin-label"><?php echo esc_html( $s['label'] ); ?></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
