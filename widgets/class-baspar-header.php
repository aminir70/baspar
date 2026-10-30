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
use function BasparElements\fa_num;
use function BasparElements\slash_url;
use function BasparElements\tel_link;
use function BasparElements\latin_digits;

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
		$this->add_control(
			'menu_style',
			array(
				'label'       => __( 'نوع منو', 'baspar-elements' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'mega',
				'options'     => array(
					'mega'    => __( 'مگا منو (پیشنهادی)', 'baspar-elements' ),
					'classic' => __( 'کشویی ساده (قبلی)', 'baspar-elements' ),
				),
				'description' => __( 'در مگا منو، آیتم‌هایی که زیرمنوی دو سطحی دارند (مثل «محصولات») به پنل بزرگ با تب دسته‌ها تبدیل می‌شوند؛ بقیه کشویی می‌مانند. کلاس CSS «mega» یا «no-mega» روی آیتم منو این را دستی تعیین می‌کند.', 'baspar-elements' ),
			)
		);
		$this->end_controls_section();

		/* ---- Mega menu ---- */
		$this->start_controls_section(
			'mega',
			array(
				'label'     => __( 'مگا منو', 'baspar-elements' ),
				'condition' => array( 'menu_style' => 'mega' ),
			)
		);
		$this->add_control( 'mm_show_counts', array( 'label' => __( 'تعداد محصولات کنار دسته‌ها', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'mm_products', array( 'label' => __( 'تعداد محصولات جدید زیر هر دسته (۰ = نمایش نده)', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 4, 'min' => 0, 'max' => 8 ) );
		$this->add_control( 'mm_all_text', array( 'label' => __( 'متن لینک «همه»', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده همه' ) );
		$this->add_control( 'promo_heading', array( 'label' => __( 'کارت تبلیغ کنار پنل', 'baspar-elements' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_control( 'promo_show', array( 'label' => __( 'نمایش کارت', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'promo_kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'GET A QUOTE', 'condition' => array( 'promo_show' => 'yes' ) ) );
		$this->add_control( 'promo_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'قیمت روز را از کارشناس بپرسید', 'condition' => array( 'promo_show' => 'yes' ) ) );
		$this->add_control( 'promo_text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => 'نام محصول، گرید و مقدار را بفرستید؛ پیش‌فاکتور با موجودی انبار همان روز می‌رسد.', 'condition' => array( 'promo_show' => 'yes' ) ) );
		$this->add_control( 'promo_btn', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام در واتس‌اپ', 'condition' => array( 'promo_show' => 'yes' ) ) );
		$this->add_control( 'promo_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ), 'condition' => array( 'promo_show' => 'yes' ) ) );
		$this->add_control( 'promo_link2_text', array( 'label' => __( 'لینک دوم', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'خرید مواد اولیه شیمیایی', 'condition' => array( 'promo_show' => 'yes' ) ) );
		$this->add_control( 'promo_link2', array( 'label' => __( 'آدرس لینک دوم', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/buy-chemical-raw-materials/' ), 'condition' => array( 'promo_show' => 'yes' ) ) );
		$this->add_control( 'mobile_heading', array( 'label' => __( 'منوی موبایل', 'baspar-elements' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_control( 'mobile_search', array( 'label' => __( 'جستجوی محصولات', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'search_placeholder', array( 'label' => __( 'متن جستجو', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'جستجوی محصول، مثلاً «رزین اپوکسی»', 'condition' => array( 'mobile_search' => 'yes' ) ) );
		$this->add_control( 'mobile_phone', array( 'label' => __( 'شماره تماس پایین منو', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '09120733965' ) );
		$this->add_control( 'mobile_hours', array( 'label' => __( 'ساعات پاسخگویی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'شنبه تا چهارشنبه ۹ تا ۱۸' ) );
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
		$this->add_color( 'cta_color', __( 'رنگ متن دکمه', 'baspar-elements' ), '.header-cta', 'color' );
		$this->add_typography( 'cta_typo', __( 'تایپوگرافی دکمه', 'baspar-elements' ), '.header-cta' );
		$this->add_padding( 'cta_padding', __( 'فاصله داخلی دکمه', 'baspar-elements' ), '.header-cta' );
		$this->add_radius( 'cta_radius', __( 'گردی گوشه دکمه', 'baspar-elements' ), '.header-cta' );
		$this->end_controls_section();
	}

	/**
	 * The chosen WP menu as a tree (with current-page flags).
	 *
	 * @param int $menu_id Menu term id.
	 * @return array<int,array>
	 */
	private function menu_tree( $menu_id ) {
		$items = wp_get_nav_menu_items( $menu_id, array( 'update_post_term_cache' => false ) );
		if ( ! $items ) {
			return array();
		}
		if ( function_exists( '_wp_menu_item_classes_by_context' ) ) {
			_wp_menu_item_classes_by_context( $items );
		}
		$by = array();
		foreach ( $items as $it ) {
			$classes = array_filter( (array) $it->classes );
			$by[ (int) $it->menu_item_parent ][] = array(
				'id'       => (int) $it->ID,
				'title'    => $it->title,
				'url'      => (string) $it->url,
				'desc'     => trim( (string) $it->description ),
				'object'   => (string) $it->object,
				'oid'      => (int) $it->object_id,
				'target'   => (string) $it->target,
				'classes'  => $classes,
				'current'  => ! empty( $it->current ) || in_array( 'current-menu-item', $classes, true ),
				'ancestor' => ! empty( $it->current_item_ancestor ) || in_array( 'current-menu-ancestor', $classes, true ) || in_array( 'current-menu-parent', $classes, true ),
			);
		}
		$build = static function ( $parent ) use ( &$build, $by ) {
			$out = array();
			foreach ( $by[ $parent ] ?? array() as $n ) {
				$n['children'] = $build( $n['id'] );
				$out[]         = $n;
			}
			return $out;
		};
		return $build( 0 );
	}

	/**
	 * Image + count for a menu node that points to a product category.
	 *
	 * @param array $n Node.
	 * @return array{img:string,count:int}
	 */
	private function cat_meta( $n ) {
		static $cache = array();
		if ( 'product_cat' !== $n['object'] || ! $n['oid'] ) {
			return array( 'img' => '', 'count' => 0 );
		}
		if ( ! isset( $cache[ $n['oid'] ] ) ) {
			$term  = get_term( $n['oid'], 'product_cat' );
			$thumb = (int) get_term_meta( $n['oid'], 'thumbnail_id', true );
			$cache[ $n['oid'] ] = array(
				'img'   => $thumb ? (string) wp_get_attachment_image_url( $thumb, 'thumbnail' ) : '',
				'count' => ( $term && ! is_wp_error( $term ) ) ? (int) $term->count : 0,
			);
		}
		return $cache[ $n['oid'] ];
	}

	/**
	 * Whether a top-level node opens the mega panel.
	 */
	private function is_mega( $n ) {
		if ( in_array( 'no-mega', $n['classes'], true ) ) {
			return false;
		}
		if ( in_array( 'mega', $n['classes'], true ) ) {
			return ! empty( $n['children'] );
		}
		foreach ( $n['children'] as $c ) {
			if ( ! empty( $c['children'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Link attributes (href, target, current) for a node.
	 */
	private function link_attrs( $n ) {
		$out = ' href="' . esc_url( slash_url( $n['url'] ) ) . '"';
		if ( '_blank' === $n['target'] ) {
			$out .= ' target="_blank" rel="noopener"';
		}
		if ( $n['current'] ) {
			$out .= ' aria-current="page"';
		}
		return $out;
	}

	private function is_void_url( $url ) {
		$url = trim( (string) $url );
		return '' === $url || '#' === $url;
	}

	/**
	 * Nested link list (levels 3+ inside a mega pane).
	 */
	private function render_links( $nodes, $depth = 0 ) {
		if ( ! $nodes ) {
			return;
		}
		echo '<ul class="mm-links' . ( $depth ? ' is-sub' : '' ) . '">';
		foreach ( $nodes as $n ) {
			echo '<li><a' . $this->link_attrs( $n ) . '>' . esc_html( $n['title'] ) . '</a>'; // phpcs:ignore
			$this->render_links( $n['children'], $depth + 1 );
			echo '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Small product list for a category pane without sub-categories.
	 */
	private function render_pane_products( $term_id, $count ) {
		if ( $count < 1 || ! $term_id || ! post_type_exists( 'product' ) ) {
			return;
		}
		$key = 'baspar_mm_p_' . (int) $term_id . '_' . (int) $count;
		$ids = get_transient( $key );
		if ( ! is_array( $ids ) ) {
			$ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => $count, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true, 'tax_query' => array( array( 'taxonomy' => 'product_cat', 'terms' => (int) $term_id ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			set_transient( $key, $ids, 6 * HOUR_IN_SECONDS );
		}
		if ( ! $ids ) {
			return;
		}
		echo '<div class="mm-prods-t">' . esc_html__( 'جدیدترین محصولات این دسته', 'baspar-elements' ) . '</div><div class="mm-prods">';
		foreach ( $ids as $id ) {
			$img = get_the_post_thumbnail_url( $id, 'thumbnail' );
			echo '<a class="mm-prod" href="' . esc_url( get_permalink( $id ) ) . '"><span class="mm-prod-img">' . ( $img ? '<img src="' . esc_url( $img ) . '" alt="" loading="lazy" width="56" height="56">' : icon_svg( 'package', 20 ) ) . '</span><span>' . esc_html( get_the_title( $id ) ) . '</span></a>'; // phpcs:ignore
		}
		echo '</div>';
	}

	/**
	 * Desktop menu bar (mega panels + simple dropdowns).
	 */
	private function render_mega_bar( $tree, $s ) {
		echo '<nav class="mm" aria-label="' . esc_attr__( 'منوی اصلی', 'baspar-elements' ) . '"><ul class="mm-bar">';
		foreach ( $tree as $top ) {
			$mega  = $this->is_mega( $top );
			$cls   = 'mm-item' . ( $top['children'] ? ( $mega ? ' has-mega' : ' has-dd' ) : '' ) . ( $top['current'] || $top['ancestor'] ? ' is-current' : '' );
			$label = esc_html( $top['title'] ) . ( $top['children'] ? '<span class="mm-caret" aria-hidden="true">' . icon_svg( 'arrow-down', 14 ) . '</span>' : '' );
			echo '<li class="' . esc_attr( $cls ) . '">';
			if ( $this->is_void_url( $top['url'] ) ) {
				echo '<button type="button" class="mm-top" aria-haspopup="true">' . $label . '</button>'; // phpcs:ignore
			} else {
				echo '<a class="mm-top"' . $this->link_attrs( $top ) . ( $top['children'] ? ' aria-haspopup="true"' : '' ) . '>' . $label . '</a>'; // phpcs:ignore
			}
			if ( $mega ) {
				$this->render_mega_panel( $top, $s );
			} elseif ( $top['children'] ) {
				echo '<div class="mm-dd"><ul>';
				foreach ( $top['children'] as $c ) {
					echo '<li><a' . $this->link_attrs( $c ) . '><span class="mm-dd-ic" aria-hidden="true">' . icon_svg( 'arrow', 14 ) . '</span><span class="mm-dd-tx"><strong>' . esc_html( $c['title'] ) . '</strong>' . ( $c['desc'] ? '<small>' . esc_html( $c['desc'] ) . '</small>' : '' ) . '</span></a></li>'; // phpcs:ignore
				}
				echo '</ul></div>';
			}
			echo '</li>';
		}
		echo '</ul></nav>';
	}

	private function render_mega_panel( $top, $s ) {
		$all   = $s['mm_all_text'] ? $s['mm_all_text'] : __( 'مشاهده همه', 'baspar-elements' );
		$count = 'yes' === ( $s['mm_show_counts'] ?? 'yes' );
		echo '<div class="mm-panel" data-mm-panel><div class="mm-panel-in' . ( 'yes' === ( $s['promo_show'] ?? 'yes' ) ? '' : ' no-promo' ) . '">';
		// Tabs (level 2).
		echo '<div class="mm-tabs">';
		foreach ( $top['children'] as $i => $c ) {
			$m = $this->cat_meta( $c );
			echo '<a class="mm-tab' . ( 0 === $i ? ' is-on' : '' ) . '" data-mm-tab="' . esc_attr( $i ) . '"' . $this->link_attrs( $c ) . '>'; // phpcs:ignore
			echo '<span class="mm-tab-ic">' . ( $m['img'] ? '<img src="' . esc_url( $m['img'] ) . '" alt="" loading="lazy" width="36" height="36">' : icon_svg( 'layers', 18 ) ) . '</span>'; // phpcs:ignore
			echo '<span class="mm-tab-t">' . esc_html( $c['title'] ) . '</span>';
			if ( $count && $m['count'] ) {
				echo '<span class="mm-count">' . esc_html( fa_num( $m['count'] ) ) . '</span>';
			}
			echo '<span class="mm-tab-go" aria-hidden="true">' . icon_svg( 'arrow', 14 ) . '</span></a>'; // phpcs:ignore
		}
		if ( ! $this->is_void_url( $top['url'] ) ) {
			echo '<a class="mm-tabs-all" href="' . esc_url( slash_url( $top['url'] ) ) . '">' . esc_html( $all . ' ' . $top['title'] ) . icon_svg( 'arrow', 14 ) . '</a>'; // phpcs:ignore
		}
		echo '</div>';
		// Panes.
		echo '<div class="mm-panes">';
		foreach ( $top['children'] as $i => $c ) {
			$m = $this->cat_meta( $c );
			echo '<div class="mm-pane' . ( 0 === $i ? ' is-on' : '' ) . '" data-mm-pane="' . esc_attr( $i ) . '">';
			echo '<div class="mm-pane-head"><strong>' . esc_html( $c['title'] ) . '</strong>';
			if ( $count && $m['count'] ) {
				echo '<span>' . esc_html( sprintf( '%s محصول', fa_num( $m['count'] ) ) ) . '</span>';
			}
			if ( ! $this->is_void_url( $c['url'] ) ) {
				echo '<a href="' . esc_url( slash_url( $c['url'] ) ) . '">' . esc_html( $all ) . icon_svg( 'arrow', 14 ) . '</a>'; // phpcs:ignore
			}
			echo '</div>';
			$has_grand = false;
			foreach ( $c['children'] as $g ) {
				if ( $g['children'] ) {
					$has_grand = true;
				}
			}
			if ( $c['children'] && $has_grand ) {
				echo '<div class="mm-cols">';
				foreach ( $c['children'] as $g ) {
					$gm = $this->cat_meta( $g );
					echo '<div class="mm-col">';
					echo '<a class="mm-col-t"' . $this->link_attrs( $g ) . '>'; // phpcs:ignore
					if ( $gm['img'] ) {
						echo '<img src="' . esc_url( $gm['img'] ) . '" alt="" loading="lazy" width="28" height="28">';
					}
					echo '<span>' . esc_html( $g['title'] ) . '</span></a>';
					$this->render_links( $g['children'] );
					echo '</div>';
				}
				echo '</div>';
			} elseif ( $c['children'] ) {
				// Only one level below: cards with image and count.
				echo '<div class="mm-cards">';
				foreach ( $c['children'] as $g ) {
					$gm = $this->cat_meta( $g );
					echo '<a class="mm-card"' . $this->link_attrs( $g ) . '><span class="mm-card-ic">' . ( $gm['img'] ? '<img src="' . esc_url( $gm['img'] ) . '" alt="" loading="lazy" width="44" height="44">' : icon_svg( 'layers', 20 ) ) . '</span><span class="mm-card-t">' . esc_html( $g['title'] ) . ( $count && $gm['count'] ? '<small>' . esc_html( sprintf( '%s محصول', fa_num( $gm['count'] ) ) ) . '</small>' : '' ) . '</span></a>'; // phpcs:ignore
				}
				echo '</div>';
			}
			$this->render_pane_products( 'product_cat' === $c['object'] ? $c['oid'] : 0, (int) ( $s['mm_products'] ?? 4 ) );
			echo '</div>';
		}
		echo '</div>';
		// Promo.
		if ( 'yes' === ( $s['promo_show'] ?? 'yes' ) ) {
			echo '<aside class="mm-promo"><span class="mm-promo-deco" aria-hidden="true"></span>';
			if ( $s['promo_kicker'] ) {
				echo '<span class="mm-promo-k">' . esc_html( $s['promo_kicker'] ) . '</span>';
			}
			if ( $s['promo_title'] ) {
				echo '<strong>' . esc_html( $s['promo_title'] ) . '</strong>';
			}
			if ( $s['promo_text'] ) {
				echo '<p>' . esc_html( $s['promo_text'] ) . '</p>';
			}
			if ( $s['promo_btn'] && ! empty( $s['promo_link']['url'] ) ) {
				echo '<a class="mm-promo-btn" href="' . esc_url( slash_url( $s['promo_link']['url'] ) ) . '" rel="nofollow noopener" target="_blank">' . icon_svg( 'whatsapp', 16 ) . esc_html( $s['promo_btn'] ) . '</a>'; // phpcs:ignore
			}
			if ( $s['promo_link2_text'] && ! empty( $s['promo_link2']['url'] ) ) {
				echo '<a class="mm-promo-link" href="' . esc_url( slash_url( $s['promo_link2']['url'] ) ) . '">' . esc_html( $s['promo_link2_text'] ) . icon_svg( 'arrow', 14 ) . '</a>'; // phpcs:ignore
			}
			echo '</aside>';
		}
		echo '</div></div>';
	}

	/**
	 * Mobile drawer with drill-down views. Moved to <body> by the script so a
	 * sticky/blurred header cannot clip it.
	 */
	private function render_drawer( $tree, $s, $logo_html ) {
		$uid   = 'mmd-' . $this->get_id();
		$views = array();
		$all   = $s['mm_all_text'] ? $s['mm_all_text'] : __( 'مشاهده همه', 'baspar-elements' );
		$walk  = function ( $nodes, $vid, $title, $parent_vid, $parent_node ) use ( &$walk, &$views, $uid ) {
			$views[] = array( 'id' => $vid, 'title' => $title, 'back' => $parent_vid, 'nodes' => $nodes, 'node' => $parent_node );
			foreach ( $nodes as $n ) {
				if ( $n['children'] ) {
					$walk( $n['children'], $uid . '-v' . $n['id'], $n['title'], $vid, $n );
				}
			}
		};
		$walk( $tree, $uid . '-root', '', '', null );
		?>
		<div class="baspar-scope mmd-root" id="<?php echo esc_attr( $uid ); ?>" data-mmd aria-hidden="true">
			<div class="mmd-ov" data-mmd-close></div>
			<div class="mmd" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'منو', 'baspar-elements' ); ?>">
				<div class="mmd-head">
					<a class="mmd-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo $logo_html; // phpcs:ignore ?><span><?php echo esc_html( $s['brand_title'] ); ?></span></a>
					<button type="button" class="mmd-x" data-mmd-close aria-label="<?php esc_attr_e( 'بستن منو', 'baspar-elements' ); ?>"><?php echo icon_svg( 'x', 20 ); // phpcs:ignore ?></button>
				</div>
				<?php if ( 'yes' === ( $s['mobile_search'] ?? 'yes' ) ) : ?>
					<form class="mmd-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input type="hidden" name="post_type" value="product">
						<span aria-hidden="true"><?php echo icon_svg( 'search', 18 ); // phpcs:ignore ?></span>
						<input type="search" name="s" placeholder="<?php echo esc_attr( $s['search_placeholder'] ); ?>" aria-label="<?php echo esc_attr( $s['search_placeholder'] ); ?>">
					</form>
				<?php endif; ?>
				<div class="mmd-views">
					<?php foreach ( $views as $k => $v ) :
						$is_root = 0 === $k;
						?>
						<div class="mmd-view<?php echo $is_root ? ' is-on' : ''; ?>" id="<?php echo esc_attr( $v['id'] ); ?>" data-mmd-view<?php echo $is_root ? '' : ' hidden'; ?>>
							<?php if ( ! $is_root ) : ?>
								<button type="button" class="mmd-back" data-mmd-back="<?php echo esc_attr( $v['back'] ); ?>"><?php echo icon_svg( 'arrow-right', 18 ); // phpcs:ignore ?><span><?php esc_html_e( 'بازگشت', 'baspar-elements' ); ?></span></button>
								<div class="mmd-vt"><?php echo esc_html( $v['title'] ); ?></div>
								<?php if ( $v['node'] && ! $this->is_void_url( $v['node']['url'] ) ) : ?>
									<a class="mmd-all" href="<?php echo esc_url( slash_url( $v['node']['url'] ) ); ?>"><?php echo esc_html( $all . ' ' . $v['title'] ); ?><?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?></a>
								<?php endif; ?>
							<?php endif; ?>
							<?php $tiles = ! $is_root && $v['node'] && in_array( $v['node']['id'], wp_list_pluck( $tree, 'id' ), true ) && $this->is_mega( $v['node'] ); ?>
							<ul class="mmd-list<?php echo $tiles ? ' is-tiles' : ''; ?>">
								<?php foreach ( $v['nodes'] as $n ) :
									$m = $this->cat_meta( $n );
									?>
									<li class="<?php echo $n['current'] || $n['ancestor'] ? 'is-current' : ''; ?>">
										<?php if ( $n['children'] ) : ?>
											<button type="button" class="mmd-item" data-mmd-go="<?php echo esc_attr( $uid . '-v' . $n['id'] ); ?>">
												<?php if ( $m['img'] ) : ?><img src="<?php echo esc_url( $m['img'] ); ?>" alt="" loading="lazy" width="40" height="40"><?php endif; ?>
												<span><?php echo esc_html( $n['title'] ); ?></span>
												<?php if ( $m['count'] && 'yes' === ( $s['mm_show_counts'] ?? 'yes' ) ) : ?><em><?php echo esc_html( fa_num( $m['count'] ) ); ?></em><?php endif; ?>
												<i aria-hidden="true"><?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?></i>
											</button>
										<?php else : ?>
											<a class="mmd-item"<?php echo $this->link_attrs( $n ); // phpcs:ignore ?>>
												<?php if ( $m['img'] ) : ?><img src="<?php echo esc_url( $m['img'] ); ?>" alt="" loading="lazy" width="40" height="40"><?php endif; ?>
												<span><?php echo esc_html( $n['title'] ); ?></span>
												<?php if ( $m['count'] && 'yes' === ( $s['mm_show_counts'] ?? 'yes' ) ) : ?><em><?php echo esc_html( fa_num( $m['count'] ) ); ?></em><?php endif; ?>
											</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="mmd-foot">
					<?php if ( ! empty( $s['cta_text'] ) && ! empty( $s['cta_link']['url'] ) ) : ?>
						<a class="mmd-wa" href="<?php echo esc_url( $s['cta_link']['url'] ); ?>" target="_blank" rel="noopener nofollow"><?php echo icon_svg( 'whatsapp', 18 ); // phpcs:ignore ?><?php echo esc_html( $s['cta_text'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $s['mobile_phone'] ) ) : ?>
						<a class="mmd-tel" href="<?php echo esc_url( tel_link( $s['mobile_phone'] ) ); ?>"><?php echo icon_svg( 'phone', 18 ); // phpcs:ignore ?><span dir="ltr"><?php echo esc_html( fa_num( latin_digits( $s['mobile_phone'] ) ) ); ?></span></a>
					<?php endif; ?>
					<?php if ( ! empty( $s['mobile_hours'] ) ) : ?><p><?php echo icon_svg( 'clock', 14 ); // phpcs:ignore ?><?php echo esc_html( $s['mobile_hours'] ); ?></p><?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$logo_html = '';
		if ( ! empty( $s['logo']['id'] ) ) {
			$logo_html = Group_Control_Image_Size::get_attachment_image_html( $s, 'logo_size', 'logo' );
		} elseif ( ! empty( $s['logo']['url'] ) ) {
			$logo_html = '<img src="' . esc_url( $s['logo']['url'] ) . '" alt="' . esc_attr( $s['brand_title'] ) . '" width="48" height="48" />';
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
					$has_menu = ! empty( $s['menu_id'] ) && '0' !== $s['menu_id'];
					$is_mega  = $has_menu && 'classic' !== ( $s['menu_style'] ?? 'mega' );
					$tree     = $is_mega ? $this->menu_tree( (int) $s['menu_id'] ) : array();
					if ( $is_mega && $tree ) {
						$this->render_mega_bar( $tree, $s );
					} elseif ( $has_menu ) {
						wp_nav_menu(
							array(
								'menu'        => (int) $s['menu_id'],
								'container'   => false,
								'menu_class'  => 'nav',
								'fallback_cb' => false,
								'depth'       => 0,
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

					<button class="hamburger"<?php echo ! empty( $is_mega ) && $tree ? ' data-mmd-open="mmd-' . esc_attr( $this->get_id() ) . '"' : ''; ?> aria-label="<?php esc_attr_e( 'باز/بستن منو', 'baspar-elements' ); ?>" aria-expanded="false" type="button">
						<span class="i-menu"><?php echo icon_svg( 'menu', 22 ); /* phpcs:ignore */ ?></span>
						<span class="i-close"><?php echo icon_svg( 'x', 22 ); /* phpcs:ignore */ ?></span>
					</button>
				</div>

				<?php
				if ( $has_menu && ! ( $is_mega && $tree ) ) {
					echo '<div class="nav-mobile">';
					wp_nav_menu(
						array(
							'menu'        => (int) $s['menu_id'],
							'container'   => false,
							'menu_class'  => 'nav-mobile-list',
							'fallback_cb' => false,
							'depth'       => 0,
						)
					);
					echo '</div>';
				}
				?>
			</header>
			<?php
			if ( $is_mega && $tree ) {
				$this->render_drawer( $tree, $s, $logo_html );
			}
			?>
		</div>
		<?php
	}
}
