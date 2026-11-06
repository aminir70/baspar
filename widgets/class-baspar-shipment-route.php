<?php
/**
 * Shipment route — origin → destination visual card (with distance/transit
 * meta) plus a side panel of delivery note + coverage-area chips. Used on
 * city landing pages to show how a shipment reaches that city.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Shipment_Route
 */
class Baspar_Shipment_Route extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-shipment-route';
	}

	public function get_title() {
		return __( 'بسپار — مسیر ارسال', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-map-pin';
	}

	protected function register_controls() {
		$this->start_controls_section( 'route', array( 'label' => __( 'کارت مسیر', 'baspar-elements' ) ) );
		$this->add_control( 'route_tag', array( 'label' => __( 'برچسب بالا', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'SHIPMENT ROUTE' ) );
		$this->add_control( 'live_tag', array( 'label' => __( 'برچسب وضعیت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'READY TO SHIP' ) );
		$this->add_control( 'origin_value', array( 'label' => __( 'مبدأ', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'انبار مرکزی تهران' ) );
		$this->add_control( 'dest_value', array( 'label' => __( 'مقصد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'شهر شما' ) );
		$this->add_control( 'distance_value', array( 'label' => __( 'فاصله', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '—' ) );
		$this->add_control( 'transit_value', array( 'label' => __( 'زمان تحویل', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '۱ تا ۲ روز کاری' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'side', array( 'label' => __( 'پنل کناری', 'baspar-elements' ) ) );
		$this->add_control( 'side_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تحویل به شهرها و مناطق اطراف' ) );
		$this->add_control( 'side_text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG ) );
		$rep = new Repeater();
		$rep->add_control( 'name', array( 'label' => __( 'نام منطقه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'areas',
			array(
				'label'       => __( 'مناطق پوشش', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ name }}}',
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
			<div class="locdel <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<div class="route-card">
					<div class="route-head">
						<span class="mono"><?php echo esc_html( $s['route_tag'] ); ?></span>
						<span class="live"><?php echo esc_html( $s['live_tag'] ); ?></span>
					</div>
					<div class="route-line">
						<div class="route-node origin">
							<div class="dotmark"><?php echo icon_svg( 'building', 26 ); // phpcs:ignore ?></div>
							<span class="mono">ORIGIN</span>
							<strong><?php echo esc_html( $s['origin_value'] ); ?></strong>
						</div>
						<div class="route-track">
							<div class="route-truck"><?php echo icon_svg( 'truck', 22 ); // phpcs:ignore ?></div>
						</div>
						<div class="route-node dest">
							<div class="dotmark"><?php echo icon_svg( 'pin', 26 ); // phpcs:ignore ?></div>
							<span class="mono">DESTINATION</span>
							<strong><?php echo esc_html( $s['dest_value'] ); ?></strong>
						</div>
					</div>
					<div class="route-meta">
						<div>
							<span class="mono">DISTANCE</span>
							<strong><?php echo esc_html( $s['distance_value'] ); ?></strong>
							<span>فاصله تا مقصد</span>
						</div>
						<div>
							<span class="mono">TRANSIT</span>
							<strong><?php echo esc_html( $s['transit_value'] ); ?></strong>
							<span>زمان تحویل تخمینی</span>
						</div>
					</div>
				</div>
				<div class="locdel-side">
					<?php if ( $s['side_title'] ) : ?><h3><?php echo esc_html( $s['side_title'] ); ?></h3><?php endif; ?>
					<?php if ( $s['side_text'] ) : ?><div class="rich-text"><?php echo wp_kses_post( $s['side_text'] ); ?></div><?php endif; ?>
					<?php if ( ! empty( $s['areas'] ) ) : ?>
						<div class="areas">
							<?php foreach ( (array) $s['areas'] as $a ) : ?>
								<span class="area-chip"><?php echo esc_html( $a['name'] ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
