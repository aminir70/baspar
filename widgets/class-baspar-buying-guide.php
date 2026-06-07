<?php
/**
 * Buying guide — numbered rows (01, 02, ...) with rich content. Each row can
 * carry a grade-grid (INDUSTRIAL/LAB/USP) and/or a check-list.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Buying_Guide extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-buying-guide'; }
	public function get_title() { return __( 'بسپار — راهنمای خرید', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-help-o'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'ردیف‌ها', 'baspar-elements' ) ) );

		$gr = new Repeater();
		$gr->add_control( 'code', array( 'label' => __( 'کد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$gr->add_control( 'title', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$gr->add_control( 'note', array( 'label' => __( 'یادداشت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );

		$ck = new Repeater();
		$ck->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );

		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => __( 'عنوان ردیف', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'body', array( 'label' => __( 'متن (HTML مجاز)', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG ) );
		$rep->add_control( 'grades', array(
			'label' => __( 'سلول‌های گرید', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $gr->get_controls(),
			'title_field' => '{{{ title }}}',
		) );
		$rep->add_control( 'checks', array(
			'label' => __( 'لیست تیک‌دار', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $ck->get_controls(),
			'title_field' => '{{{ text }}}',
		) );

		$this->add_control( 'items', array(
			'label' => __( 'ردیف‌های راهنما', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array(
					'title' => 'گرید مناسب فرمولاسیون خود را بشناسید',
					'body' => '<p>هر ماده در چندین <strong>گرید</strong> ارائه می‌شود؛ انتخاب گرید نامناسب می‌تواند کیفیت محصول نهایی را خراب کند.</p>',
					'grades' => array(
						array( 'code' => 'IND', 'title' => 'INDUSTRIAL', 'note' => 'گرید صنعتی' ),
						array( 'code' => 'LAB', 'title' => 'LAB', 'note' => 'گرید آزمایشگاهی' ),
						array( 'code' => 'USP', 'title' => 'USP', 'note' => 'گرید دارویی' ),
					),
				),
				array(
					'title' => 'استعلام قیمت و موجودی',
					'body' => '<p>برای دریافت قیمت دقیق با کارشناس ما در تماس باشید.</p>',
					'checks' => array(
						array( 'text' => 'پاسخگویی در ساعات کاری در کمتر از ۱۵ دقیقه' ),
						array( 'text' => 'ارائه پروفرما اینویس رسمی برای سفارش‌های B2B' ),
					),
				),
				array(
					'title' => 'تأیید سفارش و پرداخت',
					'body' => '<p>پس از تأیید موجودی، فاکتور رسمی صادر و آماده‌سازی انجام می‌شود.</p>',
				),
				array(
					'title' => 'دریافت COA و دیتاشیت',
					'body' => '<p>همراه هر سفارش، <strong>گواهی آنالیز (COA)</strong> و دیتاشیت معتبر ارائه می‌گردد.</p>',
				),
				array(
					'title' => 'ارسال و تحویل',
					'body' => '<p>ارسال ۲۴ ساعته به سراسر کشور؛ هزینه بر اساس وزن و مقصد محاسبه می‌شود.</p>',
				),
			),
		) );
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
			<div class="guide <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php $i = 0; foreach ( (array) $s['items'] as $row ) : ++$i; ?>
					<div class="guide-row">
						<div class="guide-num"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></div>
						<div class="guide-body">
							<h3><?php echo esc_html( $row['title'] ); ?></h3>
							<div class="guide-content"><?php echo wp_kses_post( $row['body'] ); ?></div>
							<?php if ( ! empty( $row['grades'] ) ) : ?>
								<div class="grade-grid">
									<?php foreach ( (array) $row['grades'] as $g ) : ?>
										<div class="grade-cell">
											<span class="mono"><?php echo esc_html( $g['code'] ); ?></span>
											<strong><?php echo esc_html( $g['title'] ); ?></strong>
											<span><?php echo esc_html( $g['note'] ); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $row['checks'] ) ) : ?>
								<ul class="check-list">
									<?php foreach ( (array) $row['checks'] as $c ) : ?>
										<li><?php echo icon_svg( 'check', 18 ); // phpcs:ignore ?><span><?php echo wp_kses_post( $c['text'] ); ?></span></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
