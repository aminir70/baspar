<?php
/**
 * Final CTA — centered dark banner with title, sub and buttons.
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
 * Class Baspar_Final_Cta
 */
class Baspar_Final_Cta extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-final-cta';
	}

	public function get_title() {
		return __( 'بسپار — CTA نهایی', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-text-area';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'آماده‌اید تأمین مواد اولیه‌تان را به ما بسپارید؟' ) );
		$this->add_control( 'sub', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => 'همین حالا استعلام بگیرید و از مشاوره فنی رایگان کارشناسان ما بهره‌مند شوید.' ) );

		$rep = new Repeater();
		$rep->add_control( 'text', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'whatsapp' => 'واتس‌اپ', 'phone' => 'تلفن', 'cart' => 'سبد', 'mail' => 'ایمیل' ), 'default' => 'whatsapp' ) );
		$rep->add_control( 'white', array( 'label' => __( 'دکمه سفید', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control(
			'buttons',
			array(
				'label'   => __( 'دکمه‌ها', 'baspar-elements' ),
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $rep->get_controls(),
				'title_field' => '{{{ text }}}',
				'default' => array(
					array( 'text' => 'استعلام در واتس‌اپ', 'icon' => 'whatsapp', 'white' => 'yes', 'link' => array( 'url' => 'https://wa.me/989120997651' ) ),
					array( 'text' => 'ورود به فروشگاه', 'icon' => 'cart', 'white' => '', 'link' => array( 'url' => '#' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_background( 'bg', '.finalcta' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.finalcta-inner h2' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="finalcta">
				<div class="finalcta-grid-bg" aria-hidden="true"></div>
				<div class="finalcta-inner">
					<h2><?php echo esc_html( $s['title'] ); ?></h2>
					<?php if ( $s['sub'] ) : ?><div class="rich-text"><?php echo wp_kses_post( $s['sub'] ); ?></div><?php endif; ?>
					<div class="finalcta-buttons">
						<?php foreach ( (array) $s['buttons'] as $b ) :
							$cls = ( 'yes' === $b['white'] ) ? 'btn btn-white btn-lg' : 'btn btn-ghost btn-lg';
							?>
							<a href="<?php echo esc_url( $b['link']['url'] ?? '#' ); ?>" class="<?php echo esc_attr( $cls ); ?>" style="<?php echo ( 'yes' !== $b['white'] ) ? 'color:#fff;border-color:rgba(255,255,255,.3)' : ''; ?>">
								<?php echo icon_svg( $b['icon'], 18 ); // phpcs:ignore ?>
								<?php echo esc_html( $b['text'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
