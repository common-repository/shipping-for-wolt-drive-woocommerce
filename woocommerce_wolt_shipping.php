<?php
/**
 * Plugin Name: Wolt drive for WooCommerce
 * Description: Wolt drive shipping method for woocommerce
 * Version: 1.0.6
 * Text Domain: shipping-for-wolt-drive-woocommerce
 * Domain Path: /languages
 * Author: Atenzo Digital A/S
 * Author URI: https://atenzo.dk
 * Requires at least: 4.5.2
 * Tested up to: 5.9
 * WC requires at least: 3.0.0
 * WC tested up to: 6.2
 */

defined( 'WPINC' ) || exit;

if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if ( wolt_drive_is_woocommerce_active() ) {

    define( 'WOLTDIR', __DIR__ );
    define( 'WOLTPURL',plugin_dir_url( __FILE__ ) );

    /** Start main Wolt drive for WooCommerce Plugin */
    require_once WOLTDIR . '/library/_init.php';

    function wolt_drive_language_init() {
        load_plugin_textdomain('shipping-for-wolt-drive-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    wolt_drive_language_init();
}

/**
 * Is WooCommerce active
 * @return bool
 */
function wolt_drive_is_woocommerce_active() {
    return (
        class_exists('WooCommerce')
        || in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
        || is_plugin_active( 'woocommerce/woocommerce.php')
        || is_plugin_active_for_network( 'woocommerce/woocommerce.php' )
        || is_plugin_active( '__woocommerce/woocommerce.php')
        || is_plugin_active_for_network( '__woocommerce/woocommerce.php' )
    );
}
