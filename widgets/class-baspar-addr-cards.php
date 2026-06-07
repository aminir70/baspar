<?php
/**
 * Address cards — 2 office address cards (Tehran, Shiraz) with icon column.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Addr_Cards extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-addr-cards'; }
	public function get_title() { return __( 'بسپار — کارت‌های آدرس دفتر', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-map-pin'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'دفاتر', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'pin' ) );
		$rep->add_control( 'mono', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'OFFICE' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'body', array( 'label' => __( 'آدرس', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control( 'items', array(
			'label' => __( 'کارت‌ها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'icon' => 'pin', 'mono' => 'TEHRAN OFFICE', 'title' => 'دفتر تهران', 'body' => 'تهران، خیابان آزادی، پلاک ۱۲۰' ),
				array( 'icon' => 'building', 'mono' => 'SHIRAZ OFFICE', 'title' => 'دفتر شیراز', 'body' => 'شیراز، بلوار امیرکبیر، برج صنعت' ),
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
			<div class="addr-group <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php foreach ( (array) $s['items'] as $it ) : ?>
					<div class="addr-card">
						<div class="addr-card-ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'pin', 24 ); // phpcs:ignore ?></div>
						<div class="addr-card-body">
							<span class="mono"><?php echo esc_html( $it['mono'] ); ?></span>
							<strong><?php echo esc_html( $it['title'] ); ?></strong>
							<p><?php echo esc_html( $it['body'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
