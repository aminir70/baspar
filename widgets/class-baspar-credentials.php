<?php
/**
 * Credentials — 4 official badges + an attention note (creds-note) below.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Credentials extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-credentials'; }
	public function get_title() { return __( 'بسپار — مجوزها', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-medal'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'مجوزها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'shield' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'items', array(
			'label' => __( 'مجوزها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'icon' => 'doc', 'title' => 'کارت بازرگانی معتبر', 'desc' => 'فعالیت رسمی واردات' ),
				array( 'icon' => 'shield', 'title' => 'مجوز ثبت سفارش', 'desc' => 'اتاق بازرگانی' ),
				array( 'icon' => 'award', 'title' => 'عضو رسمی اتحادیه', 'desc' => 'صنفی واردکنندگان' ),
				array( 'icon' => 'building', 'title' => 'پروفرما اینویس رسمی', 'desc' => 'PI با شناسه ملی' ),
			),
		) );
		$this->add_control( 'note', array( 'label' => __( 'یادداشت پایانی (creds-note)', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => '<strong>توجه:</strong> پیش از هرگونه پرداخت، اصالت ما را با شناسه ملی و کارت بازرگانی استعلام کنید.' ) );
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
			<div class="creds <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php $i = 0; foreach ( (array) $s['items'] as $it ) : ++$i; ?>
					<div class="cred">
						<div class="cred-ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'shield', 24 ); // phpcs:ignore ?></div>
						<h4><?php echo esc_html( $it['title'] ); ?></h4>
						<p><?php echo esc_html( $it['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( $s['note'] ) : ?>
				<div class="creds-note">
					<?php echo icon_svg( 'info', 22 ); // phpcs:ignore ?>
					<div class="rich-text"><?php echo wp_kses_post( $s['note'] ); ?></div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
