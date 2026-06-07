<?php
/**
 * Vertical timeline (year badge + title + text).
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
 * Class Baspar_Timeline
 */
class Baspar_Timeline extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-timeline';
	}

	public function get_title() {
		return __( 'بسپار — تایم‌لاین', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-time-line';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'مراحل', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'year', array( 'label' => __( 'سال', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '۱۳۹۹' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'مراحل', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ year }}} — {{{ title }}}',
				'default'     => array(
					array( 'year' => '۱۳۹۹', 'title' => 'آغاز فعالیت', 'text' => 'تأسیس شرکت کیهان بسپار نیک اندیشان و شروع واردات مواد اولیه.' ),
					array( 'year' => '۱۴۰۱', 'title' => 'گسترش سبد محصولات', 'text' => 'افزودن گریدهای جدید رنگ، رزین و کامپوزیت به سبد تأمین.' ),
					array( 'year' => '۱۴۰۲', 'title' => 'راه‌اندازی فروشگاه آنلاین', 'text' => 'عرضه آنلاین بیش از ۱۰۰ ماده اولیه با امکان استعلام آنی.' ),
					array( 'year' => '۱۴۰۴', 'title' => 'شبکه توزیع سراسری', 'text' => 'همکاری با بیش از ۹۸۰ کارخانه در سراسر کشور.' ),
				),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان مرحله', 'baspar-elements' ), '.tl-item h4' );
		$this->add_typography( 'text_typo', __( 'تایپوگرافی توضیح', 'baspar-elements' ), '.tl-item p' );

		$this->add_control( '_year_heading', array( 'label' => __( 'بَج سال', 'baspar-elements' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_typography( 'year_typo', __( 'تایپوگرافی سال', 'baspar-elements' ), '.tl-year' );
		$this->add_color( 'year_color', __( 'رنگ متن سال', 'baspar-elements' ), '.tl-year', 'color' );
		$this->add_color( 'year_bg', __( 'پس‌زمینه دایره سال', 'baspar-elements' ), '.tl-year', 'background' );
		$this->add_control(
			'year_size',
			array(
				'label'      => __( 'اندازه دایره سال (px)', 'baspar-elements' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 32, 'max' => 80, 'step' => 1 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 44 ),
				'selectors'  => array(
					'{{WRAPPER}} .tl-year' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_border( 'year_border', '.tl-year' );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = (array) $s['items'];
		$last  = count( $items ) - 1;
		?>
		<div class="baspar-scope">
			<div class="timeline <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php foreach ( $items as $i => $it ) :
					$cls = ( 0 === $i ) ? ' first' : ( ( $last === $i ) ? ' last' : '' );
					?>
					<div class="tl-item<?php echo esc_attr( $cls ); ?>">
						<div class="tl-year"><?php echo esc_html( $it['year'] ); ?></div>
						<h4><?php echo esc_html( $it['title'] ); ?></h4>
						<p><?php echo esc_html( $it['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
