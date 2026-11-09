<?php
/**
 * City industrial zones — numbered card grid, one per industrial
 * town/district a city ships to. Placed after a "Section Heading" widget.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_City_Zones
 */
class Baspar_City_Zones extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-city-zones';
	}

	public function get_title() {
		return __( 'بسپار — شهرک‌های صنعتی شهر', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'شهرک‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'name', array( 'label' => __( 'نام شهرک/ناحیه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'note', array( 'label' => __( 'توضیح کوتاه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'شهرک‌ها', 'baspar-elements' ),
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
		if ( empty( $s['items'] ) ) {
			return;
		}
		?>
		<div class="baspar-scope">
			<div class="zones-grid">
				<?php
				$i = 0;
				foreach ( (array) $s['items'] as $z ) :
					?>
					<div class="zone-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 50 ); ?>">
						<span class="zone-num mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<strong><?php echo esc_html( $z['name'] ); ?></strong>
						<?php if ( $z['note'] ) : ?><p><?php echo esc_html( $z['note'] ); ?></p><?php endif; ?>
					</div>
					<?php
					++$i;
				endforeach;
				?>
			</div>
		</div>
		<?php
	}
}
