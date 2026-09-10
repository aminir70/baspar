<?php
/**
 * City industries — icon + title + description card grid, one per local
 * industry a city's factories operate in. Placed after a "Section Heading"
 * widget.
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
 * Class Baspar_City_Industries
 */
class Baspar_City_Industries extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-city-industries';
	}

	public function get_title() {
		return __( 'بسپار — صنایع شهر', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-product-related';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'صنایع', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'factory' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'صنایع', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( empty( $s['items'] ) ) {
			return;
		}
		?>
		<div class="baspar-scope">
			<div class="locind-grid">
				<?php
				$i = 0;
				foreach ( (array) $s['items'] as $x ) :
					?>
					<div class="locind-card <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 45 ); ?>">
						<div class="locind-ic"><?php echo icon_svg( $x['icon'] ? $x['icon'] : 'factory', 26 ); // phpcs:ignore ?></div>
						<h4><?php echo esc_html( $x['title'] ); ?></h4>
						<p><?php echo esc_html( $x['desc'] ); ?></p>
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
