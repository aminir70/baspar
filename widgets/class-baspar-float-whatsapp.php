<?php
/**
 * Floating WhatsApp button (appears on scroll).
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
 * Class Baspar_Float_Whatsapp
 */
class Baspar_Float_Whatsapp extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-float-whatsapp';
	}

	public function get_title() {
		return __( 'بسپار — واتس‌اپ شناور', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-whatsapp';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control(
			'link',
			array(
				'label'   => __( 'لینک واتس‌اپ', 'baspar-elements' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => 'https://wa.me/989120997651' ),
			)
		);
		$this->add_control(
			'threshold',
			array(
				'label'   => __( 'نمایش بعد از اسکرول (px)', 'baspar-elements' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 400,
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control(
			'color',
			array(
				'label'     => __( 'رنگ دکمه', 'baspar-elements' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .bspr-fwa' => 'background: {{VALUE}}; --wa: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$url = ! empty( $s['link']['url'] ) ? $s['link']['url'] : '#';
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="bspr-fwa" data-threshold="<?php echo esc_attr( $s['threshold'] ); ?>" aria-label="استعلام در واتس‌اپ" target="_blank" rel="noopener">
			<?php echo icon_svg( 'whatsapp', 28 ); /* phpcs:ignore */ ?>
			<span class="fwa-pulse"></span>
		</a>
		<?php
	}
}
