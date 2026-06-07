<?php
/**
 * Credibility bar — 5 cells with mono label, animated number, description.
 * Sits floating on top of importer hero (margin-top: -40px) by default.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Cred_Bar extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-cred-bar'; }
	public function get_title() { return __( 'بسپار — نوار اعتبار', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-counter-circle'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'سلول‌ها', 'baspar-elements' ) ) );
		$this->add_control( 'pull_up', array( 'label' => __( 'قرارگیری روی هیرو (Pull-up)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$rep = new Repeater();
		$rep->add_control( 'label', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'num', array( 'label' => __( 'عدد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 5 ) );
		$rep->add_control( 'suffix', array( 'label' => __( 'پسوند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '+' ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'items', array(
			'label' => __( 'سلول‌ها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ label }}} {{{ num }}}',
			'default' => array(
				array( 'label' => 'EXPERIENCE', 'num' => 5, 'suffix' => '+', 'desc' => 'سال واردات' ),
				array( 'label' => 'SKU', 'num' => 100, 'suffix' => '+', 'desc' => 'ماده اولیه فعال' ),
				array( 'label' => 'COUNTRIES', 'num' => 3, 'suffix' => '', 'desc' => 'کشور مبدأ' ),
				array( 'label' => 'CLIENTS', 'num' => 980, 'suffix' => '+', 'desc' => 'کارخانه فعال' ),
				array( 'label' => 'RFQ TIME', 'num' => 24, 'suffix' => 'h', 'desc' => 'پاسخ استعلام' ),
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
		$margin = ( 'yes' === $s['pull_up'] ) ? '-40px auto 0' : '0 auto';
		?>
		<div class="baspar-scope">
			<div class="credbar <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" style="margin:<?php echo esc_attr( $margin ); ?>">
				<div class="credbar-grid">
					<?php foreach ( (array) $s['items'] as $it ) : ?>
						<div class="credbar-cell">
							<span class="lbl"><?php echo esc_html( $it['label'] ); ?></span>
							<span class="num"><span data-counter data-target="<?php echo esc_attr( $it['num'] ); ?>"><?php echo esc_html( number_format_i18n( $it['num'] ) ); ?></span><?php echo esc_html( $it['suffix'] ); ?></span>
							<span class="desc"><?php echo esc_html( $it['desc'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
