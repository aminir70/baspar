<?php
/**
 * Comments for the current post (WordPress comment list + form) in the
 * design's styling.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\current_blog_post;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Post_Comments extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-post-comments'; }
	public function get_title() { return __( 'بسپار — دیدگاه‌ها', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-comments'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'تنظیمات', 'baspar-elements' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => __( 'دیدگاه‌ها و فرم ارسال دیدگاه همین نوشته نمایش داده می‌شود (اگر دیدگاه برای نوشته باز باشد).', 'baspar-elements' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! is_singular( 'post' ) ) {
			if ( $this->is_editor() ) {
				echo '<div class="baspar-scope"><div class="bspr-comments-ph">' . esc_html__( 'دیدگاه‌های مقاله اینجا نمایش داده می‌شود.', 'baspar-elements' ) . '</div></div>';
			}
			return;
		}
		$post = current_blog_post();
		if ( ! $post || ( ! comments_open( $post ) && ! get_comments_number( $post ) ) ) {
			return;
		}
		echo '<div class="baspar-scope"><div class="bspr-comments" id="comments">';
		comments_template();
		echo '</div></div>';
	}
}
