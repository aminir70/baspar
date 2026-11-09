<?php
/**
 * Contact form — basic 4-field form. Submits to email_to via mailto: fallback
 * or via a custom Elementor Pro form handler if available.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Contact_Form extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-contact-form'; }
	public function get_title() { return __( 'بسپار — فرم تماس', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-form-horizontal'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ارسال پیام' ) );
		$this->add_control( 'subtitle', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => 'برای استعلام قیمت یا مشاوره فنی، اطلاعات زیر را تکمیل کنید.' ) );
		$this->add_control( 'submit_text', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ارسال درخواست' ) );
		$this->add_control( 'email_to', array( 'label' => __( 'ایمیل گیرنده', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'info@basparmarket.com' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$action = 'mailto:' . $s['email_to'] . '?subject=' . rawurlencode( 'درخواست از سایت' );
		?>
		<div class="baspar-scope">
			<form class="contact-form <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" method="post" action="<?php echo esc_attr( $action ); ?>" enctype="text/plain">
				<h3><?php echo esc_html( $s['title'] ); ?></h3>
				<div class="rich-text"><?php echo wp_kses_post( $s['subtitle'] ); ?></div>
				<div class="form-row">
					<div class="field"><label>نام و نام خانوادگی *</label><input type="text" name="name" required></div>
					<div class="field"><label>شماره تماس *</label><input type="tel" name="phone" required></div>
				</div>
				<div class="form-row">
					<div class="field"><label>ایمیل</label><input type="email" name="email"></div>
					<div class="field"><label>موضوع</label>
						<select name="subject"><option>استعلام قیمت</option><option>مشاوره فنی</option><option>درخواست همکاری</option><option>سایر</option></select>
					</div>
				</div>
				<div class="form-row single">
					<div class="field"><label>پیام شما *</label><textarea name="message" required></textarea></div>
				</div>
				<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px">
					<?php echo icon_svg( 'mail', 16 ); // phpcs:ignore ?>
					<?php echo esc_html( $s['submit_text'] ); ?>
				</button>
			</form>
		</div>
		<?php
	}
}
