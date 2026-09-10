<?php
/**
 * Product gallery — main image + zoom + interactive thumbnails.
 *
 * Reads the images live from the current WooCommerce product (featured image +
 * gallery). Nothing is entered by hand.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;
use function BasparElements\current_product;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Product_Gallery extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-product-gallery'; }
	public function get_title() { return __( 'بسپار — گالری محصول', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-image-rollover'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'گالری (پویا)', 'baspar-elements' ) ) );
		$this->add_control(
			'info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'تصاویر به‌صورت خودکار از تصویر شاخص و گالری همین محصول خوانده می‌شوند.', 'baspar-elements' ),
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->add_control(
			'show_sku',
			array(
				'label'        => __( 'نمایش کد محصول (SKU) روی تصویر', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$product = current_product();

		if ( ! $product ) {
			if ( $this->is_editor() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'این ویجت تصاویر را از محصول ووکامرس می‌خواند. آن را در قالب «محصول تکی» قرار دهید.', 'baspar-elements' ) . '</div>';
			}
			return;
		}

		// Collect image IDs: featured first, then gallery.
		$ids = array();
		if ( $product->get_image_id() ) {
			$ids[] = (int) $product->get_image_id();
		}
		foreach ( $product->get_gallery_image_ids() as $gid ) {
			$ids[] = (int) $gid;
		}
		$ids = array_values( array_unique( array_filter( $ids ) ) );

		$sku = $product->get_sku();
		$uid = 'bspr-gal-' . $this->get_id();
		?>
		<div class="baspar-scope">
			<div class="prod-gallery <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" id="<?php echo esc_attr( $uid ); ?>">
				<div class="prod-main-img">
					<?php if ( 'yes' === $s['show_sku'] && $sku ) : ?><div class="tag mono"><?php echo esc_html( $sku ); ?></div><?php endif; ?>
					<?php if ( ! empty( $ids ) ) : ?>
						<?php
						$main_src = wp_get_attachment_image_url( $ids[0], 'large' );
						$main_alt = trim( (string) get_post_meta( $ids[0], '_wp_attachment_image_alt', true ) );
						if ( '' === $main_alt ) {
							$main_alt = $product->get_name();
						}
						?>
						<img class="main-pic" src="<?php echo esc_url( $main_src ); ?>" alt="<?php echo esc_attr( $main_alt ); ?>" style="width:100%;height:100%;object-fit:cover;position:relative;z-index:1" />
					<?php else : ?>
						<div class="ic"><?php echo icon_svg( 'package', 96 ); // phpcs:ignore ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $ids ) ) : ?>
						<button class="zoom-btn" type="button" aria-label="zoom"><?php echo icon_svg( 'search', 18 ); // phpcs:ignore ?></button>
					<?php endif; ?>
				</div>
				<?php if ( count( $ids ) > 1 ) : ?>
					<div class="prod-thumbs">
						<?php foreach ( $ids as $i => $id ) : ?>
							<?php
							$thumb_src = wp_get_attachment_image_url( $id, 'medium' );
							$full_src  = wp_get_attachment_image_url( $id, 'large' );
							?>
							<div class="prod-thumb <?php echo 0 === $i ? 'is-active' : ''; ?>" data-src="<?php echo esc_url( $full_src ); ?>">
								<img src="<?php echo esc_url( $thumb_src ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;position:relative;z-index:1" />
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<script>
			(function(){
				var root = document.getElementById('<?php echo esc_js( $uid ); ?>');
				if (!root) return;
				var main = root.querySelector('.main-pic');
				var thumbs = root.querySelectorAll('.prod-thumb');
				thumbs.forEach(function(t){
					t.addEventListener('click', function(){
						var src = t.getAttribute('data-src');
						if (src && main) main.src = src;
						thumbs.forEach(function(x){ x.classList.toggle('is-active', x === t); });
					});
				});
			})();
			</script>
		</div>
		<?php
	}
}
