<?php
/**
 * Brand showcase rows. Each row: left intro (icon, mono, title, desc, button)
 * + right grid of product cells. Cells can link to products.
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
 * Class Baspar_Brands
 */
class Baspar_Brands extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-brands';
	}

	public function get_title() {
		return __( 'بسپار — نمایش برندها', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'برندها', 'baspar-elements' ) ) );

		// Cells repeater (used inside each brand row).
		$cell = new Repeater();
		$cell->add_control( 'code', array( 'label' => __( 'کد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$cell->add_control( 'name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$cell->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$cell->add_control( 'image', array( 'label' => __( 'تصویر (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA ) );

		$row = new Repeater();
		$row->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'flask' ) );
		$row->add_control( 'en', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$row->add_control( 'name', array( 'label' => __( 'نام برند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$row->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$row->add_control( 'btn_text', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده همه گریدها' ) );
		$row->add_control( 'btn_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$row->add_control(
			'cells',
			array(
				'label'       => __( 'سلول‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $cell->get_controls(),
				'title_field' => '{{{ name }}}',
			)
		);

		$this->add_control(
			'rows',
			array(
				'label'       => __( 'ردیف‌های برند', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $row->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array(
						'icon' => 'flask', 'en' => 'TITAN', 'name' => 'تیتان',
						'desc' => 'گریدهای مختلف تیتانیوم دی‌اکسید (TiO₂) از کارخانه‌های معتبر چینی و اروپایی برای صنایع رنگ و پلاستیک.',
						'btn_text' => 'مشاهده همه گریدها',
						'cells' => array(
							array( 'code' => 'R-838', 'name' => 'تیتان R838' ),
							array( 'code' => 'R-819', 'name' => 'تیتان R819' ),
							array( 'code' => 'R-299', 'name' => 'تیتان R299' ),
							array( 'code' => 'A-110', 'name' => 'تیتان A110' ),
							array( 'code' => 'SR-2400', 'name' => 'تیتان SR 2400' ),
						),
					),
					array(
						'icon' => 'droplet', 'en' => 'RESIN', 'name' => 'رزین',
						'desc' => 'انواع رزین‌های صنعتی شامل اپوکسی، پلی‌استر، فنالیک و آلیدیک — گرید صنعتی و ویژه.',
						'btn_text' => 'مشاهده همه گریدها',
						'cells' => array(
							array( 'code' => 'Phthalic', 'name' => 'انیدرید فتالیک' ),
							array( 'code' => 'Phenolic', 'name' => 'ایزو فتالیک اسید' ),
							array( 'code' => 'Alkyd', 'name' => 'بوتیل اکریلات' ),
							array( 'code' => 'Maleic', 'name' => 'بوتیل هیدروکسی تولوئن' ),
							array( 'code' => 'Adipic', 'name' => 'ادیپیک اسید' ),
						),
					),
				),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'name_typo', __( 'تایپوگرافی نام برند', 'baspar-elements' ), '.brand-row-left h3' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<?php
			$i = 0;
			foreach ( (array) $s['rows'] as $b ) :
				?>
				<div class="brand-row <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 100 ); ?>">
					<div class="brand-row-inner">
						<div class="brand-row-left">
							<div class="ic-lg"><?php echo icon_svg( $b['icon'] ? $b['icon'] : 'flask', 28 ); // phpcs:ignore ?></div>
							<?php if ( $b['en'] ) : ?><span class="mono"><?php echo esc_html( $b['en'] ); ?></span><?php endif; ?>
							<h3><?php echo esc_html( $b['name'] ); ?></h3>
							<p><?php echo esc_html( $b['desc'] ); ?></p>
							<?php if ( $b['btn_text'] ) : ?>
								<a href="<?php echo esc_url( $b['btn_link']['url'] ?? '#' ); ?>" class="btn btn-ghost btn-sm" style="align-self:flex-start;margin-top:8px">
									<?php echo esc_html( $b['btn_text'] ); ?><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?>
								</a>
							<?php endif; ?>
						</div>
						<div class="brand-row-right">
							<?php foreach ( (array) $b['cells'] as $c ) :
								$url = ! empty( $c['link']['url'] ) ? $c['link']['url'] : '#';
								?>
								<a href="<?php echo esc_url( $url ); ?>" class="bcell">
									<div class="bcell-img">
										<?php
										if ( ! empty( $c['image']['url'] ) ) {
											echo '<img src="' . esc_url( $c['image']['url'] ) . '" alt="' . esc_attr( $c['name'] ) . '" style="position:relative;z-index:1;width:100%;height:100%;object-fit:cover" />';
										} else {
											echo icon_svg( 'package', 28 ); // phpcs:ignore
										}
										?>
									</div>
									<?php if ( $c['code'] ) : ?><span class="pcard-cat"><?php echo esc_html( $c['code'] ); ?></span><?php endif; ?>
									<strong><?php echo esc_html( $c['name'] ); ?></strong>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php
				++$i;
			endforeach;
			?>
		</div>
		<?php
	}
}
