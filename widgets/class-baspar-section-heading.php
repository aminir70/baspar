<?php
/**
 * Reusable section heading (kicker + title + sub) with optional dynamic source:
 *   - static (manual title/sub)
 *   - current term (taxonomy archive — uses term name + term description)
 *   - current page/post (uses post title + excerpt)
 *   - archive title (any archive: shop, blog, search, etc.)
 *   - search query
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Section_Heading
 */
class Baspar_Section_Heading extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-section-heading';
	}

	public function get_title() {
		return __( 'بسپار — عنوان بخش', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-t-letter';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'منبع عنوان', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'static',
				'options' => array(
					'static'        => __( 'دستی (مقدار ثابت)', 'baspar-elements' ),
					'current_term'  => __( 'دسته/برچسب فعلی (آرشیو)', 'baspar-elements' ),
					'current_post'  => __( 'عنوان صفحه/نوشته فعلی', 'baspar-elements' ),
					'archive_title' => __( 'عنوان آرشیو فعلی', 'baspar-elements' ),
					'search_query'  => __( 'عبارت جستجو', 'baspar-elements' ),
				),
				'description' => __( 'در حالت‌های پویا، عنوان به‌صورت خودکار از صفحه فعلی خوانده می‌شود.', 'baspar-elements' ),
			)
		);

		$this->add_control(
			'title_prefix',
			array(
				'label'       => __( 'پیشوند عنوان (در حالت پویا)', 'baspar-elements' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'description' => __( 'مثلاً "دسته:" یا "نتایج جستجوی:"', 'baspar-elements' ),
				'condition'   => array( 'source!' => 'static' ),
			)
		);

		$this->add_control(
			'kicker',
			array(
				'label'   => __( 'برچسب لاتین', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'SECTION · 01',
			)
		);
		$this->add_control(
			'title',
			array(
				'label'     => __( 'عنوان', 'baspar-elements' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'عنوان بخش',
				'condition' => array( 'source' => 'static' ),
			)
		);
		$this->add_control(
			'sub',
			array(
				'label'   => __( 'توضیح', 'baspar-elements' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => '',
			)
		);
		$this->add_control(
			'auto_sub',
			array(
				'label'        => __( 'توضیح خودکار (در حالت پویا)', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'اگر روشن باشد و توضیح بالا خالی، توضیح دسته/خلاصه نوشته به‌جای آن نمایش داده می‌شود.', 'baspar-elements' ),
				'condition'    => array( 'source!' => 'static' ),
			)
		);

		$this->add_control(
			'align',
			array(
				'label'   => __( 'چینش', 'baspar-elements' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'right'  => array( 'title' => __( 'راست', 'baspar-elements' ), 'icon' => 'eicon-text-align-right' ),
					'center' => array( 'title' => __( 'وسط', 'baspar-elements' ), 'icon' => 'eicon-text-align-center' ),
				),
				'default' => 'right',
			)
		);
		$this->add_control(
			'heading_tag',
			array(
				'label'   => __( 'تگ عنوان', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				),
			)
		);
		$this->add_control(
			'dark',
			array( 'label' => __( 'حالت تیره (روی پس‌زمینه تیره)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' )
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ برچسب', 'baspar-elements' ), '.shead-kicker', 'color' );
		$this->add_color( 'line', __( 'رنگ خط', 'baspar-elements' ), '.shead-line', 'background' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.shead-title' );
		$this->add_color( 'title_color', __( 'رنگ عنوان', 'baspar-elements' ), '.shead-title', 'color' );
		$this->add_color( 'sub_color', __( 'رنگ توضیح', 'baspar-elements' ), '.shead p', 'color' );
		$this->end_controls_section();
	}

	/**
	 * Resolve title + optional sub from the current request based on source.
	 *
	 * @param array $s Settings.
	 * @return array{title:string,sub:string}
	 */
	private function resolve( $s ) {
		$title = '';
		$sub   = '';

		switch ( $s['source'] ) {
			case 'current_term':
				$obj = get_queried_object();
				if ( $obj && isset( $obj->term_id ) ) {
					$title = $obj->name;
					if ( 'yes' === $s['auto_sub'] ) {
						$sub = wp_strip_all_tags( term_description( $obj->term_id, $obj->taxonomy ) );
					}
				} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
					$title = __( 'فروشگاه', 'baspar-elements' );
				}
				break;

			case 'current_post':
				if ( get_post() ) {
					$title = get_the_title();
					if ( 'yes' === $s['auto_sub'] ) {
						$sub = wp_strip_all_tags( get_the_excerpt() );
					}
				}
				break;

			case 'archive_title':
				if ( is_archive() || is_home() || is_search() || ( function_exists( 'is_shop' ) && is_shop() ) ) {
					$title = wp_strip_all_tags( get_the_archive_title() );
					if ( 'yes' === $s['auto_sub'] ) {
						$sub = wp_strip_all_tags( get_the_archive_description() );
					}
				}
				break;

			case 'search_query':
				$title = get_search_query();
				break;

			case 'static':
			default:
				$title = (string) $s['title'];
				break;
		}

		// Apply prefix in dynamic modes.
		if ( 'static' !== $s['source'] && '' !== $title && ! empty( $s['title_prefix'] ) ) {
			$title = $s['title_prefix'] . ' ' . $title;
		}

		// Editor placeholder when dynamic source returns nothing.
		$in_editor = isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode();
		if ( '' === $title && $in_editor && 'static' !== $s['source'] ) {
			$title = __( '[ عنوان پویا — وقتی این ویجت روی صفحه آرشیو/پست قرار گیرد، اینجا عنوان واقعی نمایش داده می‌شود ]', 'baspar-elements' );
		}

		// Manual sub overrides auto sub when filled.
		if ( ! empty( $s['sub'] ) ) {
			$sub = (string) $s['sub'];
		}

		return array( 'title' => $title, 'sub' => $sub );
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$res = $this->resolve( $s );

		$cls  = 'shead';
		$cls .= ( 'center' === $s['align'] ) ? ' shead-center' : '';
		$cls .= ( 'yes' === $s['dark'] ) ? ' shead-dark' : '';
		$cls .= ' ' . $this->reveal_class( $s );
		?>
		<div class="baspar-scope">
			<div class="<?php echo esc_attr( $cls ); ?>">
				<?php if ( $s['kicker'] ) : ?>
					<div class="shead-kicker">
						<span class="shead-line"></span>
						<span class="mono"><?php echo esc_html( $s['kicker'] ); ?></span>
						<?php if ( 'center' === $s['align'] ) : ?><span class="shead-line"></span><?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( $res['title'] ) : ?>
					<?php
					$tag = in_array( $s['heading_tag'] ?? 'h2', array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ? $s['heading_tag'] : 'h2';
					echo '<' . $tag . ' class="shead-title">' . esc_html( $res['title'] ) . '</' . $tag . '>';
					?>
				<?php endif; ?>
				<?php if ( $res['sub'] ) : ?>
					<p><?php echo esc_html( $res['sub'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
