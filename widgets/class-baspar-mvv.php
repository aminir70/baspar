<?php
/**
 * Mission / Vision / Values cards (3-up).
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
 * Class Baspar_Mvv
 */
class Baspar_Mvv extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-mvv';
	}

	public function get_title() {
		return __( 'بسپار — مأموریت/چشم‌انداز/ارزش', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-info-box';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'کارت‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'target' ) );
		$rep->add_control( 'mono', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'کارت‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'icon' => 'target', 'mono' => 'MISSION', 'title' => 'مأموریت ما', 'text' => 'تأمین پایدار و باکیفیت مواد اولیه برای صنایع کشور با قیمت رقابتی.' ),
					array( 'icon' => 'eye', 'mono' => 'VISION', 'title' => 'چشم‌انداز', 'text' => 'تبدیل‌شدن به بزرگ‌ترین مرجع تأمین مواد اولیه شیمیایی در ایران.' ),
					array( 'icon' => 'heart', 'mono' => 'VALUES', 'title' => 'ارزش‌ها', 'text' => 'صداقت، کیفیت، پاسخگویی و احترام به مشتری در همه مراحل.' ),
				),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.mvv-card h3' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="mvv">
				<?php
				$i = 0;
				foreach ( (array) $s['items'] as $it ) :
					?>
					<div class="mvv-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 80 ); ?>">
						<?php
						// heart icon isn't in the base set; fall back gracefully.
						$icon = in_array( $it['icon'], array( 'heart' ), true ) ? 'star' : $it['icon'];
						?>
						<div class="mvv-ic"><?php echo icon_svg( $icon ? $icon : 'target', 26 ); // phpcs:ignore ?></div>
						<?php if ( $it['mono'] ) : ?><span class="mono"><?php echo esc_html( $it['mono'] ); ?></span><?php endif; ?>
						<h3><?php echo esc_html( $it['title'] ); ?></h3>
						<p><?php echo esc_html( $it['text'] ); ?></p>
					</div>
					<?php
					++$i;
				endforeach;
				?>
			</div>
		</div>
		<?php
	}
}
