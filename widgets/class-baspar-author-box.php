<?php
/**
 * Author box — avatar (circle with initial) + name + role + bio.
 * Reads from current post author if available.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Author_Box extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-author-box'; }
	public function get_title() { return __( 'بسپار — کارت نویسنده', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-user-circle-o'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'source', array(
			'label' => __( 'منبع', 'baspar-elements' ),
			'type' => Controls_Manager::SELECT, 'default' => 'auto',
			'options' => array( 'auto' => 'نویسنده پست فعلی', 'manual' => 'دستی' ),
		) );
		$this->add_control( 'auto_role', array( 'label' => __( 'عنوان زیر نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'تیم محتوای فنی بسپارمارکت', 'condition' => array( 'source' => 'auto' ) ) );
		$this->add_control( 'auto_bio', array( 'label' => __( 'معرفی (اگر نویسنده «اطلاعات زندگی‌نامه» نداشته باشد)', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => 'مقالات بسپارمارکت را کارشناسان فروش و فنی ما بر اساس تجربه واقعی تأمین مواد اولیه برای کارخانه‌ها می‌نویسند.', 'condition' => array( 'source' => 'auto' ) ) );
		$this->add_control( 'm_avatar', array( 'label' => __( 'تصویر', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_role', array( 'label' => __( 'سمت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_bio', array( 'label' => __( 'بیو', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'condition' => array( 'source' => 'manual' ) ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'auto' === $s['source'] && get_post() ) {
			// Never print the login name (it was shown here before 1.5.0).
			$aid    = (int) get_post_field( 'post_author', get_post() );
			$name   = get_the_author_meta( 'display_name', $aid );
			$role   = $s['auto_role'] ?? '';
			$bio    = get_the_author_meta( 'description', $aid );
			$bio    = $bio ? $bio : ( $s['auto_bio'] ?? '' );
			$avatar = get_avatar_url( $aid, array( 'size' => 128 ) );
		} else {
			$name = $s['m_name'] ? $s['m_name'] : 'نویسنده';
			$role = $s['m_role'];
			$bio = $s['m_bio'];
			$avatar = $s['m_avatar']['url'] ?? '';
		}
		$initial  = mb_substr( $name, 0, 1, 'UTF-8' );
		$bio_html = $this->clean_bio( (string) $bio );
		$long     = mb_strlen( wp_strip_all_tags( $bio_html ), 'UTF-8' ) > 230;
		$archive  = ( 'auto' === $s['source'] && ! empty( $aid ) ) ? get_author_posts_url( $aid ) : '';
		?>
		<div class="baspar-scope">
			<div class="post-author">
				<?php if ( $avatar ) : ?>
					<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="av" width="84" height="84" loading="lazy" />
				<?php else : ?>
					<div class="av"><?php echo esc_html( $initial ); ?></div>
				<?php endif; ?>
				<div class="info">
					<strong><?php echo esc_html( $name ); ?></strong>
					<?php if ( $role ) : ?><span class="au-role"><?php echo esc_html( $role ); ?></span><?php endif; ?>
					<?php if ( $bio_html ) : ?>
						<div class="au-bio<?php echo $long ? ' is-clamped' : ''; ?>" data-au-bio><?php echo $bio_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized in clean_bio(). ?></div>
						<?php if ( $long ) : ?><button type="button" class="au-more" data-au-more aria-expanded="false"><span data-more><?php esc_html_e( 'ادامه معرفی', 'baspar-elements' ); ?></span><span data-less><?php esc_html_e( 'کمتر', 'baspar-elements' ); ?></span></button><?php endif; ?>
					<?php endif; ?>
					<?php if ( $archive ) : ?><a class="au-posts" href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'مقالات این نویسنده', 'baspar-elements' ); ?></a><?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * The author biography is often pasted with HTML (and ✓ marks). Decode it,
	 * keep a few safe inline tags, turn every ✓ into its own line and close
	 * any unbalanced tags so the box can never break the layout.
	 *
	 * @param string $raw Biography as stored.
	 * @return string
	 */
	private function clean_bio( $raw ) {
		$raw = html_entity_decode( $raw, ENT_QUOTES, 'UTF-8' );
		$raw = wp_kses( $raw, array( 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'br' => array(), 'a' => array( 'href' => true, 'rel' => true, 'target' => true ) ) );
		$raw = preg_replace( '#<(strong|b|em|i)>\s*</\1>#i', '', $raw );
		$raw = preg_replace( '#(?<=\S)\s*(<(strong|b)>[^<]{2,40}[:：]\s*</\2>)#u', '<br>$1', $raw );
		$raw = preg_replace( '#\s*[✓✔☑]\s*#u', '<br><span class="au-tick" aria-hidden="true">✓</span> ', $raw );
		$raw = preg_replace( '#^(\s*<br\s*/?>)+#i', '', $raw );
		return trim( force_balance_tags( $raw ) );
	}
}
