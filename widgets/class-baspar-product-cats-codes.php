<?php
/**
 * Product category cards with code list — 6 pcat cards, each has icon, title,
 * description, a mono code list block, and a CTA footer.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Product_Cats_Codes extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-product-cats-codes'; }
	public function get_title() { return __( 'بسپار — کارت دسته با لیست کدها', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-product-categories'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'کارت‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'layers' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$rep->add_control( 'codes', array( 'label' => __( 'لیست کدها (با ویرگول جدا کنید)', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$rep->add_control( 'foot_text', array( 'label' => __( 'متن پاورقی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده گریدها' ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );

		$this->add_control( 'items', array(
			'label' => __( 'کارت‌ها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'icon' => 'layers', 'title' => 'پلیمر و پلاستیک', 'desc' => 'انواع گریدهای صنعتی و تخصصی.', 'codes' => 'TPU, CPE, ABS, EVA, NBR, PBR', 'foot_text' => 'مشاهده گریدها' ),
				array( 'icon' => 'palette', 'title' => 'رنگ و رزین', 'desc' => 'پیگمنت، تیتانیوم، اپوکسی و پلی‌استر.', 'codes' => 'TiO₂ R-838, Epoxy, PE-Resin', 'foot_text' => 'مشاهده گریدها' ),
				array( 'icon' => 'beaker', 'title' => 'شیمیایی و حلال', 'desc' => 'حلال‌های صنعتی و مواد شیمیایی پایه.', 'codes' => 'MEK, MIBK, MEG, DOP, MeCl', 'foot_text' => 'مشاهده گریدها' ),
				array( 'icon' => 'flask', 'title' => 'کامپوزیت', 'desc' => 'رزین، الیاف و کاتالیست‌های کامپوزیت.', 'codes' => 'Gelcoat, MEKP, Cobalt, CFiber', 'foot_text' => 'مشاهده گریدها' ),
				array( 'icon' => 'droplet', 'title' => 'چسب و درزگیر', 'desc' => 'مواد اولیه چسب‌های صنعتی.', 'codes' => 'Adhesive Base, Plasticizer', 'foot_text' => 'مشاهده گریدها' ),
				array( 'icon' => 'refresh', 'title' => 'لاستیک', 'desc' => 'کائوچو، شتاب‌دهنده و افزودنی.', 'codes' => 'SMR20, NBR, CBS, DCP', 'foot_text' => 'مشاهده گریدها' ),
			),
		) );
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
			<div class="pcats">
				<?php $i = 0; foreach ( (array) $s['items'] as $it ) : ++$i; ?>
					<a href="<?php echo esc_url( $it['link']['url'] ?? '#' ); ?>" class="pcat <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 40 ); ?>">
						<div class="pcat-top">
							<div class="pcat-ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'layers', 22 ); // phpcs:ignore ?></div>
							<h3><?php echo esc_html( $it['title'] ); ?></h3>
						</div>
						<p><?php echo esc_html( $it['desc'] ); ?></p>
						<?php if ( $it['codes'] ) : ?>
							<div class="pcat-codes mono"><?php echo esc_html( $it['codes'] ); ?></div>
						<?php endif; ?>
						<div class="pcat-foot"><span><?php echo esc_html( $it['foot_text'] ); ?></span><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
