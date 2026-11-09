<?php
/**
 * Related products — WooCommerce related products (same category / tags),
 * topped up with products of the same category. Cards lead to the product
 * page ("استعلام قیمت"), there is no add-to-cart.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_product;
use function BasparElements\primary_cat_name;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Pd_Related extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-pd-related'; }
	public function get_title() { return __( 'بسپار — محصول: محصولات مرتبط', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-product-related'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'محصولات مرتبط' ) );
		$this->add_control( 'anchor', array( 'label' => __( 'شناسه بخش', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'pd-related' ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 1, 'max' => 16 ) );
		$this->add_control( 'cta', array( 'label' => __( 'متن دکمه کارت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام قیمت' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$product = current_product();
		if ( ! $product || ! function_exists( 'wc_get_related_products' ) ) {
			return;
		}
		$s     = $this->get_settings_for_display();
		$count = max( 1, (int) $s['count'] );
		$ids   = wc_get_related_products( $product->get_id(), $count );
		if ( count( $ids ) < $count ) {
			$cats = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
			if ( $cats && ! is_wp_error( $cats ) ) {
				$more = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => $count - count( $ids ), 'post__not_in' => array_merge( $ids, array( $product->get_id() ) ), 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'product_cat', 'terms' => $cats ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				$ids  = array_merge( $ids, $more );
			}
		}
		if ( ! $ids ) {
			return;
		}
		$anchor = sanitize_html_class( $s['anchor'] );
		?>
		<div class="baspar-scope"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<section class="pdrel">
				<?php if ( $s['title'] ) : ?><h2 class="pd-h"><span class="pd-h-ic"><?php echo icon_svg( 'package', 18 ); // phpcs:ignore ?></span><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
				<div class="pdrel-grid <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
					<?php foreach ( $ids as $id ) :
						$cat   = primary_cat_name( $id );
						$thumb = get_the_post_thumbnail( $id, 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'alt' => esc_attr( get_the_title( $id ) ) ) );
						?>
						<a class="pdrel-card" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
							<span class="pdrel-img"><?php echo $thumb ? $thumb : icon_svg( 'package', 40 ); // phpcs:ignore ?></span>
							<span class="pdrel-body">
								<?php if ( $cat ) : ?><span class="pdrel-cat"><?php echo esc_html( $cat ); ?></span><?php endif; ?>
								<strong><?php echo esc_html( get_the_title( $id ) ); ?></strong>
								<span class="pdrel-cta"><?php echo esc_html( $s['cta'] ); ?><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		</div>
		<?php
	}
}
