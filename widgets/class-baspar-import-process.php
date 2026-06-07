<?php
/**
 * Import process — vertical timeline (vtl) with 6 steps. Each step has a
 * numbered dot on a dashed track + a body card with optional mono prefix.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Import_Process extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-import-process'; }
	public function get_title() { return __( 'بسپار — تایم‌لاین فرآیند واردات', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-time-line'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'مراحل', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'mono', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'STEP' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control( 'items', array(
			'label' => __( 'مراحل', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'mono' => 'INQUIRY', 'title' => 'دریافت RFQ از مشتری', 'desc' => 'مشتری مشخصات مورد نیاز را اعلام و کارشناس ما مبدأ، گرید و قیمت بهینه را پیشنهاد می‌دهد.' ),
				array( 'mono' => 'PROFORMA', 'title' => 'صدور پیش‌فاکتور', 'desc' => 'پروفرما اینویس رسمی برای دریافت تأییدیه ارز و گمرک صادر می‌گردد.' ),
				array( 'mono' => 'ORDER', 'title' => 'ثبت سفارش با کارخانه مبدأ', 'desc' => 'پس از تأیید مالی، سفارش با کارخانه نهایی شده و LOT تولید رزرو می‌شود.' ),
				array( 'mono' => 'SHIPPING', 'title' => 'حمل بین‌المللی', 'desc' => 'بارگیری از مبدأ و حمل دریایی/زمینی تا بنادر/مرز ایران؛ ردیابی آنلاین.' ),
				array( 'mono' => 'CUSTOMS', 'title' => 'ترخیص گمرکی', 'desc' => 'تشریفات گمرکی توسط تیم تخصصی ما انجام و کالا آماده تحویل می‌شود.' ),
				array( 'mono' => 'DELIVERY', 'title' => 'تحویل تا درب کارخانه', 'desc' => 'حمل داخلی به انبار یا کارخانه مقصد به همراه COA و دیتاشیت رسمی.' ),
			),
		) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.vtl-step h3' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$items = (array) $s['items'];
		$last = count( $items ) - 1;
		?>
		<div class="baspar-scope">
			<div class="vtl <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php foreach ( $items as $i => $it ) : ?>
					<div class="vtl-step <?php echo $i === $last ? 'is-final' : ''; ?>">
						<div class="vtl-dot"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></div>
						<div class="vtl-card">
							<h3><?php echo esc_html( $it['title'] ); ?> <?php if ( $it['mono'] ) : ?><span class="mono">· <?php echo esc_html( $it['mono'] ); ?></span><?php endif; ?></h3>
							<p><?php echo esc_html( $it['desc'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
