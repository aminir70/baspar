<?php
/**
 * Sidebar: petrochemical base prices banner — this week's date, average
 * change, dollar trend and the busiest groups, linking to the price page.
 * The same banner is inserted automatically into post / product sidebars
 * (setting in بسپار ← قیمت‌های پایه); placing this widget replaces that.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use BasparElements\Prices;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Side_Prices extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-side-prices'; }
	public function get_title() { return __( 'بسپار — کناری: بنر قیمت پایه پتروشیمی', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-price-table'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'بنر', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'قیمت پایه محصولات پتروشیمی' ) );
		$this->add_control( 'cta', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده جدول قیمت‌ها' ) );
		$this->add_control( 'groups', array( 'label' => __( 'تعداد گروه‌ها', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 3, 'min' => 0, 'max' => 6 ) );
		$this->add_control( 'link', array( 'label' => __( 'لینک (خالی = صفحه قیمت‌ها)', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! function_exists( '\\BasparElements\\Prices\\sidebar_banner' ) ) {
			return;
		}
		$s    = $this->get_settings_for_display();
		$html = Prices\sidebar_banner(
			array(
				'title'  => $s['title'],
				'cta'    => $s['cta'],
				'groups' => (int) $s['groups'],
				'url'    => ! empty( $s['link']['url'] ) ? $s['link']['url'] : '',
			)
		);
		if ( '' === $html && $this->is_editor() ) {
			echo '<div class="baspar-scope"><p style="padding:16px;border:1px dashed #D8CDEA;border-radius:14px;color:#6B5E80">' . esc_html__( 'هنوز قیمتی وارد نشده یا صفحه قیمت‌ها پیدا نشد.', 'baspar-elements' ) . '</p></div>';
			return;
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in sidebar_banner().
	}
}
