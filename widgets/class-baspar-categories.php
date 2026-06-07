<?php
/**
 * Category pills row. Can read WooCommerce product categories live, or use a
 * manual repeater. Each pill: icon + title + mono code.
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
 * Class Baspar_Categories
 */
class Baspar_Categories extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-categories';
	}

	public function get_title() {
		return __( 'بسپار — نوار دسته‌بندی', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-product-categories';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'منبع', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'woo'    => __( 'دسته‌بندی ووکامرس (پویا)', 'baspar-elements' ),
					'manual' => __( 'دستی (ریپیتر)', 'baspar-elements' ),
				),
				'default' => 'woo',
			)
		);
		$this->add_control(
			'woo_count',
			array(
				'label'     => __( 'تعداد دسته‌ها', 'baspar-elements' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 7,
				'condition' => array( 'source' => 'woo' ),
			)
		);
		$this->add_control(
			'woo_icon',
			array(
				'label'     => __( 'آیکون پیش‌فرض دسته‌ها', 'baspar-elements' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => icon_options(),
				'default'   => 'layers',
				'condition' => array( 'source' => 'woo' ),
			)
		);

		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'layers' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'code', array( 'label' => __( 'کد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'دسته‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'condition'   => array( 'source' => 'manual' ),
				'default'     => array(
					array( 'icon' => 'layers', 'title' => 'پلیمر و پلاستیک', 'code' => 'POLYMER' ),
					array( 'icon' => 'palette', 'title' => 'رنگ و رزین', 'code' => 'PAINT' ),
					array( 'icon' => 'beaker', 'title' => 'شیمیایی و حلال', 'code' => 'CHEMICAL' ),
					array( 'icon' => 'flask', 'title' => 'کامپوزیت', 'code' => 'COMPOSITE' ),
					array( 'icon' => 'droplet', 'title' => 'چسب صنعتی', 'code' => 'GLUE' ),
					array( 'icon' => 'refresh', 'title' => 'لاستیک', 'code' => 'RUBBER' ),
					array( 'icon' => 'package', 'title' => 'سایر محصولات', 'code' => 'OTHER' ),
				),
			)
		);
		$this->add_control(
			'pull_up',
			array(
				'label'        => __( 'قرارگیری روی هیرو (Pull-up)', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.cat-pill strong' );
		$this->end_controls_section();
	}

	/**
	 * Build the list of pills to render.
	 *
	 * @param array $s Settings.
	 * @return array<int,array>
	 */
	private function get_pills( $s ) {
		if ( 'manual' === $s['source'] ) {
			return (array) $s['items'];
		}
		$out = array();
		if ( taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'number'     => (int) $s['woo_count'],
					'orderby'    => 'count',
					'order'      => 'DESC',
				)
			);
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					$out[] = array(
						'icon'  => $s['woo_icon'],
						'title' => $t->name,
						'code'  => strtoupper( $t->slug ),
						'link'  => array( 'url' => get_term_link( $t ) ),
					);
				}
			}
		}
		return $out;
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = $this->get_pills( $s );
		if ( empty( $items ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'دسته‌بندی ووکامرسی یافت نشد. ووکامرس را فعال کنید یا منبع را روی «دستی» بگذارید.', 'baspar-elements' ) . '</div>';
			}
			return;
		}
		$cls = 'cat-row' . ( 'yes' === $s['pull_up'] ? ' pull-up' : '' );
		?>
		<div class="baspar-scope">
			<section class="<?php echo esc_attr( $cls ); ?>">
				<div class="cat-row-grid">
					<?php
					$i = 0;
					foreach ( $items as $c ) :
						$url = ! empty( $c['link']['url'] ) ? $c['link']['url'] : '#';
						?>
						<a href="<?php echo esc_url( $url ); ?>" class="cat-pill <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 40 ); ?>">
							<div class="cat-pill-ic"><?php echo icon_svg( $c['icon'] ? $c['icon'] : 'package', 24 ); /* phpcs:ignore */ ?></div>
							<strong><?php echo esc_html( $c['title'] ); ?></strong>
							<?php if ( ! empty( $c['code'] ) ) : ?><span class="mono"><?php echo esc_html( $c['code'] ); ?></span><?php endif; ?>
						</a>
						<?php
						++$i;
					endforeach;
					?>
				</div>
			</section>
		</div>
		<?php
	}
}
