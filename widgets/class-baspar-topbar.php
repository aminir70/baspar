<?php
/**
 * Top bar widget (hours + social + contact).
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
 * Class Baspar_Topbar
 */
class Baspar_Topbar extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-topbar';
	}

	public function get_title() {
		return __( 'بسپار — نوار بالا (TopBar)', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-header';
	}

	protected function register_controls() {
		/* ---- Content ---- */
		$this->start_controls_section(
			'content',
			array( 'label' => __( 'محتوا', 'baspar-elements' ) )
		);

		$this->add_control(
			'hours_text',
			array(
				'label'   => __( 'متن ساعات کاری', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'پاسخگویی شنبه تا چهارشنبه ۹:۰۰–۱۸:۰۰', 'baspar-elements' ),
			)
		);
		$this->add_control(
			'email',
			array(
				'label'   => __( 'ایمیل', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'info@basparmarket.com',
			)
		);
		$this->add_control(
			'phone',
			array(
				'label'   => __( 'تلفن (نمایش)', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '۰۹۱۲ ۰۷۳ ۳۹۶۵',
			)
		);
		$this->add_control(
			'phone_raw',
			array(
				'label'   => __( 'تلفن (لینک tel:)', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '+989120733965',
			)
		);
		$this->add_control(
			'telegram',
			array(
				'label'   => __( 'لینک تلگرام', 'baspar-elements' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => 'https://t.me/basparmarket' ),
			)
		);
		$this->add_control(
			'instagram',
			array(
				'label'   => __( 'لینک اینستاگرام', 'baspar-elements' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => 'https://instagram.com/basparmarket' ),
			)
		);

		$this->end_controls_section();

		/* ---- Style ---- */
		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'استایل', 'baspar-elements' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_color( 'bg', __( 'رنگ پس‌زمینه', 'baspar-elements' ), '', 'background' );
		$this->add_color( 'txt', __( 'رنگ متن', 'baspar-elements' ), '.topbar-inner', 'color' );
		$this->add_typography( 'typo', __( 'تایپوگرافی', 'baspar-elements' ), '.topbar-inner' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope bspr-topbar topbar">
			<div class="topbar-inner">
				<div class="topbar-left">
					<span class="dot"></span>
					<span><?php echo esc_html( $s['hours_text'] ); ?></span>
				</div>
				<div class="topbar-right">
					<?php if ( ! empty( $s['telegram']['url'] ) ) : ?>
						<a href="<?php echo esc_url( $s['telegram']['url'] ); ?>" class="tb-link" aria-label="تلگرام"><?php echo icon_svg( 'telegram', 14 ); /* phpcs:ignore */ ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $s['instagram']['url'] ) ) : ?>
						<a href="<?php echo esc_url( $s['instagram']['url'] ); ?>" class="tb-link" aria-label="اینستاگرام"><?php echo icon_svg( 'instagram', 14 ); /* phpcs:ignore */ ?></a>
					<?php endif; ?>
					<span class="tb-divider"></span>
					<?php if ( $s['email'] ) : ?>
						<a href="mailto:<?php echo esc_attr( $s['email'] ); ?>" class="tb-link"><?php echo icon_svg( 'mail', 14 ); /* phpcs:ignore */ ?><span class="ltr"><?php echo esc_html( $s['email'] ); ?></span></a>
					<?php endif; ?>
					<span class="tb-divider"></span>
					<?php if ( $s['phone'] ) : ?>
						<a href="tel:<?php echo esc_attr( $s['phone_raw'] ); ?>" class="tb-link"><?php echo icon_svg( 'phone', 14 ); /* phpcs:ignore */ ?><span class="ltr"><?php echo esc_html( $s['phone'] ); ?></span></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
