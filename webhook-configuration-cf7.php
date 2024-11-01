<?php
/**
 * Webhook Configuration CF7
 *
 * @package       Webhook_Configuration_CF7
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Webhook Configuration CF7
 * Requires Plugins: contact-form-7
 * Plugin URI:    https://wordpress.org/plugins/webhook-configuration-cf7
 * Description:   Plugin helps to send data of cf7 to custom webhooks
 * Version:       1.0.0
 * Author:        Ritu Trivedi
 * Author URI:    https://profiles.wordpress.org/ritu23/
 * Text Domain:   webhook-configuration-cf7
 * License:       GPL v2 or later
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 * Tags:          contact form, integration, contact form 7, webhook, webhook configuration
 */

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) || die();

// Include the main WooCommerce class.
if ( ! class_exists( 'WCCF7_Webhook_Configuration_CF7', false ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-webhook-configuration-cf7.php';
}
