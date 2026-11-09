<?php
/**
 * FAQ accordion. Numbered items, single-open or multi-open.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\inline_kses;
use function BasparElements\slash_links;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Faq
 */
class Baspar_Faq extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-faq';
	}

	public function get_title() {
		return __( 'بسپار — سوالات متداول (FAQ)', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-help-o';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'سوال‌ها', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'q', array( 'label' => __( 'سوال', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'a', array( 'label' => __( 'پاسخ', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4, 'description' => __( 'لینک داخلی با تگ a مجاز است (br, strong, em هم مجازند).', 'baspar-elements' ) ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'سوال‌ها', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ q }}}',
				'default'     => array(
					array( 'q' => 'حداقل مقدار سفارش چقدر است؟', 'a' => 'کمترین سفارش یک بسته کامل کارخانه است، مثلاً یک کیسه ۲۵ کیلویی یا یک بشکه. بسته را باز و تفکیک نمی‌کنیم، چون با باز شدن بسته ردیابی بچ و اعتبار برگه آنالیز از بین می‌رود.' ),
					array( 'q' => 'بسپارمارکت واردکننده است یا پخش‌کننده؟', 'a' => 'واردکننده. بیشتر کالا را خودمان از کارخانه سازنده وارد و از انبار تهران توزیع می‌کنیم. بعضی اقلام تولید داخل را هم مستقیم از تولیدکننده می‌گیریم. جزئیات در <a href="/chemical-importer/">صفحه واردکننده مواد اولیه شیمیایی</a> آمده است.' ),
					array( 'q' => 'فاکتور رسمی صادر می‌شود؟', 'a' => 'بله، برای خرید شرکتی فاکتور رسمی با مشخصات و کد اقتصادی خریدار صادر می‌شود.' ),
					array( 'q' => 'به اشخاص حقیقی و کارگاه‌های کوچک هم می‌فروشید؟', 'a' => 'بله، به شرط خرید یک بسته کامل. برای مواد کنترل‌شده، فروش فقط به واحدهای دارای مجوز است.' ),
					array( 'q' => 'آیا امکان دریافت نمونه پیش از خرید عمده وجود دارد؟', 'a' => 'بله، برای سفارش‌های بالای یک تن، بسته به نوع محصول و موجودی انبار، امکان ارسال  نمونه کالای درخواستی وجود دارد. این مورد را با کارشناس فروش خود مطرح کنید.' ),
					array( 'q' => 'قیمت مواد اولیه شیمیایی چرا روی سایت درج نشده است؟', 'a' => 'قیمت مواد اولیه شیمیایی به‌دلیل نوسانات نرخ ارز، تغییرات بازار جهانی و شرایط انبار، روزانه تغییر می‌کند. برای ارائه دقیق‌ترین قیمت روز، استعلام تلفنی یا از طریق واتس‌اپ توصیه می‌شود.' ),
					array( 'q' => 'آیا بسپارمارکت گواهی COA ارائه می‌دهد؟', 'a' => 'بله، تمام محصولات وارداتی با گواهی COA کارخانه‌ی سازنده تحویل می‌شوند. برای محصولات داخلی نیز در صورت درخواست، آنالیز معتبر ارائه می‌گردد.' ),
					array( 'q' => 'روش‌های پرداخت چیست؟', 'a' => 'پرداخت به‌صورت کارت‌به‌کارت، واریز بانکی به حساب شرکت یا پرداخت در محل امکان‌پذیر است.' ),
					array( 'q' => 'زمان ارسال سفارش چقدر است؟', 'a' => 'برای محصولات موجود در انبار، بارگیری در همان روز کاری و ارسال طی ۲۴ ساعت انجام می‌شود. زمان رسیدن به مقصد بسته به فاصله شهر، بین ۱ تا ۴ روز کاری متغیر است.' ),
					array( 'q' => 'گارانتی بازگشت کالا چگونه است؟', 'a' => 'در صورت مغایرت محصول با مشخصات سفارش یا COA، تا ۴۸ ساعت پس از تحویل، امکان مرجوع کردن کالا و دریافت وجه یا تعویض وجود دارد. برای این منظور باید عدم باز شدن بسته‌بندی اصلی محرز باشد.' ),
				),
			)
		);
		$this->add_control( 'single', array( 'label' => __( 'فقط یک مورد باز بماند', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'first_open', array( 'label' => __( 'اولین مورد باز باشد', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'q_typo', __( 'تایپوگرافی سوال', 'baspar-elements' ), '.faq-q' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="faq <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" <?php echo ( 'yes' === $s['single'] ) ? 'data-faq-single' : ''; ?>>
				<?php
				$i = 0;
				foreach ( (array) $s['items'] as $it ) :
					$open = ( 'yes' === $s['first_open'] && 0 === $i ) ? ' is-open' : '';
					?>
					<div class="faq-item<?php echo esc_attr( $open ); ?>">
						<button class="faq-q" type="button">
							<span class="faq-n"><?php echo esc_html( number_format_i18n( $i + 1 ) ); ?></span>
							<span class="faq-qt"><?php echo esc_html( $it['q'] ); ?></span>
							<span class="faq-icon"><?php echo icon_svg( 'plus', 18 ); // phpcs:ignore ?></span>
						</button>
						<div class="faq-a-wrap"><div class="faq-a"><?php echo slash_links( wp_kses( $it['a'], inline_kses() ) ); // phpcs:ignore ?></div></div>
					</div>
					<?php
					++$i;
				endforeach;
				?>
			</div>
		</div>
		<?php
	}
}
