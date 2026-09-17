<?php
/**
 * Product description — renders the current WooCommerce product's long
 * description (the main editor content) dynamically. Nothing is entered by hand.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_product;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Product_Description extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-product-description'; }
	public function get_title() { return __( 'بسپار — توضیحات محصول', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-text-area'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'توضیحات (پویا)', 'baspar-elements' ) ) );
		$this->add_control(
			'info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'متن توضیحات به‌صورت خودکار از توضیحات همین محصول خوانده می‌شود.', 'baspar-elements' ),
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->add_control( 'show_title', array( 'label' => __( 'نمایش عنوان', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control(
			'title',
			array(
				'label'     => __( 'عنوان بخش', 'baspar-elements' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'توضیحات محصول', 'baspar-elements' ),
				'condition' => array( 'show_title' => 'yes' ),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.prod-desc-title' );
		$this->add_color( 'title_color', __( 'رنگ عنوان', 'baspar-elements' ), '.prod-desc-title', 'color' );
		$this->add_typography( 'body_typo', __( 'تایپوگرافی متن', 'baspar-elements' ), '.prod-desc-body' );
		$this->add_color( 'body_color', __( 'رنگ متن', 'baspar-elements' ), '.prod-desc-body', 'color' );
		$this->end_controls_section();
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$product = current_product();

		if ( ! $product ) {
			if ( $this->is_editor() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'این ویجت توضیحات را از محصول ووکامرس می‌خواند. آن را در قالب «محصول تکی» قرار دهید.', 'baspar-elements' ) . '</div>';
			}
			return;
		}

		$description = $product->get_description();
		if ( '' === trim( (string) $description ) ) {
			if ( $this->is_editor() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'این محصول توضیحاتی ندارد.', 'baspar-elements' ) . '</div>';
			}
			return;
		}

		// Run WooCommerce/WordPress content filters (shortcodes, wpautop, embeds…).
		$content = apply_filters( 'the_content', $description ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		?>
		<div class="baspar-scope">
			<div class="prod-desc <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php if ( 'yes' === $s['show_title'] && $s['title'] ) : ?>
					<h2 class="prod-desc-title"><?php echo esc_html( $s['title'] ); ?></h2>
				<?php endif; ?>
				<div class="prod-desc-body"><?php echo wp_kses_post( $content ); ?></div>
			</div>
		</div>
		<?php
	}
}
