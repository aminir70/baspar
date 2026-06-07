<?php
/**
 * Product gallery — main image + zoom + interactive thumbnails (state on
 * click). Works on standalone product pages (when not in WooCommerce single).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Product_Gallery extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-product-gallery'; }
	public function get_title() { return __( 'بسپار — گالری محصول', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-image-rollover'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'گالری', 'baspar-elements' ) ) );
		$this->add_control( 'tag', array( 'label' => __( 'برچسب گوشه (مثلاً کد محصول)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'TiO₂ R-838' ) );
		$rep = new Repeater();
		$rep->add_control( 'image', array( 'label' => __( 'تصویر', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_control( 'images', array(
			'label' => __( 'تصاویر', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => 'image',
		) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$images = (array) $s['images'];
		$uid = 'bspr-gal-' . $this->get_id();
		?>
		<div class="baspar-scope">
			<div class="prod-gallery <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" id="<?php echo esc_attr( $uid ); ?>">
				<div class="prod-main-img">
					<?php if ( $s['tag'] ) : ?><div class="tag mono"><?php echo esc_html( $s['tag'] ); ?></div><?php endif; ?>
					<?php if ( ! empty( $images[0]['image']['url'] ) ) : ?>
						<img class="main-pic" src="<?php echo esc_url( $images[0]['image']['url'] ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;position:relative;z-index:1" />
					<?php else : ?>
						<div class="ic"><?php echo icon_svg( 'package', 96 ); // phpcs:ignore ?></div>
					<?php endif; ?>
					<button class="zoom-btn" type="button" aria-label="zoom"><?php echo icon_svg( 'zoom', 18 ); // phpcs:ignore ?></button>
				</div>
				<?php if ( count( $images ) > 1 ) : ?>
					<div class="prod-thumbs">
						<?php foreach ( $images as $i => $img ) : ?>
							<div class="prod-thumb <?php echo 0 === $i ? 'is-active' : ''; ?>" data-src="<?php echo esc_url( $img['image']['url'] ?? '' ); ?>">
								<?php if ( ! empty( $img['image']['url'] ) ) : ?>
									<img src="<?php echo esc_url( $img['image']['url'] ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;position:relative;z-index:1" />
								<?php else : ?>
									<?php echo icon_svg( 'package', 28 ); // phpcs:ignore ?>
								<?php endif; ?>
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
