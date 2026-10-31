<?php
/**
 * Product rail — products in a swipeable, scroll-snapping rail with arrow
 * buttons. Two layouts:
 *  - tabs:     section heading + pill tabs (one per category) with a sliding
 *              indicator; each tab has its own rail.
 *  - showcase: a category hero card (image, title, count, link) beside the
 *              rail — replaces Woodmart's "category + carousel" blocks.
 * Cards link to the product page ("استعلام قیمت"), there is no buy button.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\fa_num;
use function BasparElements\slash_url;
use function BasparElements\home_head_controls;
use function BasparElements\render_home_head;
use function BasparElements\home_term;
use function BasparElements\home_term_image;
use function BasparElements\home_term_count;
use function BasparElements\home_product_ids;
use function BasparElements\render_home_product_card;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Product_Rail extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-product-rail'; }
	public function get_title() { return __( 'بسپار — اسلایدر محصولات (تب‌دار / ویترین دسته)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-slider-push'; }

	protected function register_controls() {
		$this->start_controls_section( 'layout_s', array( 'label' => __( 'چیدمان', 'baspar-elements' ) ) );
		$this->add_control( 'layout', array(
			'label'   => __( 'نوع', 'baspar-elements' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'tabs',
			'options' => array( 'tabs' => __( 'تب‌دار (چند دسته)', 'baspar-elements' ), 'showcase' => __( 'ویترین یک دسته (کارت دسته + اسلایدر)', 'baspar-elements' ) ),
		) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد محصول در هر اسلایدر', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 10, 'min' => 2, 'max' => 24 ) );
		$this->add_control( 'order', array(
			'label'   => __( 'ترتیب', 'baspar-elements' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'latest',
			'options' => array( 'latest' => __( 'جدیدترین', 'baspar-elements' ), 'popular' => __( 'پرفروش‌ترین', 'baspar-elements' ), 'random' => __( 'تصادفی (هر ساعت)', 'baspar-elements' ) ),
		) );
		$this->add_control( 'cta', array( 'label' => __( 'متن دکمه کارت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'استعلام قیمت' ) );
		$this->add_control( 'autoplay', array( 'label' => __( 'حرکت خودکار آرام', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'head', array( 'label' => __( 'عنوان بخش', 'baspar-elements' ), 'condition' => array( 'layout' => 'tabs' ) ) );
		home_head_controls( $this, array(
			'kicker'    => 'LATEST PRODUCTS',
			'title'     => 'آخرین',
			'highlight' => 'محصولات',
			'sub'       => 'جدیدترین گریدهای اضافه‌شده به فروشگاه؛ برای قیمت روز و دیتاشیت روی هر محصول بزنید.',
			'link_text' => 'همه محصولات',
			'link'      => '/shop/',
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'tabs_s', array( 'label' => __( 'تب‌ها', 'baspar-elements' ), 'condition' => array( 'layout' => 'tabs' ) ) );
		$r = new Repeater();
		$r->add_control( 'label', array( 'label' => __( 'عنوان تب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'term', array( 'label' => __( 'شناسه دسته ووکامرس (۰ = همه)', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 0 ) );
		$this->add_control( 'tabs', array(
			'label'       => __( 'تب‌ها', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $r->get_controls(),
			'title_field' => '{{{ label }}}',
			'default'     => array(
				array( 'label' => 'پلیمر', 'term' => 114 ),
				array( 'label' => 'شیمیایی و حلال', 'term' => 115 ),
				array( 'label' => 'رنگ و رزین', 'term' => 116 ),
				array( 'label' => 'کامپوزیت', 'term' => 304 ),
			),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'show_s', array( 'label' => __( 'کارت دسته', 'baspar-elements' ), 'condition' => array( 'layout' => 'showcase' ) ) );
		$this->add_control( 'sc_term', array( 'label' => __( 'شناسه دسته ووکامرس', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 120 ) );
		$this->add_control( 'sc_kicker', array( 'label' => __( 'نام لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'TITANIUM DIOXIDE' ) );
		$this->add_control( 'sc_title', array( 'label' => __( 'عنوان (خالی = نام دسته)', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تیتان' ) );
		$this->add_control( 'sc_text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => 'دی‌اکسید تیتانیوم روتایل و آناتاز از برندهای معتبر، برای رنگ، پلاستیک، کاغذ و پوشش‌های صنعتی.' ) );
		$this->add_control( 'sc_image', array( 'label' => __( 'تصویر (خالی = تصویر دسته)', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => 'https://basparmarket.com/wp-content/uploads/2024/06/تیتان.png' ) ) );
		$this->add_control( 'sc_link_text', array( 'label' => __( 'متن لینک', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'مشاهده همه' ) );
		$this->add_control( 'sc_link', array( 'label' => __( 'لینک (خالی = صفحه دسته)', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'sc_side', array( 'label' => __( 'کارت دسته سمت چپ', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	/**
	 * Rail markup.
	 *
	 * @param int[]  $ids Product ids.
	 * @param array  $s   Settings.
	 * @param string $label Accessible label.
	 */
	private function rail( $ids, $s, $label ) {
		if ( ! $ids ) {
			echo '<p class="prl-empty">' . esc_html__( 'محصولی در این دسته پیدا نشد.', 'baspar-elements' ) . '</p>';
			return;
		}
		?>
		<div class="prl-rail" data-rail<?php echo 'yes' === $s['autoplay'] ? ' data-rail-auto' : ''; ?>>
			<button type="button" class="prl-arrow is-prev" data-rail-prev aria-label="<?php esc_attr_e( 'قبلی', 'baspar-elements' ); ?>"><?php echo icon_svg( 'arrow-right', 20 ); // phpcs:ignore ?></button>
			<div class="prl-track" data-rail-track role="list" aria-label="<?php echo esc_attr( $label ); ?>" tabindex="0">
				<?php foreach ( $ids as $k => $id ) : ?>
					<div class="prl-item" role="listitem"><?php render_home_product_card( $id, array( 'cta' => $s['cta'], 'i' => $k ) ); ?></div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="prl-arrow is-next" data-rail-next aria-label="<?php esc_attr_e( 'بعدی', 'baspar-elements' ); ?>"><?php echo icon_svg( 'arrow', 20 ); // phpcs:ignore ?></button>
			<div class="prl-progress" aria-hidden="true"><span data-rail-bar></span></div>
		</div>
		<?php
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$count = max( 2, (int) $s['count'] );
		$order = in_array( $s['order'], array( 'latest', 'popular', 'random' ), true ) ? $s['order'] : 'latest';
		$uid   = 'prl-' . $this->get_id();

		if ( 'showcase' === $s['layout'] ) {
			$term  = home_term( $s['sc_term'] );
			$title = $s['sc_title'] ? $s['sc_title'] : ( $term ? $term->name : '' );
			$img   = ! empty( $s['sc_image']['url'] ) ? $s['sc_image']['url'] : home_term_image( $s['sc_term'], 'large' );
			$url   = ! empty( $s['sc_link']['url'] ) ? slash_url( $s['sc_link']['url'] ) : ( $term ? get_term_link( $term ) : '' );
			$n     = home_term_count( $s['sc_term'] );
			$ids   = home_product_ids( (int) $s['sc_term'], $count, $order );
			?>
			<div class="baspar-scope">
				<section class="hm-sec prl is-showcase<?php echo 'yes' === $s['sc_side'] ? ' is-flip' : ''; ?>" data-anim-group>
					<div class="prl-sc" data-anim="left">
						<span class="prl-sc-bg" aria-hidden="true"><i></i><i></i></span>
						<?php if ( $s['sc_kicker'] ) : ?><span class="prl-sc-k mono"><?php echo esc_html( $s['sc_kicker'] ); ?></span><?php endif; ?>
						<?php if ( $title ) : ?><h2 class="prl-sc-t"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
						<?php if ( $n ) : ?><span class="prl-sc-n"><b><?php echo esc_html( fa_num( $n ) ); ?></b> <?php esc_html_e( 'محصول در این دسته', 'baspar-elements' ); ?></span><?php endif; ?>
						<?php if ( $s['sc_text'] ) : ?><p class="prl-sc-p"><?php echo esc_html( $s['sc_text'] ); ?></p><?php endif; ?>
						<?php if ( $img ) : ?><span class="prl-sc-img"><img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy"></span><?php endif; ?>
						<?php if ( $url && $s['sc_link_text'] ) : ?><a class="prl-sc-btn" href="<?php echo esc_url( $url ); ?>"><span><?php echo esc_html( $s['sc_link_text'] ); ?></span><?php echo icon_svg( 'arrow', 16 ); // phpcs:ignore ?></a><?php endif; ?>
					</div>
					<div class="prl-main" data-anim="up"><?php $this->rail( $ids, $s, $title ); ?></div>
				</section>
			</div>
			<?php
			return;
		}

		$tabs = array_values( array_filter( (array) $s['tabs'], static function ( $t ) { return ! empty( $t['label'] ); } ) );
		if ( ! $tabs ) {
			$tabs = array( array( 'label' => '', 'term' => 0 ) );
		}
		$extra = '';
		if ( count( $tabs ) > 1 ) {
			ob_start();
			?>
			<div class="prl-tabs" role="tablist" data-prl-tabs>
				<span class="prl-ind" aria-hidden="true"></span>
				<?php foreach ( $tabs as $i => $t ) : ?>
					<button type="button" role="tab" class="prl-tab<?php echo 0 === $i ? ' is-on' : ''; ?>" id="<?php echo esc_attr( $uid . '-t' . $i ); ?>" aria-controls="<?php echo esc_attr( $uid . '-p' . $i ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-prl-tab="<?php echo (int) $i; ?>"><?php echo esc_html( $t['label'] ); ?></button>
				<?php endforeach; ?>
			</div>
			<?php
			$extra = ob_get_clean();
		}
		?>
		<div class="baspar-scope">
			<section class="hm-sec prl is-tabs" data-anim-group data-prl>
				<?php render_home_head( $s, $extra ); ?>
				<div class="prl-panes" data-anim="up">
					<?php foreach ( $tabs as $i => $t ) : ?>
						<div class="prl-pane<?php echo 0 === $i ? ' is-on' : ''; ?>" role="tabpanel" id="<?php echo esc_attr( $uid . '-p' . $i ); ?>" aria-labelledby="<?php echo esc_attr( $uid . '-t' . $i ); ?>" data-prl-pane="<?php echo (int) $i; ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
							<?php $this->rail( home_product_ids( (int) $t['term'], $count, $order ), $s, $t['label'] ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		</div>
		<?php
	}
}
