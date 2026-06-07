<?php
/**
 * Principles grid (6 cards) — uses trust-card visual language but in 3×2 grid
 * for the about page "اصول کاری ما" section.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Principles extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-principles'; }
	public function get_title() { return __( 'بسپار — اصول کاری', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-bullet-list'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'اصول', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'shield' ) );
		$rep->add_control( 'tag', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control( 'items', array(
			'label' => __( 'کارت‌ها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'icon' => 'shield', 'tag' => 'TRUST', 'title' => 'صداقت در معامله', 'desc' => 'شفافیت کامل در اعلام قیمت، مبدأ و گرید کالا.' ),
				array( 'icon' => 'award', 'tag' => 'QUALITY', 'title' => 'کیفیت گارانتی‌دار', 'desc' => 'تأمین فقط از کارخانه‌های معتبر و با COA رسمی.' ),
				array( 'icon' => 'chat', 'tag' => 'SUPPORT', 'title' => 'پاسخگویی سریع', 'desc' => 'پاسخ استعلام در کمتر از ۱ ساعت در ساعات کاری.' ),
				array( 'icon' => 'truck', 'tag' => 'LOGISTICS', 'title' => 'تحویل به‌موقع', 'desc' => 'ارسال ۲۴ ساعته با هماهنگی کامل لجستیک.' ),
				array( 'icon' => 'refresh', 'tag' => 'WARRANTY', 'title' => 'گارانتی مغایرت', 'desc' => '۴۸ ساعت مهلت بازگشت در صورت عدم تطابق.' ),
				array( 'icon' => 'users', 'tag' => 'PARTNERSHIP', 'title' => 'شراکت بلندمدت', 'desc' => 'تأمین پایدار برای صنایع و کارخانه‌های فعال.' ),
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
			<div class="trust-grid" style="grid-template-columns:repeat(3,1fr)">
				<?php $i = 0; foreach ( (array) $s['items'] as $it ) : ++$i; ?>
					<div class="trust-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 60 ); ?>">
						<div class="trust-icon"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'shield', 28 ); // phpcs:ignore ?></div>
						<?php if ( $it['tag'] ) : ?><div class="trust-tag mono"><?php echo esc_html( $it['tag'] ); ?></div><?php endif; ?>
						<h3><?php echo esc_html( $it['title'] ); ?></h3>
						<p><?php echo esc_html( $it['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
