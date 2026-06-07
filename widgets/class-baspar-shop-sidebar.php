<?php
/**
 * Shop sidebar — 4 boxes:
 *   - Categories (WooCommerce product_cat) as real links (or filter checkboxes)
 *   - Origin countries as taxonomy/attribute links
 *   - Quick access manual links
 *   - Purple CTA box (fully styleable: bg, title color, desc color)
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Shop_Sidebar extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-shop-sidebar'; }
	public function get_title() { return __( 'بسپار — سایدبار فروشگاه', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-filter'; }

	/**
	 * Recursively render a term branch (link mode).
	 *
	 * @param \WP_Term         $term       Term to render.
	 * @param array<int,\WP_Term[]> $by_parent  Map: parent_id => list of direct children (pre-grouped).
	 * @param int              $current_id Active term id.
	 * @param bool             $auto_open  Open all branches by default.
	 * @param int              $depth      Current depth (0 = top level).
	 */
	private function render_link_branch( $term, $by_parent, $current_id, $auto_open, $depth = 0 ) {
		$kids       = isset( $by_parent[ $term->term_id ] ) ? $by_parent[ $term->term_id ] : array();
		$has_kids   = ! empty( $kids );
		$is_current = ( $current_id === (int) $term->term_id );
		$has_active = $is_current ? true : $this->branch_contains( $term->term_id, $current_id, $by_parent );
		$open       = $auto_open || $has_active;
		$cls        = 'bspr-cat-node' . ( $has_kids ? ' has-children' : '' ) . ( $open ? ' is-open' : '' ) . ( $is_current ? ' is-current' : '' );
		$prefix     = ( $depth > 0 ) ? str_repeat( '· ', $depth ) : '';
		?>
		<li class="<?php echo esc_attr( $cls ); ?>" data-depth="<?php echo esc_attr( $depth ); ?>">
			<div class="bspr-cat-row">
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="bspr-cat-link"><span><?php echo esc_html( $prefix . $term->name ); ?></span><span class="count"><?php echo esc_html( $term->count ); ?></span></a>
				<?php if ( $has_kids ) : ?>
					<button type="button" class="bspr-cat-toggle" aria-label="<?php esc_attr_e( 'باز/بسته کردن زیر دسته‌ها', 'baspar-elements' ); ?>"><?php echo icon_svg( 'arrow-down', 14 ); // phpcs:ignore ?></button>
				<?php endif; ?>
			</div>
			<?php if ( $has_kids ) : ?>
				<ul class="bspr-cat-children">
					<?php foreach ( $kids as $child ) {
						$this->render_link_branch( $child, $by_parent, $current_id, $auto_open, $depth + 1 );
					} ?>
				</ul>
			<?php endif; ?>
		</li>
		<?php
	}

	/**
	 * Recursively render a term branch (checkbox filter mode).
	 */
	private function render_filter_branch( $term, $by_parent, $depth = 0 ) {
		$kids     = isset( $by_parent[ $term->term_id ] ) ? $by_parent[ $term->term_id ] : array();
		$has_kids = ! empty( $kids );
		$prefix   = ( $depth > 0 ) ? str_repeat( '· ', $depth ) : '';
		?>
		<li class="bspr-cat-node<?php echo $has_kids ? ' has-children is-open' : ''; ?>" data-depth="<?php echo esc_attr( $depth ); ?>">
			<div class="bspr-cat-row">
				<label class="bspr-cat-link"><input type="checkbox" name="product_cat[]" value="<?php echo esc_attr( $term->slug ); ?>"> <span><?php echo esc_html( $prefix . $term->name ); ?></span><span class="count"><?php echo esc_html( $term->count ); ?></span></label>
				<?php if ( $has_kids ) : ?>
					<button type="button" class="bspr-cat-toggle"><?php echo icon_svg( 'arrow-down', 14 ); // phpcs:ignore ?></button>
				<?php endif; ?>
			</div>
			<?php if ( $has_kids ) : ?>
				<ul class="bspr-cat-children">
					<?php foreach ( $kids as $child ) {
						$this->render_filter_branch( $child, $by_parent, $depth + 1 );
					} ?>
				</ul>
			<?php endif; ?>
		</li>
		<?php
	}

	/**
	 * Recursive check: does any descendant of $parent_id equal $target_id?
	 */
	private function branch_contains( $parent_id, $target_id, $by_parent ) {
		if ( ! isset( $by_parent[ $parent_id ] ) ) { return false; }
		foreach ( $by_parent[ $parent_id ] as $child ) {
			if ( (int) $child->term_id === (int) $target_id ) { return true; }
			if ( $this->branch_contains( $child->term_id, $target_id, $by_parent ) ) { return true; }
		}
		return false;
	}

	/**
	 * Build the URL of the Woo shop/category archive with a query param applied.
	 *
	 * @param string $key Query key (e.g. orderby, origin).
	 * @param string $val Query value.
	 * @return string
	 */
	private function filter_url( $key, $val ) {
		$base = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
		if ( ! $base ) {
			$base = home_url( '/' );
		}
		return add_query_arg( $key, rawurlencode( $val ), $base );
	}

	protected function register_controls() {
		// Categories
		$this->start_controls_section( 'cats', array( 'label' => __( 'دسته‌بندی‌ها', 'baspar-elements' ) ) );
		$this->add_control( 'show_cats', array( 'label' => __( 'نمایش', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'cat_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'دسته‌بندی' ) );
		$this->add_control(
			'cat_mode',
			array(
				'label'   => __( 'حالت', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'link',
				'options' => array(
					'link'   => __( 'لینک به آرشیو دسته', 'baspar-elements' ),
					'filter' => __( 'فیلتر چک‌باکس (ارسال به فروشگاه)', 'baspar-elements' ),
				),
			)
		);
		$this->add_control(
			'cat_hierarchy',
			array(
				'label'        => __( 'نمایش زیر دسته‌ها (سلسله‌مراتبی)', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'اول دسته‌های اصلی، زیرشان زیرمجموعه‌ها با امکان باز/بسته شدن.', 'baspar-elements' ),
			)
		);
		$this->add_control(
			'cat_children_open',
			array(
				'label'        => __( 'همه شاخه‌ها از ابتدا باز باشند', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array( 'cat_hierarchy' => 'yes' ),
			)
		);
		$this->add_control(
			'cat_orderby',
			array(
				'label'   => __( 'ترتیب', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'menu_order',
				'options' => array(
					'menu_order' => __( 'ترتیب پیش‌فرض ووکامرس', 'baspar-elements' ),
					'name'       => __( 'الفبا (نام)', 'baspar-elements' ),
					'name_desc'  => __( 'الفبا — برعکس', 'baspar-elements' ),
					'count'      => __( 'تعداد محصول — بیشتر اول', 'baspar-elements' ),
					'count_asc'  => __( 'تعداد محصول — کمتر اول', 'baspar-elements' ),
					'id'         => __( 'شناسه (قدیمی‌ترین اول)', 'baspar-elements' ),
					'id_desc'    => __( 'شناسه (جدیدترین اول)', 'baspar-elements' ),
					'manual'     => __( 'دستی (با ID زیر تعیین کنید)', 'baspar-elements' ),
				),
			)
		);
		$this->add_control(
			'cat_manual_order',
			array(
				'label'       => __( 'ترتیب دستی (ID دسته‌های اصلی با کاما)', 'baspar-elements' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '17, 24, 8, 12',
				'description' => __( 'فقط ID دسته‌های اصلی را به ترتیب دلخواه با کاما وارد کنید. ID هر دسته را در پیشخوان وردپرس → دسته‌بندی محصولات می‌بینید.', 'baspar-elements' ),
				'condition'   => array( 'cat_orderby' => 'manual' ),
			)
		);
		$this->add_control( 'cat_count', array( 'label' => __( 'حداکثر تعداد دسته‌های اصلی', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 20 ) );
		$this->end_controls_section();

		// Origin
		$this->start_controls_section( 'origin', array( 'label' => __( 'کشور مبدأ', 'baspar-elements' ) ) );
		$this->add_control( 'show_origin', array( 'label' => __( 'نمایش', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'origin_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'کشور مبدأ' ) );
		$or = new Repeater();
		$or->add_control( 'name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$or->add_control( 'count', array( 'label' => __( 'تعداد', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$or->add_control( 'link', array( 'label' => __( 'لینک (خالی = ?origin=نام)', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'origins', array(
			'label' => __( 'کشورها', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $or->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => 'چین', 'count' => '۵۲' ),
				array( 'name' => 'آلمان', 'count' => '۲۸' ),
				array( 'name' => 'ترکیه', 'count' => '۱۹' ),
				array( 'name' => 'هند', 'count' => '۸' ),
			),
		) );
		$this->end_controls_section();

		// Quick links
		$this->start_controls_section( 'quick', array( 'label' => __( 'دسترسی سریع', 'baspar-elements' ) ) );
		$this->add_control( 'show_quick', array( 'label' => __( 'نمایش', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'quick_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'دسترسی سریع' ) );
		$qr = new Repeater();
		$qr->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$qr->add_control( 'link', array( 'label' => __( 'لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'quicks', array(
			'label' => __( 'موارد', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $qr->get_controls(),
			'title_field' => '{{{ text }}}',
			'default' => array(
				array( 'text' => 'پرفروش‌ترین', 'link' => array( 'url' => '?orderby=popularity' ) ),
				array( 'text' => 'محصولات ویژه', 'link' => array( 'url' => '?featured=1' ) ),
				array( 'text' => 'موجود در انبار', 'link' => array( 'url' => '?instock=1' ) ),
				array( 'text' => 'گریدهای جدید', 'link' => array( 'url' => '?orderby=date' ) ),
			),
		) );
		$this->end_controls_section();

		// CTA
		$this->start_controls_section( 'cta', array( 'label' => __( 'باکس CTA', 'baspar-elements' ) ) );
		$this->add_control( 'show_cta', array( 'label' => __( 'نمایش', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'cta_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'نیاز به مشاوره دارید؟' ) );
		$this->add_control( 'cta_desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => 'کارشناس فنی ما در واتس‌اپ پاسخگوست.' ) );
		$this->add_control( 'cta_btn', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام در واتس‌اپ' ) );
		$this->add_control( 'cta_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'https://wa.me/989120997651' ) ) );
		$this->end_controls_section();

		// Style
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );

		$this->add_control( '_cta_heading', array( 'label' => __( 'باکس CTA', 'baspar-elements' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_background( 'cta_bg', '.bspr-cta-box' );
		$this->add_color( 'cta_title_color', __( 'رنگ عنوان CTA', 'baspar-elements' ), '.bspr-cta-box strong', 'color' );
		$this->add_color( 'cta_desc_color', __( 'رنگ توضیح CTA', 'baspar-elements' ), '.bspr-cta-box p', 'color' );
		$this->add_color( 'cta_btn_bg', __( 'پس‌زمینه دکمه CTA', 'baspar-elements' ), '.bspr-cta-box .btn', 'background' );
		$this->add_color( 'cta_btn_color', __( 'رنگ متن دکمه CTA', 'baspar-elements' ), '.bspr-cta-box .btn', 'color' );
		$this->end_controls_section();
	}

	/**
	 * Sort a flat list of terms according to the chosen orderby key.
	 */
	private function sort_terms( $terms, $orderby, $manual_ids = '' ) {
		if ( empty( $terms ) ) { return $terms; }
		switch ( $orderby ) {
			case 'name':
				usort( $terms, function ( $a, $b ) { return strnatcasecmp( $a->name, $b->name ); } );
				break;
			case 'name_desc':
				usort( $terms, function ( $a, $b ) { return strnatcasecmp( $b->name, $a->name ); } );
				break;
			case 'count':
				usort( $terms, function ( $a, $b ) { return (int) $b->count - (int) $a->count; } );
				break;
			case 'count_asc':
				usort( $terms, function ( $a, $b ) { return (int) $a->count - (int) $b->count; } );
				break;
			case 'id':
				usort( $terms, function ( $a, $b ) { return (int) $a->term_id - (int) $b->term_id; } );
				break;
			case 'id_desc':
				usort( $terms, function ( $a, $b ) { return (int) $b->term_id - (int) $a->term_id; } );
				break;
			case 'manual':
				$ids = array_filter( array_map( 'intval', array_map( 'trim', explode( ',', (string) $manual_ids ) ) ) );
				if ( empty( $ids ) ) { break; }
				$ordered = array();
				$rest    = $terms;
				foreach ( $ids as $id ) {
					foreach ( $rest as $i => $t ) {
						if ( (int) $t->term_id === $id ) {
							$ordered[] = $t;
							unset( $rest[ $i ] );
							break;
						}
					}
				}
				$terms = array_merge( $ordered, array_values( $rest ) );
				break;
			case 'menu_order':
			default:
				usort( $terms, function ( $a, $b ) {
					$am = get_term_meta( $a->term_id, 'order', true );
					$bm = get_term_meta( $b->term_id, 'order', true );
					if ( $am === $bm ) { return strnatcasecmp( $a->name, $b->name ); }
					return (int) $am - (int) $bm;
				} );
		}
		return $terms;
	}

	protected function render() {
		$s          = $this->get_settings_for_display();
		$is_filter  = ( 'filter' === $s['cat_mode'] );
		$is_hier    = ( 'yes' === $s['cat_hierarchy'] );
		$auto_open  = ( 'yes' === $s['cat_children_open'] );
		$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

		// Build map: parent_id => sorted children — covers ANY depth of nesting.
		$by_parent = array();
		$top_level = array();
		if ( taxonomy_exists( 'product_cat' ) && 'yes' === $s['show_cats'] ) {
			$all = get_terms( array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 0,
			) );
			if ( ! is_wp_error( $all ) && $all ) {
				foreach ( $all as $t ) {
					$pid = (int) $t->parent;
					if ( ! isset( $by_parent[ $pid ] ) ) { $by_parent[ $pid ] = array(); }
					$by_parent[ $pid ][] = $t;
				}
				// Sort each level using the chosen orderby.
				foreach ( $by_parent as $pid => $list ) {
					$by_parent[ $pid ] = $this->sort_terms( $list, $s['cat_orderby'], $s['cat_manual_order'] );
				}
				$top_level = isset( $by_parent[0] ) ? $by_parent[0] : array();
				$top_level = array_slice( $top_level, 0, max( 1, (int) $s['cat_count'] ) );
			}
		}

		// Active term (for highlight + branch auto-open).
		$current_id = 0;
		$obj = get_queried_object();
		if ( $obj && isset( $obj->term_id ) && isset( $obj->taxonomy ) && 'product_cat' === $obj->taxonomy ) {
			$current_id = (int) $obj->term_id;
		}

		$uid = 'bspr-sidebar-' . $this->get_id();
		?>
		<div class="baspar-scope">
			<aside class="cat-sidebar" id="<?php echo esc_attr( $uid ); ?>">
				<?php if ( 'yes' === $s['show_cats'] && ! empty( $top_level ) ) : ?>
					<div class="cat-side-box">
						<h4><?php echo icon_svg( 'layers', 16 ); // phpcs:ignore ?> <?php echo esc_html( $s['cat_title'] ); ?></h4>
						<?php if ( $is_filter ) : ?>
							<form action="<?php echo esc_url( $shop_url ); ?>" method="get">
								<ul class="cat-side-list bspr-cat-tree">
									<?php
									if ( $is_hier ) {
										foreach ( $top_level as $t ) { $this->render_filter_branch( $t, $by_parent, 0 ); }
									} else {
										foreach ( $top_level as $t ) {
											echo '<li><label><input type="checkbox" name="product_cat[]" value="' . esc_attr( $t->slug ) . '"> ' . esc_html( $t->name ) . '</label><span class="count">' . esc_html( $t->count ) . '</span></li>';
										}
									}
									?>
								</ul>
								<button type="submit" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;margin-top:10px"><?php echo esc_html__( 'اعمال فیلتر', 'baspar-elements' ); ?></button>
							</form>
						<?php else : ?>
							<ul class="cat-side-list bspr-cat-tree">
								<?php
								if ( $is_hier ) {
									foreach ( $top_level as $t ) { $this->render_link_branch( $t, $by_parent, $current_id, $auto_open, 0 ); }
								} else {
									foreach ( $top_level as $t ) {
										$is_current = ( $current_id === (int) $t->term_id ) ? ' is-current' : '';
										echo '<li class="bspr-cat-leaf' . esc_attr( $is_current ) . '"><a href="' . esc_url( get_term_link( $t ) ) . '" style="display:flex;align-items:center;justify-content:space-between;width:100%;color:var(--ink-2)"><span>' . esc_html( $t->name ) . '</span><span class="count">' . esc_html( $t->count ) . '</span></a></li>';
									}
								}
								?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_origin'] ) : ?>
					<div class="cat-side-box">
						<h4><?php echo icon_svg( 'globe', 16 ); // phpcs:ignore ?> <?php echo esc_html( $s['origin_title'] ); ?></h4>
						<ul class="cat-side-list">
							<?php foreach ( (array) $s['origins'] as $o ) :
								$url = ! empty( $o['link']['url'] ) ? $o['link']['url'] : $this->filter_url( 'origin', $o['name'] );
								?>
								<li><a href="<?php echo esc_url( $url ); ?>" style="display:flex;align-items:center;justify-content:space-between;width:100%;color:var(--ink-2)"><span><?php echo esc_html( $o['name'] ); ?></span><span class="count"><?php echo esc_html( $o['count'] ); ?></span></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_quick'] ) : ?>
					<div class="cat-side-box">
						<h4><?php echo icon_svg( 'bolt', 16 ); // phpcs:ignore ?> <?php echo esc_html( $s['quick_title'] ); ?></h4>
						<ul class="cat-side-list">
							<?php foreach ( (array) $s['quicks'] as $q ) :
								$url = ! empty( $q['link']['url'] ) ? $q['link']['url'] : '#';
								// allow relative `?orderby=...` defaults by appending to shop URL
								if ( '?' === substr( $url, 0, 1 ) ) {
									$url = rtrim( $shop_url, '/' ) . '/' . $url;
								}
								?>
								<li><a href="<?php echo esc_url( $url ); ?>" style="color:var(--ink-2)"><?php echo esc_html( $q['text'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_cta'] ) : ?>
					<div class="cat-side-box bspr-cta-box" style="background:linear-gradient(135deg,var(--brand) 0%,var(--brand-dk) 100%);color:#fff;border:0">
						<strong style="display:block;font-size:15px;color:#fff;margin-bottom:8px"><?php echo esc_html( $s['cta_title'] ); ?></strong>
						<p style="font-size:12.5px;color:#E5D4FF;line-height:1.7;margin:0 0 14px"><?php echo esc_html( $s['cta_desc'] ); ?></p>
						<a href="<?php echo esc_url( $s['cta_link']['url'] ?? '#' ); ?>" class="btn btn-white btn-sm" style="width:100%;justify-content:center"><?php echo icon_svg( 'whatsapp', 14 ); // phpcs:ignore ?><?php echo esc_html( $s['cta_btn'] ); ?></a>
					</div>
				<?php endif; ?>
			</aside>
		</div>

		<style>
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-tree,
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-children{list-style:none;padding:0;margin:0}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-node{display:block;padding:0;border-bottom:0;margin-bottom:2px}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-row{display:flex;align-items:center;gap:6px;width:100%}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-link{display:flex;align-items:center;justify-content:space-between;flex:1;padding:7px 4px;border-radius:6px;color:var(--ink-2);font-size:13.5px;font-weight:500;text-decoration:none;min-width:0}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-link:hover{background:var(--brand-tint);color:var(--brand)}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-node.is-current > .bspr-cat-row > .bspr-cat-link{color:var(--brand);font-weight:700}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-tree > .bspr-cat-node > .bspr-cat-row > .bspr-cat-link > span:first-child{font-weight:700;color:var(--brand-deep)}
		/* Toggle button — explicit svg sizing to defeat themes that set svg{width:100%} */
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-toggle{width:28px;height:28px;background:transparent;border:1px solid var(--line);border-radius:5px;color:var(--ink-3);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .2s,background .15s,color .15s;flex-shrink:0;padding:0;line-height:0}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-toggle svg{width:14px!important;height:14px!important;max-width:14px;max-height:14px;display:block;flex-shrink:0}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-toggle:hover{background:var(--brand-50);color:var(--brand);border-color:var(--brand-100)}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-node.is-open > .bspr-cat-row > .bspr-cat-toggle{transform:rotate(180deg);background:var(--brand-50);color:var(--brand)}
		/* Children indent + dashed guide line */
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-children{padding:4px 18px 6px 0;border-right:1px dashed var(--line);margin-right:12px;display:none}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-node.is-open > .bspr-cat-children{display:block}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-children .bspr-cat-link{font-size:13px;font-weight:400;padding:5px 8px}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-children .bspr-cat-link > span:first-child{color:var(--ink-2);font-weight:500}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .bspr-cat-children .bspr-cat-node.is-current > .bspr-cat-row > .bspr-cat-link > span:first-child{color:var(--brand);font-weight:700}
		.baspar-scope #<?php echo esc_js( $uid ); ?> .count{font-family:var(--mono);font-size:11px;color:var(--ink-3);flex-shrink:0;margin-right:6px}
		</style>

		<script>
		(function(){
			var root = document.getElementById('<?php echo esc_js( $uid ); ?>');
			if (!root) return;
			root.querySelectorAll('.bspr-cat-toggle').forEach(function(btn){
				btn.addEventListener('click', function(e){
					e.preventDefault();
					var node = btn.closest('.bspr-cat-node');
					if (node) node.classList.toggle('is-open');
				});
			});
		})();
		</script>
		<?php
	}
}
