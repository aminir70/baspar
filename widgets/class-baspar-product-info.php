<?php
/**
 * Product info column — mono header + h1 + code row + specs table +
 * CTA box + meta row + 3 action buttons.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Product_Info extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-product-info'; }
	public function get_title() { return __( 'بسپار — اطلاعات محصول', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-product-info'; }

	protected function register_controls() {
		$this->start_controls_section( 'head', array( 'label' => __( 'هدر', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'PRODUCT · INDUSTRIAL GRADE' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تیتانیوم دی‌اکسید R-838' ) );

		$cr = new Repeater();
		$cr->add_control( 'label', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$cr->add_control( 'value', array( 'label' => __( 'مقدار', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'codes', array(
			'label' => __( 'سلول‌های کد', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $cr->get_controls(),
			'title_field' => '{{{ label }}}',
			'default' => array(
				array( 'label' => 'BRAND', 'value' => 'Lomon' ),
				array( 'label' => 'ORIGIN', 'value' => 'China' ),
				array( 'label' => 'PKG', 'value' => '۲۵kg / کیسه' ),
			),
		) );
		$this->add_control( 'desc', array( 'label' => __( 'توضیح کوتاه', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => 'تیتانیوم دی‌اکسید گرید Rutile با پراکنش بالا، مناسب صنایع رنگ‌سازی و پلاستیک.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'specs', array( 'label' => __( 'جدول مشخصات', 'baspar-elements' ) ) );
		$this->add_control( 'specs_head', array( 'label' => __( 'برچسب جدول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'KEY SPECIFICATIONS' ) );
		$sp = new Repeater();
		$sp->add_control( 'label', array( 'label' => __( 'مشخصه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$sp->add_control( 'value', array( 'label' => __( 'مقدار', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'specs', array(
			'label' => __( 'مشخصات', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $sp->get_controls(),
			'title_field' => '{{{ label }}}',
			'default' => array(
				array( 'label' => 'خلوص (TiO₂)', 'value' => '≥ ۹۸٪' ),
				array( 'label' => 'چگالی', 'value' => '۴.۱ g/cm³' ),
				array( 'label' => 'pH', 'value' => '۶.۵ – ۸.۵' ),
				array( 'label' => 'COA', 'value' => 'بله' ),
			),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'cta', array( 'label' => __( 'باکس استعلام و دکمه‌ها', 'baspar-elements' ) ) );
		$this->add_control( 'cta_kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'NEED PRICE & STOCK?' ) );
		$this->add_control( 'cta_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'برای دریافت قیمت روز و موجودی، استعلام بگیرید.' ) );
		$this->add_control( 'btn1_text', array( 'label' => __( 'دکمه ۱', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام در واتس‌اپ' ) );
		$this->add_control( 'btn1_link', array( 'label' => __( 'لینک ۱', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( 'دکمه ۲', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تماس تلفنی' ) );
		$this->add_control( 'btn2_link', array( 'label' => __( 'لینک ۲', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'tel:+989120733965' ) ) );

		$mr = new Repeater();
		$mr->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'truck' => 'ارسال', 'shield' => 'گارانتی', 'doc' => 'COA', 'package' => 'بسته‌بندی' ), 'default' => 'truck' ) );
		$mr->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'meta', array(
			'label' => __( 'بج‌های پایین', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $mr->get_controls(),
			'title_field' => '{{{ text }}}',
			'default' => array(
				array( 'icon' => 'truck', 'text' => 'ارسال ۲۴ ساعته' ),
				array( 'icon' => 'shield', 'text' => 'گارانتی مغایرت' ),
				array( 'icon' => 'doc', 'text' => 'COA و دیتاشیت' ),
				array( 'icon' => 'package', 'text' => 'بسته‌بندی ۲۵kg' ),
			),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="prod-info">
				<div class="prod-info-head">
					<span class="mono"><?php echo esc_html( $s['kicker'] ); ?></span>
					<h1><?php echo esc_html( $s['title'] ); ?></h1>
					<div class="prod-code-row">
						<?php foreach ( (array) $s['codes'] as $c ) : ?>
							<div><span class="mono"><?php echo esc_html( $c['label'] ); ?></span><strong><?php echo esc_html( $c['value'] ); ?></strong></div>
						<?php endforeach; ?>
					</div>
				</div>
				<p style="font-size:15px;color:var(--ink-2);line-height:1.85;margin:0"><?php echo esc_html( $s['desc'] ); ?></p>

				<div class="prod-specs">
					<div class="prod-specs-head"><?php echo esc_html( $s['specs_head'] ); ?></div>
					<table>
						<tbody>
							<?php foreach ( (array) $s['specs'] as $sp ) : ?>
								<tr><td><?php echo esc_html( $sp['label'] ); ?></td><td><?php echo esc_html( $sp['value'] ); ?></td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="prod-cta-box">
					<span class="mono"><?php echo esc_html( $s['cta_kicker'] ); ?></span>
					<h4><?php echo esc_html( $s['cta_title'] ); ?></h4>
					<div class="prod-cta-box-btns">
						<a href="<?php echo esc_url( $s['btn1_link']['url'] ?? '#' ); ?>" class="btn btn-whatsapp"><?php echo icon_svg( 'whatsapp', 16 ); // phpcs:ignore ?><?php echo esc_html( $s['btn1_text'] ); ?></a>
						<a href="<?php echo esc_url( $s['btn2_link']['url'] ?? '#' ); ?>" class="btn btn-ghost"><?php echo icon_svg( 'phone', 16 ); // phpcs:ignore ?><?php echo esc_html( $s['btn2_text'] ); ?></a>
					</div>
				</div>

				<div class="prod-meta-row">
					<?php foreach ( (array) $s['meta'] as $m ) : ?>
						<span class="pm"><?php echo icon_svg( $m['icon'] ? $m['icon'] : 'truck', 16 ); // phpcs:ignore ?><?php echo esc_html( $m['text'] ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
