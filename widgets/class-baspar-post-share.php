<?php
/**
 * Post share bar — "اشتراک‌گذاری" label + 4 social buttons.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Post_Share extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-post-share'; }
	public function get_title() { return __( 'بسپار — نوار اشتراک‌گذاری', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-share-arrow'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'label', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'این مقاله را به اشتراک بگذارید' ) );
		$this->add_control( 'strong', array( 'label' => __( 'متن سمت چپ', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مفید بود؟' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$url = get_permalink();
		$title = get_the_title();
		$wa = 'https://wa.me/?text=' . rawurlencode( $title . ' - ' . $url );
		$tg = 'https://t.me/share/url?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title );
		$mail = 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $url );
		?>
		<div class="baspar-scope">
			<div class="post-share">
				<span><?php echo esc_html( $s['label'] ); ?></span>
				<div class="post-share-btns">
					<a href="<?php echo esc_url( $wa ); ?>" aria-label="WhatsApp" target="_blank" rel="noopener"><?php echo icon_svg( 'whatsapp', 16 ); // phpcs:ignore ?></a>
					<a href="<?php echo esc_url( $tg ); ?>" aria-label="Telegram" target="_blank" rel="noopener"><?php echo icon_svg( 'telegram', 16 ); // phpcs:ignore ?></a>
					<a href="https://instagram.com" aria-label="Instagram" target="_blank" rel="noopener"><?php echo icon_svg( 'instagram', 16 ); // phpcs:ignore ?></a>
					<a href="<?php echo esc_url( $mail ); ?>" aria-label="Email"><?php echo icon_svg( 'mail', 16 ); // phpcs:ignore ?></a>
				</div>
				<strong><?php echo esc_html( $s['strong'] ); ?></strong>
			</div>
		</div>
		<?php
	}
}
