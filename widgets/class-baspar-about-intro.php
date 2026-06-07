<?php
/**
 * About intro — two-column layout: text + an about-visual (image, video embed
 * from Aparat/YouTube/Vimeo, or the original dashed frame with logo + mark).
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_About_Intro extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-about-intro'; }
	public function get_title() { return __( 'بسپار — معرفی شرکت (درباره)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-info-box'; }

	/**
	 * Convert an Aparat / YouTube / Vimeo watch URL to its embed URL.
	 * Returns the original string when no pattern matches so users can paste a
	 * pre-built embed URL too.
	 */
	private function to_embed_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) { return ''; }
		// Aparat: https://www.aparat.com/v/{hash}  OR  https://aparat.com/v/{hash}
		if ( preg_match( '#aparat\.com/v/([a-zA-Z0-9_-]+)#', $url, $m ) ) {
			return 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame';
		}
		// YouTube long: https://www.youtube.com/watch?v={id}
		if ( preg_match( '#youtube\.com/watch\?v=([a-zA-Z0-9_-]+)#', $url, $m ) ) {
			return 'https://www.youtube.com/embed/' . $m[1];
		}
		// YouTube short: https://youtu.be/{id}
		if ( preg_match( '#youtu\.be/([a-zA-Z0-9_-]+)#', $url, $m ) ) {
			return 'https://www.youtube.com/embed/' . $m[1];
		}
		// Vimeo: https://vimeo.com/{id}
		if ( preg_match( '#vimeo\.com/(\d+)#', $url, $m ) ) {
			return 'https://player.vimeo.com/video/' . $m[1];
		}
		// already an embed url (aparat embed / youtube embed / iframe src)
		return $url;
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'متن', 'baspar-elements' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان (<em> هایلایت)', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => 'ما <em>بسپارمارکت</em> هستیم؛ مرجع تخصصی تأمین مواد اولیه صنایع.' ) );
		$this->add_control( 'body', array( 'label' => __( 'متن (HTML)', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG, 'default' => '<p>شرکت بازرگانی <strong>کیهان بسپار نیک اندیشان</strong> با نام تجاری بسپارمارکت، در زمینه واردات، تأمین و توزیع مواد اولیه صنایع پلیمری، شیمیایی، رنگ و رزین، شوینده و غذایی فعالیت می‌کند.</p><p>هدف ما <strong>تأمین پایدار و باکیفیت</strong> با قیمت رقابتی برای واحدهای تولیدی ایران است.</p>' ) );
		$this->add_control( 'btn_text', array( 'label' => __( 'متن دکمه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تماس با ما' ) );
		$this->add_control( 'btn_link', array( 'label' => __( 'لینک دکمه', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->end_controls_section();

		$this->start_controls_section( 'visual', array( 'label' => __( 'بخش گرافیکی (راست)', 'baspar-elements' ) ) );
		$this->add_control(
			'visual_type',
			array(
				'label'   => __( 'نوع', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'image',
				'options' => array(
					'image' => __( 'تصویر (با قاب نشانی بسپار)', 'baspar-elements' ),
					'video' => __( 'ویدیو (آپارات / یوتیوب / Vimeo)', 'baspar-elements' ),
					'plain' => __( 'فقط تصویر بدون قاب', 'baspar-elements' ),
				),
			)
		);
		$this->add_control(
			'video_url',
			array(
				'label'       => __( 'آدرس ویدیو', 'baspar-elements' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'https://www.aparat.com/v/abcde',
				'description' => __( 'لینک معمولی آپارات/یوتیوب/Vimeo را بچسبانید — به‌صورت خودکار تبدیل به embed می‌شود.', 'baspar-elements' ),
				'condition'   => array( 'visual_type' => 'video' ),
			)
		);
		$this->add_control(
			'video_ratio',
			array(
				'label'   => __( 'نسبت تصویر ویدیو', 'baspar-elements' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '16/9',
				'options' => array( '16/9' => '16:9', '4/3' => '4:3', '1/1' => '1:1', '9/16' => '9:16 (ریلز)' ),
				'condition' => array( 'visual_type' => 'video' ),
			)
		);
		$this->add_control(
			'visual_image',
			array(
				'label'   => __( 'تصویر', 'baspar-elements' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => BASPAR_ELEMENTS_ASSETS . 'images/logo.png' ),
				'condition' => array( 'visual_type!' => 'video' ),
			)
		);
		$this->add_control(
			'visual_mark',
			array(
				'label'     => __( 'متن mark (داخل قاب)', 'baspar-elements' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'BASPAR MARKET',
				'condition' => array( 'visual_type' => 'image' ),
			)
		);
		$this->add_control(
			'visual_mono',
			array(
				'label'     => __( 'متن mono (داخل قاب)', 'baspar-elements' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'EST. 2020 · CHEMICAL TRADE',
				'condition' => array( 'visual_type' => 'image' ),
			)
		);
		$this->add_reveal_toggle();
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.about-intro h2' );
		$this->add_radius( 'visual_radius', __( 'گردی گوشه بخش گرافیکی', 'baspar-elements' ), '.about-visual, .bspr-video-wrap' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$type = $s['visual_type'] ? $s['visual_type'] : 'image';
		$embed = ( 'video' === $type ) ? $this->to_embed_url( $s['video_url'] ) : '';
		$ratio = $s['video_ratio'] ? $s['video_ratio'] : '16/9';
		?>
		<div class="baspar-scope">
			<div class="about-intro <?php echo esc_attr( $this->reveal_class( $s ) ); ?>" style="padding:0">
				<div>
					<h2><?php echo wp_kses_post( $s['title'] ); ?></h2>
					<?php echo wp_kses_post( $s['body'] ); ?>
					<?php if ( $s['btn_text'] ) : ?>
						<a href="<?php echo esc_url( $s['btn_link']['url'] ?? '#' ); ?>" class="btn btn-primary" style="margin-top:14px">
							<?php echo icon_svg( 'phone', 16 ); // phpcs:ignore ?>
							<?php echo esc_html( $s['btn_text'] ); ?>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( 'video' === $type ) : ?>
					<div class="bspr-video-wrap" style="position:relative;aspect-ratio:<?php echo esc_attr( $ratio ); ?>;border-radius:calc(var(--radius) + 6px);overflow:hidden;border:1px solid var(--line);background:#000;box-shadow:0 18px 40px -14px rgba(109,40,217,.18)">
						<?php if ( $embed ) : ?>
							<iframe src="<?php echo esc_url( $embed ); ?>" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture" referrerpolicy="strict-origin-when-cross-origin" loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;border:0"></iframe>
						<?php else : ?>
							<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;text-align:center;padding:20px">
								<?php echo esc_html__( 'آدرس ویدیو را در تنظیمات ویجت وارد کنید (آپارات / یوتیوب / Vimeo).', 'baspar-elements' ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php elseif ( 'plain' === $type ) : ?>
					<?php if ( ! empty( $s['visual_image']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $s['visual_image']['url'] ); ?>" alt="" style="width:100%;height:auto;border-radius:calc(var(--radius) + 6px);border:1px solid var(--line)" />
					<?php endif; ?>
				<?php else : ?>
					<div class="about-visual">
						<?php if ( ! empty( $s['visual_image']['url'] ) ) : ?>
							<img src="<?php echo esc_url( $s['visual_image']['url'] ); ?>" alt="<?php echo esc_attr( $s['visual_mark'] ); ?>" />
						<?php endif; ?>
						<?php if ( $s['visual_mark'] ) : ?><span class="mark"><?php echo esc_html( $s['visual_mark'] ); ?></span><?php endif; ?>
						<?php if ( $s['visual_mono'] ) : ?><span class="mono"><?php echo esc_html( $s['visual_mono'] ); ?></span><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
