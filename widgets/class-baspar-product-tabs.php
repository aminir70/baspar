<?php
/**
 * Latest products, tabbed by WooCommerce product category. Each chosen
 * category becomes a tab; products are queried live per category.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Product_Tabs
 */
class Baspar_Product_Tabs extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-product-tabs';
	}

	public function get_title() {
		return __( 'بسپار — محصولات تب‌دار (ووکامرس)', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-tabs';
	}

	private function cat_options() {
		$opts = array();
		if ( taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					$opts[ $t->term_id ] = $t->name;
				}
			}
		}
		return $opts;
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تب‌ها', 'baspar-elements' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد محصول هر تب', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 5 ) );

		$rep = new Repeater();
		$rep->add_control(
			'cat',
			array(
				'label'   => __( 'دسته ووکامرس', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->cat_options(),
			)
		);
		$rep->add_control( 'label', array( 'label' => __( 'برچسب تب (خالی=نام دسته)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'layers' ) );
		$this->add_control(
			'tabs',
			array(
				'label'       => __( 'تب‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ label }}}',
			)
		);
		$this->add_control( 'foot_text', array( 'label' => __( 'متن پاورقی کارت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'جزئیات و قیمت' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$tabs = (array) $s['tabs'];

		if ( ! function_exists( 'wc_get_product' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'ووکامرس فعال نیست.', 'baspar-elements' ) . '</div>';
			}
			return;
		}
		if ( empty( $tabs ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'حداقل یک تب با یک دسته ووکامرس اضافه کنید.', 'baspar-elements' ) . '</div>';
			}
			return;
		}
		$uid = 'bspr-tabs-' . $this->get_id();
		?>
		<div class="baspar-scope">
			<div data-tabs id="<?php echo esc_attr( $uid ); ?>">
				<div class="lp-tabs">
					<?php foreach ( $tabs as $idx => $t ) :
						$term  = get_term( (int) $t['cat'], 'product_cat' );
						$label = $t['label'] ? $t['label'] : ( $term && ! is_wp_error( $term ) ? $term->name : '' );
						?>
						<button class="lp-tab <?php echo 0 === $idx ? 'is-active' : ''; ?>" data-tab="tab-<?php echo esc_attr( $idx ); ?>" type="button">
							<?php echo icon_svg( $t['icon'] ? $t['icon'] : 'layers', 16 ); // phpcs:ignore ?>
							<?php echo esc_html( $label ); ?>
						</button>
					<?php endforeach; ?>
				</div>
				<?php foreach ( $tabs as $idx => $t ) :
					$q = new \WP_Query(
						array(
							'post_type'      => 'product',
							'posts_per_page' => max( 1, (int) $s['count'] ),
							'post_status'    => 'publish',
							'orderby'        => 'date',
							'order'          => 'DESC',
							'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
								array(
									'taxonomy' => 'product_cat',
									'field'    => 'term_id',
									'terms'    => (int) $t['cat'],
								),
							),
						)
					);
					?>
					<div class="bs-grid cols-5 lp-panel <?php echo 0 === $idx ? 'is-active' : ''; ?>" data-panel="tab-<?php echo esc_attr( $idx ); ?>">
						<?php
						if ( $q->have_posts() ) :
							while ( $q->have_posts() ) :
								$q->the_post();
								$product = wc_get_product( get_the_ID() );
								$sku     = $product ? $product->get_sku() : '';
								$thumb   = get_the_post_thumbnail( get_the_ID(), 'medium', array( 'loading' => 'lazy' ) );
								?>
								<a href="<?php the_permalink(); ?>" class="pcard">
									<div class="pcard-img <?php echo $thumb ? 'has-image' : ''; ?>">
										<?php if ( $sku ) : ?><span class="pcard-tag"><?php echo esc_html( $sku ); ?></span><?php endif; ?>
										<?php echo $thumb ? $thumb : icon_svg( 'package', 36 ); // phpcs:ignore ?>
									</div>
									<div class="pcard-body"><div class="pcard-name"><?php the_title(); ?></div></div>
									<div class="pcard-foot"><span><?php echo esc_html( $s['foot_text'] ); ?></span><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></div>
								</a>
								<?php
							endwhile;
							wp_reset_postdata();
						else :
							echo '<p class="dim">' . esc_html__( 'محصولی در این دسته نیست.', 'baspar-elements' ) . '</p>';
						endif;
						?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
