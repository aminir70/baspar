<?php
/**
 * Industries grid (on dark background). Icon + title + small text per card.
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
 * Class Baspar_Industries
 */
class Baspar_Industries extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-industries';
	}

	public function get_title() {
		return __( 'بسپار — صنایع', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'صنایع', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'factory' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'صنایع', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'icon' => 'palette', 'title' => 'رنگ و رزین', 'text' => 'پیگمنت، رزین، حلال' ),
					array( 'icon' => 'layers', 'title' => 'پلاستیک', 'text' => 'گرید تزریقی و بادی' ),
					array( 'icon' => 'flask', 'title' => 'کامپوزیت', 'text' => 'رزین و الیاف' ),
					array( 'icon' => 'droplet', 'title' => 'چسب و درزگیر', 'text' => 'مواد اولیه چسب' ),
					array( 'icon' => 'beaker', 'title' => 'شوینده', 'text' => 'مواد فعال سطحی' ),
					array( 'icon' => 'refresh', 'title' => 'لاستیک', 'text' => 'کائوچو و افزودنی' ),
					array( 'icon' => 'package', 'title' => 'بسته‌بندی', 'text' => 'فیلم و گرانول' ),
					array( 'icon' => 'globe', 'title' => 'سایر صنایع', 'text' => 'تأمین تخصصی' ),
				),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'bg', __( 'پس‌زمینه', 'baspar-elements' ), '.section-dark', 'background' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="section-dark" style="border-radius:calc(var(--radius) + 6px)">
				<div class="inner">
					<div class="ind-grid <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
						<?php foreach ( (array) $s['items'] as $it ) : ?>
							<div class="ind-card">
								<div class="ind-icon"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'factory', 24 ); // phpcs:ignore ?></div>
								<h4><?php echo esc_html( $it['title'] ); ?></h4>
								<span><?php echo esc_html( $it['text'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
