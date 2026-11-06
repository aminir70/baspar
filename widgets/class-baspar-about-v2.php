<?php
/**
 * About (home v2) — logo inside rotating orbit rings with orbiting icons,
 * the company story, animated counters and feature chips.
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

class Baspar_About_V2 extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-about-v2'; }
	public function get_title() { return __( 'بسپار — درباره ما (صفحه اصلی ۲، متحرک)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-info-box'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'logo', array( 'label' => __( 'لوگو', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => 'https://basparmarket.com/wp-content/uploads/2026/06/logo-2.png' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ABOUT BASPARMARKET' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'درباره بسپارمارکت' ) );
		$this->add_control( 'title_tag', array( 'label' => __( 'تگ عنوان', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'div' => 'div' ) ) );
		$this->add_control( 'text', array(
			'label'   => __( 'متن', 'baspar-elements' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 8,
			'default' => 'شرکت بازرگانی کیهان بسپار نیک اندیشان با نام تجاری بسپار مارکت در زمینه واردات، تامین و توزیع مواد اولیه صنایع مختلف از جمله صنایع پلیمری، شیمیایی، رنگ و رزین، شوینده و غذایی فعالیت می کند. ما همواره در تلاشیم تا با بهره گیری از کارشناسان مجرب و متخصص در حوزه بازرگانی، علاوه بر ارائه بهترین و مقرون به صرفه ترین مواد اولیه بتوانیم با استفاده از مطالب آموزشی که بصورت هفتگی در سایت قرار داده می شود و برگزاری کارگاههای آموزشی و ارائه مشاوره در جهت کاهش مشکلات تولید در حوزه های مختلف صنعت گامی کوچک برداریم.',
		) );
		$rc = new Repeater();
		$rc->add_control( 'num', array( 'label' => __( 'عدد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER ) );
		$rc->add_control( 'prefix', array( 'label' => __( 'پیشوند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '+' ) );
		$rc->add_control( 'suffix', array( 'label' => __( 'پسوند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rc->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rc->add_control( 'sub', array( 'label' => __( 'زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rc->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'users' ) );
		$this->add_control( 'counters', array(
			'label'       => __( 'شمارنده‌ها', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rc->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array( 'num' => 1125, 'prefix' => '+', 'title' => 'رضایت مشتری', 'sub' => '۱۰۰٪ تضمین کیفیت', 'icon' => 'users' ),
				array( 'num' => 2500, 'prefix' => '+', 'title' => 'فروش موفق', 'sub' => 'به سراسر کشور', 'icon' => 'truck' ),
			),
		) );
		$rf = new Repeater();
		$rf->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'check' ) );
		$rf->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'features', array(
			'label'       => __( 'ویژگی‌ها (چیپ)', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rf->get_controls(),
			'title_field' => '{{{ text }}}',
			'default'     => array(
				array( 'icon' => 'globe', 'text' => 'واردات مستقیم' ),
				array( 'icon' => 'doc', 'text' => 'مطالب آموزشی هفتگی' ),
				array( 'icon' => 'users', 'text' => 'کارگاه و مشاوره فنی' ),
			),
		) );
		$this->add_control( 'btn_text', array( 'label' => __( 'دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تماس با ما' ) );
		$this->add_control( 'btn_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/contact-us/' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( 'دکمه دوم (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'درباره ما' ) );
		$this->add_control( 'btn2_link', array( 'label' => __( 'لینک دکمه دوم', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/about-us/' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$tag = in_array( $s['title_tag'], array( 'h2', 'h3', 'div' ), true ) ? $s['title_tag'] : 'h2';
		$orb = array( 'flask', 'layers', 'palette', 'beaker', 'droplet', 'factory' );
		?>
		<div class="baspar-scope">
			<section class="hm-sec abv" data-anim-group>
				<div class="abv-art" data-anim="zoom" aria-hidden="true">
					<span class="abv-ring r1"></span><span class="abv-ring r2"></span><span class="abv-ring r3"></span>
					<span class="abv-orbit">
						<?php foreach ( $orb as $k => $ic ) : ?><i style="--k:<?php echo (int) $k; ?>"><b><?php echo icon_svg( $ic, 18 ); // phpcs:ignore ?></b></i><?php endforeach; ?>
					</span>
					<span class="abv-core"><?php if ( ! empty( $s['logo']['url'] ) ) : ?><img src="<?php echo esc_url( $s['logo']['url'] ); ?>" alt="" width="140" height="140" loading="lazy"><?php endif; ?></span>
				</div>
				<div class="abv-copy">
					<?php if ( $s['kicker'] ) : ?><span class="hm-kicker" data-anim="up"><i aria-hidden="true"></i><span class="mono"><?php echo esc_html( $s['kicker'] ); ?></span></span><?php endif; ?>
					<?php if ( $s['title'] ) : ?><<?php echo $tag; // phpcs:ignore ?> class="hm-title" data-anim="up" style="--i:1"><?php echo esc_html( $s['title'] ); ?></<?php echo $tag; // phpcs:ignore ?>><?php endif; ?>
					<?php if ( $s['text'] ) : ?><p class="abv-p" data-anim="up" style="--i:2"><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
					<?php if ( ! empty( $s['features'] ) ) : ?>
						<ul class="abv-feats" data-anim="up" style="--i:3"><?php foreach ( (array) $s['features'] as $f ) : if ( empty( $f['text'] ) ) { continue; } ?><li><?php echo icon_svg( $f['icon'] ? $f['icon'] : 'check', 16 ); // phpcs:ignore ?><?php echo esc_html( $f['text'] ); ?></li><?php endforeach; ?></ul>
					<?php endif; ?>
					<?php if ( ! empty( $s['counters'] ) ) : ?>
						<div class="abv-stats">
							<?php foreach ( (array) $s['counters'] as $k => $c ) : ?>
								<div class="abv-stat" data-anim="up" style="--i:<?php echo 4 + (int) $k; ?>">
									<span class="ic"><?php echo icon_svg( $c['icon'] ? $c['icon'] : 'users', 20 ); // phpcs:ignore ?></span>
									<span class="tx"><strong><?php echo esc_html( $c['prefix'] ); ?><span data-counter data-target="<?php echo esc_attr( (int) $c['num'] ); ?>"><?php echo esc_html( fa_num( number_format( (int) $c['num'] ) ) ); ?></span><?php echo esc_html( $c['suffix'] ); ?></strong><span><?php echo esc_html( $c['title'] ); ?></span><?php if ( $c['sub'] ) : ?><small><?php echo esc_html( $c['sub'] ); ?></small><?php endif; ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<div class="abv-acts" data-anim="up" style="--i:6">
						<?php if ( $s['btn_text'] && ! empty( $s['btn_link']['url'] ) ) : ?><a class="h2v-btn is-primary" href="<?php echo esc_url( slash_url( $s['btn_link']['url'] ) ); ?>"><?php echo icon_svg( 'phone', 18 ); // phpcs:ignore ?><span><?php echo esc_html( $s['btn_text'] ); ?></span></a><?php endif; ?>
						<?php if ( $s['btn2_text'] && ! empty( $s['btn2_link']['url'] ) ) : ?><a class="h2v-btn is-ghost" href="<?php echo esc_url( slash_url( $s['btn2_link']['url'] ) ); ?>"><span><?php echo esc_html( $s['btn2_text'] ); ?></span><?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?></a><?php endif; ?>
					</div>
				</div>
			</section>
		</div>
		<?php
	}
}
