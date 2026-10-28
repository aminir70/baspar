<?php
/**
 * Product specifications table — read from the product's WooCommerce
 * attributes (Products › Attributes), plus optional brand, category, SKU,
 * weight and dimensions rows.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_product;
use function BasparElements\product_specs;
use function BasparElements\product_brand_terms;
use function BasparElements\product_cat_trail;
use function BasparElements\fa_num_smart;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Pd_Specs extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-pd-specs'; }
	public function get_title() { return __( 'بسپار — محصول: جدول مشخصات فنی', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-table'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشخصات فنی' ) );
		$this->add_control( 'anchor', array( 'label' => __( 'شناسه بخش', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'pd-specs' ) );
		$this->add_control( 'show_brand', array( 'label' => __( 'ردیف برند', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_cat', array( 'label' => __( 'ردیف دسته', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_sku', array( 'label' => __( 'ردیف کد محصول', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_dims', array( 'label' => __( 'وزن و ابعاد (اگر وارد شده باشد)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'note', array( 'label' => __( 'یادداشت زیر جدول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'برای دریافت برگه آنالیز (COA) و دیتاشیت این محصول با کارشناس تماس بگیرید.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$product = current_product();
		if ( ! $product ) {
			return;
		}
		$s    = $this->get_settings_for_display();
		$rows = array();
		if ( 'yes' === $s['show_cat'] ) {
			$trail = product_cat_trail( $product->get_id() );
			if ( $trail ) {
				$rows[ __( 'دسته‌بندی', 'baspar-elements' ) ] = implode( ' › ', wp_list_pluck( $trail, 'name' ) );
			}
		}
		if ( 'yes' === $s['show_brand'] ) {
			$b = product_brand_terms( $product->get_id() );
			if ( $b ) {
				$rows[ __( 'برند', 'baspar-elements' ) ] = implode( '، ', wp_list_pluck( $b, 'name' ) );
			}
		}
		if ( 'yes' === $s['show_sku'] && $product->get_sku() ) {
			$rows[ __( 'کد محصول', 'baspar-elements' ) ] = $product->get_sku();
		}
		$rows = array_merge( $rows, product_specs( $product ) );
		if ( 'yes' === $s['show_dims'] ) {
			if ( $product->has_weight() ) {
				$rows[ __( 'وزن', 'baspar-elements' ) ] = wc_format_weight( $product->get_weight() );
			}
			if ( $product->has_dimensions() ) {
				$rows[ __( 'ابعاد', 'baspar-elements' ) ] = wc_format_dimensions( $product->get_dimensions( false ) );
			}
		}
		if ( ! $rows ) {
			return;
		}
		$anchor = sanitize_html_class( $s['anchor'] );
		?>
		<div class="baspar-scope"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<section class="pds">
				<?php if ( $s['title'] ) : ?><h2 class="pd-h"><span class="pd-h-ic"><?php echo icon_svg( 'layers', 18 ); // phpcs:ignore ?></span><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
				<table class="pds-table">
					<tbody>
						<?php foreach ( $rows as $label => $value ) : ?>
							<tr><th scope="row"><?php echo esc_html( $label ); ?></th><td><?php echo esc_html( fa_num_smart( $value ) ); ?></td></tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php if ( $s['note'] ) : ?><p class="pds-note"><?php echo icon_svg( 'info', 15 ); // phpcs:ignore ?><?php echo esc_html( $s['note'] ); ?></p><?php endif; ?>
			</section>
		</div>
		<?php
	}
}
