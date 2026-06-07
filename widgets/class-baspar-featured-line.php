<?php
/**
 * Featured products line — 4 horizontal pcards, centered text style.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Featured_Line extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-featured-line'; }
	public function get_title() { return __( 'بسپار — خط محصولات شاخص', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-products-archive'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محصولات', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'beaker' ) );
		$rep->add_control( 'code', array( 'label' => __( 'کد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items', array(
			'label' => __( 'محصولات شاخص', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'icon' => 'package', 'code' => 'Powder', 'name' => 'پودر فوکو' ),
				array( 'icon' => 'layers', 'code' => 'TPU', 'name' => 'TPU پلی‌اورتان' ),
				array( 'icon' => 'droplet', 'code' => 'DOP', 'name' => 'روغن DOP' ),
				array( 'icon' => 'beaker', 'code' => 'MeCl', 'name' => 'متیلن کلراید' ),
			),
		) );
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
			<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
				<?php $i = 0; foreach ( (array) $s['items'] as $it ) : ++$i;
					$I = $it['icon'] ? $it['icon'] : 'beaker';
					?>
					<a href="<?php echo esc_url( $it['link']['url'] ?? '#' ); ?>" class="pcard <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 50 ); ?>" style="text-align:center">
						<div class="pcard-img" style="aspect-ratio:1.4/1">
							<span class="pcard-tag"><?php echo esc_html( $it['code'] ); ?></span>
							<?php echo icon_svg( $I, 38 ); // phpcs:ignore ?>
						</div>
						<div class="pcard-body" style="align-items:center;text-align:center">
							<div class="pcard-name" style="font-size:16px"><?php echo esc_html( $it['name'] ); ?></div>
							<div class="pcard-cat"><?php echo esc_html( $it['code'] ); ?></div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
