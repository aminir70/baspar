<?php
/**
 * Product page trust strip — icon, title and short line per item.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use function BasparElements\icon_svg;
use function BasparElements\icon_options;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Pd_Features extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-pd-features'; }
	public function get_title() { return __( 'بسپار — محصول: مزایای خرید', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-icon-box'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'موارد', 'baspar-elements' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'آیکون', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'options' => icon_options(), 'default' => 'shield' ) );
		$rep->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$rep->add_control( 'text', array( 'label' => __( 'توضیح', 'baspar-elements' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'items', array(
			'label'       => __( 'موارد', 'baspar-elements' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array( 'icon' => 'award', 'title' => 'تضمین کیفیت و اصالت', 'text' => 'فروش مستقیم از شرکت' ),
				array( 'icon' => 'doc', 'title' => 'برگه آنالیز (COA)', 'text' => 'همراه هر محموله' ),
				array( 'icon' => 'tag', 'title' => 'قیمت روز و رقابتی', 'text' => 'پیش‌فاکتور رسمی' ),
				array( 'icon' => 'refresh', 'title' => 'امکان مرجوع کردن', 'text' => 'در صورت مغایرت' ),
				array( 'icon' => 'truck', 'title' => 'ارسال به سراسر ایران', 'text' => 'با باربری و پست' ),
			),
		) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => __( 'استایل', 'baspar-elements' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_color( 'accent', __( 'رنگ اصلی', 'baspar-elements' ), '', '--brand' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="baspar-scope">
			<ul class="pdf">
				<?php foreach ( (array) $s['items'] as $it ) :
					if ( empty( $it['title'] ) ) {
						continue;
					}
					?>
					<li><span class="pdf-ic"><?php echo icon_svg( $it['icon'] ? $it['icon'] : 'shield', 22 ); // phpcs:ignore ?></span><span><strong><?php echo esc_html( $it['title'] ); ?></strong><?php if ( ! empty( $it['text'] ) ) : ?><small><?php echo esc_html( $it['text'] ); ?></small><?php endif; ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
