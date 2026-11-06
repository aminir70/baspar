<?php
/**
 * Product contact card — "get price & advice" with the experts of the
 * product's category (WhatsApp / Bale / call). For the sidebar or anywhere
 * on the product page.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_product;
use function BasparElements\render_expert_list;
use function BasparElements\experts_settings;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Pd_Contact extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-pd-contact'; }
	public function get_title() { return __( 'بسپار — محصول: کارت استعلام و کارشناسان', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-headphones'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'FREE CONSULTATION' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'سوال فنی یا قیمت روز؟' ) );
		$this->add_control( 'text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => 'کارشناس این دسته در انتخاب گرید و مقدار مناسب راهنمایی‌تان می‌کند.' ) );
		$this->add_control( 'layout', array( 'label' => __( 'چیدمان', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'compact', 'options' => array( 'compact' => __( 'فشرده (ستون کناری)', 'baspar-elements' ), 'full' => __( 'کامل', 'baspar-elements' ) ) ) );
		$this->add_control( 'show_bale', array( 'label' => __( 'دکمه بله', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_phone', array( 'label' => __( 'شماره و دکمه تماس', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$set = experts_settings();
		?>
		<div class="baspar-scope">
			<aside class="pdq pdq-side">
				<div class="pdq-head">
					<?php if ( $s['kicker'] ) : ?><span class="pdq-kicker mono"><?php echo esc_html( $s['kicker'] ); ?></span><?php endif; ?>
					<?php if ( $s['title'] ) : ?><strong class="pdq-title"><?php echo esc_html( $s['title'] ); ?></strong><?php endif; ?>
					<?php if ( $s['text'] ) : ?><p><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
				</div>
				<?php render_expert_list( current_product(), array( 'layout' => $s['layout'], 'show_bale' => 'yes' === $s['show_bale'], 'show_phone' => 'yes' === $s['show_phone'] ) ); ?>
				<?php if ( ! empty( $set['hours'] ) ) : ?><div class="pdq-hours"><?php echo icon_svg( 'clock', 14 ); // phpcs:ignore ?><?php echo esc_html( $set['hours'] ); ?></div><?php endif; ?>
			</aside>
		</div>
		<?php
	}
}
