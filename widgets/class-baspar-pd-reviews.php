<?php
/**
 * Product reviews — rating summary with distribution, the approved reviews
 * and the review form (stars work without JavaScript).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_product;
use function BasparElements\fa_num;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Pd_Reviews extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-pd-reviews'; }
	public function get_title() { return __( 'بسپار — محصول: نظرات و امتیاز', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-review'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'نظرات خریداران' ) );
		$this->add_control( 'anchor', array( 'label' => __( 'شناسه بخش', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'pd-reviews' ) );
		$this->add_control( 'empty', array( 'label' => __( 'متن وقتی نظری نیست', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'هنوز نظری ثبت نشده است. اگر این محصول را از ما خریده‌اید، تجربه‌تان را بنویسید.' ) );
		$this->add_control( 'per_page', array( 'label' => __( 'حداکثر نظرات نمایش‌داده‌شده', 'baspar-elements' ), 'type' => Controls_Manager::NUMBER, 'default' => 20 ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	private function stars( $n ) {
		$out = '<span class="pdr-stars" aria-label="' . esc_attr( sprintf( 'امتیاز %s از ۵', fa_num( $n ) ) ) . '">';
		for ( $i = 1; $i <= 5; $i++ ) {
			$out .= '<i class="' . ( $i <= round( (float) $n ) ? 'on' : '' ) . '">' . icon_svg( 'star', 15 ) . '</i>';
		}
		return $out . '</span>';
	}

	protected function render() {
		$product = current_product();
		if ( ! $product ) {
			return;
		}
		$s       = $this->get_settings_for_display();
		$pid     = $product->get_id();
		$reviews = get_comments( array( 'post_id' => $pid, 'status' => 'approve', 'type' => array( 'review', 'comment' ), 'parent' => 0, 'number' => max( 1, (int) $s['per_page'] ) ) );
		$count   = (int) $product->get_review_count();
		$avg     = (float) $product->get_average_rating();
		$dist    = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );
		foreach ( (array) $product->get_rating_counts() as $r => $c ) {
			if ( isset( $dist[ (int) $r ] ) ) {
				$dist[ (int) $r ] = (int) $c;
			}
		}
		$total  = max( 1, array_sum( $dist ) );
		$anchor = sanitize_html_class( $s['anchor'] );
		$open   = comments_open( $pid );
		?>
		<div class="baspar-scope"<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?>>
			<section class="pdr">
				<?php if ( $s['title'] ) : ?><h2 class="pd-h"><span class="pd-h-ic"><?php echo icon_svg( 'chat', 18 ); // phpcs:ignore ?></span><?php echo esc_html( $s['title'] ); ?><?php if ( $count ) : ?><span class="pd-h-n"><?php echo esc_html( fa_num( $count ) ); ?></span><?php endif; ?></h2><?php endif; ?>
				<div class="pdr-grid">
					<div class="pdr-summary">
						<div class="pdr-avg"><strong><?php echo esc_html( fa_num( $count ? number_format( $avg, 1 ) : '—' ) ); ?></strong><span><?php echo $this->stars( $avg ); // phpcs:ignore ?><small><?php echo esc_html( sprintf( 'از %s نظر', fa_num( $count ) ) ); ?></small></span></div>
						<ul class="pdr-dist">
							<?php foreach ( $dist as $r => $c ) : ?>
								<li><span><?php echo esc_html( fa_num( $r ) ); ?></span><?php echo icon_svg( 'star', 12 ); // phpcs:ignore ?><i><b style="width:<?php echo esc_attr( round( 100 * $c / $total ) ); ?>%"></b></i><small><?php echo esc_html( fa_num( $c ) ); ?></small></li>
							<?php endforeach; ?>
						</ul>
						<?php if ( $open ) : ?><a class="btn btn-primary pdr-write" href="#pdr-form"><?php echo icon_svg( 'chat', 16 ); // phpcs:ignore ?><?php esc_html_e( 'نوشتن نظر', 'baspar-elements' ); ?></a><?php endif; ?>
					</div>
					<div class="pdr-list">
						<?php if ( ! $reviews ) : ?>
							<div class="pdr-empty"><?php echo icon_svg( 'chat', 28 ); // phpcs:ignore ?><p><?php echo esc_html( $s['empty'] ); ?></p></div>
						<?php else : ?>
							<?php foreach ( $reviews as $r ) :
								$rating   = (int) get_comment_meta( $r->comment_ID, 'rating', true );
								$verified = function_exists( 'wc_review_is_from_verified_owner' ) && wc_review_is_from_verified_owner( $r->comment_ID );
								?>
								<article class="pdr-item">
									<header>
										<span class="pdr-av"><?php echo esc_html( mb_substr( $r->comment_author, 0, 1, 'UTF-8' ) ); ?></span>
										<span class="pdr-who"><strong><?php echo esc_html( $r->comment_author ); ?></strong><small><?php echo esc_html( get_comment_date( 'j F Y', $r ) ); ?><?php if ( $verified ) : ?> · <em><?php esc_html_e( 'خریدار', 'baspar-elements' ); ?></em><?php endif; ?></small></span>
										<?php if ( $rating ) { echo $this->stars( $rating ); } // phpcs:ignore ?>
									</header>
									<div class="pdr-text"><?php echo wp_kses_post( wpautop( $r->comment_content ) ); ?></div>
								</article>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php if ( $open ) : ?>
							<div class="pdr-form" id="pdr-form">
								<?php
								$stars = '';
								if ( function_exists( 'wc_review_ratings_enabled' ) && wc_review_ratings_enabled() ) {
									$stars = '<fieldset class="pdr-rate"><legend>' . esc_html__( 'امتیاز شما', 'baspar-elements' ) . ( wc_review_ratings_required() ? ' <span class="required">*</span>' : '' ) . '</legend><div class="pdr-rate-in">';
									for ( $i = 5; $i >= 1; $i-- ) {
										$stars .= '<input type="radio" name="rating" id="pdr-r' . $i . '" value="' . $i . '"' . ( wc_review_ratings_required() && 5 === $i ? '' : '' ) . '><label for="pdr-r' . $i . '" title="' . esc_attr( fa_num( $i ) ) . '">' . icon_svg( 'star', 24 ) . '</label>';
									}
									$stars .= '</div></fieldset>';
								}
								comment_form(
									array(
										'title_reply'          => esc_html__( 'نظر خود را بنویسید', 'baspar-elements' ),
										'title_reply_to'       => esc_html__( 'پاسخ به %s', 'baspar-elements' ),
										'cancel_reply_before'  => '',
										'cancel_reply_after'   => '',
										'cancel_reply_link'    => '',
										'title_reply_before'   => '<h3 class="pdr-form-title">',
										'title_reply_after'    => '</h3>',
										'comment_notes_before' => '<p class="pdr-note">' . esc_html__( 'نشانی ایمیل شما منتشر نمی‌شود.', 'baspar-elements' ) . '</p>',
										'comment_notes_after'  => '',
										'label_submit'         => esc_html__( 'ثبت نظر', 'baspar-elements' ),
										'class_submit'         => 'btn btn-primary',
										'comment_field'        => $stars . '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'نظر شما', 'baspar-elements' ) . ' <span class="required">*</span></label><textarea id="comment" name="comment" rows="5" required></textarea></p>',
									),
									$pid
								);
								?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		</div>
		<?php
	}
}
