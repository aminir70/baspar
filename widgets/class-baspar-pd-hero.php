<?php
/**
 * Product page hero — gallery, title/meta/short description/key specs and
 * the "get price & advice" card with the category's sales experts
 * (WhatsApp / Bale / call). No add-to-cart: prices are on request.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_product;
use function BasparElements\product_specs;
use function BasparElements\product_brand_terms;
use function BasparElements\product_cat_trail;
use function BasparElements\render_expert_list;
use function BasparElements\experts_settings;
use function BasparElements\experts_for_product;
use function BasparElements\whatsapp_link;
use function BasparElements\expert_message;
use function BasparElements\tel_link;
use function BasparElements\expert_bale_link;
use function BasparElements\fa_num;
use function BasparElements\fa_num_smart;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Pd_Hero extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-pd-hero'; }
	public function get_title() { return __( 'بسپار — محصول: بالای صفحه (گالری + اطلاعات + استعلام)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-single-product'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'اطلاعات محصول', 'baspar-elements' ) ) );
		$this->add_control( 'show_crumbs', array( 'label' => __( 'مسیر صفحه (breadcrumb)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'specs_count', array( 'label' => __( 'تعداد مشخصات کلیدی کنار عنوان', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 4, 'min' => 0, 'max' => 8, 'description' => __( 'از «ویژگی‌های» محصول در ووکامرس خوانده می‌شود.', 'baspar-elements' ) ) );
		$this->add_control( 'highlights', array( 'label' => __( 'نکات کوتاه (هر خط یک مورد)', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4, 'default' => "برگه آنالیز (COA) همراه محموله\nفروش عمده و بسته‌ای\nارسال به سراسر ایران" ) );
		$this->add_control( 'price_title', array( 'label' => __( 'عنوان قیمت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'قیمت: استعلامی' ) );
		$this->add_control( 'price_text', array( 'label' => __( 'توضیح قیمت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'قیمت با نرخ روز ارز تغییر می‌کند؛ از کارشناس این دسته بپرسید.' ) );
		$this->add_control( 'show_share', array( 'label' => __( 'دکمه‌های اشتراک‌گذاری', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'contact', array( 'label' => __( 'کارت استعلام قیمت', 'baspar-elements' ) ) );
		$this->add_control( 'contact_kicker', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'GET A QUOTE' ) );
		$this->add_control( 'contact_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'دریافت قیمت روز و مشاوره رایگان' ) );
		$this->add_control( 'contact_text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => 'نام گرید و مقدار مورد نیاز را بگویید؛ کارشناس این دسته قیمت روز، موجودی انبار و پیش‌فاکتور را برایتان می‌فرستد.' ) );
		$this->add_control( 'show_bale', array( 'label' => __( 'دکمه بله', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_phone', array( 'label' => __( 'شماره و دکمه تماس', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'mobile_bar', array( 'label' => __( 'نوار تماس ثابت پایین صفحه (موبایل)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'experts_note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => __( 'کارشناسان هر دسته در «پیشخوان › بسپار › کارشناسان فروش» تنظیم می‌شوند.', 'baspar-elements' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.pdh-title' );
		$this->end_controls_section();
	}

	/**
	 * Gallery image ids: featured first, then the product gallery.
	 */
	private function image_ids( $product ) {
		$ids = array();
		if ( $product->get_image_id() ) {
			$ids[] = (int) $product->get_image_id();
		}
		foreach ( $product->get_gallery_image_ids() as $g ) {
			$ids[] = (int) $g;
		}
		return array_values( array_unique( array_filter( $ids ) ) );
	}

	protected function render() {
		$product = current_product();
		if ( ! $product ) {
			echo '<div class="baspar-scope"><p class="pdx-empty">' . esc_html__( 'این ویجت در قالب صفحه محصول کار می‌کند.', 'baspar-elements' ) . '</p></div>';
			return;
		}
		$s      = $this->get_settings_for_display();
		$pid    = $product->get_id();
		$title  = $product->get_name();
		$imgs   = $this->image_ids( $product );
		$trail  = product_cat_trail( $pid );
		$brands = product_brand_terms( $pid );
		$specs  = product_specs( $product );
		$sku    = $product->get_sku();
		$short  = $product->get_short_description();
		$lines  = array_filter( array_map( 'trim', preg_split( '/\R/u', (string) $s['highlights'] ) ) );
		$status = $product->get_stock_status();
		$status_txt = array( 'instock' => __( 'موجود در انبار', 'baspar-elements' ), 'outofstock' => __( 'فعلاً ناموجود — قابل سفارش', 'baspar-elements' ), 'onbackorder' => __( 'سفارشی — با زمان تحویل', 'baspar-elements' ) );
		$url    = get_permalink( $pid );
		$set    = experts_settings();
		$first  = experts_for_product( $pid );
		$first  = $first ? $first[0] : null;
		?>
		<div class="baspar-scope">
			<section class="pdh" id="pd-top">
				<?php if ( 'yes' === $s['show_crumbs'] ) : ?>
					<nav class="pdh-crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'baspar-elements' ); ?>">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'baspar-elements' ); ?></a>
						<?php foreach ( $trail as $t ) : ?><span aria-hidden="true">/</span><a href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a><?php endforeach; ?>
						<span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html( $title ); ?></span>
					</nav>
				<?php endif; ?>

				<div class="pdh-grid">
					<div class="pdh-gallery">
						<?php if ( $imgs ) :
							$main = $imgs[0];
							?>
							<div class="pdg" data-pdg>
								<a class="pdg-main" href="<?php echo esc_url( wp_get_attachment_image_url( $main, 'full' ) ); ?>" target="_blank" rel="noopener" data-pdg-main aria-label="<?php esc_attr_e( 'نمایش تصویر بزرگ', 'baspar-elements' ); ?>">
									<?php echo wp_get_attachment_image( $main, 'woocommerce_single', false, array( 'class' => 'pdg-img', 'alt' => $title, 'fetchpriority' => 'high', 'loading' => 'eager', 'data-pdg-img' => '1' ) ); // phpcs:ignore ?>
									<span class="pdg-zoom" aria-hidden="true"><?php echo icon_svg( 'zoom', 18 ); // phpcs:ignore ?></span>
								</a>
								<?php if ( count( $imgs ) > 1 ) : ?>
									<div class="pdg-thumbs">
										<?php foreach ( $imgs as $k => $iid ) : ?>
											<button type="button" class="pdg-th<?php echo 0 === $k ? ' is-on' : ''; ?>" data-pdg-th data-large="<?php echo esc_url( wp_get_attachment_image_url( $iid, 'woocommerce_single' ) ); ?>" data-full="<?php echo esc_url( wp_get_attachment_image_url( $iid, 'full' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( 'تصویر %s', fa_num( $k + 1 ) ) ); ?>">
												<?php echo wp_get_attachment_image( $iid, 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore ?>
											</button>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<div class="pdg pdg-empty"><?php echo icon_svg( 'package', 64 ); // phpcs:ignore ?></div>
						<?php endif; ?>
					</div>

					<div class="pdh-info">
						<div class="pdh-chips">
							<?php if ( $trail ) :
								$leaf = end( $trail );
								?>
								<a class="pdh-chip" href="<?php echo esc_url( get_term_link( $leaf ) ); ?>"><?php echo icon_svg( 'layers', 14 ); // phpcs:ignore ?><?php echo esc_html( $leaf->name ); ?></a>
							<?php endif; ?>
							<?php foreach ( $brands as $b ) : ?>
								<a class="pdh-chip pdh-brand" href="<?php echo esc_url( $b['url'] ); ?>">
									<?php if ( $b['logo'] ) : ?><img src="<?php echo esc_url( $b['logo'] ); ?>" alt="<?php echo esc_attr( $b['name'] ); ?>" loading="lazy"><?php endif; ?>
									<span><?php echo esc_html( $b['name'] ); ?></span>
								</a>
							<?php endforeach; ?>
						</div>

						<h1 class="pdh-title"><?php echo esc_html( $title ); ?></h1>
						<?php if ( $sku ) : ?><div class="pdh-sku"><?php esc_html_e( 'کد محصول:', 'baspar-elements' ); ?> <span dir="ltr"><?php echo esc_html( $sku ); ?></span></div><?php endif; ?>

						<?php if ( $short ) : ?><div class="pdh-short rich-text"><?php echo wp_kses_post( wpautop( $short ) ); ?></div><?php endif; ?>

						<?php if ( $specs && (int) $s['specs_count'] > 0 ) : ?>
							<dl class="pdh-specs">
								<?php foreach ( array_slice( $specs, 0, (int) $s['specs_count'], true ) as $label => $value ) : ?>
									<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( fa_num_smart( $value ) ); ?></dd></div>
								<?php endforeach; ?>
							</dl>
							<?php if ( count( $specs ) > (int) $s['specs_count'] ) : ?><a class="pdh-more" href="#pd-specs"><?php esc_html_e( 'همه مشخصات فنی', 'baspar-elements' ); ?><?php echo icon_svg( 'arrow-down', 14 ); // phpcs:ignore ?></a><?php endif; ?>
						<?php endif; ?>

						<?php if ( $lines ) : ?>
							<ul class="pdh-lines"><?php foreach ( $lines as $l ) : ?><li><?php echo icon_svg( 'check', 14 ); // phpcs:ignore ?><?php echo esc_html( $l ); ?></li><?php endforeach; ?></ul>
						<?php endif; ?>

						<?php if ( 'yes' === $s['show_share'] ) :
							$enc = rawurlencode( $url );
							$t   = rawurlencode( wp_strip_all_tags( $title ) );
							?>
							<div class="pdh-share">
								<span><?php echo icon_svg( 'share', 15 ); // phpcs:ignore ?><?php esc_html_e( 'اشتراک‌گذاری:', 'baspar-elements' ); ?></span>
								<a href="https://wa.me/?text=<?php echo $t . '%20' . $enc; // phpcs:ignore ?>" target="_blank" rel="noopener nofollow" aria-label="WhatsApp"><?php echo icon_svg( 'whatsapp', 16 ); // phpcs:ignore ?></a>
								<a href="https://t.me/share/url?url=<?php echo $enc; // phpcs:ignore ?>&amp;text=<?php echo $t; // phpcs:ignore ?>" target="_blank" rel="noopener nofollow" aria-label="Telegram"><?php echo icon_svg( 'telegram', 16 ); // phpcs:ignore ?></a>
								<a href="mailto:?subject=<?php echo $t; // phpcs:ignore ?>&amp;body=<?php echo $enc; // phpcs:ignore ?>" aria-label="Email"><?php echo icon_svg( 'mail', 16 ); // phpcs:ignore ?></a>
								<button type="button" data-copy="<?php echo esc_url( $url ); ?>" aria-label="<?php esc_attr_e( 'کپی لینک', 'baspar-elements' ); ?>"><?php echo icon_svg( 'link', 16 ); // phpcs:ignore ?></button>
							</div>
						<?php endif; ?>
					</div>

					<aside class="pdh-buy" id="pd-contact">
						<div class="pdq">
							<div class="pdq-price">
								<span class="pdq-status pdq-<?php echo esc_attr( $status ); ?>"><i aria-hidden="true"></i><?php echo esc_html( $status_txt[ $status ] ?? $status_txt['instock'] ); ?></span>
								<strong><?php echo icon_svg( 'tag', 18 ); // phpcs:ignore ?><?php echo esc_html( $s['price_title'] ); ?></strong>
								<?php if ( $s['price_text'] ) : ?><p><?php echo esc_html( $s['price_text'] ); ?></p><?php endif; ?>
							</div>
							<div class="pdq-head">
								<?php if ( $s['contact_kicker'] ) : ?><span class="pdq-kicker mono"><?php echo esc_html( $s['contact_kicker'] ); ?></span><?php endif; ?>
								<strong class="pdq-title"><?php echo esc_html( $s['contact_title'] ); ?></strong>
								<?php if ( $s['contact_text'] ) : ?><p><?php echo esc_html( $s['contact_text'] ); ?></p><?php endif; ?>
							</div>
							<?php render_expert_list( $product, array( 'layout' => 'full', 'show_bale' => 'yes' === $s['show_bale'], 'show_phone' => 'yes' === $s['show_phone'] ) ); ?>
							<?php if ( ! empty( $set['hours'] ) ) : ?><div class="pdq-hours"><?php echo icon_svg( 'clock', 14 ); // phpcs:ignore ?><?php echo esc_html( $set['hours'] ); ?></div><?php endif; ?>
						</div>
					</aside>
				</div>

				<?php if ( 'yes' === $s['mobile_bar'] && $first ) :
					$wa  = ! empty( $first['whatsapp'] ) ? whatsapp_link( $first['whatsapp'], expert_message( $product, $first ) ) : '';
					$tel = ! empty( $first['phone'] ) ? tel_link( $first['phone'] ) : '';
					$ble = 'yes' === $s['show_bale'] ? expert_bale_link( $first ) : '';
					?>
					<div class="pd-mbar" role="region" aria-label="<?php esc_attr_e( 'استعلام قیمت', 'baspar-elements' ); ?>">
						<a class="pd-mbar-main" href="#pd-contact"><?php echo icon_svg( 'tag', 18 ); // phpcs:ignore ?><span><?php esc_html_e( 'دریافت قیمت', 'baspar-elements' ); ?></span></a>
						<?php if ( $wa ) : ?><a class="pd-mbar-wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php esc_attr_e( 'واتس‌اپ', 'baspar-elements' ); ?>"><?php echo icon_svg( 'whatsapp', 22 ); // phpcs:ignore ?></a><?php endif; ?>
						<?php if ( $ble ) : ?><a class="pd-mbar-bale" href="<?php echo esc_url( $ble ); ?>" target="_blank" rel="noopener nofollow" aria-label="<?php esc_attr_e( 'بله', 'baspar-elements' ); ?>"><?php echo icon_svg( 'bale', 22 ); // phpcs:ignore ?></a><?php endif; ?>
						<?php if ( $tel ) : ?><a class="pd-mbar-tel" href="<?php echo esc_url( $tel ); ?>" aria-label="<?php esc_attr_e( 'تماس', 'baspar-elements' ); ?>"><?php echo icon_svg( 'phone', 20 ); // phpcs:ignore ?></a><?php endif; ?>
					</div>
				<?php endif; ?>
			</section>
		</div>
		<?php
	}
}
