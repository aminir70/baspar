<?php
/**
 * Brands strip — auto-scrolling marquee of brand name pills.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Brands_Strip extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-brands-strip'; }
	public function get_title() { return __( 'بسپار — نوار اسلایدر برندها', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-slider-album'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'TRUSTED PARTNERS · BRANDS' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'برندهایی که با آن‌ها کار می‌کنیم' ) );
		$rep = new Repeater();
		$rep->add_control( 'name', array( 'label' => __( 'نام برند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'items', array(
			'label' => __( 'برندها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => 'BASF' ), array( 'name' => 'Bayer' ), array( 'name' => 'Dow' ),
				array( 'name' => 'SABIC' ), array( 'name' => 'LyondellBasell' ), array( 'name' => 'Mitsui' ),
				array( 'name' => 'Hexion' ), array( 'name' => 'Shell' ), array( 'name' => 'Lomon' ),
				array( 'name' => 'Huntsman' ),
			),
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.brands-head h4' );
		$this->add_color( 'title_color', __( 'رنگ عنوان', 'baspar-elements' ), '.brands-head h4', 'color' );
		$this->add_typography( 'brand_typo', __( 'تایپوگرافی نام برندها', 'baspar-elements' ), '.brand-logo' );
		$this->add_color( 'brand_color', __( 'رنگ نام برندها', 'baspar-elements' ), '.brand-logo', 'color' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$items = (array) $s['items'];
		$double = array_merge( $items, $items ); // duplicate for seamless loop
		?>
		<div class="baspar-scope">
			<section class="brands">
				<div class="brands-head">
					<span class="mono"><?php echo esc_html( $s['kicker'] ); ?></span>
					<h4><?php echo esc_html( $s['title'] ); ?></h4>
				</div>
				<div class="brands-strip">
					<div class="brands-track">
						<?php foreach ( $double as $b ) : ?>
							<span class="brand-logo"><?php echo esc_html( $b['name'] ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		</div>
		<?php
	}
}
