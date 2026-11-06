<?php
/**
 * Blog topics — tiles (icon, title, description, live article count) that
 * filter the article list by keyword or link anywhere.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;
use function BasparElements\fa_num;
use function BasparElements\slash_url;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Blog_Topics extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-blog-topics'; }
	public function get_title() { return __( 'بسپار — موضوعات وبلاگ', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'موضوعات', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'flask' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'desc', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$rep->add_control( 'keyword', array( 'label' => __( 'کلیدواژه (برای فیلتر و شمارش)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'یا لینک دلخواه', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items', array(
			'label'       => __( 'موضوعات', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array( 'icon' => 'flask', 'title' => 'رزین و اپوکسی', 'desc' => 'انواع رزین، هاردنر و نکته‌های فرمولاسیون', 'keyword' => 'رزین' ),
				array( 'icon' => 'layers', 'title' => 'پلیمر و پلاستیک', 'desc' => 'گرانول‌ها، گریدها و کاربرد در تولید', 'keyword' => 'پلی' ),
				array( 'icon' => 'beaker', 'title' => 'حلال‌ها', 'desc' => 'استون، تولوئن، گلایکول‌ها و ایمنی کار', 'keyword' => 'حلال' ),
				array( 'icon' => 'refresh', 'title' => 'لاستیک', 'desc' => 'کائوچو، شتاب‌دهنده و افزودنی‌ها', 'keyword' => 'لاستیک' ),
				array( 'icon' => 'palette', 'title' => 'رنگ و پیگمنت', 'desc' => 'تیتان، پیگمنت و مواد رنگ‌سازی', 'keyword' => 'رنگ' ),
				array( 'icon' => 'droplet', 'title' => 'چسب و کامپوزیت', 'desc' => 'مواد اولیه چسب، الیاف و کاتالیست', 'keyword' => 'چسب' ),
			),
		) );
		$this->add_control( 'show_count', array( 'label' => __( 'نمایش تعداد مقالات', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'anchor', array( 'label' => __( 'شناسه بخش لیست مقالات', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'articles' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$anchor = sanitize_html_class( $s['anchor'] ?? '' );
		$base   = remove_query_arg( array( 'bq', 'bp', 'bcat' ) );
		?>
		<div class="baspar-scope">
			<div class="bt-grid <?php echo esc_attr( $this->reveal_class( $s ) ); ?>">
				<?php $i = 0; foreach ( (array) $s['items'] as $it ) :
					if ( empty( $it['title'] ) ) {
						continue;
					}
					$kw  = trim( (string) ( $it['keyword'] ?? '' ) );
					$url = ! empty( $it['link']['url'] ) ? slash_url( $it['link']['url'] ) : add_query_arg( 'bq', rawurlencode( $kw ? $kw : $it['title'] ), $base ) . ( $anchor ? '#' . $anchor : '' );
					$cnt = 0;
					if ( 'yes' === $s['show_count'] && '' !== $kw ) {
						$cq  = new \WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 's' => $kw, 'posts_per_page' => 1, 'fields' => 'ids' ) );
						$cnt = (int) $cq->found_posts;
					}
					?>
					<a class="bt-card" href="<?php echo esc_url( $url ); ?>" data-reveal-delay="<?php echo esc_attr( $i * 50 ); ?>">
						<span class="bt-ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'flask', 24 ); // phpcs:ignore ?></span>
						<span class="bt-body">
							<strong><?php echo esc_html( $it['title'] ); ?></strong>
							<?php if ( ! empty( $it['desc'] ) ) : ?><span class="bt-desc"><?php echo esc_html( $it['desc'] ); ?></span><?php endif; ?>
						</span>
						<span class="bt-foot">
							<?php if ( $cnt ) : ?><span class="bt-count"><?php echo esc_html( sprintf( '%s مقاله', fa_num( $cnt ) ) ); ?></span><?php endif; ?>
							<span class="bt-go"><?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?></span>
						</span>
					</a>
				<?php ++$i; endforeach; ?>
			</div>
		</div>
		<?php
	}
}
