<?php
/**
 * Image banners — a responsive row of linked image banners with a 3D tilt,
 * light sweep and zoom on hover and a staggered entrance. One column works
 * for a single wide banner (e.g. the company slogan).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\slash_url;
use function BasparElements\home_head_controls;
use function BasparElements\render_home_head;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Image_Banners extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-image-banners'; }
	public function get_title() { return __( 'بسپار — بنرهای تصویری متحرک', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-image-box'; }

	protected function register_controls() {
		$this->start_controls_section( 'head', array( 'label' => __( 'عنوان بخش (اختیاری)', 'baspar-elements' ) ) );
		home_head_controls( $this, array() );
		$this->end_controls_section();

		$this->start_controls_section( 'items_s', array( 'label' => __( 'بنرها', 'baspar-elements' ) ) );
		$r = new Repeater();
		$r->add_control( 'image', array( 'label' => __( 'تصویر', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA ) );
		$r->add_control( 'title', array( 'label' => __( 'عنوان (متن جایگزین تصویر)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'sub', array( 'label' => __( 'زیرنویس (فقط وقتی «نمایش عنوان» روشن است)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$up = 'https://basparmarket.com/wp-content/uploads/';
		$this->add_control( 'items', array(
			'label'       => __( 'بنرها', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $r->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array( 'title' => 'متیلن کلراید', 'sub' => 'حلال صنعتی', 'image' => array( 'url' => $up . '2024/06/متیلن-کلراید-300x237.png' ), 'link' => array( 'url' => '/product/methylene-chloride/' ) ),
				array( 'title' => 'روغن DOP', 'sub' => 'نرم‌کننده PVC', 'image' => array( 'url' => $up . '2024/06/روغن-dop-300x237.png' ), 'link' => array( 'url' => '/product/dop/' ) ),
				array( 'title' => 'TPU یورتان‌های ترموپلاستیک', 'sub' => 'گرید تزریق و اکستروژن', 'image' => array( 'url' => $up . '2025/11/tpu-300x237.png' ), 'link' => array( 'url' => '/product/tpu-%db%8c%d9%88%d8%b1%d8%aa%d8%a7%d9%86-%d9%87%d8%a7%db%8c-%d8%aa%d8%b1%d9%85%d9%88%d9%be%d9%84%d8%a7%d8%b3%d8%aa%db%8c%da%a9/' ) ),
				array( 'title' => 'پودر فوکو', 'sub' => 'افزودنی رنگ و پوشش', 'image' => array( 'url' => $up . '2025/05/پودر-فوکو-اصلی-300x237.png' ), 'link' => array( 'url' => '/product/foucault-powder/' ) ),
			),
		) );
		$this->add_responsive_control( 'columns', array(
			'label'          => __( 'ستون‌ها', 'baspar-elements' ),
			'type'           => Controls_Manager::SELECT,
			'default'        => '4',
			'tablet_default' => '2',
			'mobile_default' => '2',
			'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5' ),
			'selectors'      => array( '{{WRAPPER}} .ibn-grid' => '--cols:{{VALUE}}' ),
		) );
		$this->add_control( 'ratio', array(
			'label'   => __( 'نسبت تصویر', 'baspar-elements' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'auto',
			'options' => array( 'auto' => __( 'اندازه اصلی تصویر', 'baspar-elements' ), '300/237' => '300×237', '16/9' => '16:9', '4/3' => '4:3', '1/1' => '1:1', '21/6' => __( 'نواری (21:6)', 'baspar-elements' ) ),
		) );
		$this->add_control( 'show_title', array( 'label' => __( 'نمایش عنوان روی بنر', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'tilt', array( 'label' => __( 'حرکت سه‌بعدی با ماوس', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'frame', array( 'label' => __( 'قاب و سایه', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_radius( 'radius', __( 'گردی گوشه', 'baspar-elements' ), '.ibn-card' );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array_values( array_filter( (array) $s['items'], static function ( $i ) { return ! empty( $i['image']['url'] ); } ) );
		if ( ! $items ) {
			return;
		}
		$cols  = max( 1, min( 5, (int) ( $s['columns'] ?? 4 ) ) );
		$ratio = preg_match( '#^\d+/\d+$#', (string) $s['ratio'] ) ? $s['ratio'] : '';
		$cls   = 'ibn-grid' . ( 'yes' === $s['frame'] ? ' is-framed' : '' ) . ( 1 === $cols ? ' is-wide' : '' );
		?>
		<div class="baspar-scope">
			<section class="hm-sec ibn" data-anim-group>
				<?php render_home_head( $s ); ?>
				<div class="<?php echo esc_attr( $cls ); ?>" data-cols="<?php echo (int) $cols; ?>"<?php echo $ratio ? ' style="--ratio:' . esc_attr( $ratio ) . '"' : ''; ?>>
					<?php foreach ( $items as $i => $it ) :
						$url = ! empty( $it['link']['url'] ) ? slash_url( $it['link']['url'] ) : '';
						$tag = $url ? 'a' : 'div';
						$ext = $url && ! empty( $it['link']['is_external'] ) ? ' target="_blank" rel="noopener"' : '';
						?>
						<<?php echo $tag; // phpcs:ignore ?> class="ibn-card<?php echo $ratio ? ' has-ratio' : ''; ?>"<?php echo $url ? ' href="' . esc_url( $url ) . '"' . $ext : ''; // phpcs:ignore ?><?php echo 'yes' === $s['tilt'] ? ' data-tilt' : ''; ?> data-anim="up" style="--i:<?php echo (int) $i; ?>"<?php echo $url && ! empty( $it['title'] ) && 'yes' !== $s['show_title'] ? ' aria-label="' . esc_attr( $it['title'] ) . '"' : ''; ?>>
							<span class="ibn-img"><img src="<?php echo esc_url( $it['image']['url'] ); ?>" alt="<?php echo esc_attr( $it['title'] ); ?>" loading="lazy"<?php echo ! empty( $it['image']['id'] ) ? ' data-id="' . (int) $it['image']['id'] . '"' : ''; ?>></span>
							<span class="ibn-shine" aria-hidden="true"></span>
							<?php if ( 'yes' === $s['show_title'] && ! empty( $it['title'] ) ) : ?>
								<span class="ibn-cap"><strong><?php echo esc_html( $it['title'] ); ?></strong><?php if ( ! empty( $it['sub'] ) ) : ?><small><?php echo esc_html( $it['sub'] ); ?></small><?php endif; ?></span>
							<?php endif; ?>
							<?php if ( $url ) : ?><span class="ibn-go" aria-hidden="true"><?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?></span><?php endif; ?>
						</<?php echo $tag; // phpcs:ignore ?>>
					<?php endforeach; ?>
				</div>
			</section>
		</div>
		<?php
	}
}
