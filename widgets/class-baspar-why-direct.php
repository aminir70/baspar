<?php
/**
 * Why-direct — 4 numbered cards with icon-inside-title style.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Why_Direct extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-why-direct'; }
	public function get_title() { return __( 'بسپار — چرا واردات مستقیم', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-check-circle'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'کارت‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'shield' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control( 'items', array(
			'label' => __( 'کارت‌ها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'icon' => 'shield', 'title' => 'حذف واسطه‌ها', 'desc' => 'تأمین مستقیم از کارخانه با حذف واسطه‌ها، قیمت تمام‌شده را به حداقل می‌رساند.' ),
				array( 'icon' => 'award', 'title' => 'تضمین اصالت کالا', 'desc' => 'هر سفارش با COA و دیتاشیت معتبر از مبدأ همراه است.' ),
				array( 'icon' => 'truck', 'title' => 'لجستیک تخصصی', 'desc' => 'حمل، ترخیص و تحویل تا درب کارخانه با تیم تخصصی.' ),
				array( 'icon' => 'chat', 'title' => 'پشتیبانی فنی', 'desc' => 'مشاوره فرمولاسیون و انتخاب گرید توسط کارشناس مجرب.' ),
			),
		) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.why-card h3' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="why-grid">
				<?php $i = 0; foreach ( (array) $s['items'] as $it ) : ++$i; ?>
					<div class="why-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 60 ); ?>">
						<div class="why-num"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></div>
						<h3><span class="ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'shield', 22 ); // phpcs:ignore ?></span> <?php echo esc_html( $it['title'] ); ?></h3>
						<p><?php echo esc_html( $it['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
