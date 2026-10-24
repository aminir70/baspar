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
		$initial = mb_substr( $name, 0, 1, 'UTF-8' );
		?>
		<div class="baspar-scope">
			<div class="post-author">
				<?php if ( $avatar ) : ?>
					<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="av" />
				<?php else : ?>
					<div class="av"><?php echo esc_html( $initial ); ?></div>
				<?php endif; ?>
				<div class="info">
					<strong><?php echo esc_html( $name ); ?></strong>
					<?php if ( $role ) : ?><span class="mono"><?php echo esc_html( $role ); ?></span><?php endif; ?>
					<?php if ( $bio ) : ?><p><?php echo esc_html( $bio ); ?></p><?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
