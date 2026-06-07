<?php
/**
 * B2B services — 6 service cards. Each card has a large semi-transparent
 * number behind, an icon, title, description.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_B2B_Services extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-b2b-services'; }
	public function get_title() { return __( 'بسپار — خدمات B2B', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-info-box'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'کارت‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'package' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control( 'items', array(
			'label' => __( 'خدمات', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'icon' => 'doc', 'title' => 'پروفرما اینویس رسمی', 'desc' => 'صدور پیش‌فاکتور رسمی <strong>(PI)</strong> برای تأییدیه ارز و رویه‌های گمرکی.' ),
				array( 'icon' => 'truck', 'title' => 'حمل و ترخیص', 'desc' => 'هماهنگی کامل حمل از مبدأ تا درب کارخانه و انجام تشریفات ترخیص.' ),
				array( 'icon' => 'shield', 'title' => 'گارانتی کیفیت', 'desc' => 'گارانتی <strong>۴۸ ساعته</strong> مغایرت و بازگشت کالا در صورت عدم تطابق با COA.' ),
				array( 'icon' => 'chat', 'title' => 'مشاوره فرمولاسیون', 'desc' => 'انتخاب گرید مناسب توسط <strong>کارشناس فنی</strong> برای فرمولاسیون شما.' ),
				array( 'icon' => 'globe', 'title' => 'تأمین گرید خاص', 'desc' => 'تأمین گریدهای ویژه و کم‌تیراژ از کارخانه‌های ا‌روپایی به سفارش مشتری.' ),
				array( 'icon' => 'award', 'title' => 'پشتیبانی مشتری ویژه', 'desc' => 'مدیر حساب اختصاصی برای کارخانه‌های با مصرف بالا و سفارش‌های پیوسته.' ),
			),
		) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.b2bs-card h3' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="b2bs">
				<?php $i = 0; foreach ( (array) $s['items'] as $it ) : ++$i; ?>
					<div class="b2bs-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 40 ); ?>">
						<div class="b2bs-num"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></div>
						<div class="b2bs-ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'package', 22 ); // phpcs:ignore ?></div>
						<h3><?php echo esc_html( $it['title'] ); ?></h3>
						<p><?php echo wp_kses_post( $it['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
