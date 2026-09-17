<?php
/**
 * About box — gradient panel with logo column + body, two animated counters
 * and a CTA button.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_About_Box
 */
class Baspar_About_Box extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-about-box';
	}

	public function get_title() {
		return __( 'بسپار — باکس درباره ما', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-info-circle-o';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'logo', array( 'label' => __( 'لوگو', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => BASPAR_ELEMENTS_ASSETS . 'images/logo.png' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ABOUT BASPARMARKET' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'درباره بسپارمارکت' ) );
		$this->add_control(
			'text',
			array(
				'label'   => __( 'متن', 'baspar-elements' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => 'شرکت بازرگانی کیهان بسپار نیک اندیشان با نام تجاری بسپارمارکت، در زمینه واردات، تأمین و توزیع مواد اولیه صنایع مختلف از جمله صنایع پلیمری، شیمیایی، رنگ و رزین، شوینده و غذایی فعالیت می‌کند.',
			)
		);
		$this->add_control( 'c1_num', array( 'label' => __( 'شمارنده ۱ — عدد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 412 ) );
		$this->add_control( 'c1_title', array( 'label' => __( 'شمارنده ۱ — عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'رضایت مشتری' ) );
		$this->add_control( 'c1_sub', array( 'label' => __( 'شمارنده ۱ — زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '۱۰۰٪ تضمین کیفیت' ) );
		$this->add_control( 'c2_num', array( 'label' => __( 'شمارنده ۲ — عدد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 914 ) );
		$this->add_control( 'c2_title', array( 'label' => __( 'شمارنده ۲ — عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'فروش موفق' ) );
		$this->add_control( 'c2_sub', array( 'label' => __( 'شمارنده ۲ — زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'به سراسر کشور' ) );
		$this->add_control( 'btn_text', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'بیشتر درباره ما' ) );
		$this->add_control( 'btn_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_background( 'bg', '.aboutbox' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.aboutbox-body h3' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="aboutbox">
				<div class="aboutbox-inner">
					<div class="aboutbox-logo">
						<?php if ( ! empty( $s['logo']['url'] ) ) : ?>
							<img src="<?php echo esc_url( $s['logo']['url'] ); ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" />
						<?php endif; ?>
					</div>
					<div class="aboutbox-body">
						<span class="mono"><?php echo esc_html( $s['kicker'] ); ?></span>
						<h3><?php echo esc_html( $s['title'] ); ?></h3>
						<div class="rich-text"><?php echo wp_kses_post( $s['text'] ); ?></div>
						<div class="aboutbox-stats">
							<div>
								<div class="num"><span data-counter data-target="<?php echo esc_attr( $s['c1_num'] ); ?>" data-locale="fa">+<?php echo esc_html( number_format_i18n( $s['c1_num'] ) ); ?></span></div>
								<div class="t"><?php echo esc_html( $s['c1_title'] ); ?></div>
								<div class="s"><?php echo esc_html( $s['c1_sub'] ); ?></div>
							</div>
							<div class="sep"></div>
							<div>
								<div class="num"><span data-counter data-target="<?php echo esc_attr( $s['c2_num'] ); ?>" data-locale="fa">+<?php echo esc_html( number_format_i18n( $s['c2_num'] ) ); ?></span></div>
								<div class="t"><?php echo esc_html( $s['c2_title'] ); ?></div>
								<div class="s"><?php echo esc_html( $s['c2_sub'] ); ?></div>
							</div>
						</div>
						<?php if ( $s['btn_text'] ) : ?>
							<a href="<?php echo esc_url( $s['btn_link']['url'] ?? '#' ); ?>" class="btn btn-white"><?php echo icon_svg( 'info', 16 ); // phpcs:ignore ?><?php echo esc_html( $s['btn_text'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
