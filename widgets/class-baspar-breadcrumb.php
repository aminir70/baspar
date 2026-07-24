<?php
/**
 * Breadcrumb hero — dark purple banner with kicker, title, sub and a path.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Breadcrumb
 */
class Baspar_Breadcrumb extends Baspar_Widget_Base {

	public function get_name() {
		return 'baspar-breadcrumb';
	}

	public function get_title() {
		return __( 'بسپار — هدر بردکرامب', 'baspar-elements' );
	}

	public function get_icon() {
		return 'eicon-navigation-horizontal';
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'ABOUT US' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'درباره ما' ) );
		$this->add_control( 'sub', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::WYSIWYG ) );

		$rep = new Repeater();
		$rep->add_control( 'text', array( 'label' => __( 'متن', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'link', array( 'label' => __( 'لینک (خالی=صفحه فعلی)', 'baspar-elements' ), 'type' => Controls_Manager::URL ) );
		$this->add_control(
			'path',
			array(
				'label'       => __( 'مسیر', 'baspar-elements' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array(
					array( 'text' => 'صفحه اصلی', 'link' => array( 'url' => '/' ) ),
					array( 'text' => 'درباره ما', 'link' => array( 'url' => '' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_background( 'bg', '.bcrumb-hero' );
		$this->add_typography( 'title_typo', __( 'تایپوگرافی عنوان', 'baspar-elements' ), '.bcrumb-hero h1' );
		$this->add_padding( 'pad', __( 'فاصله داخلی', 'baspar-elements' ), '.bcrumb-hero' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<section class="bcrumb-hero">
				<div class="bcrumb-grid-bg" aria-hidden="true"></div>
				<div class="bcrumb-inner">
					<?php if ( $s['kicker'] ) : ?><div class="bcrumb-kicker mono"><?php echo esc_html( $s['kicker'] ); ?></div><?php endif; ?>
					<h1><?php echo esc_html( $s['title'] ); ?></h1>
					<?php if ( $s['sub'] ) : ?><div class="rich-text"><?php echo wp_kses_post( $s['sub'] ); ?></div><?php endif; ?>
					<nav class="bcrumb-nav">
						<?php
						$count = count( (array) $s['path'] );
						foreach ( (array) $s['path'] as $idx => $it ) {
							if ( $idx > 0 ) {
								echo '<span class="bcrumb-sep">/</span>';
							}
							$url = ! empty( $it['link']['url'] ) ? $it['link']['url'] : '';
							if ( $url && $idx < $count - 1 ) {
								echo '<a href="' . esc_url( $url ) . '">' . esc_html( $it['text'] ) . '</a>';
							} else {
								echo '<span class="current">' . esc_html( $it['text'] ) . '</span>';
							}
						}
						?>
					</nav>
				</div>
			</section>
		</div>
		<?php
	}
}
