<?php
/**
 * Comparison table with our column highlighted. Rows are repeater; columns are
 * fixed: feature | us | others.
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
 * Class Baspar_Compare_Table
 */
class Baspar_Compare_Table extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-compare-table';
	}

	public function get_title() {
		return __( 'بسپار — جدول مقایسه', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'col_feature', array( 'label' => __( 'عنوان ستون ویژگی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ویژگی' ) );
		$this->add_control( 'col_us', array( 'label' => __( 'عنوان ستون ما', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'بسپارمارکت' ) );
		$this->add_control( 'col_others', array( 'label' => __( 'عنوان ستون دیگران', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'سایر تأمین‌کنندگان' ) );

		$rep = new Repeater();
		$rep->add_control( 'feature', array( 'label' => __( 'ویژگی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'us', array( 'label' => __( 'ما', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'others', array( 'label' => __( 'دیگران', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'rows',
			array(
				'label'       => __( 'ردیف‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ feature }}}',
				'default'     => array(
					array( 'feature' => 'گواهی آنالیز (COA)', 'us' => 'همراه هر سفارش', 'others' => 'اغلب ندارند' ),
					array( 'feature' => 'زمان ارسال', 'us' => '۲۴ ساعته', 'others' => '۳ تا ۷ روز' ),
					array( 'feature' => 'مشاوره فنی', 'us' => 'رایگان', 'others' => 'محدود' ),
					array( 'feature' => 'گارانتی مغایرت', 'us' => '۴۸ ساعت', 'others' => 'ندارند' ),
					array( 'feature' => 'تنوع گرید', 'us' => '+۱۰۰ گرید', 'others' => 'محدود' ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ ستون ما', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="cmp-wrap">
				<table class="cmp">
					<thead>
						<tr>
							<th><?php echo esc_html( $s['col_feature'] ); ?></th>
							<th class="us"><?php echo esc_html( $s['col_us'] ); ?></th>
							<th><?php echo esc_html( $s['col_others'] ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( (array) $s['rows'] as $r ) : ?>
							<tr>
								<td><?php echo esc_html( $r['feature'] ); ?></td>
								<td class="us"><?php echo icon_svg( 'check', 16 ); // phpcs:ignore ?> <?php echo esc_html( $r['us'] ); ?></td>
								<td class="dim"><?php echo esc_html( $r['others'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
}
