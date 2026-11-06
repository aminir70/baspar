<?php
/**
 * Blog landing hero — kicker, H1, intro, article search and topic chips,
 * with a small stats row (article / category counts read live).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\fa_num;
use function BasparElements\slash_url;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Blog_Hero extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-blog-hero'; }
	public function get_title() { return __( 'بسپار — هیرو وبلاگ', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-site-search'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'BASPARMARKET JOURNAL' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مجله تخصصی مواد اولیه شیمیایی' ) );
		$this->add_control( 'title_tag', array( 'label' => __( 'تگ عنوان', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'div' => 'div' ) ) );
		$this->add_control( 'sub', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => 'راهنمای خرید، مقایسه گریدها و نکته‌های فنی رزین، پلیمر، حلال و افزودنی‌ها؛ نوشته‌شده برای مدیران خرید و کارشناسان تولید.' ) );
		$this->add_control( 'show_search', array( 'label' => __( 'جستجو', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'placeholder', array( 'label' => __( 'متن راهنمای جستجو', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'جستجو در مقالات… مثلاً «هاردنر اپوکسی»', 'condition' => array( 'show_search' => 'yes' ) ) );
		$this->add_control( 'search_target', array( 'label' => __( 'صفحه نتایج (اختیاری)', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'description' => __( 'خالی = همین صفحه (ویجت «لیست مقالات» نتایج را با پارامتر bq نشان می‌دهد).', 'baspar-elements' ), 'condition' => array( 'show_search' => 'yes' ) ) );
		$this->add_control( 'anchor', array( 'label' => __( 'شناسه بخش نتایج', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'articles', 'condition' => array( 'show_search' => 'yes' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'keyword', array( 'label' => __( 'کلیدواژه جستجو', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'یا لینک دلخواه', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'chips', array(
			'label'       => __( 'موضوعات سریع', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ text }}}',
			'default'     => array(
				array( 'text' => 'رزین و اپوکسی', 'keyword' => 'رزین' ),
				array( 'text' => 'پلیمر و پلاستیک', 'keyword' => 'پلی' ),
				array( 'text' => 'حلال‌ها', 'keyword' => 'حلال' ),
				array( 'text' => 'لاستیک', 'keyword' => 'لاستیک' ),
				array( 'text' => 'رنگ و پیگمنت', 'keyword' => 'رنگ' ),
				array( 'text' => 'کامپوزیت', 'keyword' => 'کامپوزیت' ),
			),
		) );
		$this->add_control( 'show_stats', array( 'label' => __( 'آمار (تعداد مقالات / دسته‌ها)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'stat3', array( 'label' => __( 'آمار سوم (متن آزاد)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'نوشته‌شده توسط کارشناسان فنی', 'condition' => array( 'show_stats' => 'yes' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.bh-title' );
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$tag    = in_array( $s['title_tag'] ?? 'h1', array( 'h1', 'h2', 'div' ), true ) ? $s['title_tag'] : 'h1';
		$target = ! empty( $s['search_target']['url'] ) ? slash_url( $s['search_target']['url'] ) : '';
		$anchor = sanitize_html_class( $s['anchor'] ?? '' );
		$q      = isset( $_GET['bq'] ) ? sanitize_text_field( wp_unslash( $_GET['bq'] ) ) : ''; // phpcs:ignore
		?>
		<div class="baspar-scope">
			<section class="bh">
				<div class="bh-bg" aria-hidden="true"></div>
				<div class="bh-inner">
					<?php if ( $s['kicker'] ) : ?><div class="bh-kicker mono"><span></span><?php echo esc_html( $s['kicker'] ); ?><span></span></div><?php endif; ?>
					<?php if ( $s['title'] ) : ?><<?php echo $tag; // phpcs:ignore ?> class="bh-title"><?php echo esc_html( $s['title'] ); ?></<?php echo $tag; // phpcs:ignore ?>><?php endif; ?>
					<?php if ( $s['sub'] ) : ?><div class="bh-sub rich-text"><?php echo wp_kses_post( $s['sub'] ); ?></div><?php endif; ?>

					<?php if ( 'yes' === $s['show_search'] ) : ?>
						<form class="bh-search" role="search" method="get" action="<?php echo esc_url( $target ); ?><?php echo $anchor ? '#' . esc_attr( $anchor ) : ''; ?>">
							<span class="bh-search-ic" aria-hidden="true"><?php echo icon_svg( 'search', 20 ); // phpcs:ignore ?></span>
							<input type="search" name="bq" value="<?php echo esc_attr( $q ); ?>" placeholder="<?php echo esc_attr( $s['placeholder'] ); ?>" aria-label="<?php echo esc_attr( $s['placeholder'] ); ?>">
							<button type="submit"><?php esc_html_e( 'جستجو', 'baspar-elements' ); ?></button>
						</form>
					<?php endif; ?>

					<?php if ( ! empty( $s['chips'] ) ) : ?>
						<div class="bh-chips">
							<?php foreach ( (array) $s['chips'] as $c ) :
								if ( empty( $c['text'] ) ) {
									continue;
								}
								if ( ! empty( $c['link']['url'] ) ) {
									$url = slash_url( $c['link']['url'] );
								} else {
									$url = add_query_arg( 'bq', rawurlencode( $c['keyword'] ? $c['keyword'] : $c['text'] ), $target ? $target : remove_query_arg( array( 'bq', 'bp', 'bcat' ) ) ) . ( $anchor ? '#' . $anchor : '' );
								}
								$on = '' !== $q && $q === ( $c['keyword'] ? $c['keyword'] : $c['text'] );
								?>
								<a class="bh-chip<?php echo $on ? ' is-on' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $c['text'] ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( 'yes' === $s['show_stats'] ) :
						$posts = (int) wp_count_posts( 'post' )->publish;
						$cats  = (int) wp_count_terms( array( 'taxonomy' => 'category', 'hide_empty' => true ) );
						?>
						<div class="bh-stats">
							<div><strong><?php echo esc_html( fa_num( $posts ) ); ?></strong><span><?php esc_html_e( 'مقاله تخصصی', 'baspar-elements' ); ?></span></div>
							<div><strong><?php echo esc_html( fa_num( $cats ) ); ?></strong><span><?php esc_html_e( 'دسته موضوعی', 'baspar-elements' ); ?></span></div>
							<?php if ( $s['stat3'] ) : ?><div><span class="bh-stat-ic"><?php echo icon_svg( 'award', 18 ); // phpcs:ignore ?></span><span><?php echo esc_html( $s['stat3'] ); ?></span></div><?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
		</div>
		<?php
	}
}
