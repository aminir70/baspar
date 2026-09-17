<?php
/**
 * Product info column — mono header + h1 + code row + specs table +
 * CTA box + meta row.
 *
 * Product data (title, SKU, brand, categories, short description and the spec
 * table) is read live from the current WooCommerce product. The CTA box and the
 * trust badges are site-wide, so they stay configurable.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\current_product;
use function BasparElements\product_brand;
use function BasparElements\product_specs;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Product_Info extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-product-info'; }
	public function get_title() { return __( 'بسپار — اطلاعات محصول', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-product-info'; }

	protected function register_controls() {
		$this->start_controls_section( 'head', array( 'label' => __( 'اطلاعات محصول (پویا)', 'baspar-elements' ) ) );
		$this->add_control(
			'info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'عنوان، کد (SKU)، برند، دسته‌بندی، توضیح کوتاه و جدول مشخصات به‌صورت خودکار از همین محصول خوانده می‌شوند.', 'baspar-elements' ),
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->add_control( 'specs_head', array( 'label' => __( 'برچسب جدول مشخصات', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'KEY SPECIFICATIONS' ) );
		$this->add_control( 'show_short_desc', array( 'label' => __( 'نمایش توضیح کوتاه محصول', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'cta', array( 'label' => __( 'باکس استعلام و دکمه‌ها', 'baspar-elements' ) ) );
		$this->add_control( 'cta_kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'NEED PRICE & STOCK?' ) );
		$this->add_control( 'cta_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'برای دریافت قیمت روز و موجودی، استعلام بگیرید.' ) );
		$this->add_control( 'btn1_text', array( 'label' => __( 'دکمه ۱', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام در واتس‌اپ' ) );
		$this->add_control( 'btn1_link', array( 'label' => __( 'لینک ۱', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( 'دکمه ۲', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تماس تلفنی' ) );
		$this->add_control( 'btn2_link', array( 'label' => __( 'لینک ۲', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'tel:+989120733965' ) ) );

		$mr = new Repeater();
		$mr->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'truck' => 'ارسال', 'shield' => 'گارانتی', 'info' => 'COA', 'package' => 'بسته‌بندی' ), 'default' => 'truck' ) );
		$mr->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'meta', array(
			'label' => __( 'بج‌های پایین', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $mr->get_controls(),
			'title_field' => '{{{ text }}}',
			'default' => array(
				array( 'icon' => 'truck', 'text' => 'ارسال ۲۴ ساعته' ),
				array( 'icon' => 'shield', 'text' => 'گارانتی مغایرت' ),
				array( 'icon' => 'info', 'text' => 'COA و دیتاشیت' ),
				array( 'icon' => 'package', 'text' => 'بسته‌بندی استاندارد' ),
			),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.prod-info-head h1' );
		$this->end_controls_section();
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$product = current_product();

		if ( ! $product ) {
			if ( $this->is_editor() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'این ویجت اطلاعات را از محصول ووکامرس می‌خواند. آن را در قالب «محصول تکی» قرار دهید.', 'baspar-elements' ) . '</div>';
			}
			return;
		}

		$pid     = $product->get_id();
		$sku     = $product->get_sku();
		$brand   = product_brand( $product );
		$specs   = product_specs( $product );
		$short   = $product->get_short_description();

		// Category names for the kicker + code row.
		$cat_names = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'names' ) );
		$cat_names = is_wp_error( $cat_names ) ? array() : $cat_names;
		$primary_cat = ! empty( $cat_names ) ? $cat_names[0] : '';
		if ( $primary_cat ) {
			$kicker = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $primary_cat, 'UTF-8' ) : strtoupper( $primary_cat );
		} else {
			$kicker = __( 'محصول', 'baspar-elements' );
		}

		// Code row: SKU + brand + primary category (skip empties).
		$codes = array();
		if ( $sku ) {
			$codes[] = array( 'label' => 'SKU', 'value' => $sku );
		}
		if ( $brand ) {
			$codes[] = array( 'label' => 'BRAND', 'value' => $brand );
		}
		if ( $primary_cat ) {
			$codes[] = array( 'label' => 'CATEGORY', 'value' => $primary_cat );
		}
		?>
		<div class="baspar-scope">
			<div class="prod-info">
				<div class="prod-info-head">
					<span class="mono"><?php echo esc_html( $kicker ); ?></span>
					<h1><?php echo esc_html( $product->get_name() ); ?></h1>
					<?php if ( ! empty( $codes ) ) : ?>
						<div class="prod-code-row">
							<?php foreach ( $codes as $c ) : ?>
								<div><span class="mono"><?php echo esc_html( $c['label'] ); ?></span><strong><?php echo esc_html( $c['value'] ); ?></strong></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( 'yes' === $s['show_short_desc'] && $short ) : ?>
					<div style="font-size:15px;color:var(--ink-2);line-height:1.85;margin:0"><?php echo wp_kses_post( wpautop( $short ) ); ?></div>
				<?php endif; ?>

				<?php if ( ! empty( $specs ) ) : ?>
					<div class="prod-specs">
						<?php if ( $s['specs_head'] ) : ?><div class="prod-specs-head"><?php echo esc_html( $s['specs_head'] ); ?></div><?php endif; ?>
						<table>
							<tbody>
								<?php foreach ( $specs as $label => $value ) : ?>
									<tr><td><?php echo esc_html( $label ); ?></td><td><?php echo esc_html( $value ); ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>

				<div class="prod-cta-box">
					<span class="mono"><?php echo esc_html( $s['cta_kicker'] ); ?></span>
					<h4><?php echo esc_html( $s['cta_title'] ); ?></h4>
					<div class="prod-cta-box-btns">
						<a href="<?php echo esc_url( $s['btn1_link']['url'] ?? '#' ); ?>" class="btn btn-whatsapp"><?php echo icon_svg( 'whatsapp', 16 ); // phpcs:ignore ?><?php echo esc_html( $s['btn1_text'] ); ?></a>
						<a href="<?php echo esc_url( $s['btn2_link']['url'] ?? '#' ); ?>" class="btn btn-ghost"><?php echo icon_svg( 'phone', 16 ); // phpcs:ignore ?><?php echo esc_html( $s['btn2_text'] ); ?></a>
					</div>
				</div>

				<?php if ( ! empty( $s['meta'] ) ) : ?>
					<div class="prod-meta-row">
						<?php foreach ( (array) $s['meta'] as $m ) : ?>
							<span class="pm"><?php echo icon_svg( $m['icon'] ? $m['icon'] : 'truck', 16 ); // phpcs:ignore ?><?php echo esc_html( $m['text'] ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
