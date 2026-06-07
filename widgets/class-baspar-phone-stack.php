<?php
/**
 * Phone stack — vertical list of phone rows with icon, label and number.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Phone_Stack extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-phone-stack'; }
	public function get_title() { return __( 'بسپار — لیست تلفن‌ها', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-phone-field'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'ردیف‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'phone' ) );
		$rep->add_control( 'label', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'number', array( 'label' => __( 'شماره', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک (tel: یا mailto:)', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items', array(
			'label' => __( 'ردیف‌ها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ number }}}',
			'default' => array(
				array( 'icon' => 'phone', 'label' => 'TEHRAN', 'number' => '۰۲۱-۲۸۴۲۶۰۷۵', 'link' => array( 'url' => 'tel:+982128426075' ) ),
				array( 'icon' => 'phone', 'label' => 'SHIRAZ', 'number' => '۰۷۱-۳۸۳۸۵۶۸۶', 'link' => array( 'url' => 'tel:+987138385686' ) ),
				array( 'icon' => 'whatsapp', 'label' => 'WHATSAPP / مدیر فروش', 'number' => '+98 912 099 7651', 'link' => array( 'url' => 'https://wa.me/989120997651' ) ),
				array( 'icon' => 'whatsapp', 'label' => 'WHATSAPP / کارشناس فنی', 'number' => '+98 912 073 3965', 'link' => array( 'url' => 'https://wa.me/989120733965' ) ),
				array( 'icon' => 'mail', 'label' => 'EMAIL', 'number' => 'info@basparmarket.com', 'link' => array( 'url' => 'mailto:info@basparmarket.com' ) ),
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
			<div class="phone-stack <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php foreach ( (array) $s['items'] as $it ) :
					$url = ! empty( $it['link']['url'] ) ? $it['link']['url'] : '';
					$tag = $url ? 'a' : 'div';
					?>
					<<?php echo esc_attr( $tag ); ?> class="phone-row" <?php echo $url ? 'href="' . esc_url( $url ) . '"' : ''; ?>>
						<div class="ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'phone', 20 ); // phpcs:ignore ?></div>
						<div class="body">
							<span><?php echo esc_html( $it['label'] ); ?></span>
							<strong><?php echo esc_html( $it['number'] ); ?></strong>
						</div>
						<?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?>
					</<?php echo esc_attr( $tag ); ?>>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
