<?php
/**
 * Home hero — text column + animated "live catalog console" with rows and two
 * floating cards. Console rows are a repeater; everything is editable.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Hero
 */
class Baspar_Hero extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-hero';
	}

	public function get_title() {
		return __( 'بسپار — هیرو صفحه اصلی', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-banner';
	}

	protected function register_controls() {
		/* ---- Text column ---- */
		$this->start_controls_section( 'text', array( 'label' => __( 'متن هیرو', 'baspar-elements' ) ) );
		$this->add_control( 'eyebrow_mono', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'EST. 2020 · CHEMICAL TRADE' ) );
		$this->add_control( 'eyebrow_fa', array( 'label' => __( 'برچسب فارسی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'کیهان بسپار نیک اندیشان' ) );
		$this->add_control(
			'title',
			array(
				'label'   => __( 'عنوان (از <em> برای هایلایت استفاده کنید)', 'baspar-elements' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => 'مرجع تخصصی <em>تأمین مواد اولیه</em><br>صنایع پلیمری، شیمیایی، رنگ و چسب.',
			)
		);
		$this->add_control(
			'subtitle',
			array(
				'label'   => __( 'زیرعنوان', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 4,
				'default' => 'بسپارمارکت با تجربه‌ای بیش از ۵ سال در واردات و توزیع، تأمین‌کننده‌ی بیش از ۱۰۰ ماده اولیه‌ی شیمیایی برای کارخانه‌ها و واحدهای تولیدی در سراسر ایران است.',
			)
		);
		$this->add_control( 'btn1_text', array( 'label' => __( 'دکمه اول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ورود به فروشگاه' ) );
		$this->add_control( 'btn1_link', array( 'label' => __( 'لینک دکمه اول', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( 'دکمه دوم', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام قیمت' ) );
		$this->add_control( 'btn2_link', array( 'label' => __( 'لینک دکمه دوم', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );

		$rb = new Repeater();
		$rb->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'bullets',
			array(
				'label'       => __( 'ویژگی‌ها (با تیک)', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rb->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array(
					array( 'text' => 'ارسال ۲۴ ساعته به سراسر ایران' ),
					array( 'text' => 'گارانتی ۴۸ ساعته مغایرت' ),
					array( 'text' => 'مشاوره فنی رایگان' ),
				),
			)
		);
		$this->end_controls_section();

		/* ---- Console ---- */
		$this->start_controls_section( 'console', array( 'label' => __( 'کنسول کاتالوگ', 'baspar-elements' ) ) );
		$this->add_control( 'console_title', array( 'label' => __( 'عنوان کنسول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'BASPARMARKET · CATALOG' ) );
		$this->add_control( 'console_live', array( 'label' => __( 'برچسب LIVE', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'LIVE · امروز' ) );
		$this->add_control( 'console_logo', array( 'label' => __( 'لوگو کنسول', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => BASPAR_ELEMENTS_ASSETS . 'images/logo.png' ) ) );
		$this->add_control( 'console_brand', array( 'label' => __( 'نام برند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'بسپارمارکت' ) );
		$this->add_control( 'console_brand_sub', array( 'label' => __( 'زیرعنوان برند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'CHEMICAL TRADE · EST. 2020' ) );

		$rr = new Repeater();
		$rr->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'layers' ) );
		$rr->add_control( 'code', array( 'label' => __( 'کد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'TPU' ) );
		$rr->add_control( 'name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rr->add_control( 'sub', array( 'label' => __( 'زیرنام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rr->add_control( 'status', array( 'label' => __( 'وضعیت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'موجود' ) );
		$rr->add_control( 'warn', array( 'label' => __( 'وضعیت هشدار (نارنجی)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->add_control(
			'rows',
			array(
				'label'       => __( 'ردیف‌های محصول', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rr->get_controls(),
				'title_field' => '{{{ code }}} — {{{ name }}}',
				'default'     => array(
					array( 'icon' => 'layers', 'code' => 'TPU', 'name' => 'ترموپلاستیک پلی‌اورتان', 'sub' => 'Elastollan 1185A — آلمان', 'status' => 'موجود', 'warn' => '' ),
					array( 'icon' => 'beaker', 'code' => 'MEK', 'name' => 'متیل اتیل کتون', 'sub' => 'Shell — چین', 'status' => 'موجود', 'warn' => '' ),
					array( 'icon' => 'palette', 'code' => 'TiO₂', 'name' => 'تیتانیوم دی‌اکسید', 'sub' => 'Lomon R-838 — چین', 'status' => 'موجود', 'warn' => '' ),
					array( 'icon' => 'flask', 'code' => 'Epoxy', 'name' => 'رزین اپوکسی', 'sub' => 'Hexion — آلمان', 'status' => 'محدود', 'warn' => 'yes' ),
				),
			)
		);
		$this->add_control( 'foot_text', array( 'label' => __( 'متن پاورقی کنسول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '+۹۶ گرید دیگر در فروشگاه' ) );
		$this->add_control( 'foot_link_text', array( 'label' => __( 'لینک پاورقی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده همه ←' ) );

		$this->add_control( 'float1_title', array( 'label' => __( 'کارت شناور ۱ — عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ارسال ۲۴ ساعته' ) );
		$this->add_control( 'float1_sub', array( 'label' => __( 'کارت شناور ۱ — زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'NATIONWIDE' ) );
		$this->add_control( 'float2_mono', array( 'label' => __( 'کارت شناور ۲ — برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'FACTORY · PARTNERS' ) );
		$this->add_control( 'float2_num', array( 'label' => __( 'کارت شناور ۲ — عدد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '۹۸۰+' ) );
		$this->add_control( 'float2_label', array( 'label' => __( 'کارت شناور ۲ — برچسب پایین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'کارخانه فعال' ) );
		$this->end_controls_section();

		/* ---- Style ---- */
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'h1_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.home-hero h1' );
		$this->add_color( 'h1_color', __( 'رنگ عنوان', 'baspar-elements' ), '.home-hero h1', 'color' );
		$this->add_color( 'sub_color', __( 'رنگ زیرعنوان', 'baspar-elements' ), '.home-hero-sub', 'color' );
		$this->add_reveal_toggle();
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$rev = $this->reveal_class( $s );
		?>
		<div class="baspar-scope">
			<section class="home-hero">
				<div class="home-hero-grid" aria-hidden="true"></div>
				<div class="home-hero-inner">
					<div class="<?php echo esc_attr( $rev ); ?>">
						<div class="hero-eyebrow">
							<span class="mono"><?php echo esc_html( $s['eyebrow_mono'] ); ?></span>
							<span class="eyebrow-line"></span>
							<span><?php echo esc_html( $s['eyebrow_fa'] ); ?></span>
						</div>
						<h1><?php echo wp_kses_post( $s['title'] ); ?></h1>
						<p class="home-hero-sub"><?php echo esc_html( $s['subtitle'] ); ?></p>
						<div class="home-hero-ctas">
							<?php if ( $s['btn1_text'] ) : ?>
								<a href="<?php echo esc_url( $s['btn1_link']['url'] ?? '#' ); ?>" class="btn btn-primary"><?php echo icon_svg( 'cart', 18 ); /* phpcs:ignore */ ?><?php echo esc_html( $s['btn1_text'] ); ?></a>
							<?php endif; ?>
							<?php if ( $s['btn2_text'] ) : ?>
								<a href="<?php echo esc_url( $s['btn2_link']['url'] ?? '#' ); ?>" class="btn btn-ghost"><?php echo icon_svg( 'whatsapp', 18 ); /* phpcs:ignore */ ?><?php echo esc_html( $s['btn2_text'] ); ?></a>
							<?php endif; ?>
						</div>
						<div class="home-hero-bullets">
							<?php foreach ( (array) $s['bullets'] as $b ) : ?>
								<span><?php echo icon_svg( 'check', 14 ); /* phpcs:ignore */ ?><?php echo esc_html( $b['text'] ); ?></span>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="hero-art <?php echo esc_attr( $rev ); ?>" data-reveal-delay="120">
						<div class="hh-console">
							<div class="hh-console-head">
								<div class="hh-console-head-l">
									<div class="hh-console-dots"><span></span><span></span><span></span></div>
									<span class="hh-console-title"><?php echo esc_html( $s['console_title'] ); ?></span>
								</div>
								<span class="hh-console-live"><?php echo esc_html( $s['console_live'] ); ?></span>
							</div>
							<div class="hh-console-body">
								<div class="hh-console-brand">
									<?php if ( ! empty( $s['console_logo']['url'] ) ) : ?>
										<img src="<?php echo esc_url( $s['console_logo']['url'] ); ?>" alt="" />
									<?php endif; ?>
									<div>
										<strong><?php echo esc_html( $s['console_brand'] ); ?></strong>
										<span class="mono"><?php echo esc_html( $s['console_brand_sub'] ); ?></span>
									</div>
								</div>
								<div class="hh-rows">
									<?php foreach ( (array) $s['rows'] as $r ) : ?>
										<div class="hh-row">
											<div class="hh-row-ic"><?php echo icon_svg( $r['icon'] ? $r['icon'] : 'package', 18 ); /* phpcs:ignore */ ?></div>
											<div class="hh-row-code"><?php echo esc_html( $r['code'] ); ?></div>
											<div class="hh-row-name"><?php echo esc_html( $r['name'] ); ?><span><?php echo esc_html( $r['sub'] ); ?></span></div>
											<div class="hh-row-stat <?php echo ( 'yes' === $r['warn'] ) ? 'warn' : ''; ?>"><?php echo esc_html( $r['status'] ); ?></div>
											<?php echo str_replace( '<svg', '<svg class="arr"', icon_svg( 'arrow', 14 ) ); /* phpcs:ignore */ ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="hh-console-foot">
								<span class="mono"><?php echo esc_html( $s['foot_text'] ); ?></span>
								<strong><?php echo esc_html( $s['foot_link_text'] ); ?></strong>
							</div>
						</div>
						<div class="hh-float-1">
							<div class="ic"><?php echo icon_svg( 'truck', 20 ); /* phpcs:ignore */ ?></div>
							<div>
								<strong><?php echo esc_html( $s['float1_title'] ); ?></strong>
								<span><?php echo esc_html( $s['float1_sub'] ); ?></span>
							</div>
						</div>
						<div class="hh-float-2">
							<span class="mono"><?php echo esc_html( $s['float2_mono'] ); ?></span>
							<strong><?php echo esc_html( $s['float2_num'] ); ?></strong>
							<span><?php echo esc_html( $s['float2_label'] ); ?></span>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php
	}
}
