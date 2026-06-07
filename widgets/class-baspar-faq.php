<?php
/**
 * FAQ accordion. Numbered items, single-open or multi-open.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Faq
 */
class Baspar_Faq extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-faq';
	}

	public function get_title() {
		return __( 'بسپار — سوالات متداول (FAQ)', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-help-o';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'سوال‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'q', array( 'label' => __( 'سوال', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'a', array( 'label' => __( 'پاسخ', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4 ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'سوال‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ q }}}',
				'default'     => array(
					array( 'q' => 'حداقل میزان سفارش چقدر است؟', 'a' => 'بسته به نوع محصول متفاوت است؛ برای اطلاع دقیق با کارشناسان ما در واتس‌اپ در تماس باشید.' ),
					array( 'q' => 'آیا COA و دیتاشیت ارائه می‌شود؟', 'a' => 'بله، همراه هر سفارش گواهی آنالیز (COA) و دیتاشیت معتبر ارائه می‌گردد.' ),
					array( 'q' => 'زمان و هزینه ارسال چگونه است؟', 'a' => 'ارسال به سراسر ایران معمولاً ۲۴ ساعته انجام می‌شود؛ هزینه بر اساس وزن و مقصد محاسبه می‌گردد.' ),
				),
			)
		);
		$this->add_control( 'single', array( 'label' => __( 'فقط یک مورد باز بماند', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'first_open', array( 'label' => __( 'اولین مورد باز باشد', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'q_typo', __( 'تایپوگرافی سوال', 'baspar-elements' ), '.faq-q' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="faq <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" <?php echo ( 'yes' === $s['single'] ) ? 'data-faq-single' : ''; ?>>
				<?php
				$i = 0;
				foreach ( (array) $s['items'] as $it ) :
					$open = ( 'yes' === $s['first_open'] && 0 === $i ) ? ' is-open' : '';
					?>
					<div class="faq-item<?php echo esc_attr( $open ); ?>">
						<button class="faq-q" type="button">
							<span class="faq-n"><?php echo esc_html( number_format_i18n( $i + 1 ) ); ?></span>
							<span class="faq-qt"><?php echo esc_html( $it['q'] ); ?></span>
							<span class="faq-icon"><?php echo icon_svg( 'plus', 18 ); // phpcs:ignore ?></span>
						</button>
						<div class="faq-a-wrap"><div class="faq-a"><?php echo esc_html( $it['a'] ); ?></div></div>
					</div>
					<?php
					++$i;
				endforeach;
				?>
			</div>
		</div>
		<?php
	}
}
