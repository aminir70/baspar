<?php
/**
 * Origin countries — large cards with SVG flag, name, description and tags.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\flag_svg;
use function BasparElements\flag_options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Origin_Countries
 */
class Baspar_Origin_Countries extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-origin-countries';
	}

	public function get_title() {
		return __( 'بسپار — کشورهای مبدأ', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-globe';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'کشورها', 'baspar-elements' ) ) );

		$tag = new Repeater();
		$tag->add_control( 'text', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );

		$rep = new Repeater();
		$rep->add_control( 'flag', array( 'label' => __( 'پرچم', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => flag_options(), 'default' => 'china' ) );
		$rep->add_control( 'name', array( 'label' => __( 'نام کشور', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'code', array( 'label' => __( 'کد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$rep->add_control(
			'tags',
			array(
				'label'       => __( 'برچسب‌های محصول', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $tag->get_controls(),
				'title_field' => '{{{ text }}}',
			)
		);
		$this->add_control(
			'items',
			array(
				'label'       => __( 'کشورها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array(
						'flag' => 'china', 'name' => 'چین', 'code' => 'CN',
						'desc' => 'تأمین گسترده‌ی مواد اولیه شیمیایی و پلیمری از کارخانه‌های معتبر چینی با قیمت رقابتی.',
						'tags' => array( array( 'text' => 'TiO₂' ), array( 'text' => 'MEK' ), array( 'text' => 'رزین' ) ),
					),
					array(
						'flag' => 'germany', 'name' => 'آلمان', 'code' => 'DE',
						'desc' => 'گریدهای ویژه و باکیفیت اروپایی برای صنایع تخصصی و فرمولاسیون‌های حساس.',
						'tags' => array( array( 'text' => 'TPU' ), array( 'text' => 'Epoxy' ), array( 'text' => 'افزودنی' ) ),
					),
					array(
						'flag' => 'turkey', 'name' => 'ترکیه', 'code' => 'TR',
						'desc' => 'حمل سریع و نزدیک، مناسب برای سفارش‌های فوری و تأمین پیوسته.',
						'tags' => array( array( 'text' => 'حلال' ), array( 'text' => 'پلاستیک' ) ),
					),
				),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'name_typo', __( 'تایپوگرافی نام', 'baspar-elements' ), '.origin-top strong' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="origin-grid">
				<?php
				$i = 0;
				foreach ( (array) $s['items'] as $c ) :
					?>
					<div class="origin-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 80 ); ?>">
						<div class="origin-top">
							<span class="origin-flag"><?php echo flag_svg( $c['flag'] ); // phpcs:ignore ?></span>
							<span><strong><?php echo esc_html( $c['name'] ); ?></strong><span class="mono"><?php echo esc_html( $c['code'] ); ?></span></span>
						</div>
						<div class="origin-body">
							<p><?php echo esc_html( $c['desc'] ); ?></p>
							<div class="origin-tags">
								<?php foreach ( (array) $c['tags'] as $t ) : ?><span class="origin-tag"><?php echo esc_html( $t['text'] ); ?></span><?php endforeach; ?>
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
