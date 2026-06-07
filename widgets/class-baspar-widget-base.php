<?php
/**
 * Abstract base widget. Provides shared category, keywords and reusable control
 * helpers so each section widget can expose consistent, exhaustive styling
 * options (typography, colors, spacing, borders) with minimal repetition.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Baspar_Widget_Base
 */
abstract class Baspar_Widget_Base extends Widget_Base {

	/**
	 * Elementor category.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'baspar' );
	}

	/**
	 * Search keywords in the panel.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'baspar', 'بسپار', 'بسپارمارکت', 'chemical', 'شیمیایی' );
	}

	/**
	 * Wrap Elementor's render pipeline so a runtime error inside a single
	 * widget's render() can never white-screen the page. Errors are logged and,
	 * in the editor only, shown inline so they're easy to spot.
	 */
	public function render_content() {
		try {
			parent::render_content();
		} catch ( \Throwable $e ) {
			if ( function_exists( 'baspar_elements_log_fatal' ) ) {
				\baspar_elements_log_fatal( $e );
			}
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				printf(
					'<div style="padding:14px 16px;border:1px solid #f5c2c7;background:#f8d7da;color:#842029;border-radius:8px;font:13px/1.6 sans-serif;direction:ltr;text-align:left">%s</div>',
					esc_html( 'Baspar widget error: ' . $e->getMessage() . ' @ ' . basename( $e->getFile() ) . ':' . $e->getLine() )
				);
			}
		}
	}

	/**
	 * Safe check for Elementor edit mode (used by widgets for placeholders).
	 *
	 * @return bool
	 */
	protected function is_editor() {
		return isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Stylesheets this widget depends on.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'baspar-elements', 'baspar-elements-fonts' );
	}

	/**
	 * Scripts this widget depends on.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'baspar-elements' );
	}

	/**
	 * Add a typography group control bound to a selector.
	 *
	 * @param string $name     Control name.
	 * @param string $label    Control label.
	 * @param string $selector CSS selector (relative to {{WRAPPER}}).
	 */
	protected function add_typography( $name, $label, $selector ) {
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $name,
				'label'    => $label,
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);
	}

	/**
	 * Add a simple color control writing to `color` on the given selector.
	 *
	 * @param string $name     Control name.
	 * @param string $label    Control label.
	 * @param string $selector CSS selector.
	 * @param string $property CSS property (default: color).
	 */
	protected function add_color( $name, $label, $selector, $property = 'color' ) {
		$this->add_control(
			$name,
			array(
				'label'     => $label,
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $selector => $property . ': {{VALUE}};',
				),
			)
		);
	}

	/**
	 * Add a background group control.
	 *
	 * @param string $name     Control name.
	 * @param string $selector CSS selector.
	 */
	protected function add_background( $name, $selector ) {
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => $name,
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);
	}

	/**
	 * Add a border group control.
	 *
	 * @param string $name     Control name.
	 * @param string $selector CSS selector.
	 */
	protected function add_border( $name, $selector ) {
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => $name,
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);
	}

	/**
	 * Add a box-shadow group control.
	 *
	 * @param string $name     Control name.
	 * @param string $selector CSS selector.
	 */
	protected function add_shadow( $name, $selector ) {
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => $name,
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);
	}

	/**
	 * Add a border-radius (dimensions) control.
	 *
	 * @param string $name     Control name.
	 * @param string $label    Control label.
	 * @param string $selector CSS selector.
	 */
	protected function add_radius( $name, $label, $selector ) {
		$this->add_responsive_control(
			$name,
			array(
				'label'      => $label,
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
	}

	/**
	 * Add a padding (dimensions) control.
	 *
	 * @param string $name     Control name.
	 * @param string $label    Control label.
	 * @param string $selector CSS selector.
	 */
	protected function add_padding( $name, $label, $selector ) {
		$this->add_responsive_control(
			$name,
			array(
				'label'      => $label,
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
	}

	/**
	 * Add a margin (dimensions) control.
	 *
	 * @param string $name     Control name.
	 * @param string $label    Control label.
	 * @param string $selector CSS selector.
	 */
	protected function add_margin( $name, $label, $selector ) {
		$this->add_responsive_control(
			$name,
			array(
				'label'      => $label,
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
	}

	/**
	 * Open a "Section Spacing" style section with padding + max-width controls,
	 * shared by full-width section widgets.
	 *
	 * @param string $selector Inner container selector.
	 */
	protected function register_section_spacing_controls( $selector = '.baspar-section' ) {
		$this->start_controls_section(
			'section_spacing',
			array(
				'label' => __( 'فاصله بخش', 'baspar-elements' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_padding( 'section_padding', __( 'فاصله داخلی (Padding)', 'baspar-elements' ), $selector );
		$this->add_margin( 'section_margin', __( 'فاصله بیرونی (Margin)', 'baspar-elements' ), $selector );

		$this->end_controls_section();
	}

	/**
	 * Reusable opacity/animation note.
	 *
	 * Adds a "reveal" toggle that disables the scroll-in animation when off.
	 */
	protected function add_reveal_toggle() {
		$this->add_control(
			'enable_reveal',
			array(
				'label'        => __( 'انیمیشن ظاهرشدن هنگام اسکرول', 'baspar-elements' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'فعال', 'baspar-elements' ),
				'label_off'    => __( 'غیرفعال', 'baspar-elements' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
	}

	/**
	 * Helper: build the reveal class string from the setting.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	protected function reveal_class( $settings ) {
		return ( isset( $settings['enable_reveal'] ) && 'yes' === $settings['enable_reveal'] ) ? 'baspar-reveal' : '';
	}
}
