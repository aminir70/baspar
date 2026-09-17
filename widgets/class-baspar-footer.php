<?php
/**
 * Big 4-column footer. Link columns can pull from a WP menu or be entered
 * manually via repeater. Contact rows + socials are editable.
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
 * Class Baspar_Footer
 */
class Baspar_Footer extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-footer';
	}

	public function get_title() {
		return __( 'بسپار — فوتر', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-footer';
	}

	private function get_menus() {
		$menus = array( '0' => __( '— دستی (ریپیتر) —', 'baspar-elements' ) );
		foreach ( wp_get_nav_menus() as $menu ) {
			$menus[ $menu->term_id ] = $menu->name;
		}
		return $menus;
	}

	protected function register_controls() {
		/* ---- Brand column ---- */
		$this->start_controls_section( 'brand', array( 'label' => __( 'ستون برند', 'baspar-elements' ) ) );
		$this->add_control(
			'logo',
			array(
				'label'   => __( 'لوگو', 'baspar-elements' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => BASPAR_ELEMENTS_ASSETS . 'images/logo.png' ),
			)
		);
		$this->add_control( 'brand_title', array( 'label' => __( 'نام برند', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'بسپارمارکت' ) );
		$this->add_control( 'brand_sub', array( 'label' => __( 'زیرعنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'BASPARMARKET.COM' ) );
		$this->add_control(
			'about',
			array(
				'label'   => __( 'متن معرفی', 'baspar-elements' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => 'شرکت بازرگانی کیهان بسپار نیک اندیشان با نام تجاری بسپارمارکت، در زمینه واردات، تأمین و توزیع مواد اولیه صنایع پلیمری، شیمیایی، رنگ، رزین، شوینده و غذایی فعالیت می‌کند.',
			)
		);
		$this->add_control( 'wa', array( 'label' => __( 'واتس‌اپ', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$this->add_control( 'tg', array( 'label' => __( 'تلگرام', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://t.me/basparmarket' ) ) );
		$this->add_control( 'ig', array( 'label' => __( 'اینستاگرام', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://instagram.com/basparmarket' ) ) );
		$this->end_controls_section();

		/* ---- Two link columns ---- */
		foreach ( array( 1, 2 ) as $n ) {
			$this->start_controls_section(
				'links_' . $n,
				array( 'label' => sprintf( __( 'ستون لینک %d', 'baspar-elements' ), $n ) )
			);
			$this->add_control( 'links_title_' . $n, array( 'label' => __( 'عنوان ستون', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 1 === $n ? 'لینک‌های مهم' : 'پیلار پیج‌ها' ) );
			$this->add_control(
				'links_menu_' . $n,
				array(
					'label'   => __( 'منبع لینک‌ها', 'baspar-elements' ),
					'type'    => Controls_Manager::SELECT,
					'options' => $this->get_menus(),
					'default' => '0',
				)
			);
			$rep = new Repeater();
			$rep->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
			$rep->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
			$this->add_control(
				'links_items_' . $n,
				array(
					'label'       => __( 'آیتم‌های دستی', 'baspar-elements' ),
					'type'        => Controls_Manager::REPEATER,
					'fields'      => $rep->get_controls(),
					'title_field' => '{{{ text }}}',
					'condition'   => array( 'links_menu_' . $n => '0' ),
					'default'     => 1 === $n
						? array(
							array( 'text' => 'صفحه اصلی', 'link' => array( 'url' => '#' ) ),
							array( 'text' => 'فروشگاه', 'link' => array( 'url' => '#' ) ),
							array( 'text' => 'درباره ما', 'link' => array( 'url' => '#' ) ),
							array( 'text' => 'تماس با ما', 'link' => array( 'url' => '#' ) ),
							array( 'text' => 'وبلاگ', 'link' => array( 'url' => '#' ) ),
						)
						: array(
							array( 'text' => 'خرید مواد اولیه شیمیایی', 'link' => array( 'url' => '#' ) ),
							array( 'text' => 'واردکننده مواد اولیه شیمیایی', 'link' => array( 'url' => '#' ) ),
							array( 'text' => 'دسته‌بندی محصولات', 'link' => array( 'url' => '#' ) ),
						),
				)
			);
			$this->end_controls_section();
		}

		/* ---- Contact column ---- */
		$this->start_controls_section( 'contact', array( 'label' => __( 'ستون تماس', 'baspar-elements' ) ) );
		$this->add_control( 'contact_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'اطلاعات تماس' ) );
		$rep2 = new Repeater();
		$rep2->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'phone' ) );
		$rep2->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep2->add_control( 'ltr', array( 'label' => __( 'چپ‌چین (LTR)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->add_control(
			'contact_items',
			array(
				'label'       => __( 'ردیف‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep2->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array(
					array( 'icon' => 'phone', 'text' => '۰۲۱-۲۸۴۲۶۰۷۵ · ۰۷۱-۳۸۳۸۵۶۸۶', 'ltr' => 'yes' ),
					array( 'icon' => 'mail', 'text' => 'info@basparmarket.com', 'ltr' => 'yes' ),
					array( 'icon' => 'pin', 'text' => 'شیراز، بلوار امیرکبیر، برج صنعت', 'ltr' => '' ),
					array( 'icon' => 'clock', 'text' => 'شنبه تا چهارشنبه ۸ تا ۱۷', 'ltr' => '' ),
				),
			)
		);
		$this->end_controls_section();

		/* ---- Bottom bar ---- */
		$this->start_controls_section( 'bottom', array( 'label' => __( 'نوار پایین', 'baspar-elements' ) ) );
		$this->add_control( 'copyright', array( 'label' => __( 'کپی‌رایت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '© ۲۰۲۶ · BASPAR MARKET · ALL RIGHTS RESERVED' ) );
		$this->add_control( 'tagline', array( 'label' => __( 'شعار', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'طراحی با ❤ برای صنعت ایران' ) );
		$this->end_controls_section();

		/* ---- Style ---- */
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'bg', __( 'پس‌زمینه', 'baspar-elements' ), '', 'background' );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_color( 'head_color', __( 'رنگ عناوین ستون', 'baspar-elements' ), '.footer-col h5', 'color' );
		$this->add_typography( 'typo', __( 'تایپوگرافی', 'baspar-elements' ), '.footer-col li a' );
		$this->end_controls_section();
	}

	/**
	 * Render a link column either from a WP menu or repeater.
	 *
	 * @param array  $s Settings.
	 * @param int    $n Column number.
	 */
	private function render_links_col( $s, $n ) {
		echo '<div class="footer-col"><h5>' . esc_html( $s[ 'links_title_' . $n ] ) . '</h5>';
		$menu_id = $s[ 'links_menu_' . $n ];
		if ( ! empty( $menu_id ) && '0' !== $menu_id ) {
			wp_nav_menu(
				array(
					'menu'        => (int) $menu_id,
					'container'   => false,
					'menu_class'  => '',
					'fallback_cb' => false,
					'depth'       => 1,
				)
			);
		} else {
			echo '<ul>';
			foreach ( (array) $s[ 'links_items_' . $n ] as $it ) {
				$url = ! empty( $it['link']['url'] ) ? $it['link']['url'] : '#';
				echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $it['text'] ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</div>';
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope bspr-footer footer-big">
			<div class="footer-inner">
				<div class="footer-grid">
					<div class="footer-col footer-brand-col">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-brand">
							<?php if ( ! empty( $s['logo']['url'] ) ) : ?>
								<img src="<?php echo esc_url( $s['logo']['url'] ); ?>" alt="<?php echo esc_attr( $s['brand_title'] ); ?>" />
							<?php endif; ?>
							<span>
								<strong><?php echo esc_html( $s['brand_title'] ); ?></strong>
								<span class="mono"><?php echo esc_html( $s['brand_sub'] ); ?></span>
							</span>
						</a>
						<div class="rich-text"><?php echo wp_kses_post( $s['about'] ); ?></div>
						<div class="footer-socials">
							<?php if ( ! empty( $s['wa']['url'] ) ) : ?><a href="<?php echo esc_url( $s['wa']['url'] ); ?>" aria-label="واتس‌اپ"><?php echo icon_svg( 'whatsapp', 18 ); /* phpcs:ignore */ ?></a><?php endif; ?>
							<?php if ( ! empty( $s['tg']['url'] ) ) : ?><a href="<?php echo esc_url( $s['tg']['url'] ); ?>" aria-label="تلگرام"><?php echo icon_svg( 'telegram', 18 ); /* phpcs:ignore */ ?></a><?php endif; ?>
							<?php if ( ! empty( $s['ig']['url'] ) ) : ?><a href="<?php echo esc_url( $s['ig']['url'] ); ?>" aria-label="اینستاگرام"><?php echo icon_svg( 'instagram', 18 ); /* phpcs:ignore */ ?></a><?php endif; ?>
						</div>
					</div>
					<?php
					$this->render_links_col( $s, 1 );
					$this->render_links_col( $s, 2 );
					?>
					<div class="footer-col">
						<h5><?php echo esc_html( $s['contact_title'] ); ?></h5>
						<ul class="footer-contact">
							<?php foreach ( (array) $s['contact_items'] as $it ) : ?>
								<li><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'info', 16 ); /* phpcs:ignore */ ?><span class="<?php echo ( 'yes' === $it['ltr'] ) ? 'ltr' : ''; ?>"><?php echo esc_html( $it['text'] ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
			<div class="footer-bottom">
				<div class="footer-inner">
					<span class="mono"><?php echo esc_html( $s['copyright'] ); ?></span>
					<span><?php echo esc_html( $s['tagline'] ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}
}
