<?php
/**
 * Trust cards (4-up): icon, mono tag, title, description.
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
 * Class Baspar_Trust
 */
class Baspar_Trust extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-trust';
	}

	public function get_title() {
		return __( 'بسپار — کارت‌های اعتماد', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-shield-check';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'کارت‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'truck' ) );
		$rep->add_control( 'tag', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'کارت‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'icon' => 'truck', 'tag' => 'LOGISTICS', 'title' => 'ارسال ۲۴ ساعته', 'desc' => 'ارسال سریع به سراسر ایران' ),
					array( 'icon' => 'shield', 'tag' => 'QUALITY', 'title' => 'اصالت کالا', 'desc' => 'COA و دیتاشیت معتبر همراه هر سفارش' ),
					array( 'icon' => 'chat', 'tag' => 'SUPPORT', 'title' => 'مشاوره آنلاین', 'desc' => 'پاسخگویی کارشناس فنی در ساعات کاری' ),
					array( 'icon' => 'refresh', 'tag' => 'WARRANTY', 'title' => 'گارانتی مغایرت', 'desc' => '۴۸ ساعت مهلت بازگشت در صورت مغایرت' ),
				),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_color( 'card_bg', __( 'پس‌زمینه کارت', 'baspar-elements' ), '.trust-card', 'background' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.trust-card h3' );
		$this->add_radius( 'radius', __( 'گردی گوشه', 'baspar-elements' ), '.trust-card' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="trust-grid">
				<?php
				$i = 0;
				foreach ( (array) $s['items'] as $it ) :
					?>
					<div class="trust-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 60 ); ?>">
						<div class="trust-icon"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'shield', 28 ); /* phpcs:ignore */ ?></div>
						<?php if ( $it['tag'] ) : ?><div class="trust-tag mono"><?php echo esc_html( $it['tag'] ); ?></div><?php endif; ?>
						<h3><?php echo esc_html( $it['title'] ); ?></h3>
						<p><?php echo esc_html( $it['desc'] ); ?></p>
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
