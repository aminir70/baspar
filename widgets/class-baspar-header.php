<?php
/**
 * Header widget — logo + dynamic WP menu + CTA. Menu is read live from the
 * site via wp_nav_menu(); the chosen menu's sub-menus render as the design's
 * hover dropdown.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Header
 */
class Baspar_Header extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-header';
	}

	public function get_title() {
		return __( 'بسپار — هدر + منو', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	/**
	 * Build the list of registered nav menus for the select control.
	 *
	 * @return array<string,string>
	 */
	private function get_menus() {
		$menus = array( '0' => __( '— انتخاب منو —', 'baspar-elements' ) );
		foreach ( wp_get_nav_menus() as $menu ) {
			$menus[ $menu->term_id ] = $menu->name;
		}
		return $menus;
	}

	protected function register_controls() {
		/* ---- Branding ---- */
		$this->start_controls_section(
			'branding',
			array( 'label' => __( 'برند و لوگو', 'baspar-elements' ) )
		);
		$this->add_control(
			'logo',
			array(
				'label'   => __( 'لوگو', 'baspar-elements' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => BASPAR_ELEMENTS_ASSETS . 'images/logo.png' ),
			)
		);
		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'logo_size',
				'default' => 'thumbnail',
			)
		);
		$this->add_control(
			'brand_title',
			array(
				'label'   => __( 'نام برند', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'بسپارمارکت', 'baspar-elements' ),
			)
		);
		$this->add_control(
			'brand_sub',
			array(
				'label'   => __( 'زیرعنوان (لاتین)', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'BASPARMARKET.COM',
			)
		);
		$this->add_control(
			'logo_link',
			array(
				'label'   => __( 'لینک لوگو', 'baspar-elements' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => home_url( '/' ) ),
			)
		);
		$this->end_controls_section();

		/* ---- Menu ---- */
		$this->start_controls_section(
			'menu',
			array( 'label' => __( 'منو (پویا از سایت)', 'baspar-elements' ) )
		);
		$this->add_control(
			'menu_id',
			array(
				'label'       => __( 'منوی وردپرس', 'baspar-elements' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->get_menus(),
				'default'     => '0',
				'description' => __( 'منو از «نمایش ← منوها» در پیشخوان خوانده می‌شود.', 'baspar-elements' ),
			)
		);
		$this->end_controls_section();

		/* ---- CTA ---- */
		$this->start_controls_section(
			'cta',
			array( 'label' => __( 'دکمه فراخوان', 'baspar-elements' ) )
		);
		$this->add_control(
			'cta_text',
			array(
				'label'   => __( 'متن دکمه', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'استعلام قیمت', 'baspar-elements' ),
			)
		);
		$this->add_control(
			'cta_link',
			array(
				'label'   => __( 'لینک دکمه', 'baspar-elements' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => 'https://wa.me/989120997651' ),
			)
		);
		$this->add_control(
			'cta_icon',
			array(
				'label'        => __( 'آیکون واتس‌اپ روی دکمه', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'sticky',
			array(
				'label'        => __( 'چسبان (Sticky)', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->end_controls_section();

		/* ---- Style ---- */
		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'استایل', 'baspar-elements' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_color( 'bg', __( 'پس‌زمینه هدر', 'baspar-elements' ), '.header', 'background' );
		$this->add_color( 'brand_color', __( 'رنگ نام برند', 'baspar-elements' ), '.brand-text strong', 'color' );
		$this->add_typography( 'nav_typo', __( 'تایپوگرافی منو', 'baspar-elements' ), '.nav a' );
		$this->add_color( 'nav_color', __( 'رنگ منو', 'baspar-elements' ), '.nav a', 'color' );
		$this->add_color( 'accent', __( 'رنگ اصلی (هاور/خط)', 'baspar-elements' ), '', '--brand' );
		$this->add_color( 'cta_bg', __( 'پس‌زمینه دکمه', 'baspar-elements' ), '.header-cta', 'background' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$logo_html = '';
		if ( ! empty( $s['logo']['id'] ) ) {
			$logo_html = Group_Control_Image_Size::get_attachment_image_html( $s, 'logo_size', 'logo' );
		} elseif ( ! empty( $s['logo']['url'] ) ) {
			$logo_html = '<img src="' . esc_url( $s['logo']['url'] ) . '" alt="' . esc_attr( $s['brand_title'] ) . '" />';
		}

		$logo_url   = ! empty( $s['logo_link']['url'] ) ? $s['logo_link']['url'] : home_url( '/' );
		$header_cls = 'header' . ( 'yes' === $s['sticky'] ? '' : ' not-sticky' );
		?>
		<div class="baspar-scope">
			<header class="<?php echo esc_attr( $header_cls ); ?>"<?php echo ( 'yes' !== $s['sticky'] ) ? ' style="position:relative"' : ''; ?>>
				<div class="header-inner">
					<a href="<?php echo esc_url( $logo_url ); ?>" class="brand">
						<?php echo $logo_html; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
						<span class="brand-text">
							<strong><?php echo esc_html( $s['brand_title'] ); ?></strong>
							<span><?php echo esc_html( $s['brand_sub'] ); ?></span>
						</span>
					</a>

					<?php
					if ( ! empty( $s['menu_id'] ) && '0' !== $s['menu_id'] ) {
						wp_nav_menu(
							array(
								'menu'        => (int) $s['menu_id'],
								'container'   => false,
								'menu_class'  => 'nav',
								'fallback_cb' => false,
								'depth'       => 2,
							)
						);
					} else {
						echo '<ul class="nav"><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'یک منو از تنظیمات ویجت انتخاب کنید', 'baspar-elements' ) . '</a></li></ul>';
					}
					?>

					<?php if ( ! empty( $s['cta_text'] ) ) : ?>
						<a href="<?php echo esc_url( $s['cta_link']['url'] ?? '#' ); ?>" class="header-cta">
							<?php if ( 'yes' === $s['cta_icon'] ) { echo icon_svg( 'whatsapp', 16 ); /* phpcs:ignore */ } ?>
							<?php echo esc_html( $s['cta_text'] ); ?>
						</a>
					<?php endif; ?>

					<button class="hamburger" aria-label="منو" type="button"><?php echo icon_svg( 'menu', 22 ); /* phpcs:ignore */ ?></button>
				</div>

				<?php
				if ( ! empty( $s['menu_id'] ) && '0' !== $s['menu_id'] ) {
					echo '<div class="nav-mobile">';
					wp_nav_menu(
						array(
							'menu'        => (int) $s['menu_id'],
							'container'   => false,
							'menu_class'  => 'nav-mobile-list',
							'fallback_cb' => false,
							'depth'       => 2,
						)
					);
					echo '</div>';
				}
				?>
			</header>
		</div>
		<?php
	}
}
