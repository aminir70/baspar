<?php
/**
 * Mid CTA — gradient panel, text on one side + stacked buttons on the other.
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
 * Class Baspar_Mid_Cta
 */
class Baspar_Mid_Cta extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-mid-cta';
	}

	public function get_title() {
		return __( 'بسپار — CTA میانی', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'NEED A QUOTE?' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'برای دریافت قیمت و موجودی همین حالا تماس بگیرید' ) );
		$this->add_control( 'text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => 'کارشناسان ما آماده پاسخگویی و ارائه مشاوره فنی رایگان هستند.' ) );

		$rep = new Repeater();
		$rep->add_control( 'text', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'sub', array( 'label' => __( 'زیرنویس کوچک', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'whatsapp' => 'واتس‌اپ', 'phone' => 'تلفن', 'mail' => 'ایمیل' ), 'default' => 'whatsapp' ) );
		$rep->add_control( 'white', array( 'label' => __( 'دکمه سفید', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control(
			'buttons',
			array(
				'label'       => __( 'دکمه‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array(
					array( 'text' => 'استعلام در واتس‌اپ', 'sub' => '۲۴/۷', 'icon' => 'whatsapp', 'white' => 'yes', 'link' => array( 'url' => 'https://wa.me/989120997651' ) ),
					array( 'text' => 'تماس تلفنی', 'sub' => '۹–۱۸', 'icon' => 'phone', 'white' => '', 'link' => array( 'url' => 'tel:+989120733965' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_background( 'bg', '.midcta' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.midcta-text h2' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="midcta">
				<div class="midcta-inner">
					<div class="midcta-text">
						<?php if ( $s['kicker'] ) : ?><span class="kicker-light mono"><?php echo esc_html( $s['kicker'] ); ?></span><?php endif; ?>
						<h2><?php echo esc_html( $s['title'] ); ?></h2>
						<p><?php echo esc_html( $s['text'] ); ?></p>
					</div>
					<div class="midcta-buttons">
						<?php foreach ( (array) $s['buttons'] as $b ) :
							$cls = ( 'yes' === $b['white'] ) ? 'btn btn-white' : 'btn btn-ghost';
							?>
							<a href="<?php echo esc_url( $b['link']['url'] ?? '#' ); ?>" class="<?php echo esc_attr( $cls ); ?>">
								<?php echo icon_svg( $b['icon'], 18 ); // phpcs:ignore ?>
								<?php echo esc_html( $b['text'] ); ?>
								<?php if ( $b['sub'] ) : ?><span class="small"><?php echo esc_html( $b['sub'] ); ?></span><?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
