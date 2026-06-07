<?php
/**
 * Term description — dynamic styled box that prints the description of:
 *   - the current queried term (taxonomy archive), or
 *   - a term resolved from ?product_cat=/?cat=/?category= query params, or
 *   - the current post / shop / archive title as a fallback, or
 *   - a manually picked term, or
 *   - custom text.
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
 * Class Baspar_Term_Description
 */
class Baspar_Term_Description extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-term-description';
	}

	public function get_title() {
		return __( 'بسپار — توضیحات دسته (پویا)', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-archive-posts';
	}

	private function term_options() {
		$opts  = array( '0' => __( '— انتخاب کنید —', 'baspar-elements' ) );
		$taxes = array( 'category', 'product_cat', 'post_tag', 'product_tag' );
		foreach ( $taxes as $tax ) {
			if ( ! taxonomy_exists( $tax ) ) {
				continue;
			}
			$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'number' => 200 ) );
			if ( is_wp_error( $terms ) ) {
				continue;
			}
			foreach ( $terms as $t ) {
				$key          = $tax . ':' . $t->term_id;
				$opts[ $key ] = $t->name . ' (' . $tax . ')';
			}
		}
		return $opts;
	}

	protected function register_controls() {
		/* ---- Content ---- */
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'منبع', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'   => __( 'خودکار از صفحه فعلی', 'baspar-elements' ),
					'manual' => __( 'انتخاب دستی یک دسته', 'baspar-elements' ),
					'custom' => __( 'متن دلخواه', 'baspar-elements' ),
				),
			)
		);
		$this->add_control(
			'manual_term',
			array(
				'label'     => __( 'دسته', 'baspar-elements' ),
				'type'      => Controls_Manager::SELECT2,
				'options'   => $this->term_options(),
				'default'   => '0',
				'condition' => array( 'source' => 'manual' ),
			)
		);
		$this->add_control(
			'show_title',
			array(
				'label'        => __( 'نمایش عنوان دسته', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'title_prefix',
			array(
				'label'     => __( 'پیشوند عنوان', 'baspar-elements' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'درباره دسته',
				'condition' => array( 'show_title' => 'yes' ),
			)
		);
		$this->add_control(
			'icon',
			array(
				'label'   => __( 'آیکون (اختیاری)', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'info',
				'options' => \BasparElements\icon_options(),
			)
		);
		$this->add_control(
			'custom_title',
			array(
				'label'     => __( 'عنوان دلخواه', 'baspar-elements' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'custom_text',
			array(
				'label'     => __( 'متن دلخواه (HTML مجاز)', 'baspar-elements' ),
				'type'      => Controls_Manager::WYSIWYG,
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'fallback_text',
			array(
				'label'       => __( 'متن پیش‌فرض (اگر توضیح دسته خالی بود)', 'baspar-elements' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => 'این دسته به‌زودی با توضیحات کامل به‌روزرسانی می‌شود. در صورت نیاز به مشاوره، با کارشناس ما در واتس‌اپ تماس بگیرید.',
				'description' => __( 'اگر دسته توضیح ندارد، این متن نمایش داده می‌شود.', 'baspar-elements' ),
				'condition'   => array( 'source!' => 'custom' ),
			)
		);
		$this->add_control(
			'hide_when_empty',
			array(
				'label'        => __( 'مخفی‌سازی کامل وقتی هیچ محتوایی نیست', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => __( 'پیش‌فرض خاموش — حتی وقتی توضیح خالی است، عنوان نمایش داده می‌شود.', 'baspar-elements' ),
				'condition'    => array( 'source!' => 'custom' ),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		/* ---- Style ---- */
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );

		$this->add_control( '_box_heading', array( 'label' => __( 'باکس', 'baspar-elements' ), 'type' => Controls_Manager::HEADING ) );
		$this->add_background( 'box_bg', '.bspr-termdesc' );
		$this->add_border( 'box_border', '.bspr-termdesc' );
		$this->add_radius( 'box_radius', __( 'گردی گوشه', 'baspar-elements' ), '.bspr-termdesc' );
		$this->add_padding( 'box_padding', __( 'فاصله داخلی', 'baspar-elements' ), '.bspr-termdesc' );
		$this->add_shadow( 'box_shadow', '.bspr-termdesc' );

		$this->add_control( '_title_heading', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.bspr-termdesc h3' );
		$this->add_color( 'title_color', __( 'رنگ عنوان', 'baspar-elements' ), '.bspr-termdesc h3', 'color' );

		$this->add_control( '_text_heading', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_typography( 'text_typo', __( 'تایپوگرافی متن', 'baspar-elements' ), '.bspr-termdesc .desc' );
		$this->add_color( 'text_color', __( 'رنگ متن', 'baspar-elements' ), '.bspr-termdesc .desc', 'color' );

		$this->add_control( '_icon_heading', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_color( 'icon_color', __( 'رنگ آیکون', 'baspar-elements' ), '.bspr-termdesc .ic', 'color' );
		$this->add_color( 'icon_bg', __( 'پس‌زمینه آیکون', 'baspar-elements' ), '.bspr-termdesc .ic', 'background' );

		$this->end_controls_section();
	}

	/**
	 * Robust term/context resolver.
	 *
	 * Returns ['title' => ..., 'desc' => ..., 'has_term' => bool].
	 */
	private function resolve( $s ) {
		$out = array( 'title' => '', 'desc' => '', 'has_term' => false );

		// 1) Custom text mode — use as-is.
		if ( 'custom' === $s['source'] ) {
			$out['title'] = (string) $s['custom_title'];
			$out['desc']  = (string) $s['custom_text'];
			return $out;
		}

		$term = null;

		// 2) Manual term picker.
		if ( 'manual' === $s['source'] && ! empty( $s['manual_term'] ) && '0' !== $s['manual_term'] ) {
			$parts = explode( ':', $s['manual_term'] );
			if ( 2 === count( $parts ) ) {
				$t = get_term( (int) $parts[1], $parts[0] );
				if ( $t && ! is_wp_error( $t ) ) {
					$term = $t;
				}
			}
		}

		// 3) Auto mode — try multiple ways to find a term.
		if ( ! $term && 'auto' === $s['source'] ) {
			// 3a) queried object (taxonomy archive).
			$obj = get_queried_object();
			if ( $obj && isset( $obj->term_id ) && isset( $obj->taxonomy ) ) {
				$term = $obj;
			}

			// 3b) is_category / is_tag / is_tax (defensive).
			if ( ! $term && ( is_category() || is_tag() || ( function_exists( 'is_tax' ) && is_tax() ) ) ) {
				$obj = get_queried_object();
				if ( $obj && isset( $obj->term_id ) ) {
					$term = $obj;
				}
			}

			// 3c) ?product_cat=slug / ?cat=slug / ?category=slug query params.
			if ( ! $term ) {
				$candidates = array(
					'product_cat' => 'product_cat',
					'category'    => 'category',
					'cat'         => 'category',
					'tag'         => 'post_tag',
					'product_tag' => 'product_tag',
				);
				foreach ( $candidates as $qk => $tx ) {
					if ( empty( $_GET[ $qk ] ) ) { // phpcs:ignore
						continue;
					}
					if ( ! taxonomy_exists( $tx ) ) {
						continue;
					}
					$slug = sanitize_text_field( wp_unslash( $_GET[ $qk ] ) ); // phpcs:ignore
					$t    = get_term_by( 'slug', $slug, $tx );
					if ( ! $t ) {
						$t = get_term_by( 'name', $slug, $tx );
					}
					if ( $t && ! is_wp_error( $t ) ) {
						$term = $t;
						break;
					}
				}
			}
		}

		if ( $term ) {
			$out['title']    = $term->name;
			$desc            = term_description( $term->term_id, $term->taxonomy );
			$out['desc']     = $desc ? $desc : '';
			$out['has_term'] = true;
		} elseif ( 'auto' === $s['source'] ) {
			// 4) No term — last-resort fallbacks so the widget never looks "broken".
			if ( function_exists( 'is_shop' ) && is_shop() ) {
				$out['title'] = __( 'فروشگاه', 'baspar-elements' );
				$desc_id      = get_option( 'woocommerce_shop_page_id' );
				if ( $desc_id ) {
					$shop_post = get_post( $desc_id );
					if ( $shop_post && ! empty( $shop_post->post_excerpt ) ) {
						$out['desc'] = wpautop( $shop_post->post_excerpt );
					}
				}
			} elseif ( is_archive() || is_home() || is_search() ) {
				$out['title'] = wp_strip_all_tags( get_the_archive_title() );
				$adesc        = get_the_archive_description();
				if ( $adesc ) {
					$out['desc'] = $adesc;
				}
			} elseif ( get_post() && is_singular() ) {
				$out['title'] = get_the_title();
				if ( has_excerpt() ) {
					$out['desc'] = get_the_excerpt();
				}
			} else {
				// Last resort — generic page-title from <title>.
				$out['title'] = wp_get_document_title();
			}
		}

		// Apply fallback text if description is still empty.
		if ( '' === trim( wp_strip_all_tags( $out['desc'] ) ) && ! empty( $s['fallback_text'] ) ) {
			$out['desc'] = wpautop( $s['fallback_text'] );
		}

		return $out;
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$res = $this->resolve( $s );

		$in_editor = isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode();

		// Editor preview: when auto mode finds nothing, show a clearly-labelled
		// placeholder so the user knows where the real content will appear.
		if ( $in_editor && 'auto' === $s['source'] && '' === trim( $res['title'] ) && '' === trim( wp_strip_all_tags( $res['desc'] ) ) ) {
			$res['title'] = __( '[ پیش‌نمایش ] عنوان دسته اینجا نمایش داده می‌شود', 'baspar-elements' );
			$res['desc']  = '<p>' . esc_html__( 'این یک پیش‌نمایش است. در صفحه آرشیو دسته‌بندی، توضیحی که در پیشخوان وردپرس برای آن دسته نوشته‌اید، اینجا نمایش داده می‌شود.', 'baspar-elements' ) . '</p>';
		}

		$has_title = ( 'yes' === $s['show_title'] ) && '' !== trim( $res['title'] );
		$has_desc  = '' !== trim( wp_strip_all_tags( $res['desc'] ) );

		// Hide entirely only when explicitly requested AND nothing to show.
		if ( ! $has_title && ! $has_desc ) {
			if ( 'yes' === $s['hide_when_empty'] ) {
				return;
			}
			// Otherwise show a minimal placeholder so the widget remains visible.
			$has_desc    = true;
			$res['desc'] = '<p>' . esc_html__( 'محتوایی برای نمایش وجود ندارد.', 'baspar-elements' ) . '</p>';
		}

		// Compose the title with optional prefix.
		$display_title = '';
		if ( $has_title ) {
			if ( 'auto' === $s['source'] && $res['has_term'] && ! empty( $s['title_prefix'] ) ) {
				$display_title = $s['title_prefix'] . ' ' . $res['title'];
			} else {
				$display_title = $res['title'];
			}
		}
		?>
		<div class="baspar-scope">
			<div class="bspr-termdesc <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" style="background:#fff;border:1px solid var(--line);border-right:4px solid var(--brand);border-radius:var(--radius);padding:24px 28px;display:flex;gap:18px;align-items:flex-start">
				<?php if ( ! empty( $s['icon'] ) ) : ?>
					<div class="ic" style="width:48px;height:48px;border-radius:10px;background:var(--brand-50);color:var(--brand);display:flex;align-items:center;justify-content:center;flex-shrink:0">
						<?php echo icon_svg( $s['icon'], 22 ); // phpcs:ignore ?>
					</div>
				<?php endif; ?>
				<div style="flex:1;min-width:0">
					<?php if ( $display_title ) : ?>
						<h3 style="font-size:20px;color:var(--brand-deep);margin:0 0 10px;letter-spacing:-0.01em;line-height:1.35">
							<?php echo esc_html( $display_title ); ?>
						</h3>
					<?php endif; ?>
					<?php if ( $has_desc ) : ?>
						<div class="desc" style="font-size:14.5px;color:var(--ink-2);line-height:1.85"><?php echo wp_kses_post( $res['desc'] ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
