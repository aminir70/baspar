<?php
/**
 * Seller-type comparison — section header (kicker / heading / intro), a real
 * <table> built from a repeater, and an optional closing paragraph.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\inline_kses;
use function BasparElements\slash_links;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Seller_Compare extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-seller-compare'; }
	public function get_title() { return __( 'بسپار — مقایسه انواع فروشنده', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-table'; }

	protected function register_controls() {
		$this->start_controls_section( 'head', array( 'label' => __( 'سرتیتر بخش', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'WHERE TO BUY' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'فروشنده مواد شیمیایی؛ از واردکننده بخریم، پخش‌کننده یا فروشگاه؟' ) );
		$this->add_control( 'heading_tag', array( 'label' => __( 'تگ عنوان', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div', 'p' => 'p' ) ) );
		$this->add_control( 'intro', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => 'بازار مواد شیمیایی ایران چند لایه است و هر لایه برای یک نوع خریدار ساخته شده. اگر بدانید از کدام بخرید، هم پول کمتری می‌دهید هم ریسک کمتری می‌کنید.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'table', array( 'label' => __( 'جدول', 'baspar-elements' ) ) );
		$this->add_control( 'caption', array( 'label' => __( 'عنوان پنهان جدول (برای دسترس‌پذیری)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'col1', array( 'label' => __( 'ستون ۱', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'نوع فروشنده' ) );
		$this->add_control( 'col2', array( 'label' => __( 'ستون ۲', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مناسب برای' ) );
		$this->add_control( 'col3', array( 'label' => __( 'ستون ۳', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'حداقل خرید' ) );
		$this->add_control( 'col4', array( 'label' => __( 'ستون ۴', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'برگه آنالیز' ) );
		$this->add_control( 'col5', array( 'label' => __( 'ستون ۵', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'نکته' ) );

		$rep = new Repeater();
		$rep->add_control( 'c1', array( 'label' => __( 'ستون ۱', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'c2', array( 'label' => __( 'ستون ۲', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'c3', array( 'label' => __( 'ستون ۳', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'c4', array( 'label' => __( 'ستون ۴', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'c5', array( 'label' => __( 'ستون ۵', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$rep->add_control( 'highlight', array( 'label' => __( 'ردیف مشخص‌شده', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'rows', array(
			'label'       => __( 'ردیف‌ها', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ c1 }}}',
			'default'     => array(
				array( 'c1' => 'واردکننده مستقیم', 'c2' => 'تولیدکننده با مصرف ماهانه ثابت', 'c3' => 'یک بسته کامل تا چند تن', 'c4' => 'معمولاً COA کارخانه سازنده', 'c5' => 'کمترین واسطه؛ تنوع برند به واردات خودش محدود است', 'highlight' => 'yes' ),
				array( 'c1' => 'پخش‌کننده و بنکدار', 'c2' => 'کارگاهی که چند ماده را از یک‌جا می‌خواهد', 'c3' => 'متغیر', 'c4' => 'بسته به منبع', 'c5' => 'تنوع بیشتر؛ ردیابی بچ سخت‌تر', 'highlight' => '' ),
				array( 'c1' => 'بازارگاه آنلاین چندفروشنده', 'c2' => 'مقایسه سریع قیمت', 'c3' => 'به فروشنده بستگی دارد', 'c4' => 'به فروشنده بستگی دارد', 'c5' => 'پیدا کردن آسان؛ مسئولیت کیفیت با هر فروشنده جداست', 'highlight' => '' ),
				array( 'c1' => 'فروشگاه مواد آزمایشگاهی', 'c2' => 'نمونه، پژوهش، دانشجو', 'c3' => 'چند گرم تا چند کیلو', 'c4' => 'گرید آزمایشگاهی', 'c5' => 'برای مصرف صنعتی گران تمام می‌شود', 'highlight' => '' ),
			),
		) );
		$this->add_control( 'closing', array( 'label' => __( 'پاراگراف پایانی', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => 'ما در ردیف اول هستیم: واردکننده‌ای که از انبار تهران مستقیم می‌فروشد. اگر فقط چند صد گرم برای آزمایش لازم دارید، فروشگاه مواد آزمایشگاهی گزینه بهتری است؛ برای تولید ماهانه، خرید از واردکننده معمولاً هم ارزان‌تر تمام می‌شود هم ردیابی بچ را ممکن می‌کند.' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.shead-title' );
		$this->add_color( 'title_color', __( 'رنگ عنوان', 'baspar-elements' ), '.shead-title', 'color' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$tag  = in_array( $s['heading_tag'] ?? 'h2', array( 'h2', 'h3', 'h4', 'div', 'p' ), true ) ? $s['heading_tag'] : 'h2';
		$cols = array( $s['col1'], $s['col2'], $s['col3'], $s['col4'], $s['col5'] );
		$rows = array_filter( (array) $s['rows'], static function ( $r ) {
			return ! empty( $r['c1'] );
		} );
		?>
		<div class="baspar-scope">
			<section class="seller-compare <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php if ( $s['kicker'] || $s['title'] || $s['intro'] ) : ?>
					<div class="shead">
						<?php if ( $s['kicker'] ) : ?>
							<div class="shead-kicker"><span class="shead-line"></span><span class="mono"><?php echo esc_html( $s['kicker'] ); ?></span></div>
						<?php endif; ?>
						<?php if ( $s['title'] ) : ?>
							<<?php echo $tag; // phpcs:ignore ?> class="shead-title"><?php echo esc_html( $s['title'] ); ?></<?php echo $tag; // phpcs:ignore ?>>
						<?php endif; ?>
						<?php if ( $s['intro'] ) : ?>
							<div class="shead-sub rich-text"><?php echo slash_links( wp_kses_post( $s['intro'] ) ); // phpcs:ignore ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( $rows ) : ?>
					<div class="sc-wrap" tabindex="0">
						<table class="sc-table">
							<?php if ( ! empty( $s['caption'] ) ) : ?><caption class="sc-caption"><?php echo esc_html( $s['caption'] ); ?></caption><?php endif; ?>
							<thead>
								<tr>
									<?php foreach ( $cols as $c ) : ?><th scope="col"><?php echo esc_html( $c ); ?></th><?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $rows as $r ) : ?>
									<tr<?php echo ( 'yes' === ( $r['highlight'] ?? '' ) ) ? ' class="is-us"' : ''; ?>>
										<th scope="row"><?php echo esc_html( $r['c1'] ); ?></th>
										<td><?php echo esc_html( $r['c2'] ?? '' ); ?></td>
										<td><?php echo esc_html( $r['c3'] ?? '' ); ?></td>
										<td><?php echo esc_html( $r['c4'] ?? '' ); ?></td>
										<td><?php echo esc_html( $r['c5'] ?? '' ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $s['closing'] ) ) : ?>
					<div class="sc-closing rich-text"><?php echo slash_links( wp_kses( $s['closing'], array_merge( inline_kses(), array( 'p' => array() ) ) ) ); // phpcs:ignore ?></div>
				<?php endif; ?>
			</section>
		</div>
		<?php
	}
}
