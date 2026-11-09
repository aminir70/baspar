<?php
/**
 * CTA strip — a wide promo band with a slowly moving gradient, animated
 * rings and a floating icon badge. Used for "خرید مواد اولیه شیمیایی" and
 * "وارد کننده مواد اولیه شیمیایی" on the home page.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;
use function BasparElements\slash_url;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Cta_Strip extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-cta-strip'; }
	public function get_title() { return __( 'بسپار — نوار تبلیغاتی متحرک', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-call-to-action'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'tone', array(
			'label'   => __( 'رنگ', 'baspar-elements' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'violet',
			'options' => array( 'violet' => __( 'بنفش', 'baspar-elements' ), 'ink' => __( 'تیره', 'baspar-elements' ), 'light' => __( 'روشن', 'baspar-elements' ), 'amber' => __( 'کهربایی', 'baspar-elements' ) ),
		) );
		$this->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'flask' ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'BUY CHEMICAL RAW MATERIALS' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'خرید مواد اولیه شیمیایی' ) );
		$this->add_control( 'title_tag', array( 'label' => __( 'تگ عنوان', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'div' => 'div' ) ) );
		$this->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4, 'default' => 'بسپارمارکت با بیش از ۷ سال تجربه در واردات مستقیم و توزیع مواد اولیه شیمیایی، تأمین‌کننده‌ی صنایع پلیمری، رنگ و رزین، شوینده، غذایی و دارویی در سراسر ایران است. استعلام قیمت روز، ارسال سریع، تضمین کیفیت.' ) );
		$rp = new Repeater();
		$rp->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'points', array(
			'label'       => __( 'نکات (چیپ)', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rp->get_controls(),
			'title_field' => '{{{ text }}}',
			'default'     => array( array( 'text' => 'قیمت روز' ), array( 'text' => 'ارسال سریع' ), array( 'text' => 'تضمین کیفیت' ) ),
		) );
		$this->add_control( 'btn_text', array( 'label' => __( 'دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده راهنمای خرید' ) );
		$this->add_control( 'btn_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/buy-chemical-raw-materials/' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( 'دکمه دوم (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام در واتس‌اپ' ) );
		$this->add_control( 'btn2_link', array( 'label' => __( 'لینک دکمه دوم', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$this->add_control( 'reverse', array( 'label' => __( 'جابجایی آیکون و متن', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.cts-t' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$tone = in_array( $s['tone'], array( 'violet', 'ink', 'light', 'amber' ), true ) ? $s['tone'] : 'violet';
		$tag  = in_array( $s['title_tag'], array( 'h2', 'h3', 'div' ), true ) ? $s['title_tag'] : 'h2';
		$wa   = static function ( $u ) { return false !== strpos( (string) $u, 'wa.me' ) || false !== strpos( (string) $u, 'whatsapp' ); };
		?>
		<div class="baspar-scope">
			<section class="cts is-<?php echo esc_attr( $tone ); ?><?php echo 'yes' === $s['reverse'] ? ' is-rev' : ''; ?>" data-anim="up">
				<span class="cts-bg" aria-hidden="true"><i class="r1"></i><i class="r2"></i><i class="r3"></i><span class="cts-mesh"></span></span>
				<div class="cts-badge" aria-hidden="true">
					<span class="cts-orbit"><i></i></span>
					<span class="cts-ic"><?php echo icon_svg( $s['icon'] ? $s['icon'] : 'flask', 40 ); // phpcs:ignore ?></span>
				</div>
				<div class="cts-copy">
					<?php if ( $s['kicker'] ) : ?><span class="cts-k mono"><?php echo esc_html( $s['kicker'] ); ?></span><?php endif; ?>
					<?php if ( $s['title'] ) : ?><<?php echo $tag; // phpcs:ignore ?> class="cts-t"><?php echo esc_html( $s['title'] ); ?></<?php echo $tag; // phpcs:ignore ?>><?php endif; ?>
					<?php if ( $s['text'] ) : ?><p class="cts-p"><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
					<?php if ( ! empty( $s['points'] ) ) : ?><ul class="cts-pts"><?php foreach ( (array) $s['points'] as $p ) : if ( empty( $p['text'] ) ) { continue; } ?><li><?php echo icon_svg( 'check', 14 ); // phpcs:ignore ?><?php echo esc_html( $p['text'] ); ?></li><?php endforeach; ?></ul><?php endif; ?>
				</div>
				<div class="cts-acts">
					<?php if ( $s['btn_text'] && ! empty( $s['btn_link']['url'] ) ) : ?><a class="cts-btn is-main" href="<?php echo esc_url( slash_url( $s['btn_link']['url'] ) ); ?>"><span><?php echo esc_html( $s['btn_text'] ); ?></span><?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?></a><?php endif; ?>
					<?php if ( $s['btn2_text'] && ! empty( $s['btn2_link']['url'] ) ) : $is_wa = $wa( $s['btn2_link']['url'] ); ?><a class="cts-btn is-alt<?php echo $is_wa ? ' is-wa' : ''; ?>" href="<?php echo esc_url( slash_url( $s['btn2_link']['url'] ) ); ?>"<?php echo $is_wa ? ' target="_blank" rel="noopener nofollow"' : ''; ?>><?php echo $is_wa ? icon_svg( 'whatsapp', 16 ) : ''; // phpcs:ignore ?><span><?php echo esc_html( $s['btn2_text'] ); ?></span></a><?php endif; ?>
				</div>
			</section>
		</div>
		<?php
	}
}
