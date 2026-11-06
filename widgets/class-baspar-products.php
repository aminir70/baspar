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
use function BasparElements\product_card_html;
use function BasparElements\more_query_args;
use function BasparElements\more_token;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Products
 */
class Baspar_Products extends Baspar_Widget_Base {

	/**
	 * Args of the last product query (for the "load more" token).
	 *
	 * @var array
	 */
	private $last_args = array();

	/**
	 * Paging mode: '' (off), 'more' or 'numbers'.
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	private function pager( $s ) {
		if ( 'yes' !== ( $s['enable_pagination'] ?? '' ) ) {
			return '';
		}
		return ( isset( $s['pager'] ) && 'numbers' === $s['pager'] ) ? 'numbers' : 'more';
	}

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
				'description'  => __( 'فعال کنید تا بقیه محصولات هم در دسترس باشند (دکمه «مشاهده بیشتر» یا شماره صفحه).', 'baspar-elements' ),
			)
		);
		$this->add_control(
			'pager',
			array(
				'label'       => __( 'نوع صفحه‌بندی', 'baspar-elements' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'more',
				'options'     => array(
					'more'    => __( 'دکمه «مشاهده بیشتر» (یک صفحه برای هر دسته)', 'baspar-elements' ),
					'numbers' => __( 'شماره صفحه', 'baspar-elements' ),
				),
				'description' => __( 'با دکمه «مشاهده بیشتر» هر دسته فقط یک آدرس دارد و محصولات بعدی بدون رفتن به صفحه دیگر اضافه می‌شوند (بهتر برای سئو).', 'baspar-elements' ),
				'condition'   => array( 'enable_pagination' => 'yes' ),
			)
		);
		$this->add_control(
			'more_text',
			array(
				'label'     => __( 'متن دکمه', 'baspar-elements' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'مشاهده محصولات بیشتر',
				'condition' => array( 'enable_pagination' => 'yes', 'pager' => 'more' ),
			)
		);
		$this->add_control(
			'more_step',
			array(
				'label'       => __( 'تعداد محصول با هر کلیک', 'baspar-elements' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 48,
				'description' => __( 'خالی = همان «تعداد در هر صفحه».', 'baspar-elements' ),
				'condition'   => array( 'enable_pagination' => 'yes', 'pager' => 'more' ),
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
		$per_page = max( 1, (int) $s['count'] );
		$paged_qv = 1;
		if ( 'numbers' === $this->pager( $s ) ) {
			$paged_qv = ( get_query_var( 'paged' ) ) ? (int) get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? (int) get_query_var( 'page' ) : 1 );
			// ?bspr_page= also works where /page/N/ is redirected (product archives).
			if ( isset( $_GET['bspr_page'] ) ) { // phpcs:ignore
				$paged_qv = max( 1, (int) $_GET['bspr_page'] ); // phpcs:ignore
			}
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

			$args['baspar_stable'] = true;
			if ( isset( $args['orderby'] ) && 'rand' === $args['orderby'] ) {
				$args['orderby'] = 'RAND(' . wp_rand( 1, 99999 ) . ')'; // Same random order for «مشاهده بیشتر».
			}
			$this->last_args = $args;
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

		$args['baspar_stable'] = true;
		if ( isset( $args['orderby'] ) && 'rand' === $args['orderby'] ) {
			$args['orderby'] = 'RAND(' . wp_rand( 1, 99999 ) . ')'; // Same random order for «مشاهده بیشتر».
		}
		$this->last_args = $args;
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
				$i    = 0;
				$card = array(
					'show_cat' => 'yes' === $s['show_cat'],
					'foot'     => $s['foot_text'],
					'class'    => $this->reveal_class( $s ),
				);
				while ( $q->have_posts() ) :
					$q->the_post();
					echo product_card_html( get_the_ID(), $card + array( 'delay' => $i * 40 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in product_card_html().
					++$i;
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php
			if ( 'more' === $this->pager( $s ) && $q->found_posts > $q->post_count ) {
				$this->render_more( $s, $q );
			} elseif ( 'numbers' === $this->pager( $s ) && $q->max_num_pages > 1 ) {
				$current = max( 1, (int) ( isset( $_GET['bspr_page'] ) ? $_GET['bspr_page'] : ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ) ) ); // phpcs:ignore
				$big     = 999999999;
				// Product archives live on one URL (/page/N/ redirects), so
				// number them with ?bspr_page=N there.
				$archive = \BasparElements\single_page_archives() && \BasparElements\is_product_archive();
				$links   = paginate_links(
					array(
						'base'      => $archive ? add_query_arg( 'bspr_page', '%#%', remove_query_arg( 'bspr_page', get_pagenum_link( 1, false ) ) ) : str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format'    => $archive ? '' : '?paged=%#%',
						'current'   => $current,
						'total'     => $q->max_num_pages,
						'type'      => 'array',
						'prev_text' => icon_svg( 'arrow-right', 14 ),
						'next_text' => icon_svg( 'arrow', 14 ),
						'mid_size'  => 1,
						'end_size'  => 1,
					)
				);
				if ( $links ) {
					echo '<nav class="pagination" aria-label="' . esc_attr__( 'صفحه‌بندی محصولات', 'baspar-elements' ) . '">';
					foreach ( $links as $link ) {
						// Convert WP's <a> / <span class="current"> into our styled items.
						// (Match the class names, not the bare word "current": the arrow
						// SVGs contain stroke="currentColor".)
						$is_current = (bool) preg_match( '#class=["\'][^"\']*\bcurrent\b#', $link );
						$is_dots    = (bool) preg_match( '#class=["\'][^"\']*\bdots\b#', $link );
						if ( $is_dots ) {
							echo '<span class="ellipsis">…</span>';
							continue;
						}
						if ( preg_match( '#^<(?:a|span)\b[^>]*>(.*)</(?:a|span)>$#s', trim( $link ), $inner ) ) {
							$label = $inner[1];
						} else {
							continue;
						}
						$is_arrow = false !== strpos( $label, '<svg' );
						$label    = $is_arrow ? $label : esc_html( wp_strip_all_tags( $label ) );
						if ( $is_current ) {
							echo '<span class="is-on" aria-current="page">' . $label . '</span>'; // phpcs:ignore
						} elseif ( preg_match( '#href=[\'"]([^\'"]+)[\'"]#', $link, $h ) ) {
							$rel = '';
							if ( false !== strpos( $link, 'next' ) ) {
								$rel = ' rel="next" aria-label="' . esc_attr__( 'صفحه بعد', 'baspar-elements' ) . '"';
							} elseif ( false !== strpos( $link, 'prev' ) ) {
								$rel = ' rel="prev" aria-label="' . esc_attr__( 'صفحه قبل', 'baspar-elements' ) . '"';
							}
							echo '<a href="' . esc_url( html_entity_decode( $h[1] ) ) . '"' . $rel . '>' . $label . '</a>'; // phpcs:ignore
						}
					}
					echo '</nav>';
				}
			}
			?>
		</div>
		<?php
	}

	/**
	 * «مشاهده محصولات بیشتر» button; the script loads the next batch from
	 * /wp-json/baspar/v1/products and appends it to the grid.
	 *
	 * @param array     $s Settings.
	 * @param \WP_Query $q First-page query.
	 */
	private function render_more( $s, $q ) {
		$total = (int) $q->found_posts;
		$shown = (int) $q->post_count;
		$step  = (int) ( $s['more_step'] ?? 0 );
		$step  = $step > 0 ? min( 48, $step ) : max( 1, (int) $s['count'] );
		$data  = array(
			'data-bspr-more' => more_token( more_query_args( $this->last_args ) ),
			'data-url'       => rest_url( 'baspar/v1/products' ),
			'data-total'     => $total,
			'data-loaded'    => $shown,
			'data-step'      => $step,
			'data-cat'       => 'yes' === $s['show_cat'] ? 1 : 0,
			'data-foot'      => $s['foot_text'],
		);
		$attr  = '';
		foreach ( $data as $k => $v ) {
			$attr .= ' ' . $k . '="' . esc_attr( (string) $v ) . '"';
		}
		$text = '' !== trim( (string) ( $s['more_text'] ?? '' ) ) ? $s['more_text'] : __( 'مشاهده محصولات بیشتر', 'baspar-elements' );
		?>
		<div class="bspr-more"<?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>>
			<p class="bspr-more-count" aria-live="polite"><?php
				/* translators: 1: shown, 2: total */
				echo wp_kses_post( sprintf( __( 'نمایش <b>%1$s</b> از <b>%2$s</b> محصول', 'baspar-elements' ), \BasparElements\fa_num( number_format_i18n( $shown ) ), \BasparElements\fa_num( number_format_i18n( $total ) ) ) );
			?></p>
			<span class="bspr-more-bar" aria-hidden="true"><i style="width:<?php echo esc_attr( (string) round( 100 * $shown / max( 1, $total ), 1 ) ); ?>%"></i></span>
			<button type="button" class="bspr-more-btn">
				<span class="bspr-more-t"><?php echo esc_html( $text ); ?></span>
				<?php echo icon_svg( 'arrow-down', 16 ); // phpcs:ignore ?>
			</button>
		</div>
		<?php
	}
}
