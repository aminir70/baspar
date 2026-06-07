<?php
/**
 * Contact methods grid (2-up cards): icon, mono label, title, value.
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
 * Class Baspar_Contact_Methods
 */
class Baspar_Contact_Methods extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-contact-methods';
	}

	public function get_title() {
		return __( 'بسپار — روش‌های تماس', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-contact-form';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'روش‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'phone' ) );
		$rep->add_control( 'mono', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'value', array( 'label' => __( 'مقدار', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'روش‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'icon' => 'phone', 'mono' => 'PHONE', 'title' => 'تلفن دفتر', 'value' => '۰۷۱-۳۸۳۸۵۶۸۶', 'link' => array( 'url' => 'tel:+987138385686' ) ),
					array( 'icon' => 'whatsapp', 'mono' => 'WHATSAPP', 'title' => 'واتس‌اپ', 'value' => '۰۹۱۲ ۰۹۹ ۷۶۵۱', 'link' => array( 'url' => 'https://wa.me/989120997651' ) ),
					array( 'icon' => 'mail', 'mono' => 'EMAIL', 'title' => 'ایمیل', 'value' => 'info@basparmarket.com', 'link' => array( 'url' => 'mailto:info@basparmarket.com' ) ),
					array( 'icon' => 'clock', 'mono' => 'HOURS', 'title' => 'ساعات کاری', 'value' => 'شنبه تا چهارشنبه ۹–۱۸', 'link' => array( 'url' => '' ) ),
				),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="contact-methods <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php
				foreach ( (array) $s['items'] as $it ) :
					$url = ! empty( $it['link']['url'] ) ? $it['link']['url'] : '';
					$tag = $url ? 'a' : 'div';
					?>
					<<?php echo esc_attr( $tag ); ?> class="cm-card" <?php echo $url ? 'href="' . esc_url( $url ) . '"' : ''; ?>>
						<div class="cm-ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'info', 22 ); // phpcs:ignore ?></div>
						<?php if ( $it['mono'] ) : ?><span class="mono"><?php echo esc_html( $it['mono'] ); ?></span><?php endif; ?>
						<strong><?php echo esc_html( $it['title'] ); ?></strong>
						<span class="v"><?php echo esc_html( $it['value'] ); ?></span>
					</<?php echo esc_attr( $tag ); ?>>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
