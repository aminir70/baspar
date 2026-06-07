<?php
/**
 * Process steps (numbered circles connected by a dotted line).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Process
 */
class Baspar_Process extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-process';
	}

	public function get_title() {
		return __( 'بسپار — مراحل خرید', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-number-field';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'مراحل', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'مراحل', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'title' => 'انتخاب محصول', 'desc' => 'گرید مورد نظر را از فروشگاه یا با مشاوره کارشناس انتخاب کنید.' ),
					array( 'title' => 'استعلام و ثبت سفارش', 'desc' => 'قیمت و موجودی را در واتس‌اپ استعلام و سفارش را ثبت کنید.' ),
					array( 'title' => 'پرداخت و صدور فاکتور', 'desc' => 'فاکتور رسمی صادر و پس از تأیید پرداخت، آماده‌سازی انجام می‌شود.' ),
					array( 'title' => 'ارسال سریع', 'desc' => 'ارسال ۲۴ ساعته به همراه COA و دیتاشیت به سراسر ایران.' ),
				),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="process <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<div class="process-line" aria-hidden="true"></div>
				<?php
				$i = 0;
				foreach ( (array) $s['items'] as $it ) :
					?>
					<div class="process-step">
						<div class="ps-n"><?php echo esc_html( number_format_i18n( $i + 1 ) ); ?></div>
						<div class="ps-body">
							<h4><?php echo esc_html( $it['title'] ); ?></h4>
							<p><?php echo esc_html( $it['desc'] ); ?></p>
						</div>
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
