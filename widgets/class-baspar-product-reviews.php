<?php
/**
 * Product reviews — score header (average / stars) + review cards.
 *
 * Reads the rating summary and the individual reviews live from the current
 * WooCommerce product's approved comments. Nothing is entered by hand.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;
use function BasparElements\fa_num;
use function BasparElements\current_product;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Product_Reviews extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-product-reviews'; }
	public function get_title() { return __( 'بسپار — نظرات محصول', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-rating'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'نظرات (پویا)', 'baspar-elements' ) ) );
		$this->add_control(
			'info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'امتیاز، تعداد و متن نظرات به‌صورت خودکار از نظرات ثبت‌شده‌ی همین محصول خوانده می‌شوند.', 'baspar-elements' ),
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->add_control( 'summary_title', array( 'label' => __( 'عنوان جعبه امتیاز', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'رضایت بالای مشتریان' ) );
		$this->add_control( 'summary_sub', array( 'label' => __( 'زیرعنوان جعبه امتیاز', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'بر اساس نظرات ثبت‌شده توسط مشتریان واقعی.' ) );
		$this->add_control( 'limit', array( 'label' => __( 'حداکثر تعداد نمایش', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 4, 'min' => 1, 'max' => 30 ) );
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
		$s       = $this->get_settings_for_display();
		$product = current_product();

		if ( ! $product ) {
			if ( $this->is_editor() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'این ویجت نظرات را از محصول ووکامرس می‌خواند. آن را در قالب «محصول تکی» قرار دهید.', 'baspar-elements' ) . '</div>';
			}
			return;
		}

		$pid     = $product->get_id();
		$average = (float) $product->get_average_rating();
		$total   = (int) $product->get_review_count();

		$reviews = get_comments(
			array(
				'post_id' => $pid,
				'status'  => 'approve',
				'type'    => 'review',
				'number'  => max( 1, (int) $s['limit'] ),
			)
		);

		if ( empty( $reviews ) ) {
			if ( $this->is_editor() ) {
				echo '<div class="baspar-scope" style="padding:24px;text-align:center;color:#6B5E80">' . esc_html__( 'هنوز نظری برای این محصول ثبت نشده است.', 'baspar-elements' ) . '</div>';
			}
			return;
		}

		$count_label = sprintf(
			/* translators: %s: number of reviews. */
			_n( '%s نظر', '%s نظر', $total, 'baspar-elements' ),
			fa_num( $total )
		);
		?>
		<div class="baspar-scope">
			<div class="<?php echo esc_attr( $this->reveal_class( $s ) ); ?>" style="display:flex;align-items:center;gap:20px;padding:24px 28px;background:linear-gradient(180deg,var(--brand-tint),#fff);border:1px solid var(--line);border-radius:calc(var(--radius) + 4px);margin-bottom:24px">
				<div style="text-align:center;padding-left:24px;border-left:1px solid var(--line)">
					<div style="font-size:48px;font-weight:800;color:var(--brand-deep);line-height:1;letter-spacing:-0.02em"><?php echo esc_html( fa_num( number_format_i18n( $average, 1 ) ) ); ?></div>
					<div style="display:flex;gap:2px;justify-content:center;margin-top:8px"><?php echo $this->stars( (int) round( $average ) ); // phpcs:ignore ?></div>
					<div style="font-size:12px;color:var(--ink-3);margin-top:6px"><?php echo esc_html( $count_label ); ?></div>
				</div>
				<div>
					<strong style="font-size:17px;color:var(--brand-deep);display:block;margin-bottom:6px"><?php echo esc_html( $s['summary_title'] ); ?></strong>
					<span style="font-size:13.5px;color:var(--ink-2)"><?php echo esc_html( $s['summary_sub'] ); ?></span>
				</div>
			</div>
			<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
				<?php
				foreach ( $reviews as $review ) :
					$rating   = (int) get_comment_meta( $review->comment_ID, 'rating', true );
					$verified = wc_review_is_from_verified_owner( $review->comment_ID );
					$ago      = sprintf(
						/* translators: %s: human-readable time difference. */
						__( '%s پیش', 'baspar-elements' ),
						human_time_diff( strtotime( $review->comment_date_gmt ), current_time( 'timestamp', true ) )
					);
					?>
					<div style="background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:22px 24px">
						<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
							<div>
								<strong style="font-size:14.5px;color:var(--brand-deep);display:block"><?php echo esc_html( $review->comment_author ); ?></strong>
								<?php if ( $verified ) : ?>
									<span style="font-size:12px;color:var(--brand)"><?php echo esc_html__( 'خریدار تأییدشده', 'baspar-elements' ); ?></span>
								<?php endif; ?>
							</div>
							<span style="font-family:var(--mono);font-size:11px;color:var(--ink-3)"><?php echo esc_html( fa_num( $ago ) ); ?></span>
						</div>
						<?php if ( $rating ) : ?>
							<div style="display:flex;gap:2px;margin-bottom:10px"><?php echo $this->stars( $rating ); // phpcs:ignore ?></div>
						<?php endif; ?>
						<p style="font-size:13.5px;color:var(--ink-2);line-height:1.7;margin:0"><?php echo esc_html( $review->comment_content ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
