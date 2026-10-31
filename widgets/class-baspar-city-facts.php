<?php
/**
 * City quick-facts row — 4 icon+label+value cards, placed right under the
 * breadcrumb hero on a city landing page (province, dispatch origin,
 * transit time, guarantee).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_City_Facts
 */
class Baspar_City_Facts extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-city-facts';
	}

	public function get_title() {
		return __( 'بسپار — واقعیت‌های سریع شهر', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-info-circle-o';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'موارد', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'pin' ) );
		$rep->add_control( 'label', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'value', array( 'label' => __( 'مقدار', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'موارد', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array( 'icon' => 'pin', 'label' => 'استان', 'value' => 'تهران' ),
					array( 'icon' => 'truck', 'label' => 'ارسال از', 'value' => 'انبار مرکزی تهران' ),
					array( 'icon' => 'clock', 'label' => 'زمان تحویل', 'value' => 'همان روز تا ۱ روز کاری' ),
					array( 'icon' => 'shield', 'label' => 'گارانتی مغایرت', 'value' => '۴۸ ساعت رسمی' ),
				),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="city-facts <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<div class="city-facts-grid">
					<?php foreach ( (array) $s['items'] as $f ) : ?>
						<div class="cfact">
							<div class="cfact-ic"><?php echo icon_svg( $f['icon'] ? $f['icon'] : 'pin', 22 ); // phpcs:ignore ?></div>
							<div class="cfact-body">
								<span class="mono"><?php echo esc_html( $f['label'] ); ?></span>
								<strong><?php echo esc_html( $f['value'] ); ?></strong>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
