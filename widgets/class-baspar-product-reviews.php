<?php
/**
 * Product reviews — score header (4.8 / 5 stars) + 4 review cards.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Product_Reviews extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-product-reviews'; }
	public function get_title() { return __( 'بسپار — نظرات محصول', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-rating'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'score', array( 'label' => __( 'امتیاز کلی', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '۴.۸' ) );
		$this->add_control( 'stars', array( 'label' => __( 'تعداد ستاره (۱-۵)', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 5, 'min' => 1, 'max' => 5 ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد نظرات', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => '۲۴ نظر' ) );

		$rep = new Repeater();
		$rep->add_control( 'name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'company', array( 'label' => __( 'شرکت/شغل', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'date', array( 'label' => __( 'تاریخ', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'stars', array( 'label' => __( 'ستاره (۱-۵)', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 5, 'min' => 1, 'max' => 5 ) );
		$rep->add_control( 'text', array( 'label' => __( 'نظر', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control( 'items', array(
			'label' => __( 'نظرات', 'baspar-elements' ),
			'type' => Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => 'احمدی', 'company' => 'کارخانه رنگ تهران', 'date' => '۲ ماه پیش', 'stars' => 5, 'text' => 'کیفیت گرید عالی، تطابق کامل با COA. توصیه می‌کنم.' ),
				array( 'name' => 'رضایی', 'company' => 'صنایع کامپوزیت', 'date' => '۱ ماه پیش', 'stars' => 5, 'text' => 'تحویل سریع و پشتیبانی فنی خیلی خوب.' ),
				array( 'name' => 'کریمی', 'company' => 'تولیدی پلاستیک', 'date' => '۳ هفته پیش', 'stars' => 4, 'text' => 'گرید مناسب فرمولاسیون ما؛ قیمت کمی بالاتر از انتظار.' ),
				array( 'name' => 'محمدی', 'company' => 'شرکت پوشش', 'date' => '۲ هفته پیش', 'stars' => 5, 'text' => 'مشاوره کارشناس فنی واقعاً کارگشا بود.' ),
			),
		) );
		$this->add_reveal_toggle();
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	private function stars( $n ) {
		$out = '';
		for ( $i = 1; $i <= 5; $i++ ) {
			$color = ( $i <= $n ) ? '#FFB547' : 'var(--line-2)';
			$out  .= '<span style="color:' . $color . '">' . icon_svg( 'star', 14 ) . '</span>';
		}
		return $out;
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<div class="<?php echo esc_attr( $this->reveal_class( $s ) ); ?>" style="display:flex;align-items:center;gap:20px;padding:24px 28px;background:linear-gradient(180deg,var(--brand-tint),#fff);border:1px solid var(--line);border-radius:calc(var(--radius) + 4px);margin-bottom:24px">
				<div style="text-align:center;padding-left:24px;border-left:1px solid var(--line)">
					<div style="font-size:48px;font-weight:800;color:var(--brand-deep);line-height:1;letter-spacing:-0.02em"><?php echo esc_html( $s['score'] ); ?></div>
					<div style="display:flex;gap:2px;justify-content:center;margin-top:8px"><?php echo $this->stars( (int) $s['stars'] ); // phpcs:ignore ?></div>
					<div style="font-size:12px;color:var(--ink-3);margin-top:6px"><?php echo esc_html( $s['count'] ); ?></div>
				</div>
				<div>
					<strong style="font-size:17px;color:var(--brand-deep);display:block;margin-bottom:6px">رضایت بالای مشتریان</strong>
					<span style="font-size:13.5px;color:var(--ink-2)">بر اساس نظرات ثبت‌شده توسط مشتریان واقعی.</span>
				</div>
			</div>
			<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
				<?php foreach ( (array) $s['items'] as $r ) : ?>
					<div style="background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:22px 24px">
						<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
							<div>
								<strong style="font-size:14.5px;color:var(--brand-deep);display:block"><?php echo esc_html( $r['name'] ); ?></strong>
								<span style="font-size:12px;color:var(--ink-3)"><?php echo esc_html( $r['company'] ); ?></span>
							</div>
							<span style="font-family:var(--mono);font-size:11px;color:var(--ink-3)"><?php echo esc_html( $r['date'] ); ?></span>
						</div>
						<div style="display:flex;gap:2px;margin-bottom:10px"><?php echo $this->stars( (int) $r['stars'] ); // phpcs:ignore ?></div>
						<p style="font-size:13.5px;color:var(--ink-2);line-height:1.7;margin:0"><?php echo esc_html( $r['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
