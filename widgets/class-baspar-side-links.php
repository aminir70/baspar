<?php
/**
 * Sidebar link cards — icon, title, short description and arrow.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;
use function BasparElements\slash_url;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Side_Links extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-side-links'; }
	public function get_title() { return __( 'بسپار — کناری: لینک‌های مهم', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-link'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'لینک‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'cart' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح کوتاه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items', array(
			'label'       => __( 'لینک‌ها', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array( 'icon' => 'cart', 'title' => 'خرید مواد اولیه شیمیایی', 'desc' => 'قیمت روز، موجودی انبار و راهنمای خرید', 'link' => array( 'url' => '/buy-chemical-raw-materials/' ) ),
				array( 'icon' => 'globe', 'title' => 'وارد کننده مواد اولیه شیمیایی', 'desc' => 'واردات مستقیم از کارخانه سازنده', 'link' => array( 'url' => '/chemical-importer/' ) ),
			),
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<nav class="sln" aria-label="<?php esc_attr_e( 'لینک‌های مهم', 'baspar-elements' ); ?>">
				<?php foreach ( (array) $s['items'] as $it ) :
					if ( empty( $it['title'] ) || empty( $it['link']['url'] ) ) {
						continue;
					}
					?>
					<a class="sln-item" href="<?php echo esc_url( slash_url( $it['link']['url'] ) ); ?>">
						<span class="sln-ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'cart', 22 ); // phpcs:ignore ?></span>
						<span class="sln-body"><strong><?php echo esc_html( $it['title'] ); ?></strong><?php if ( ! empty( $it['desc'] ) ) : ?><span><?php echo esc_html( $it['desc'] ); ?></span><?php endif; ?></span>
						<span class="sln-go" aria-hidden="true"><?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		</div>
		<?php
	}
}
