<?php
/**
 * Home hero v2 — dark aurora hero with drifting molecules, a rotating
 * highlighted word, animated counters and a glass "live catalogue" card.
 * Every motion is CSS (respects reduced motion); content stays readable
 * without JavaScript.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;
use function BasparElements\fa_num;
use function BasparElements\slash_url;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Hero_V2 extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-hero-v2'; }
	public function get_title() { return __( 'بسپار — هیرو صفحه اصلی (نسخه ۲، متحرک)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-header'; }

	protected function register_controls() {
		$this->start_controls_section( 'text', array( 'label' => __( 'متن', 'baspar-elements' ) ) );
		$this->add_control( 'eyebrow_mono', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'EST. 2018 · CHEMICAL TRADE' ) );
		$this->add_control( 'eyebrow_fa', array( 'label' => __( 'برچسب فارسی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'کیهان بسپار نیک اندیشان' ) );
		$this->add_control( 'title_1', array( 'label' => __( 'عنوان — خط اول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مرجع تخصصی' ) );
		$this->add_control( 'title_2', array( 'label' => __( 'عنوان — بخش رنگی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تأمین مواد اولیه' ) );
		$this->add_control( 'title_3', array( 'label' => __( 'عنوان — قبل از کلمه چرخان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'صنایع' ) );
		$rw = new Repeater();
		$rw->add_control( 'word', array( 'label' => __( 'کلمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'words', array(
			'label'       => __( 'کلمات چرخان', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rw->get_controls(),
			'title_field' => '{{{ word }}}',
			'default'     => array( array( 'word' => 'پلیمری' ), array( 'word' => 'شیمیایی' ), array( 'word' => 'رنگ و رزین' ), array( 'word' => 'چسب' ) ),
		) );
		$this->add_control( 'subtitle', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => 'بسپارمارکت با تجربه‌ای بیش از ۷ سال در واردات و توزیع، تأمین‌کننده‌ی بیش از ۱۰۰ ماده اولیه‌ی شیمیایی برای کارخانه‌ها و واحدهای تولیدی در سراسر ایران است.' ) );
		$this->add_control( 'btn1_text', array( 'label' => __( 'دکمه اول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ورود به فروشگاه' ) );
		$this->add_control( 'btn1_link', array( 'label' => __( 'لینک دکمه اول', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/shop/' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( 'دکمه دوم', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام قیمت' ) );
		$this->add_control( 'btn2_link', array( 'label' => __( 'لینک دکمه دوم', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$rb = new Repeater();
		$rb->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'bullets', array(
			'label'       => __( 'نکات زیر دکمه‌ها', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rb->get_controls(),
			'title_field' => '{{{ text }}}',
			'default'     => array( array( 'text' => 'ارسال ۲۴ ساعته به سراسر ایران' ), array( 'text' => 'گارانتی ۴۸ ساعته مغایرت' ), array( 'text' => 'مشاوره فنی رایگان' ) ),
		) );
		$rs = new Repeater();
		$rs->add_control( 'num', array( 'label' => __( 'عدد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER ) );
		$rs->add_control( 'suffix', array( 'label' => __( 'پسوند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '+' ) );
		$rs->add_control( 'label', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'stats', array(
			'label'       => __( 'آمار (شمارنده متحرک)', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rs->get_controls(),
			'title_field' => '{{{ label }}}',
			'default'     => array(
				array( 'num' => 7, 'suffix' => '+', 'label' => 'سال تجربه واردات' ),
				array( 'num' => 100, 'suffix' => '+', 'label' => 'ماده اولیه و گرید' ),
				array( 'num' => 980, 'suffix' => '+', 'label' => 'کارخانه فعال' ),
			),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'card', array( 'label' => __( 'کارت کاتالوگ', 'baspar-elements' ) ) );
		$this->add_control( 'card_title', array( 'label' => __( 'عنوان کارت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'BASPARMARKET · CATALOG' ) );
		$this->add_control( 'card_live', array( 'label' => __( 'برچسب زنده', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'LIVE · امروز' ) );
		$rr = new Repeater();
		$rr->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'layers' ) );
		$rr->add_control( 'code', array( 'label' => __( 'کد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rr->add_control( 'name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rr->add_control( 'sub', array( 'label' => __( 'زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rr->add_control( 'status', array( 'label' => __( 'وضعیت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'موجود' ) );
		$rr->add_control( 'warn', array( 'label' => __( 'وضعیت هشدار (محدود)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$rr->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'rows', array(
			'label'       => __( 'ردیف‌های محصول', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rr->get_controls(),
			'title_field' => '{{{ code }}} — {{{ name }}}',
			'default'     => array(
				array( 'icon' => 'layers', 'code' => 'TPU', 'name' => 'ترموپلاستیک پلی‌اورتان', 'sub' => 'Elastollan 1185A — آلمان', 'status' => 'موجود' ),
				array( 'icon' => 'flask', 'code' => 'MEK', 'name' => 'متیل اتیل کتون', 'sub' => 'Shell — چین', 'status' => 'موجود' ),
				array( 'icon' => 'palette', 'code' => 'TiO₂', 'name' => 'تیتانیوم دی‌اکسید', 'sub' => 'Lomon R-838 — چین', 'status' => 'موجود' ),
				array( 'icon' => 'beaker', 'code' => 'Epoxy', 'name' => 'رزین اپوکسی', 'sub' => 'Hexion — آلمان', 'status' => 'محدود', 'warn' => 'yes' ),
			),
		) );
		$this->add_control( 'card_foot', array( 'label' => __( 'متن پایین کارت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '+۹۶ گرید دیگر در فروشگاه' ) );
		$this->add_control( 'card_foot_link', array( 'label' => __( 'لینک «مشاهده همه»', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/shop/' ) ) );
		$this->add_control( 'float1_title', array( 'label' => __( 'کارت شناور ۱ — عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ارسال ۲۴ ساعته' ) );
		$this->add_control( 'float1_sub', array( 'label' => __( 'کارت شناور ۱ — زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'NATIONWIDE' ) );
		$this->add_control( 'float2_title', array( 'label' => __( 'کارت شناور ۲ — عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'برگه آنالیز COA' ) );
		$this->add_control( 'float2_sub', array( 'label' => __( 'کارت شناور ۲ — زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'همراه هر محموله' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'motion', array( 'label' => __( 'انیمیشن‌های پس‌زمینه', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_typography( 'h1_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.h2v-title' );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$words = array_values( array_filter( array_map( static function ( $w ) {
			return trim( (string) ( $w['word'] ?? '' ) );
		}, (array) $s['words'] ) ) );
		$n     = max( 1, count( $words ) );
		?>
		<div class="baspar-scope">
			<section class="h2v<?php echo 'yes' === $s['motion'] ? ' is-moving' : ''; ?>" style="--words:<?php echo (int) $n; ?>">
				<div class="h2v-bg" aria-hidden="true">
					<span class="h2v-blob b1"></span><span class="h2v-blob b2"></span><span class="h2v-blob b3"></span>
					<span class="h2v-grid"></span>
					<svg class="h2v-mol m1" viewBox="0 0 120 120"><g fill="none" stroke="currentColor" stroke-width="2"><polygon points="60,10 103,35 103,85 60,110 17,85 17,35"/><line x1="60" y1="10" x2="60" y2="-20"/><line x1="103" y1="85" x2="130" y2="100"/></g><g fill="currentColor"><circle cx="60" cy="10" r="5"/><circle cx="103" cy="35" r="5"/><circle cx="103" cy="85" r="5"/><circle cx="60" cy="110" r="5"/><circle cx="17" cy="85" r="5"/><circle cx="17" cy="35" r="5"/></g></svg>
					<svg class="h2v-mol m2" viewBox="0 0 120 120"><g fill="none" stroke="currentColor" stroke-width="2"><polygon points="60,10 103,35 103,85 60,110 17,85 17,35"/><polygon points="60,30 86,45 86,75 60,90 34,75 34,45"/></g><g fill="currentColor"><circle cx="60" cy="10" r="4"/><circle cx="103" cy="85" r="4"/><circle cx="17" cy="35" r="4"/></g></svg>
					<svg class="h2v-mol m3" viewBox="0 0 120 120"><g fill="none" stroke="currentColor" stroke-width="2"><circle cx="60" cy="60" r="14"/><line x1="60" y1="46" x2="60" y2="10"/><line x1="72" y1="67" x2="104" y2="86"/><line x1="48" y1="67" x2="16" y2="86"/></g><g fill="currentColor"><circle cx="60" cy="10" r="7"/><circle cx="104" cy="86" r="7"/><circle cx="16" cy="86" r="7"/></g></svg>
					<span class="h2v-dots"></span>
				</div>
				<div class="h2v-in">
					<div class="h2v-copy">
						<div class="h2v-eyebrow"><i aria-hidden="true"></i><span class="mono"><?php echo esc_html( $s['eyebrow_mono'] ); ?></span><b aria-hidden="true"></b><span><?php echo esc_html( $s['eyebrow_fa'] ); ?></span></div>
						<h1 class="h2v-title">
							<span class="h2v-l1"><?php echo esc_html( $s['title_1'] ); ?></span>
							<span class="h2v-l2"><?php echo esc_html( $s['title_2'] ); ?></span>
							<span class="h2v-l3"><?php echo esc_html( $s['title_3'] ); ?>
								<?php if ( $words ) : ?>
									<span class="screen-reader-text bspr-sr"><?php echo esc_html( implode( '، ', $words ) ); ?></span>
									<span class="h2v-rot" aria-hidden="true"><span class="h2v-rot-in">
										<?php foreach ( array_merge( $words, array( $words[0] ) ) as $w ) : ?><span><?php echo esc_html( $w ); ?></span><?php endforeach; ?>
									</span></span>
								<?php endif; ?>
							</span>
						</h1>
						<?php if ( $s['subtitle'] ) : ?><p class="h2v-sub"><?php echo esc_html( $s['subtitle'] ); ?></p><?php endif; ?>
						<div class="h2v-ctas">
							<?php if ( $s['btn1_text'] && ! empty( $s['btn1_link']['url'] ) ) : ?><a class="h2v-btn is-primary" href="<?php echo esc_url( slash_url( $s['btn1_link']['url'] ) ); ?>"><?php echo icon_svg( 'cart', 18 ); // phpcs:ignore ?><span><?php echo esc_html( $s['btn1_text'] ); ?></span></a><?php endif; ?>
							<?php if ( $s['btn2_text'] && ! empty( $s['btn2_link']['url'] ) ) : ?><a class="h2v-btn is-glass" href="<?php echo esc_url( slash_url( $s['btn2_link']['url'] ) ); ?>" target="_blank" rel="noopener nofollow"><?php echo icon_svg( 'whatsapp', 18 ); // phpcs:ignore ?><span><?php echo esc_html( $s['btn2_text'] ); ?></span></a><?php endif; ?>
						</div>
						<?php if ( ! empty( $s['bullets'] ) ) : ?>
							<ul class="h2v-bullets"><?php foreach ( (array) $s['bullets'] as $b ) : if ( empty( $b['text'] ) ) { continue; } ?><li><?php echo icon_svg( 'check', 14 ); // phpcs:ignore ?><?php echo esc_html( $b['text'] ); ?></li><?php endforeach; ?></ul>
						<?php endif; ?>
						<?php if ( ! empty( $s['stats'] ) ) : ?>
							<div class="h2v-stats">
								<?php foreach ( (array) $s['stats'] as $st ) : ?>
									<div><strong><span data-counter data-target="<?php echo esc_attr( (int) $st['num'] ); ?>"><?php echo esc_html( fa_num( number_format( (int) $st['num'] ) ) ); ?></span><?php echo esc_html( $st['suffix'] ); ?></strong><span><?php echo esc_html( $st['label'] ); ?></span></div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="h2v-art">
						<span class="h2v-ring" aria-hidden="true"></span>
						<div class="h2v-card">
							<div class="h2v-card-h"><span class="h2v-dots3" aria-hidden="true"><i></i><i></i><i></i></span><span class="mono"><?php echo esc_html( $s['card_title'] ); ?></span><span class="h2v-live"><i aria-hidden="true"></i><?php echo esc_html( $s['card_live'] ); ?></span></div>
							<ul class="h2v-rows">
								<?php $i = 0; foreach ( (array) $s['rows'] as $r ) :
									$tag  = ! empty( $r['link']['url'] ) ? 'a' : 'div';
									$href = 'a' === $tag ? ' href="' . esc_url( slash_url( $r['link']['url'] ) ) . '"' : '';
									?>
									<li style="--i:<?php echo (int) $i++; ?>"><<?php echo $tag; // phpcs:ignore ?> class="h2v-row"<?php echo $href; // phpcs:ignore ?>>
										<span class="ic"><?php echo icon_svg( $r['icon'] ? $r['icon'] : 'package', 18 ); // phpcs:ignore ?></span>
										<span class="code"><?php echo esc_html( $r['code'] ); ?></span>
										<span class="nm"><strong><?php echo esc_html( $r['name'] ); ?></strong><small><?php echo esc_html( $r['sub'] ); ?></small></span>
										<span class="st<?php echo 'yes' === ( $r['warn'] ?? '' ) ? ' warn' : ''; ?>"><?php echo esc_html( $r['status'] ); ?></span>
									</<?php echo $tag; // phpcs:ignore ?>></li>
								<?php endforeach; ?>
							</ul>
							<div class="h2v-card-f"><span><?php echo esc_html( $s['card_foot'] ); ?></span><?php if ( ! empty( $s['card_foot_link']['url'] ) ) : ?><a href="<?php echo esc_url( slash_url( $s['card_foot_link']['url'] ) ); ?>"><?php esc_html_e( 'مشاهده همه', 'baspar-elements' ); ?><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></a><?php endif; ?></div>
						</div>
						<?php if ( $s['float1_title'] ) : ?><div class="h2v-float f1"><span class="ic"><?php echo icon_svg( 'truck', 20 ); // phpcs:ignore ?></span><span><strong><?php echo esc_html( $s['float1_title'] ); ?></strong><small><?php echo esc_html( $s['float1_sub'] ); ?></small></span></div><?php endif; ?>
						<?php if ( $s['float2_title'] ) : ?><div class="h2v-float f2"><span class="ic"><?php echo icon_svg( 'shield', 20 ); // phpcs:ignore ?></span><span><strong><?php echo esc_html( $s['float2_title'] ); ?></strong><small><?php echo esc_html( $s['float2_sub'] ); ?></small></span></div><?php endif; ?>
					</div>
				</div>
				<a class="h2v-scroll" href="#home-cats" aria-label="<?php esc_attr_e( 'ادامه صفحه', 'baspar-elements' ); ?>"><span></span></a>
			</section>
		</div>
		<?php
	}
}
