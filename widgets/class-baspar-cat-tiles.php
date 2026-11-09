<?php
/**
 * Category tiles (bento) — the main product categories as animated tiles:
 * a large featured tile, a light tile per category with the category
 * image, live product count, cursor spotlight and staggered entrance,
 * plus a wide "all products" tile.
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
use function BasparElements\home_head_controls;
use function BasparElements\render_home_head;
use function BasparElements\home_term;
use function BasparElements\home_term_image;
use function BasparElements\home_term_count;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Cat_Tiles extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-cat-tiles'; }
	public function get_title() { return __( 'بسپار — کاشی دسته‌بندی‌ها (صفحه اصلی ۲)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }

	protected function register_controls() {
		$this->start_controls_section( 'head', array( 'label' => __( 'عنوان بخش', 'baspar-elements' ) ) );
		home_head_controls( $this, array(
			'kicker'    => 'PRODUCT CATEGORIES',
			'title'     => 'دسته‌بندی',
			'highlight' => 'مواد اولیه',
			'sub'       => 'از پلیمر و لاستیک تا حلال، رنگ و رزین؛ دسته مورد نیازتان را انتخاب کنید.',
		) );
		$this->add_control( 'anchor', array( 'label' => __( 'شناسه بخش (anchor)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'home-cats' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'items_s', array( 'label' => __( 'دسته‌ها', 'baspar-elements' ) ) );
		$r = new Repeater();
		$r->add_control( 'title', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'latin', array( 'label' => __( 'نام لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'term', array( 'label' => __( 'شناسه دسته ووکامرس (برای تصویر و تعداد خودکار)', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER ) );
		$r->add_control( 'link', array( 'label' => __( 'لینک (خالی = لینک دسته)', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$r->add_control( 'image', array( 'label' => __( 'تصویر (خالی = تصویر دسته)', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA ) );
		$r->add_control( 'icon', array( 'label' => __( 'آیکون (وقتی تصویر نیست)', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'layers' ) );
		$r->add_control( 'desc', array( 'label' => __( 'توضیح کوتاه (فقط کاشی بزرگ)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'items', array(
			'label'       => __( 'دسته‌ها', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $r->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array( 'title' => 'لاستیک', 'latin' => 'RUBBER', 'term' => 183, 'link' => array( 'url' => '/product-category/polymer/rubber/' ), 'icon' => 'car', 'desc' => 'کائوچو، EPDM، NBR، CPE و افزودنی‌های صنعت لاستیک' ),
				array( 'title' => 'پلاستیک', 'latin' => 'POLYMER', 'term' => 184, 'link' => array( 'url' => '/product-category/polymer/plastic/' ), 'icon' => 'package' ),
				array( 'title' => 'شیمیایی و حلال', 'latin' => 'CHEMICAL', 'term' => 115, 'link' => array( 'url' => '/product-category/chemical/' ), 'icon' => 'flask' ),
				array( 'title' => 'رنگ و رزین', 'latin' => 'PAINT', 'term' => 116, 'link' => array( 'url' => '/product-category/colourresin/' ), 'icon' => 'palette' ),
				array( 'title' => 'کامپوزیت', 'latin' => 'COMPOSITE', 'term' => 304, 'link' => array( 'url' => '/product-category/composite/' ), 'icon' => 'layers' ),
				array( 'title' => 'چسب', 'latin' => 'GLUE', 'term' => 385, 'link' => array( 'url' => '/product-category/glue/' ), 'icon' => 'droplet' ),
				array( 'title' => 'سایر محصولات', 'latin' => 'OTHER', 'term' => 117, 'link' => array( 'url' => '/shop/' ), 'icon' => 'star' ),
			),
		) );
		$this->add_control( 'feature_first', array( 'label' => __( 'کاشی اول بزرگ (برجسته)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'media', array(
			'label'       => __( 'نماد هر دسته', 'baspar-elements' ),
			'type'        => Controls_Manager::SELECT,
			'default'     => 'icon',
			'options'     => array( 'icon' => __( 'آیکون (پیشنهادی)', 'baspar-elements' ), 'image' => __( 'تصویر', 'baspar-elements' ) ),
			'description' => __( 'تصویرهای فعلی دسته‌ها متن دارند و کنار عنوان تکراری دیده می‌شوند؛ برای حالت «تصویر» یک تصویر بدون متن (PNG شفاف) در هر ردیف بگذارید.', 'baspar-elements' ),
		) );
		$this->add_control( 'show_count', array( 'label' => __( 'نمایش تعداد محصولات', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'all_title', array( 'label' => __( 'کاشی «همه محصولات» — عنوان (خالی = حذف)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ورود به فروشگاه بسپارمارکت' ) );
		$this->add_control( 'all_sub', array( 'label' => __( 'کاشی «همه محصولات» — زیرنویس', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'بیش از ۱۰۰ ماده اولیه با قیمت روز و مشاوره رایگان' ) );
		$this->add_control( 'all_link', array( 'label' => __( 'کاشی «همه محصولات» — لینک', 'baspar-elements' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/shop/' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$items  = array_values( array_filter( (array) $s['items'], static function ( $i ) { return ! empty( $i['title'] ); } ) );
		$anchor = sanitize_html_class( (string) $s['anchor'] );
		$feat   = 'yes' === $s['feature_first'];
		?>
		<div class="baspar-scope"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<section class="hm-sec cti" data-anim-group>
				<?php render_home_head( $s ); ?>
				<div class="cti-grid<?php echo $feat ? ' has-feat' : ''; ?>">
					<?php foreach ( $items as $i => $it ) :
						$term  = home_term( $it['term'] ?? 0 );
						$url   = ! empty( $it['link']['url'] ) ? slash_url( $it['link']['url'] ) : ( $term ? get_term_link( $term ) : '' );
						$img   = 'image' === $s['media'] ? ( ! empty( $it['image']['url'] ) ? $it['image']['url'] : home_term_image( $it['term'] ?? 0, 'medium' ) ) : '';
						$count = 'yes' === $s['show_count'] ? home_term_count( $it['term'] ?? 0 ) : 0;
						$big   = $feat && 0 === $i;
						$tag   = $url ? 'a' : 'div';
						?>
						<<?php echo $tag; // phpcs:ignore ?> class="cti-tile<?php echo $big ? ' is-feat' : ''; ?>"<?php echo $url ? ' href="' . esc_url( $url ) . '"' : ''; ?> data-spot data-anim="zoom" style="--i:<?php echo (int) $i; ?>">
							<span class="cti-spot" aria-hidden="true"></span>
							<span class="cti-latin mono" aria-hidden="true"><?php echo esc_html( $it['latin'] ); ?></span>
							<span class="cti-media<?php echo $img ? '' : ' is-icon'; ?>"><?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy" width="160" height="160"><?php else : ?><?php if ( $big ) : ?><i class="cti-halo" aria-hidden="true"></i><i class="cti-halo h2" aria-hidden="true"></i><?php endif; ?><span class="cti-ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'layers', $big ? 56 : 28 ); // phpcs:ignore ?></span><?php endif; ?></span>
							<span class="cti-body">
								<?php if ( $count ) : ?><span class="cti-count"><b><?php echo esc_html( fa_num( $count ) ); ?></b> <?php esc_html_e( 'محصول', 'baspar-elements' ); ?></span><?php endif; ?>
								<strong class="cti-t"><?php echo esc_html( $it['title'] ); ?></strong>
								<?php if ( $big && ! empty( $it['desc'] ) ) : ?><span class="cti-d"><?php echo esc_html( $it['desc'] ); ?></span><?php endif; ?>
							</span>
							<span class="cti-go" aria-hidden="true"><?php echo icon_svg( 'arrow', 18 ); // phpcs:ignore ?></span>
						</<?php echo $tag; // phpcs:ignore ?>>
					<?php endforeach; ?>
					<?php if ( $s['all_title'] && ! empty( $s['all_link']['url'] ) ) : ?>
						<a class="cti-tile is-all" href="<?php echo esc_url( slash_url( $s['all_link']['url'] ) ); ?>" data-spot data-anim="zoom" style="--i:<?php echo count( $items ); ?>">
							<span class="cti-spot" aria-hidden="true"></span>
							<span class="cti-all-ic" aria-hidden="true"><?php echo icon_svg( 'grid', 26 ); // phpcs:ignore ?></span>
							<span class="cti-body"><strong class="cti-t"><?php echo esc_html( $s['all_title'] ); ?></strong><?php if ( $s['all_sub'] ) : ?><span class="cti-d"><?php echo esc_html( $s['all_sub'] ); ?></span><?php endif; ?></span>
							<span class="cti-go" aria-hidden="true"><?php echo icon_svg( 'arrow', 18 ); // phpcs:ignore ?></span>
						</a>
					<?php endif; ?>
				</div>
			</section>
		</div>
		<?php
	}
}
