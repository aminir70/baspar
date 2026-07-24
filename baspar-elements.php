<?php
/**
 * Plugin Name: Baspar Elements
 * Plugin URI:  https://github.com/shirazdm509-sys/basparweb
 * Description: مجموعه ویجت‌های اختصاصی المنتور برای سایت بسپارمارکت — هدر، هیرو، دسته‌بندی، محصولات (ووکامرس)، وبلاگ، فوتر و... . هر بخش یک ویجت جدا، کاملاً قابل تغییر (فونت، رنگ، لوگو، عکس) و با خواندن پویای منو/مقالات/محصولات از سایت.
 * Version:     1.2.1
 * Author:      Baspar Market
 * Author URI:  https://basparmarket.com
 * Text Domain: baspar-elements
 * Domain Path: /languages
 * Requires Plugins: elementor
 * Elementor tested up to: 3.25.0
 * Elementor Pro tested up to: 3.25.0
 *
 * @package BasparElements
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'BASPAR_ELEMENTS_VERSION', '1.2.1' );
define( 'BASPAR_ELEMENTS_FILE', __FILE__ );
define( 'BASPAR_ELEMENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'BASPAR_ELEMENTS_URL', plugin_dir_url( __FILE__ ) );
define( 'BASPAR_ELEMENTS_ASSETS', BASPAR_ELEMENTS_URL . 'assets/' );

/**
 * Minimum requirements.
 */
define( 'BASPAR_ELEMENTS_MIN_ELEMENTOR', '3.5.0' );
define( 'BASPAR_ELEMENTS_MIN_PHP', '7.4' );

/**
 * Bootstrap the plugin once all plugins are loaded so we can check for Elementor.
 */
function baspar_elements_load() {
	// Check Elementor is active.
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'baspar_elements_missing_elementor_notice' );
		return;
	}

	// Check Elementor version.
	if ( ! version_compare( ELEMENTOR_VERSION, BASPAR_ELEMENTS_MIN_ELEMENTOR, '>=' ) ) {
		add_action( 'admin_notices', 'baspar_elements_minimum_elementor_notice' );
		return;
	}

	// Check PHP version.
	if ( version_compare( PHP_VERSION, BASPAR_ELEMENTS_MIN_PHP, '<' ) ) {
		add_action( 'admin_notices', 'baspar_elements_minimum_php_notice' );
		return;
	}

	// Defensive bootstrap: a fatal here must never white-screen the whole site.
	// Any error is logged and surfaced as an admin notice instead.
	try {
		require_once BASPAR_ELEMENTS_PATH . 'includes/class-plugin.php';
		\BasparElements\Plugin::instance();
	} catch ( \Throwable $e ) {
		baspar_elements_log_fatal( $e );
		add_action(
			'admin_notices',
			function () use ( $e ) {
				printf(
					'<div class="notice notice-error"><p><strong>Baspar Elements:</strong> %s</p></div>',
					esc_html( $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() )
				);
			}
		);
	}
}
add_action( 'plugins_loaded', 'baspar_elements_load' );

/**
 * Write a Throwable to the PHP/WordPress error log with a recognizable prefix.
 *
 * @param \Throwable $e Caught throwable.
 */
function baspar_elements_log_fatal( $e ) {
	if ( function_exists( 'error_log' ) ) {
		error_log( '[Baspar Elements] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}

/**
 * Admin notice: Elementor not installed/active.
 */
function baspar_elements_missing_elementor_notice() {
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
	$message = esc_html__( 'افزونه «Baspar Elements» برای کار کردن به افزونه Elementor نیاز دارد. لطفاً ابتدا Elementor را نصب و فعال کنید.', 'baspar-elements' );
	printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Admin notice: Elementor version too low.
 */
function baspar_elements_minimum_elementor_notice() {
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
	$message = sprintf(
		/* translators: %s: minimum Elementor version */
		esc_html__( 'افزونه «Baspar Elements» به Elementor نسخه %s یا بالاتر نیاز دارد.', 'baspar-elements' ),
		BASPAR_ELEMENTS_MIN_ELEMENTOR
	);
	printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Admin notice: PHP version too low.
 */
function baspar_elements_minimum_php_notice() {
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
	$message = sprintf(
		/* translators: %s: minimum PHP version */
		esc_html__( 'افزونه «Baspar Elements» به PHP نسخه %s یا بالاتر نیاز دارد.', 'baspar-elements' ),
		BASPAR_ELEMENTS_MIN_PHP
	);
	printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
