<?php
/**
 * Dynamic WooCommerce products grid (used for "most viewed" / "featured" /
 * "latest"). Reads products live from WooCommerce; falls back to a notice in
 * the editor when Woo is missing.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Products
 */
class Baspar_Products extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-products';
	}

	public function get_title() {
		return __( 'بسپار — محصولات (ووکامرس)', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	/**
	 * Product categories for the select control.
	 *
	 * @return array<string,string>
	 */
	private function cat_options() {
		$opts = array( '0' => __( 'همه دسته‌ها', 'baspar-elements' ) );
		if ( taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					$opts[ $t->term_id ] = $t->name;
				}
			}
		}
		return $opts;
	}

	protected function register_controls() {
		$this->start_controls_section( 'query', array( 'label' => __( 'منبع محصولات', 'baspar-elements' ) ) );
		$this->add_control(
			'query_source',
			array(
				'label'       => __( 'منبع کوئری', 'baspar-elements' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'custom',
				'options'     => array(
					'custom'  => __( 'کوئری دلخواه (با تنظیمات این ویجت)', 'baspar-elements' ),
					'current' => __( 'کوئری اصلی صفحه فعلی (پویا)', 'baspar-elements' ),
				),
				'description' => __( 'اگر «کوئری اصلی صفحه فعلی» انتخاب شود، در آرشیو هر دسته، محصولات آن دسته خوانده می‌شود (مثل ووکامرس استاندارد).', 'baspar-elements' ),
			)
		);
		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'مرتب‌سازی', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'popularity',
				'options' => array(
					'popularity' => __( 'پربازدید/پرفروش', 'baspar-elements' ),
					'date'       => __( 'جدیدترین', 'baspar-elements' ),
					'featured'   => __( 'محصولات ویژه', 'baspar-elements' ),
					'rand'       => __( 'تصادفی', 'baspar-elements' ),
					'title'      => __( 'الفبا', 'baspar-elements' ),
				),
				'condition' => array( 'query_source' => 'custom' ),
			)
		);
		$this->add_control(
			'category',
			array(
				'label'   => __( 'دسته‌بندی', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->cat_options(),
				'default' => '0',
				'condition' => array( 'query_source' => 'custom' ),
			)
		);
		$this->add_control( 'count', array( 'label' => __( 'تعداد در هر صفحه', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 6 ) );
		$this->add_control(
			'columns',
			array(
				'label'   => __( 'ستون‌ها', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '4',
				'options' => array( '3' => '3', '4' => '4', '5' => '5' ),
			)
		);
		$this->add_control( 'foot_text', array( 'label' => __( 'متن پاورقی کارت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام قیمت' ) );
		$this->add_control( 'show_cat', array( 'label' => __( 'نمایش دسته روی کارت', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control(
			'enable_pagination',
			array(
				'label'        => __( 'صفحه‌بندی', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => __( 'فعال کنید تا لینک‌های صفحه‌بندی زیر گرید نشان داده شوند.', 'baspar-elements' ),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'name_typo', __( 'تایپوگرافی نام محصول', 'baspar-elements' ), '.pcard-name' );
		$this->add_radius( 'radius', __( 'گردی گوشه کارت', 'baspar-elements' ), '.pcard' );
		$this->end_controls_section();
	}

	/**
	 * Apply WooCommerce-style orderby keys to a WP_Query args array.
	 *
	 * @param array  $args    Existing query args.
	 * @param string $orderby Orderby key (date|popularity|featured|rand|title|price|price-desc|rating|menu_order).
	 * @return array
	 */
	private function apply_orderby( $args, $orderby ) {
		switch ( $orderby ) {
			case 'date':
				$args['orderby'] = 'date'; $args['order'] = 'DESC'; break;
			case 'popularity':
				$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value_num'; $args['order'] = 'DESC'; break;
			case 'featured':
				$args['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'featured',
				);
				break;
			case 'rand':
				$args['orderby'] = 'rand'; break;
			case 'title':
				$args['orderby'] = 'title'; $args['order'] = 'ASC'; break;
			case 'price':
				$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value_num'; $args['order'] = 'ASC'; break;
			case 'price-desc':
				$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value_num'; $args['order'] = 'DESC'; break;
			case 'rating':
				$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value_num'; $args['order'] = 'DESC'; break;
			case 'menu_order':
			default:
				$args['orderby'] = 'menu_order'; $args['order'] = 'ASC';
		}
		return $args;
	}

	/**
	 * Run the product query.
	 *
	 * When `query_source = current`, we inherit the args from the main query so
	 * the widget shows products of the *current* term archive / search / etc.
	 *
	 * @param array $s Settings.
	 * @return \WP_Query|null
	 */
	private function query( $s ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return null;
		}
		$per_page  = max( 1, (int) $s['count'] );
		$paged_qv  = ( get_query_var( 'paged' ) ) ? (int) get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? (int) get_query_var( 'page' ) : 1 );
		// Allow ?bspr_page= on pages where the main query's paged is unused.
		if ( isset( $_GET['bspr_page'] ) ) { // phpcs:ignore
			$paged_qv = max( 1, (int) $_GET['bspr_page'] ); // phpcs:ignore
		}

		// ---------- Current query mode: inherit from the page's main query ----------
		if ( 'current' === $s['query_source'] ) {
			global $wp_query;
			$args = array();
			if ( $wp_query && isset( $wp_query->query_vars ) ) {
				$args = $wp_query->query_vars;
			}
			// Force product post type + per-page + paged.
			$args['post_type']      = 'product';
			$args['posts_per_page'] = $per_page;
			$args['post_status']    = 'publish';
			$args['paged']          = $paged_qv;

			// If on a product_cat / product_tag archive, build a clean tax_query
			// so other meta on $wp_query (like the actual page id) doesn't leak.
			$obj = get_queried_object();
			if ( $obj && isset( $obj->term_id ) && isset( $obj->taxonomy ) && in_array( $obj->taxonomy, array( 'product_cat', 'product_tag' ), true ) ) {
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy'         => $obj->taxonomy,
						'field'            => 'term_id',
						'terms'            => (int) $obj->term_id,
						'include_children' => true,
					),
				);
				// Keep search and orderby if present in the request.
			}

			// Search query inheritance.
			if ( ! empty( $_GET['s'] ) ) { // phpcs:ignore
				$args['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) ); // phpcs:ignore
			}

			// Order from URL (Woo standard `?orderby=...`).
			if ( ! empty( $_GET['orderby'] ) ) { // phpcs:ignore
				$args = $this->apply_orderby( $args, sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ); // phpcs:ignore
			}

			return new \WP_Query( $args );
		}

		// ---------- Custom mode (existing behaviour) ----------
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $per_page,
			'post_status'    => 'publish',
			'paged'          => $paged_qv,
			'meta_query'     => array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		);

		switch ( $s['orderby'] ) {
			case 'date':
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				break;
			case 'popularity':
				$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				break;
			case 'featured':
				$args['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'featured',
				);
				break;
			case 'rand':
				$args['orderby'] = 'rand';
				break;
			case 'title':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
		}

		if ( ! empty( $s['category'] ) && '0' !== $s['category'] ) {
			$args['tax_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => (int) $s['category'],
			);
		}

		return new \WP_Query( $args );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$q = $this->query( $s );

		if ( null === $q ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'ووکامرس فعال نیست. این ویجت محصولات را از ووکامرس می‌خواند.', 'baspar-elements' ) . '</div>';
			}
			return;
		}
		if ( ! $q->have_posts() ) {
			echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'محصولی یافت نشد.', 'baspar-elements' ) . '</div>';
			return;
		}
		?>
		<div class="baspar-scope">
			<div class="bs-grid cols-<?php echo esc_attr( $s['columns'] ); ?>">
				<?php
				$i = 0;
				while ( $q->have_posts() ) :
					$q->the_post();
					$product = wc_get_product( get_the_ID() );
					if ( ! $product ) {
						continue;
					}
					$cats     = wc_get_product_category_list( get_the_ID() );
					$cat_name = '';
					$terms    = get_the_terms( get_the_ID(), 'product_cat' );
					if ( $terms && ! is_wp_error( $terms ) ) {
						$cat_name = strtoupper( $terms[0]->slug );
					}
					$sku   = $product->get_sku();
					$thumb = get_the_post_thumbnail( get_the_ID(), 'medium', array( 'loading' => 'lazy' ) );
					?>
					<a href="<?php the_permalink(); ?>" class="pcard <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 40 ); ?>">
						<div class="pcard-img <?php echo $thumb ? 'has-image' : ''; ?>">
							<?php if ( $sku ) : ?><span class="pcard-tag"><?php echo esc_html( $sku ); ?></span><?php endif; ?>
							<?php
							if ( $thumb ) {
								echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								echo icon_svg( 'package', 40 ); // phpcs:ignore
							}
							?>
						</div>
						<div class="pcard-body">
							<?php if ( 'yes' === $s['show_cat'] && $cat_name ) : ?><span class="pcard-cat"><?php echo esc_html( $cat_name ); ?></span><?php endif; ?>
							<div class="pcard-name"><?php the_title(); ?></div>
						</div>
						<div class="pcard-foot">
							<span><?php echo esc_html( $s['foot_text'] ); ?></span>
							<?php echo icon_svg( 'arrow', 14 ); // phpcs:ignore ?>
						</div>
					</a>
					<?php
					++$i;
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php
			if ( 'yes' === $s['enable_pagination'] && $q->max_num_pages > 1 ) {
				$current = max( 1, (int) ( isset( $_GET['bspr_page'] ) ? $_GET['bspr_page'] : ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ) ) ); // phpcs:ignore
				$big     = 999999999;
				$links   = paginate_links(
					array(
						'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format'    => '?paged=%#%',
						'current'   => $current,
						'total'     => $q->max_num_pages,
						'type'      => 'array',
						'prev_text' => icon_svg( 'arrow-down', 14 ),
						'next_text' => icon_svg( 'arrow', 14 ),
						'mid_size'  => 1,
						'end_size'  => 1,
					)
				);
				if ( $links ) {
					echo '<div class="pagination" style="margin-top:32px">';
					foreach ( $links as $link ) {
						// convert WP's <a> / <span class="current"> into our styled buttons.
						$is_current = false !== strpos( $link, 'current' );
						$is_dots    = false !== strpos( $link, 'dots' );
						if ( $is_dots ) {
							echo '<span class="ellipsis">…</span>';
							continue;
						}
						// strip outer tag and reuse text/href
						if ( $is_current ) {
							if ( preg_match( '#>(.+?)<#s', $link, $m ) ) {
								echo '<button class="is-on" type="button">' . wp_kses_post( $m[1] ) . '</button>';
							}
						} else {
							if ( preg_match( '#href=[\'\"]([^\'\"]+)[\'\"][^>]*>(.+?)</a>#s', $link, $m ) ) {
								echo '<a href="' . esc_url( $m[1] ) . '" style="display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:8px;background:#fff;border:1px solid var(--line);font-size:13.5px;font-weight:600;color:var(--ink-2);text-decoration:none">' . wp_kses_post( $m[2] ) . '</a>';
							}
						}
					}
					echo '</div>';
				}
			}
			?>
		</div>
		<?php
	}
}
