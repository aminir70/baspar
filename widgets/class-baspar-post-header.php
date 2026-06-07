<?php
/**
 * Post header — pill + h1 + meta row (author / date / views / read time).
 * For Theme Builder Single Post, this reads from current post.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Post_Header extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-post-header'; }
	public function get_title() { return __( 'بسپار — هدر پست', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-post-title'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'source', array(
			'label' => __( 'منبع', 'baspar-elements' ),
			'type' => Controls_Manager::SELECT, 'default' => 'auto',
			'options' => array( 'auto' => 'پست فعلی (داینامیک)', 'manual' => 'دستی' ),
		) );
		$this->add_control( 'm_pill', array( 'label' => __( 'برچسب', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'POLYMER · مقاله فنی', 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_author', array( 'label' => __( 'نویسنده', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_role', array( 'label' => __( 'سمت', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_date', array( 'label' => __( 'تاریخ', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_views', array( 'label' => __( 'بازدید', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'm_read', array( 'label' => __( 'زمان مطالعه', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'source' => 'manual' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.post-title' );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'auto' === $s['source'] && get_post() ) {
			$cats = get_the_category();
			$pill = ( $cats && ! is_wp_error( $cats ) ) ? strtoupper( $cats[0]->slug ) . ' · مقاله' : 'مقاله';
			$title = get_the_title();
			$author = get_the_author();
			$role = get_the_author_meta( 'description' );
			$date = get_the_date( 'j F Y' );
			$views = '';
			$read = ceil( str_word_count( strip_tags( get_the_content() ) ) / 200 ) . ' دقیقه';
		} else {
			$pill = $s['m_pill'];
			$title = $s['m_title'] ? $s['m_title'] : 'عنوان مقاله';
			$author = $s['m_author'] ? $s['m_author'] : 'نویسنده';
			$role = $s['m_role'];
			$date = $s['m_date'] ? $s['m_date'] : '۱۲ خرداد';
			$views = $s['m_views'];
			$read = $s['m_read'] ? $s['m_read'] : '۵ دقیقه';
		}
		$initial = mb_substr( $author, 0, 1, 'UTF-8' );
		?>
		<div class="baspar-scope">
			<div class="post-pill mono"><?php echo esc_html( $pill ); ?></div>
			<h1 class="post-title"><?php echo esc_html( $title ); ?></h1>
			<div class="post-meta-row">
				<span class="author">
					<span class="au-av"><?php echo esc_html( $initial ); ?></span>
					<span><strong><?php echo esc_html( $author ); ?></strong>
					<?php if ( $role ) : ?><span class="au-role mono"><?php echo esc_html( $role ); ?></span><?php endif; ?>
					</span>
				</span>
				<span class="sep"></span>
				<span><?php echo icon_svg( 'clock', 14 ); // phpcs:ignore ?> <?php echo esc_html( $date ); ?></span>
				<?php if ( $views ) : ?><span class="sep"></span><span><?php echo icon_svg( 'eye', 14 ); // phpcs:ignore ?> <?php echo esc_html( $views ); ?></span><?php endif; ?>
				<span class="sep"></span>
				<span><?php echo icon_svg( 'users', 14 ); // phpcs:ignore ?> <?php echo esc_html( $read ); ?></span>
			</div>
		</div>
		<?php
	}
}
