<?php
/**
 * Prose block — a simple rich-text paragraph block (max-width article copy),
 * meant to follow a "Section Heading" widget. Used for the intro text on
 * city landing pages, but generic enough for any long-form copy block.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Prose
 */
class Baspar_Prose extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-prose';
	}

	public function get_title() {
		return __( 'بسپار — متن مقدمه (Prose)', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-text-area';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'متن', 'baspar-elements' ) ) );
		$this->add_control( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'body_typo', __( 'تایپوگرافی متن', 'baspar-elements' ), '.city-prose' );
		$this->add_color( 'body_color', __( 'رنگ متن', 'baspar-elements' ), '.city-prose', 'color' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( '' === trim( wp_strip_all_tags( (string) $s['content'] ) ) ) {
			return;
		}
		?>
		<div class="baspar-scope">
			<div class="city-prose rich-text <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php echo wp_kses_post( $s['content'] ); ?>
			</div>
		</div>
		<?php
	}
}
