<?php
/**
 * Contact map. Either an animated graphic pin (design style) or a real
 * embedded iframe (Google Maps / Neshan) via the embed URL.
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
 * Class Baspar_Contact_Map
 */
class Baspar_Contact_Map extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-contact-map';
	}

	public function get_title() {
		return __( 'بسپار — نقشه تماس', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-google-maps';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'نقشه', 'baspar-elements' ) ) );
		$this->add_control(
			'mode',
			array(
				'label'   => __( 'حالت', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'graphic',
				'options' => array(
					'graphic' => __( 'گرافیکی متحرک (پین پالس‌دار)', 'baspar-elements' ),
					'embed'   => __( 'نقشه واقعی (iframe)', 'baspar-elements' ),
				),
			)
		);
		$this->add_control( 'label', array( 'label' => __( 'عنوان / برچسب پین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'دفتر مرکزی بسپارمارکت' ) );
		$this->add_control(
			'embed',
			array(
				'label'       => __( 'کد embed نقشه', 'baspar-elements' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'description' => __( 'کل کد iframe گوگل‌مپ / نشان یا فقط آدرس src آن را وارد کنید.', 'baspar-elements' ),
				'condition'   => array( 'mode' => 'embed' ),
			)
		);
		$this->add_control( 'show_card', array( 'label' => __( 'کارت اطلاعات روی نقشه', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'mode' => 'embed' ) ) );
		$this->add_control( 'address', array( 'label' => __( 'آدرس (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => '', 'condition' => array( 'mode' => 'embed', 'show_card' => 'yes' ) ) );
		$this->add_control( 'route_text', array( 'label' => __( 'متن دکمه مسیریابی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مسیریابی', 'condition' => array( 'mode' => 'embed', 'show_card' => 'yes' ) ) );
		$this->add_control( 'route_link', array( 'label' => __( 'لینک مسیریابی (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'description' => __( 'خالی بماند تا از مختصات نقشه ساخته شود.', 'baspar-elements' ), 'condition' => array( 'mode' => 'embed', 'show_card' => 'yes' ) ) );
		$this->add_control( 'height', array( 'label' => __( 'ارتفاع (px)', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 360 ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	/**
	 * Accept either a bare embed URL or the full <iframe> code and return the
	 * src URL (empty string when nothing usable is found).
	 *
	 * @param string $raw Control value.
	 * @return string
	 */
	private function embed_src( $raw ) {
		$raw = trim( (string) $raw );
		if ( preg_match( '#<iframe[^>]*\ssrc=(["\'])(.*?)\1#is', $raw, $m ) ) {
			$raw = $m[2];
		}
		$raw = html_entity_decode( $raw, ENT_QUOTES );
		return preg_match( '#^https?://#i', $raw ) ? $raw : '';
	}

	/**
	 * Directions URL from the coordinates inside a Google Maps embed URL
	 * (`!2d<lng>!3d<lat>`), or '' when they cannot be found.
	 *
	 * @param string $src Embed URL.
	 * @return string
	 */
	private function route_url( $src ) {
		if ( preg_match( '#!2d(-?[\d.]+)!3d(-?[\d.]+)#', $src, $m ) ) {
			return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $m[2] . ',' . $m[1] );
		}
		return '';
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$h   = max( 220, (int) $s['height'] );
		$src = 'embed' === $s['mode'] ? $this->embed_src( $s['embed'] ?? '' ) : '';
		?>
		<div class="baspar-scope">
			<?php if ( '' !== $src ) :
				$route = ! empty( $s['route_link']['url'] ) ? $s['route_link']['url'] : $this->route_url( $src );
				?>
				<div class="cmap-card" style="--cmap-h:<?php echo esc_attr( $h ); ?>px">
					<div class="cmap-frame">
						<div class="cmap-skeleton" aria-hidden="true"><?php echo icon_svg( 'pin', 28 ); // phpcs:ignore ?></div>
						<iframe src="<?php echo esc_url( $src ); ?>" title="<?php echo esc_attr( $s['label'] ? $s['label'] : __( 'نقشه', 'baspar-elements' ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
					</div>
					<?php if ( 'yes' === ( $s['show_card'] ?? 'yes' ) && ( $s['label'] || ! empty( $s['address'] ) || $route ) ) : ?>
						<div class="cmap-info">
							<span class="cmap-info-ic" aria-hidden="true"><span class="cmap-info-ring"></span><?php echo icon_svg( 'pin', 20 ); // phpcs:ignore ?></span>
							<div class="cmap-info-body">
								<?php if ( $s['label'] ) : ?><strong><?php echo esc_html( $s['label'] ); ?></strong><?php endif; ?>
								<?php if ( ! empty( $s['address'] ) ) : ?><span><?php echo esc_html( $s['address'] ); ?></span><?php endif; ?>
							</div>
							<?php if ( $route && ! empty( $s['route_text'] ) ) : ?>
								<a class="cmap-route" href="<?php echo esc_url( $route ); ?>" target="_blank" rel="noopener"><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?><span><?php echo esc_html( $s['route_text'] ); ?></span></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<div class="contact-map" style="height:<?php echo esc_attr( $h ); ?>px">
					<div class="cmap-grid" aria-hidden="true"></div>
					<div class="cmap-pulse" aria-hidden="true"></div>
					<div class="cmap-pin">
						<div class="cmap-pin-mark"><?php echo icon_svg( 'pin', 26 ); // phpcs:ignore ?></div>
						<div class="cmap-pin-label"><?php echo esc_html( $s['label'] ); ?></div>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
