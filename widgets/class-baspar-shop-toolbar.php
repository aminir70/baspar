<?php
/**
 * Shop toolbar — count, working search field, working sort select, and a
 * grid/list view toggle that flips the target container's class.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Shop_Toolbar extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-shop-toolbar'; }
	public function get_title() { return __( 'بسپار — toolbar فروشگاه', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-toolbar'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control(
			'count_mode',
			array(
				'label'   => __( 'نمایش تعداد', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'   => __( 'خودکار از WooCommerce', 'baspar-elements' ),
					'custom' => __( 'متن دلخواه', 'baspar-elements' ),
					'off'    => __( 'مخفی', 'baspar-elements' ),
				),
			)
		);
		$this->add_control( 'count_text', array(
			'label' => __( 'متن دلخواه', 'baspar-elements' ),
			'type' => Controls_Manager::TEXT,
			'default' => 'نمایش <strong>۱۲</strong> محصول از <strong>۱۰۰+</strong>',
			'condition' => array( 'count_mode' => 'custom' ),
		) );
		$this->add_control( 'show_search', array( 'label' => __( 'جستجو', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'search_placeholder', array( 'label' => __( 'متن placeholder جستجو', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'جستجو در محصولات...' ) );
		$this->add_control( 'show_sort', array( 'label' => __( 'مرتب‌سازی', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_view', array( 'label' => __( 'تغییر نما (گرید/لیست)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control(
			'view_target',
			array(
				'label'       => __( 'انتخابگر CSS کانتینر محصولات', 'baspar-elements' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '.products, .bs-grid',
				'description' => __( 'وقتی روی آیکون لیست بزنی، کلاس "is-list" به این انتخابگر افزوده می‌شود.', 'baspar-elements' ),
				'condition'   => array( 'show_view' => 'yes' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Auto count from WooCommerce when available.
	 */
	private function auto_count() {
		global $wp_query;
		if ( ! function_exists( 'wc_get_loop_prop' ) ) {
			return '';
		}
		$total    = wc_get_loop_prop( 'total', 0 );
		$per_page = wc_get_loop_prop( 'per_page', 12 );
		$current  = wc_get_loop_prop( 'current_page', 1 );
		if ( ! $total ) {
			$total = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
		}
		if ( ! $total ) {
			return '';
		}
		$from = ( $current - 1 ) * $per_page + 1;
		$to   = min( $from + $per_page - 1, $total );
		return sprintf(
			/* translators: 1: from, 2: to, 3: total */
			__( 'نمایش <strong>%1$s–%2$s</strong> از <strong>%3$s</strong> محصول', 'baspar-elements' ),
			number_format_i18n( $from ),
			number_format_i18n( $to ),
			number_format_i18n( $total )
		);
	}

	protected function render() {
		$s        = $this->get_settings_for_display();
		$uid      = 'bspr-tb-' . $this->get_id();
		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

		// Build count text.
		$count_html = '';
		if ( 'auto' === $s['count_mode'] ) {
			$count_html = $this->auto_count();
		} elseif ( 'custom' === $s['count_mode'] ) {
			$count_html = wp_kses_post( $s['count_text'] );
		}

		// Current values for sort / search to preselect.
		$current_orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore
		$current_s       = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore
		?>
		<div class="baspar-scope">
			<div class="cat-toolbar" id="<?php echo esc_attr( $uid ); ?>">
				<?php if ( $count_html ) : ?>
					<div class="cat-count"><?php echo wp_kses_post( $count_html ); ?></div>
				<?php else : ?>
					<div></div>
				<?php endif; ?>

				<div class="cat-tools">
					<?php if ( 'yes' === $s['show_search'] ) : ?>
						<form style="position:relative" action="<?php echo esc_url( $shop_url ); ?>" method="get" role="search">
							<input type="hidden" name="post_type" value="product">
							<input type="search" name="s" value="<?php echo esc_attr( $current_s ); ?>" placeholder="<?php echo esc_attr( $s['search_placeholder'] ); ?>" style="padding:8px 12px 8px 32px;border:1px solid var(--line-2);border-radius:8px;font-family:inherit;font-size:13px;width:200px" />
							<span style="position:absolute;left:8px;top:50%;transform:translateY(-50%);color:var(--ink-3);pointer-events:none"><?php echo icon_svg( 'search', 14 ); // phpcs:ignore ?></span>
						</form>
					<?php endif; ?>

					<?php if ( 'yes' === $s['show_sort'] ) : ?>
						<form method="get" class="bspr-sort-form" style="display:contents">
							<?php
							// Preserve existing query parameters except orderby and paged.
							foreach ( $_GET as $k => $v ) : // phpcs:ignore
								if ( in_array( $k, array( 'orderby', 'paged', 'product-page' ), true ) ) { continue; }
								if ( is_array( $v ) ) {
									foreach ( $v as $vv ) {
										echo '<input type="hidden" name="' . esc_attr( $k ) . '[]" value="' . esc_attr( wp_unslash( $vv ) ) . '">';
									}
								} else {
									echo '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( sanitize_text_field( wp_unslash( $v ) ) ) . '">';
								}
							endforeach;
							?>
							<select class="cat-sort" name="orderby" onchange="this.form.submit()">
								<option value="menu_order" <?php selected( $current_orderby, 'menu_order' ); ?>><?php esc_html_e( 'پیش‌فرض', 'baspar-elements' ); ?></option>
								<option value="date" <?php selected( $current_orderby, 'date' ); ?>><?php esc_html_e( 'جدیدترین', 'baspar-elements' ); ?></option>
								<option value="popularity" <?php selected( $current_orderby, 'popularity' ); ?>><?php esc_html_e( 'پرفروش‌ترین', 'baspar-elements' ); ?></option>
								<option value="title" <?php selected( $current_orderby, 'title' ); ?>><?php esc_html_e( 'الفبا', 'baspar-elements' ); ?></option>
								<option value="price" <?php selected( $current_orderby, 'price' ); ?>><?php esc_html_e( 'قیمت: کم به زیاد', 'baspar-elements' ); ?></option>
								<option value="price-desc" <?php selected( $current_orderby, 'price-desc' ); ?>><?php esc_html_e( 'قیمت: زیاد به کم', 'baspar-elements' ); ?></option>
								<option value="rating" <?php selected( $current_orderby, 'rating' ); ?>><?php esc_html_e( 'بیشترین امتیاز', 'baspar-elements' ); ?></option>
							</select>
						</form>
					<?php endif; ?>

					<?php if ( 'yes' === $s['show_view'] ) : ?>
						<div class="cat-view" data-view-toggle data-target="<?php echo esc_attr( $s['view_target'] ); ?>">
							<button type="button" class="is-on" data-mode="grid" aria-label="grid"><?php echo icon_svg( 'grid', 16 ); // phpcs:ignore ?></button>
							<button type="button" data-mode="list" aria-label="list"><?php echo icon_svg( 'list', 16 ); // phpcs:ignore ?></button>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( 'yes' === $s['show_view'] ) : ?>
			<script>
			(function(){
				var root = document.getElementById('<?php echo esc_js( $uid ); ?>');
				if (!root) return;
				var toggle = root.querySelector('[data-view-toggle]');
				if (!toggle) return;
				var sel = toggle.getAttribute('data-target') || '.products';
				var stored = null;
				try { stored = window.localStorage.getItem('bspr_shop_view'); } catch(e){}
				function apply(mode){
					var targets = document.querySelectorAll(sel);
					targets.forEach(function(t){
						t.classList.toggle('is-list', mode === 'list');
					});
					toggle.querySelectorAll('button').forEach(function(b){
						b.classList.toggle('is-on', b.getAttribute('data-mode') === mode);
					});
					try { window.localStorage.setItem('bspr_shop_view', mode); } catch(e){}
				}
				if (stored === 'list' || stored === 'grid') apply(stored);
				toggle.querySelectorAll('button').forEach(function(b){
					b.addEventListener('click', function(){ apply(b.getAttribute('data-mode')); });
				});
			})();
			</script>
			<?php endif; ?>
		</div>
		<?php
	}
}
