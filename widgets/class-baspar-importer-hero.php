<?php
/**
 * Importer hero — text column + animated "supply network map" (origin country
 * flags connected by dashed lines to a central hub) with a footer stats strip.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\flag_svg;
use function BasparElements\flag_options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Importer_Hero
 */
class Baspar_Importer_Hero extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-importer-hero';
	}

	public function get_title() {
		return __( 'بسپار — هیرو واردکننده (نقشه تأمین)', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-globe';
	}

	protected function register_controls() {
		/* ---- Text ---- */
		$this->start_controls_section( 'text', array( 'label' => __( 'متن', 'baspar-elements' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'DIRECT IMPORT · B2B' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان (<em> برای هایلایت)', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => 'واردات مستقیم <em>مواد اولیه شیمیایی</em> از مبدأ' ) );
		$this->add_control( 'subtitle', array( 'label' => __( 'زیرعنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4, 'default' => 'تأمین و واردات مستقیم از کارخانه‌های معتبر چین، آلمان و ترکیه؛ با پروفرما اینویس رسمی، COA و پشتیبانی فنی کامل.' ) );
		$this->add_control( 'btn1_text', array( 'label' => __( 'دکمه اول', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'درخواست استعلام (RFQ)' ) );
		$this->add_control( 'btn1_link', array( 'label' => __( 'لینک دکمه اول', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( 'دکمه دوم', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'کاتالوگ محصولات' ) );
		$this->add_control( 'btn2_link', array( 'label' => __( 'لینک دکمه دوم', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );

		$rb = new Repeater();
		$rb->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'bullets',
			array(
				'label'       => __( 'ویژگی‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rb->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array(
					array( 'text' => 'پروفرما اینویس رسمی' ),
					array( 'text' => 'COA و دیتاشیت معتبر' ),
					array( 'text' => 'ترخیص و حمل تا درب کارخانه' ),
				),
			)
		);
		$this->end_controls_section();

		/* ---- Map ---- */
		$this->start_controls_section( 'map', array( 'label' => __( 'نقشه شبکه تأمین', 'baspar-elements' ) ) );
		$this->add_control( 'map_title', array( 'label' => __( 'عنوان نقشه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'SUPPLY NETWORK' ) );
		$this->add_control( 'hub_logo', array( 'label' => __( 'لوگوی مرکز', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => BASPAR_ELEMENTS_ASSETS . 'images/logo.png' ) ) );
		$this->add_control( 'hub_label', array( 'label' => __( 'برچسب مرکز', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'IRAN · HUB' ) );

		$rn = new Repeater();
		$rn->add_control( 'flag', array( 'label' => __( 'پرچم', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => flag_options(), 'default' => 'china' ) );
		$rn->add_control( 'label', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rn->add_control( 'code', array( 'label' => __( 'کد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'nodes',
			array(
				'label'       => __( 'کشورهای مبدأ (۳ مورد)', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rn->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array( 'flag' => 'china', 'label' => 'چین', 'code' => 'CN' ),
					array( 'flag' => 'germany', 'label' => 'آلمان', 'code' => 'DE' ),
					array( 'flag' => 'turkey', 'label' => 'ترکیه', 'code' => 'TR' ),
				),
			)
		);

		$rf = new Repeater();
		$rf->add_control( 'mono', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rf->add_control( 'value', array( 'label' => __( 'مقدار', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'stats',
			array(
				'label'       => __( 'آمار پایین نقشه (۳ مورد)', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rf->get_controls(),
				'title_field' => '{{{ value }}}',
				'default'     => array(
					array( 'mono' => 'EXPORT', 'value' => '+۵ سال' ),
					array( 'mono' => 'SKU', 'value' => '+۱۰۰' ),
					array( 'mono' => 'COUNTRIES', 'value' => '۳' ),
				),
			)
		);
		$this->end_controls_section();

		/* ---- Style ---- */
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'h1_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.home-hero h1' );
		$this->add_color( 'h1_color', __( 'رنگ عنوان', 'baspar-elements' ), '.home-hero h1', 'color' );
		$this->add_reveal_toggle();
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$rev   = $this->reveal_class( $s );
		$nodes = array_values( (array) $s['nodes'] );
		$pos   = array( 'snm-cn', 'snm-de', 'snm-tr' );
		?>
		<div class="baspar-scope">
			<section class="imp-hero home-hero">
				<div class="home-hero-grid" aria-hidden="true"></div>
				<div class="imp-hero-inner">
					<div class="<?php echo esc_attr( $rev ); ?>">
						<div class="hero-eyebrow"><span class="mono"><?php echo esc_html( $s['eyebrow'] ); ?></span></div>
						<h1><?php echo wp_kses_post( $s['title'] ); ?></h1>
						<p class="home-hero-sub"><?php echo esc_html( $s['subtitle'] ); ?></p>
						<div class="home-hero-ctas">
							<?php if ( $s['btn1_text'] ) : ?><a href="<?php echo esc_url( $s['btn1_link']['url'] ?? '#' ); ?>" class="btn btn-primary"><?php echo icon_svg( 'whatsapp', 18 ); // phpcs:ignore ?><?php echo esc_html( $s['btn1_text'] ); ?></a><?php endif; ?>
							<?php if ( $s['btn2_text'] ) : ?><a href="<?php echo esc_url( $s['btn2_link']['url'] ?? '#' ); ?>" class="btn btn-ghost"><?php echo icon_svg( 'package', 18 ); // phpcs:ignore ?><?php echo esc_html( $s['btn2_text'] ); ?></a><?php endif; ?>
						</div>
						<div class="home-hero-bullets">
							<?php foreach ( (array) $s['bullets'] as $b ) : ?><span><?php echo icon_svg( 'check', 14 ); // phpcs:ignore ?><?php echo esc_html( $b['text'] ); ?></span><?php endforeach; ?>
						</div>
					</div>

					<div class="<?php echo esc_attr( $rev ); ?>" data-reveal-delay="120">
						<div class="snm-card">
							<div class="snm-head">
								<span class="mono"><?php echo esc_html( $s['map_title'] ); ?></span>
								<span class="live">LIVE</span>
							</div>
							<div class="snm-body"></div>
							<div class="snm-grid" aria-hidden="true"></div>
							<svg class="snm-svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
								<path class="path" d="M78,28 L50,50"/>
								<path class="path" d="M22,28 L50,50"/>
								<path class="path" d="M50,80 L50,50"/>
							</svg>
							<?php foreach ( $nodes as $i => $n ) :
								if ( $i > 2 ) { break; }
								?>
								<div class="snm-node <?php echo esc_attr( $pos[ $i ] ); ?>">
									<span class="flag"><?php echo flag_svg( $n['flag'] ); // phpcs:ignore ?></span>
									<span class="lbl"><?php echo esc_html( $n['label'] ); ?><span class="mono"><?php echo esc_html( $n['code'] ); ?></span></span>
								</div>
							<?php endforeach; ?>
							<div class="snm-hub">
								<?php if ( ! empty( $s['hub_logo']['url'] ) ) : ?><img src="<?php echo esc_url( $s['hub_logo']['url'] ); ?>" alt="" /><?php endif; ?>
								<span class="mono"><?php echo esc_html( $s['hub_label'] ); ?></span>
							</div>
							<div class="snm-foot">
								<?php foreach ( (array) $s['stats'] as $st ) : ?>
									<div class="snm-foot-cell"><span class="mono"><?php echo esc_html( $st['mono'] ); ?></span><strong><?php echo esc_html( $st['value'] ); ?></strong></div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
		<?php
	}
}
