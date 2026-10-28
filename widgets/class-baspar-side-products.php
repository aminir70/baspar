<?php
/**
 * Sidebar box — latest WooCommerce products (thumbnail, name, category).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\render_side_head;
use function BasparElements\primary_cat_name;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Side_Products extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-side-products'; }
	public function get_title() { return __( 'بسپار — کناری: آخرین محصولات', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-products'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'آخرین محصولات' ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 5, 'min' => 1, 'max' => 12 ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'متن کنار هر محصول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام قیمت' ) );
		$this->add_control( 'more_text', array( 'label' => __( 'متن «همه»', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'همه محصولات' ) );
		$this->add_control( 'more_link', array( 'label' => __( 'لینک «همه»', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/shop/' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! post_type_exists( 'product' ) ) {
			return;
		}
		$s     = $this->get_settings_for_display();
		$posts = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => max( 1, (int) $s['count'] ), 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
		if ( ! $posts ) {
			return;
		}
		?>
		<div class="baspar-scope">
			<section class="sbx">
				<?php render_side_head( 'package', $s['title'], $s['more_link']['url'] ?? '', $s['more_text'] ); ?>
				<ul class="spr-list">
					<?php foreach ( $posts as $p ) :
						$thumb = get_the_post_thumbnail( $p->ID, 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) );
						$cat   = primary_cat_name( $p->ID );
						?>
						<li>
							<a class="spr-item" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
								<span class="spr-thumb"><?php echo $thumb ? $thumb : icon_svg( 'package', 22 ); // phpcs:ignore ?></span>
								<span class="spr-body">
									<span class="spr-title"><?php echo esc_html( get_the_title( $p ) ); ?></span>
									<?php if ( $cat ) : ?><span class="spr-cat"><?php echo esc_html( $cat ); ?></span><?php endif; ?>
								</span>
								<?php if ( $s['cta_text'] ) : ?><span class="spr-go"><?php echo esc_html( $s['cta_text'] ); ?><?php echo icon_svg( 'arrow', 12 ); // phpcs:ignore ?></span><?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		</div>
		<?php
	}
}
