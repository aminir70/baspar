<?php
/**
 * Product bottom text — the per-product "متن پایین صفحه" written in the
 * product editor (meta box), shown with the article typography. Renders
 * nothing when the product has no bottom text.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_product;
use function BasparElements\normalize_content_html;
use function BasparElements\wrap_content_tables;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Pd_Bottom_Text extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-pd-bottom-text'; }
	public function get_title() { return __( 'بسپار — محصول: متن پایین صفحه', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-text'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => __( 'متن هر محصول در صفحه ویرایش همان محصول، کادر «بسپار — متن پایین صفحه محصول» نوشته می‌شود.', 'baspar-elements' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'fallback', array( 'label' => __( 'متن پیش‌فرض (برای محصولاتی که متن ندارند)', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => '' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$product = current_product();
		$s       = $this->get_settings_for_display();
		$html    = $product ? (string) get_post_meta( $product->get_id(), '_baspar_bottom_text', true ) : '';
		if ( '' === trim( $html ) ) {
			$html = (string) $s['fallback'];
		}
		if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
			if ( $this->is_editor() ) {
				echo '<div class="baspar-scope"><p class="pdx-empty">' . esc_html__( 'این محصول متن پایین صفحه ندارد (ویرایش محصول › کادر «متن پایین صفحه»).', 'baspar-elements' ) . '</p></div>';
			}
			return;
		}
		$html = wrap_content_tables( normalize_content_html( wpautop( do_shortcode( $html ) ) ) );
		?>
		<div class="baspar-scope">
			<section class="pdb">
				<?php if ( $s['title'] ) : ?><h2 class="pd-h"><span class="pd-h-ic"><?php echo icon_svg( 'doc', 18 ); // phpcs:ignore ?></span><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
				<div class="bspr-article"><?php echo wp_kses_post( $html ); ?></div>
			</section>
		</div>
		<?php
	}
}
