<?php
/**
 * Product page section navigation — sticky pills that jump to specs,
 * description, reviews and related products.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\current_product;
use function BasparElements\fa_num;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Pd_Anchors extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-pd-anchors'; }
	public function get_title() { return __( 'بسپار — محصول: منوی بخش‌ها', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-anchor'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'بخش‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'target', array( 'label' => __( 'شناسه بخش (بدون #)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'items', array(
			'label'       => __( 'بخش‌ها', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ text }}}',
			'default'     => array(
				array( 'text' => 'مشخصات فنی', 'target' => 'pd-specs' ),
				array( 'text' => 'توضیحات', 'target' => 'pd-desc' ),
				array( 'text' => 'نظرات', 'target' => 'pd-reviews' ),
				array( 'text' => 'محصولات مرتبط', 'target' => 'pd-related' ),
			),
		) );
		$this->add_control( 'show_cta', array( 'label' => __( 'دکمه «دریافت قیمت» در نوار', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$product = current_product();
		$count   = $product ? (int) $product->get_review_count() : 0;
		?>
		<div class="baspar-scope">
			<nav class="pda" aria-label="<?php esc_attr_e( 'بخش‌های صفحه محصول', 'baspar-elements' ); ?>">
				<div class="pda-in">
					<?php foreach ( (array) $s['items'] as $it ) :
						if ( empty( $it['text'] ) || empty( $it['target'] ) ) {
							continue;
						}
						?>
						<a href="#<?php echo esc_attr( sanitize_html_class( $it['target'] ) ); ?>"><?php echo esc_html( $it['text'] ); ?><?php if ( 'pd-reviews' === $it['target'] && $count ) : ?><span class="pda-n"><?php echo esc_html( fa_num( $count ) ); ?></span><?php endif; ?></a>
					<?php endforeach; ?>
					<?php if ( 'yes' === $s['show_cta'] ) : ?><a class="pda-cta" href="#pd-contact"><?php esc_html_e( 'دریافت قیمت', 'baspar-elements' ); ?></a><?php endif; ?>
				</div>
			</nav>
		</div>
		<?php
	}
}
