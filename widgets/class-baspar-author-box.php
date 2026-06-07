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
		$this->add_control( 'm_avatar', array( 'label' => __( 'تصویر', 'baspar-elements' ), 'type' => Controls_Manager::MEDIA, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_name', array( 'label' => __( 'نام', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_role', array( 'label' => __( 'سمت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_bio', array( 'label' => __( 'بیو', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'condition' => array( 'source' => 'manual' ) ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'auto' === $s['source'] && get_post() ) {
			$name = get_the_author();
			$role = get_the_author_meta( 'user_login' );
			$bio = get_the_author_meta( 'description' );
			$avatar = '';
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
					<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="av" style="object-fit:cover" />
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
