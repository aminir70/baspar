<?php
/**
 * Categories tabs — horizontal tab bar + two-column panel (left: big icon +
 * description + CTA, right: 2-col list of product codes/names).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Categories_Tabs extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-categories-tabs'; }
	public function get_title() { return __( 'بسپار — دسته‌بندی تب‌دار', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-tabs'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تب‌ها', 'baspar-elements' ) ) );

		$itm = new Repeater();
		$itm->add_control( 'code', array( 'label' => __( 'کد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$itm->add_control( 'name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );

		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'layers' ) );
		$rep->add_control( 'label', array( 'label' => __( 'برچسب تب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان پنل', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$rep->add_control( 'btn_text', array( 'label' => __( 'دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده محصولات' ) );
		$rep->add_control( 'btn_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$rep->add_control( 'grid_label', array( 'label' => __( 'برچسب گرید', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'گریدهای پرفروش' ) );
		$rep->add_control( 'items', array(
			'label' => __( 'کدهای محصول', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $itm->get_controls(),
			'title_field' => '{{{ name }}}',
		) );

		$this->add_control( 'tabs', array(
			'label' => __( 'تب‌ها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ label }}}',
			'default' => array(
				array(
					'icon' => 'layers', 'label' => 'پلیمر و پلاستیک', 'title' => 'مواد اولیه پلیمر و پلاستیک',
					'desc' => 'انواع گریدهای صنعتی، تزریقی، بادی و بسته‌بندی از معتبرترین تولیدکنندگان دنیا.',
					'btn_text' => 'مشاهده همه', 'grid_label' => 'گریدهای پرفروش',
					'items' => array(
						array( 'code' => 'TPU', 'name' => 'ترموپلاستیک پلی‌اورتان' ),
						array( 'code' => 'CPE', 'name' => 'کلره پلی‌اتیلن' ),
						array( 'code' => 'ABS', 'name' => 'اکریلونیتریل بوتادین' ),
						array( 'code' => 'EVA', 'name' => 'اتیلن وینیل استات' ),
						array( 'code' => 'PBR', 'name' => 'پلی بوتادین رابر' ),
						array( 'code' => 'NBR', 'name' => 'NBR رابر' ),
					),
				),
				array(
					'icon' => 'palette', 'label' => 'رنگ و رزین', 'title' => 'مواد اولیه رنگ و رزین',
					'desc' => 'پیگمنت، تیتانیوم، رزین‌های اپوکسی/پلی‌استر و حلال‌های صنعتی برای صنایع رنگ‌سازی.',
					'btn_text' => 'مشاهده همه', 'grid_label' => 'گریدهای محبوب',
					'items' => array(
						array( 'code' => 'TiO₂', 'name' => 'تیتانیوم دی‌اکسید R-838' ),
						array( 'code' => 'Epoxy', 'name' => 'رزین اپوکسی' ),
						array( 'code' => 'PE-Resin', 'name' => 'رزین پلی‌استر' ),
						array( 'code' => 'Pigment', 'name' => 'پیگمنت‌های وارداتی' ),
					),
				),
				array(
					'icon' => 'beaker', 'label' => 'شیمیایی و حلال', 'title' => 'مواد شیمیایی و حلال‌ها',
					'desc' => 'حلال‌های صنعتی، اسیدها و مواد شیمیایی پایه برای صنایع مختلف.',
					'btn_text' => 'مشاهده همه', 'grid_label' => 'حلال‌های پرفروش',
					'items' => array(
						array( 'code' => 'MEK', 'name' => 'متیل اتیل کتون' ),
						array( 'code' => 'MIBK', 'name' => 'متیل ایزوبوتیل کتون' ),
						array( 'code' => 'MEG', 'name' => 'منو اتیلن گلایکول' ),
						array( 'code' => 'DOP', 'name' => 'دی اتیل هگزیل فتالات' ),
					),
				),
			),
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$tabs = (array) $s['tabs'];
		$uid = 'bspr-cattabs-' . $this->get_id();
		?>
		<div class="baspar-scope">
			<div class="cats" data-tabs id="<?php echo esc_attr( $uid ); ?>">
				<div class="cats-tabs">
					<?php foreach ( $tabs as $i => $t ) : ?>
						<button class="cats-tab <?php echo 0 === $i ? 'is-active' : ''; ?>" data-tab="t<?php echo esc_attr( $i ); ?>" type="button">
							<?php echo icon_svg( $t['icon'] ? $t['icon'] : 'layers', 18 ); // phpcs:ignore ?>
							<span><?php echo esc_html( $t['label'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
				<?php foreach ( $tabs as $i => $t ) : ?>
					<div class="cats-panel" data-panel="t<?php echo esc_attr( $i ); ?>" style="display:<?php echo 0 === $i ? 'grid' : 'none'; ?>">
						<div class="cats-panel-left">
							<div class="cats-icon-lg"><?php echo icon_svg( $t['icon'] ? $t['icon'] : 'layers', 32 ); // phpcs:ignore ?></div>
							<h3><?php echo esc_html( $t['title'] ); ?></h3>
							<p><?php echo esc_html( $t['desc'] ); ?></p>
							<?php if ( $t['btn_text'] ) : ?>
								<a href="<?php echo esc_url( $t['btn_link']['url'] ?? '#' ); ?>" class="btn btn-primary btn-sm">
									<?php echo esc_html( $t['btn_text'] ); ?> <?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?>
								</a>
							<?php endif; ?>
						</div>
						<div class="cats-panel-right">
							<div class="cats-grid-label"><?php echo esc_html( $t['grid_label'] ); ?></div>
							<ul class="cats-items">
								<?php foreach ( (array) $t['items'] as $it ) : ?>
									<li><span class="mono">›</span> <span><strong><?php echo esc_html( $it['code'] ); ?></strong> — <?php echo esc_html( $it['name'] ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<script>
		(function(){
			var root = document.getElementById('<?php echo esc_js( $uid ); ?>');
			if (!root) return;
			var tabs = root.querySelectorAll('[data-tab]');
			var panels = root.querySelectorAll('[data-panel]');
			tabs.forEach(function(t){
				t.addEventListener('click', function(){
					var id = t.getAttribute('data-tab');
					tabs.forEach(function(x){ x.classList.toggle('is-active', x === t); });
					panels.forEach(function(p){ p.style.display = (p.getAttribute('data-panel') === id ? 'grid' : 'none'); });
				});
			});
		})();
		</script>
		<?php
	}
}
