<?php
/**
 * Social showcase — gradient panel with WhatsApp / Telegram / Instagram cards.
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
 * Class Baspar_Social_Showcase
 */
class Baspar_Social_Showcase extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-social-showcase';
	}

	public function get_title() {
		return __( 'بسپار — شبکه‌های اجتماعی', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-social-icons';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'FOLLOW US' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ما را در شبکه‌های اجتماعی دنبال کنید' ) );
		$this->add_control( 'sub', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => 'آخرین محصولات، تخفیف‌ها و مطالب فنی را در شبکه‌های اجتماعی ما ببینید.' ) );

		$rep = new Repeater();
		$rep->add_control(
			'type',
			array(
				'label'   => __( 'نوع', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array( 'wa' => 'واتس‌اپ', 'tg' => 'تلگرام', 'ig' => 'اینستاگرام' ),
				'default' => 'wa',
			)
		);
		$rep->add_control( 'mono', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'handle', array( 'label' => __( 'آیدی/شماره', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'کارت‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'type' => 'wa', 'mono' => 'WHATSAPP', 'title' => 'واتس‌اپ', 'handle' => '+98 912 099 7651', 'link' => array( 'url' => 'https://wa.me/989120997651' ) ),
					array( 'type' => 'tg', 'mono' => 'TELEGRAM', 'title' => 'تلگرام', 'handle' => '@basparmarket', 'link' => array( 'url' => 'https://t.me/basparmarket' ) ),
					array( 'type' => 'ig', 'mono' => 'INSTAGRAM', 'title' => 'اینستاگرام', 'handle' => '@basparmarket', 'link' => array( 'url' => 'https://instagram.com/basparmarket' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_background( 'bg', '.social-showcase' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$imap = array( 'wa' => 'whatsapp', 'tg' => 'telegram', 'ig' => 'instagram' );
		?>
		<div class="baspar-scope">
			<div class="social-showcase">
				<div class="social-showcase-head">
					<?php if ( $s['kicker'] ) : ?><span class="mono"><?php echo esc_html( $s['kicker'] ); ?></span><?php endif; ?>
					<h3><?php echo esc_html( $s['title'] ); ?></h3>
					<?php if ( $s['sub'] ) : ?><div class="rich-text"><?php echo wp_kses_post( $s['sub'] ); ?></div><?php endif; ?>
				</div>
				<div class="social-cards">
					<?php foreach ( (array) $s['items'] as $it ) :
						$url  = ! empty( $it['link']['url'] ) ? $it['link']['url'] : '#';
						$icon = isset( $imap[ $it['type'] ] ) ? $imap[ $it['type'] ] : 'whatsapp';
						?>
						<a href="<?php echo esc_url( $url ); ?>" class="scc <?php echo esc_attr( $it['type'] ); ?>" target="_blank" rel="noopener">
							<div class="scc-ic"><?php echo icon_svg( $icon, 22 ); // phpcs:ignore ?></div>
							<?php if ( $it['mono'] ) : ?><span class="mono"><?php echo esc_html( $it['mono'] ); ?></span><?php endif; ?>
							<strong><?php echo esc_html( $it['title'] ); ?></strong>
							<span class="scc-handle"><?php echo esc_html( $it['handle'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
