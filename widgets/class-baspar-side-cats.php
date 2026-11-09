<?php
/**
 * Sidebar box — main (top-level) WooCommerce product categories with image
 * or icon and product count.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\render_side_head;
use function BasparElements\fa_num;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Side_Cats extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-side-cats'; }
	public function get_title() { return __( 'بسپار — کناری: دسته‌های اصلی محصولات', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-product-categories'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'دسته‌های اصلی محصولات' ) );
		$this->add_control( 'limit', array( 'label' => __( 'حداکثر تعداد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 1, 'max' => 20 ) );
		$this->add_control( 'orderby', array( 'label' => __( 'مرتب‌سازی', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'count', 'options' => array( 'count' => __( 'بیشترین محصول', 'baspar-elements' ), 'name' => __( 'الفبا', 'baspar-elements' ), 'menu_order' => __( 'ترتیب ووکامرس', 'baspar-elements' ) ) ) );
		$this->add_control( 'exclude', array( 'label' => __( 'حذف دسته‌ها (شناسه‌ها با ویرگول)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'description' => __( 'دسته‌ی «دسته‌بندی نشده» همیشه حذف می‌شود.', 'baspar-elements' ) ) );
		$this->add_control( 'show_count', array( 'label' => __( 'تعداد محصول', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'more_text', array( 'label' => __( 'متن «همه»', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'فروشگاه' ) );
		$this->add_control( 'more_link', array( 'label' => __( 'لینک «همه»', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/shop/' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return;
		}
		$s       = $this->get_settings_for_display();
		$exclude = array_filter( array_map( 'intval', preg_split( '/[\s,،]+/u', (string) $s['exclude'] ) ) );
		$exclude[] = (int) get_option( 'default_product_cat' );
		$terms   = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => true, 'exclude' => $exclude, 'orderby' => 'menu_order' === $s['orderby'] ? 'name' : $s['orderby'], 'order' => 'count' === $s['orderby'] ? 'DESC' : 'ASC', 'number' => max( 1, (int) $s['limit'] ) ) );
		if ( is_wp_error( $terms ) || ! $terms ) {
			return;
		}
		?>
		<div class="baspar-scope">
			<section class="sbx">
				<?php render_side_head( 'layers', $s['title'], $s['more_link']['url'] ?? '', $s['more_text'] ); ?>
				<ul class="sc-list">
					<?php foreach ( $terms as $t ) :
						$tid = (int) get_term_meta( $t->term_id, 'thumbnail_id', true );
						$img = $tid ? wp_get_attachment_image( $tid, 'thumbnail', false, array( 'loading' => 'lazy', 'alt' => '' ) ) : '';
						?>
						<li>
							<a class="sc-item" href="<?php echo esc_url( get_term_link( $t ) ); ?>">
								<span class="sc-ic"><?php echo $img ? $img : icon_svg( 'package', 18 ); // phpcs:ignore ?></span>
								<span class="sc-name"><?php echo esc_html( $t->name ); ?></span>
								<?php if ( 'yes' === $s['show_count'] ) : ?><span class="sc-count"><?php echo esc_html( fa_num( $t->count ) ); ?></span><?php endif; ?>
								<span class="sc-go" aria-hidden="true"><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		</div>
		<?php
	}
}
