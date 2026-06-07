<?php
/**
 * Cities/coverage grid.
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
 * Class Baspar_Cities
 */
class Baspar_Cities extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-cities';
	}

	public function get_title() {
		return __( 'بسپار — شهرها', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-map-pin';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'شهرها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'name', array( 'label' => __( 'شهر', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'شهرها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array( 'name' => 'تهران' ),
					array( 'name' => 'شیراز' ),
					array( 'name' => 'اصفهان' ),
					array( 'name' => 'تبریز' ),
					array( 'name' => 'مشهد' ),
					array( 'name' => 'اهواز' ),
					array( 'name' => 'کرج' ),
					array( 'name' => 'قم' ),
					array( 'name' => 'یزد' ),
					array( 'name' => 'سایر شهرها' ),
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
			<div class="cities <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php foreach ( (array) $s['items'] as $c ) :
					$url = ! empty( $c['link']['url'] ) ? $c['link']['url'] : '#';
					?>
					<a href="<?php echo esc_url( $url ); ?>" class="city-card">
						<span class="city-dot"></span>
						<span><?php echo esc_html( $c['name'] ); ?></span>
						<?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
