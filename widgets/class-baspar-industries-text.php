<?php
/**
 * Industries (text-only variant for B2B page) — 4×2 grid of text cards
 * with a purple side accent, no icons.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Industries_Text extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-industries-text'; }
	public function get_title() { return __( 'بسپار — صنایع (متنی)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-text-area'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'صنایع', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$this->add_control( 'items', array(
			'label' => __( 'صنایع', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'title' => 'تولید رنگ صنعتی', 'text' => 'پیگمنت‌ها، تیتانیوم و رزین‌های پایه برای فرمولاسیون.' ),
				array( 'title' => 'صنعت پلاستیک', 'text' => 'گریدهای تزریقی، بادی و فیلم برای کارخانه‌های تبدیلی.' ),
				array( 'title' => 'کامپوزیت‌سازی', 'text' => 'رزین‌ها، الیاف و کاتالیست‌های تخصصی.' ),
				array( 'title' => 'صنعت چسب', 'text' => 'مواد اولیه چسب‌های صنعتی، PVA و SBR.' ),
				array( 'title' => 'تولید شوینده', 'text' => 'مواد فعال سطحی، الکیل‌بنزن و سود.' ),
				array( 'title' => 'تولید لاستیک', 'text' => 'کائوچوی طبیعی و سنتزی + شتاب‌دهنده.' ),
				array( 'title' => 'بسته‌بندی', 'text' => 'PE/PP فیلم و گریدهای شفاف.' ),
				array( 'title' => 'صنایع غذایی', 'text' => 'مواد افزودنی و نگه‌دارنده گرید FOOD.' ),
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
			<div class="indb2b <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php foreach ( (array) $s['items'] as $it ) : ?>
					<div class="indb2b-card">
						<strong><?php echo esc_html( $it['title'] ); ?></strong>
						<p><?php echo esc_html( $it['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
