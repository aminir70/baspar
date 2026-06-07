<?php
/**
 * Hours banner — purple panel with big clock icon + 3 hour cells.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Hours_Banner extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-hours-banner'; }
	public function get_title() { return __( 'بسپار — بنر ساعات کاری', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-clock-o'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'WORKING HOURS' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ساعات پاسخگویی دفتر' ) );

		$rep = new Repeater();
		$rep->add_control( 'days', array( 'label' => __( 'روزها', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'hours', array( 'label' => __( 'ساعت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'closed', array( 'label' => __( 'تعطیل', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->add_control( 'items', array(
			'label' => __( 'ستون‌ها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ days }}}',
			'default' => array(
				array( 'days' => 'شنبه تا چهارشنبه', 'hours' => '۰۹:۰۰ — ۱۸:۰۰' ),
				array( 'days' => 'پنج‌شنبه', 'hours' => '۰۹:۰۰ — ۱۳:۰۰' ),
				array( 'days' => 'جمعه', 'hours' => 'تعطیل', 'closed' => 'yes' ),
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
			<div class="hours-banner <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" style="display:grid;grid-template-columns:160px 1fr;gap:24px;align-items:center;background:linear-gradient(135deg,var(--brand) 0%,var(--brand-dk) 100%);color:#fff;border-radius:calc(var(--radius) + 6px);padding:36px 32px;position:relative;overflow:hidden">
				<div style="text-align:center">
					<div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,.15);color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 10px"><?php echo icon_svg( 'clock', 36 ); // phpcs:ignore ?></div>
					<div style="font-family:var(--mono);font-size:10.5px;letter-spacing:.12em;color:#E5D4FF;margin-bottom:6px"><?php echo esc_html( $s['kicker'] ); ?></div>
					<strong style="font-size:15px;color:#fff;display:block"><?php echo esc_html( $s['title'] ); ?></strong>
				</div>
				<div style="display:grid;grid-template-columns:repeat(<?php echo count( (array) $s['items'] ); ?>,1fr);gap:14px">
					<?php foreach ( (array) $s['items'] as $h ) :
						$on = ( 'yes' === $h['closed'] ) ? '' : 'is-open';
						?>
						<div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:var(--radius);padding:14px 16px;text-align:center;backdrop-filter:blur(4px)">
							<div style="font-size:12px;color:#C4B0E8;margin-bottom:6px"><?php echo esc_html( $h['days'] ); ?></div>
							<strong style="font-family:var(--mono);font-size:14.5px;color:<?php echo ( 'yes' === $h['closed'] ) ? '#FFB547' : '#fff'; ?>;letter-spacing:.04em"><?php echo esc_html( $h['hours'] ); ?></strong>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
