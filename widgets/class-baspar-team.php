<?php
/**
 * Team members grid. Avatar image (or initials), name, role, bio, socials.
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
 * Class Baspar_Team
 */
class Baspar_Team extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-team';
	}

	public function get_title() {
		return __( 'بسپار — تیم', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'اعضا', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'photo', array( 'label' => __( 'عکس', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA ) );
		$rep->add_control( 'initials', array( 'label' => __( 'حروف اول (اگر عکس نبود)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'role', array( 'label' => __( 'سمت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'bio', array( 'label' => __( 'بیو', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$rep->add_control( 'wa', array( 'label' => __( 'لینک واتس‌اپ', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'placeholder' => 'https://wa.me/989120997651' ) );
		$rep->add_control( 'tg', array( 'label' => __( 'لینک تلگرام', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'placeholder' => 'https://t.me/username' ) );
		$rep->add_control( 'phone', array( 'label' => __( 'شماره تماس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'placeholder' => '+989120733965' ) );
		$rep->add_control( 'mail', array( 'label' => __( 'ایمیل (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'اعضای تیم', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array( 'initials' => 'ک ب', 'name' => 'مدیر بازرگانی', 'role' => 'SALES MANAGER', 'bio' => 'مسئول تأمین و قیمت‌گذاری مواد اولیه.' ),
					array( 'initials' => 'ف ن', 'name' => 'کارشناس فنی', 'role' => 'TECHNICAL', 'bio' => 'مشاوره فرمولاسیون و انتخاب گرید.' ),
					array( 'initials' => 'ل و', 'name' => 'واحد لجستیک', 'role' => 'LOGISTICS', 'bio' => 'هماهنگی ارسال سراسری.' ),
					array( 'initials' => 'پ م', 'name' => 'پشتیبانی مشتری', 'role' => 'SUPPORT', 'bio' => 'پاسخگویی و پیگیری سفارش‌ها.' ),
				),
			)
		);
		$this->add_control(
			'columns',
			array(
				'label'   => __( 'تعداد ستون', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '3',
				'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'name_typo', __( 'تایپوگرافی نام', 'baspar-elements' ), '.team-body strong' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$cols = isset( $s['columns'] ) && $s['columns'] ? (int) $s['columns'] : 3;
		?>
		<div class="baspar-scope">
			<div class="team-grid" style="grid-template-columns:repeat(<?php echo esc_attr( $cols ); ?>,1fr)">
				<?php
				$i = 0;
				foreach ( (array) $s['items'] as $m ) :
					$phone_raw = preg_replace( '/[^\d+]/', '', (string) $m['phone'] );
					?>
					<div class="team-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 60 ); ?>">
						<div class="team-avatar">
							<?php
							if ( ! empty( $m['photo']['url'] ) ) {
								echo '<img src="' . esc_url( $m['photo']['url'] ) . '" alt="' . esc_attr( $m['name'] ) . '" />';
							} else {
								echo '<div class="team-initials">' . esc_html( $m['initials'] ) . '</div>';
							}
							?>
						</div>
						<div class="team-body">
							<strong><?php echo esc_html( $m['name'] ); ?></strong>
							<div class="team-role mono"><?php echo esc_html( $m['role'] ); ?></div>
							<?php if ( $m['bio'] ) : ?><p class="team-bio"><?php echo esc_html( $m['bio'] ); ?></p><?php endif; ?>
							<div class="team-socials">
								<?php if ( ! empty( $m['wa']['url'] ) ) : ?><a href="<?php echo esc_url( $m['wa']['url'] ); ?>" aria-label="واتس‌اپ" target="_blank" rel="noopener"><?php echo icon_svg( 'whatsapp', 15 ); // phpcs:ignore ?></a><?php endif; ?>
								<?php if ( ! empty( $m['tg']['url'] ) ) : ?><a href="<?php echo esc_url( $m['tg']['url'] ); ?>" aria-label="تلگرام" target="_blank" rel="noopener"><?php echo icon_svg( 'telegram', 15 ); // phpcs:ignore ?></a><?php endif; ?>
								<?php if ( ! empty( $phone_raw ) ) : ?><a href="tel:<?php echo esc_attr( $phone_raw ); ?>" aria-label="تماس" title="<?php echo esc_attr( $m['phone'] ); ?>"><?php echo icon_svg( 'phone', 15 ); // phpcs:ignore ?></a><?php endif; ?>
								<?php if ( ! empty( $m['mail'] ) ) : ?><a href="mailto:<?php echo esc_attr( $m['mail'] ); ?>" aria-label="ایمیل"><?php echo icon_svg( 'mail', 15 ); // phpcs:ignore ?></a><?php endif; ?>
							</div>
						</div>
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
