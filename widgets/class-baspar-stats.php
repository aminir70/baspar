<?php
/**
 * Animated stats bar (dark). Each stat: animated number + suffix + label.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Stats
 */
class Baspar_Stats extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-stats';
	}

	public function get_title() {
		return __( 'بسپار — نوار شمارنده', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-counter';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'شمارنده‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'num', array( 'label' => __( 'عدد', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 5 ) );
		$rep->add_control( 'suffix', array( 'label' => __( 'پسوند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '+' ) );
		$rep->add_control( 'label', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'شمارنده‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ num }}}{{{ suffix }}} — {{{ label }}}',
				'default'     => array(
					array( 'num' => 5, 'suffix' => '+', 'label' => 'سال تجربه' ),
					array( 'num' => 100, 'suffix' => '+', 'label' => 'ماده اولیه' ),
					array( 'num' => 980, 'suffix' => '+', 'label' => 'کارخانه فعال' ),
					array( 'num' => 24, 'suffix' => 'h', 'label' => 'زمان ارسال' ),
				),
			)
		);
		$this->add_control( 'locale', array( 'label' => __( 'اعداد فارسی', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'fa', 'default' => 'fa' ) );
		$this->add_control(
			'show_grid_bg',
			array(
				'label'        => __( 'نمایش پس‌زمینه شبکه‌ای', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'bg', __( 'پس‌زمینه', 'baspar-elements' ), '.bspr-stats', 'background' );
		$this->add_color( 'num_color', __( 'رنگ عدد', 'baspar-elements' ), '.stat-n', 'color' );
		$this->add_color( 'label_color', __( 'رنگ برچسب', 'baspar-elements' ), '.stat-l', 'color' );
		$this->add_responsive_control(
			'inner_padding',
			array(
				'label'      => __( 'فاصله داخلی', 'baspar-elements' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array( 'top' => '36', 'right' => '24', 'bottom' => '36', 'left' => '24', 'unit' => 'px', 'isLinked' => false ),
				'selectors'  => array(
					'{{WRAPPER}} .bspr-stats' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
		$this->add_radius( 'radius', __( 'گردی گوشه', 'baspar-elements' ), '.bspr-stats' );
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$locale = $s['locale'] ? $s['locale'] : 'en';
		$grid_cls = ( 'yes' === $s['show_grid_bg'] ) ? ' has-grid' : '';
		?>
		<div class="baspar-scope">
			<div class="bspr-stats<?php echo esc_attr( $grid_cls ); ?>" style="background:var(--brand-deep);color:#EEE7F8;border-radius:calc(var(--radius) + 6px);padding:36px 24px;position:relative;overflow:hidden">
				<?php if ( 'yes' === $s['show_grid_bg'] ) : ?>
					<div aria-hidden="true" style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(ellipse at center,black 30%,transparent 75%)"></div>
				<?php endif; ?>
				<div class="stats-inner" style="position:relative;max-width:var(--container);margin:0 auto">
					<?php foreach ( (array) $s['items'] as $it ) :
						$display = ( 'fa' === $locale ) ? number_format_i18n( $it['num'] ) : number_format( $it['num'] );
						?>
						<div class="stat">
							<div class="stat-n">
								<span class="num" data-counter data-target="<?php echo esc_attr( $it['num'] ); ?>" data-locale="<?php echo esc_attr( $locale ); ?>"><?php echo esc_html( $display ); ?></span>
								<?php if ( $it['suffix'] ) : ?><span class="suf"><?php echo esc_html( $it['suffix'] ); ?></span><?php endif; ?>
							</div>
							<div class="stat-l"><?php echo esc_html( $it['label'] ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
