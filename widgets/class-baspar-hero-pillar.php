<?php
/**
 * Pillar hero — text column + product mockup card (hv-frame with tag,
 * placeholder, 3-col meta) + two floating cards.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Hero_Pillar extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-hero-pillar'; }
	public function get_title() { return __( 'بسپار — هیرو پیلار (محصول)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-product-image'; }

	protected function register_controls() {
		$this->start_controls_section( 'text', array( 'label' => __( 'متن', 'baspar-elements' ) ) );
		$this->add_control( 'eyebrow_mono', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'EST. 2020 · CHEMICAL TRADE' ) );
		$this->add_control( 'eyebrow_fa', array( 'label' => __( 'برچسب فارسی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'کیهان بسپار نیک اندیشان' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان (<em> هایلایت)', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => 'خرید مستقیم <em>مواد اولیه شیمیایی</em><br>از مرجع تخصصی صنایع' ) );
		$this->add_control( 'subtitle', array( 'label' => __( 'زیرعنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4, 'default' => 'تأمین و توزیع تخصصی مواد اولیه پلیمر، رنگ، رزین و شیمیایی برای کارخانه‌ها و واحدهای تولیدی در سراسر ایران.' ) );
		$this->add_control( 'btn1_text', array( 'label' => __( 'دکمه ۱', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام در واتس‌اپ' ) );
		$this->add_control( 'btn1_link', array( 'label' => __( 'لینک ۱', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( 'دکمه ۲', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تماس تلفنی' ) );
		$this->add_control( 'btn2_link', array( 'label' => __( 'لینک ۲', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'tel:+989120733965' ) ) );

		$rb = new Repeater();
		$rb->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'bullets', array(
			'label' => __( 'ویژگی‌ها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rb->get_controls(),
			'title_field' => '{{{ text }}}',
			'default' => array(
				array( 'text' => 'ارسال ۲۴ ساعته به سراسر ایران' ),
				array( 'text' => 'گارانتی ۴۸ ساعته مغایرت' ),
				array( 'text' => 'COA و دیتاشیت رسمی همراه هر سفارش' ),
			),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'product', array( 'label' => __( 'کارت محصول (mockup)', 'baspar-elements' ) ) );
		$this->add_control( 'frame_tag', array( 'label' => __( 'برچسب بالای فریم', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'PRODUCT · LOT 2026' ) );
		$this->add_control( 'product_image', array( 'label' => __( 'تصویر محصول (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_control( 'product_title', array( 'label' => __( 'نام محصول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تیتانیوم دی‌اکسید R-838' ) );
		$this->add_control( 'product_sub', array( 'label' => __( 'زیرعنوان محصول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'گرید Rutile · صنعتی' ) );
		$this->add_control( 'meta1_lbl', array( 'label' => __( 'مشخصه ۱ — برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'PURITY' ) );
		$this->add_control( 'meta1_val', array( 'label' => __( 'مشخصه ۱ — مقدار', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '۹۸.۵٪' ) );
		$this->add_control( 'meta2_lbl', array( 'label' => __( 'مشخصه ۲ — برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ORIGIN' ) );
		$this->add_control( 'meta2_val', array( 'label' => __( 'مشخصه ۲ — مقدار', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'China' ) );
		$this->add_control( 'meta3_lbl', array( 'label' => __( 'مشخصه ۳ — برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'CERT' ) );
		$this->add_control( 'meta3_val', array( 'label' => __( 'مشخصه ۳ — مقدار', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'COA' ) );
		$this->add_control( 'float1_title', array( 'label' => __( 'کارت شناور ۱', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ارسال ۲۴ ساعته' ) );
		$this->add_control( 'float1_sub', array( 'label' => __( 'کارت ۱ زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'NATIONWIDE' ) );
		$this->add_control( 'float2_lbl', array( 'label' => __( 'کارت شناور ۲ — برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'SKU CATALOG' ) );
		$this->add_control( 'float2_num', array( 'label' => __( 'کارت ۲ عدد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '۱۰۰+' ) );
		$this->add_control( 'float2_sub', array( 'label' => __( 'کارت ۲ زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'گرید موجود' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'h1_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.hero-h1' );
		$this->add_color( 'h1_color', __( 'رنگ عنوان', 'baspar-elements' ), '.hero-h1', 'color' );
		$this->add_reveal_toggle();
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$rev = $this->reveal_class( $s );
		?>
		<div class="baspar-scope">
			<section class="hero">
				<div class="hero-grid-bg" aria-hidden="true"></div>
				<div class="hero-inner">
					<div class="<?php echo esc_attr( $rev ); ?>">
						<div class="hero-eyebrow"><span class="mono"><?php echo esc_html( $s['eyebrow_mono'] ); ?></span><span class="eyebrow-line"></span><span><?php echo esc_html( $s['eyebrow_fa'] ); ?></span></div>
						<h1 class="hero-h1"><?php echo wp_kses_post( $s['title'] ); ?></h1>
						<p class="hero-sub"><?php echo esc_html( $s['subtitle'] ); ?></p>
						<div class="hero-ctas">
							<?php if ( $s['btn1_text'] ) : ?><a href="<?php echo esc_url( $s['btn1_link']['url'] ?? '#' ); ?>" class="btn btn-whatsapp"><?php echo icon_svg( 'whatsapp', 18 ); // phpcs:ignore ?><?php echo esc_html( $s['btn1_text'] ); ?></a><?php endif; ?>
							<?php if ( $s['btn2_text'] ) : ?><a href="<?php echo esc_url( $s['btn2_link']['url'] ?? '#' ); ?>" class="btn btn-ghost"><?php echo icon_svg( 'phone', 18 ); // phpcs:ignore ?><?php echo esc_html( $s['btn2_text'] ); ?></a><?php endif; ?>
						</div>
						<ul class="hero-bullets">
							<?php foreach ( (array) $s['bullets'] as $b ) : ?><li><?php echo icon_svg( 'check', 14 ); // phpcs:ignore ?><?php echo esc_html( $b['text'] ); ?></li><?php endforeach; ?>
						</ul>
					</div>
					<div class="hero-visual <?php echo esc_attr( $rev ); ?>" data-reveal-delay="120">
						<div class="hv-frame">
							<div class="hv-tag"><?php echo esc_html( $s['frame_tag'] ); ?></div>
							<div class="hv-placeholder">
								<?php if ( ! empty( $s['product_image']['url'] ) ) : ?>
									<img src="<?php echo esc_url( $s['product_image']['url'] ); ?>" alt="<?php echo esc_attr( $s['product_title'] ); ?>" style="width:100%;height:100%;object-fit:cover" />
								<?php else : ?>
									<div class="hv-stripes" aria-hidden="true"></div>
									<div class="hv-ph-text">
										<?php echo icon_svg( 'package', 56 ); // phpcs:ignore ?>
										<span><?php echo esc_html( $s['product_title'] ); ?></span>
										<span class="mono dim small"><?php echo esc_html( $s['product_sub'] ); ?></span>
									</div>
								<?php endif; ?>
							</div>
							<div class="hv-meta">
								<div><span class="mono"><?php echo esc_html( $s['meta1_lbl'] ); ?></span><strong><?php echo esc_html( $s['meta1_val'] ); ?></strong></div>
								<div><span class="mono"><?php echo esc_html( $s['meta2_lbl'] ); ?></span><strong><?php echo esc_html( $s['meta2_val'] ); ?></strong></div>
								<div><span class="mono"><?php echo esc_html( $s['meta3_lbl'] ); ?></span><strong><?php echo esc_html( $s['meta3_val'] ); ?></strong></div>
							</div>
						</div>
						<div class="hv-card hv-card-1">
							<span class="mono">DELIVERY</span>
							<strong><?php echo esc_html( $s['float1_title'] ); ?></strong>
							<span class="dim"><?php echo esc_html( $s['float1_sub'] ); ?></span>
						</div>
						<div class="hv-card hv-card-2">
							<span class="mono"><?php echo esc_html( $s['float2_lbl'] ); ?></span>
							<strong><?php echo esc_html( $s['float2_num'] ); ?></strong>
							<span class="dim"><?php echo esc_html( $s['float2_sub'] ); ?></span>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php
	}
}
