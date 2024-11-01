<?php
/**
 * Webhook_Configuration_CF7
 *
 * @package         Webhook_Configuration_CF7
 * @subpackage      WCCF7_Module_Webhook
 * @since           1.0.0
 */

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );
// Create class for the webhook configuration.
if ( ! class_exists( 'WCCF7_Module_Webhook' ) ) {
	/**
	 * Webhook class for the configuration method.
	 */
	class WCCF7_Module_Webhook {

		/**
		 * The Module Indentify
		 *
		 * @since    1.0.0
		 */
		const MODULE_SLUG = 'webhook';

		/**
		 * Metadata identifier
		 *
		 * @since    1.0.0
		 */
		const METADATA = 'wccf7_configuration';

		/**
		 * Register all the hooks for this module
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_hooks() {
			add_action( 'wccf7_trigger_webhook', array( $this, 'pull_the_triggerred_data' ), 10, 5 );
		}
		/**
		 * Send data to Zapier
		 *
		 * @since    1.0.0
		 * @access   private
		 * @param array $data   fetch data on submission.
		 * @param array $hook_url   hook the webhook url on submission.
		 * @param array $properties   fetch properties on submission.
		 * @param array $contact_form   fetch contact form details on submission.
		 * @throws Exception  If invalid order item.
		 */
		public function pull_the_triggerred_data( array $data, $hook_url, $properties, $contact_form ) {
			/**
			 * Filter: wccf7_ignore_default_webhook
			 *
			 * The 'wccf7_ignore_default_webhook' filter can be used to ignore
			 * core request, if you want to trigger your own request.
			 *
			 * add_filter( 'wccf7_ignore_default_webhook', '__return_true' );
			 *
			 * @since    2.3.0
			 */
			if ( apply_filters( 'wccf7_ignore_default_webhook', false ) ) {
				return;
			}

			$args = array(
				'method'  => 'POST',
				'body'    => wp_json_encode( $data ),
				'headers' => $this->create_headers( $properties['custom_headers'] ?? '' ),
			);

			/**
			 * Filter: wccf7_hook_url
			 *
			 * The 'wccf7_hook_url' filter webhook URL so developers can use form
			 * data or other information to change webhook URL.
			 *
			 * @since    2.1.4
			 */
			$hook_url = apply_filters( 'wccf7_hook_url', $hook_url, $data );

			/**
			 * Filter: wccf7_post_request_args
			 *
			 * The 'wccf7_post_request_args' filter POST args so developers
			 * can modify the request args if any service demands a particular header or body.
			 *
			 * @since    1.1.0
			 */
			$result = wp_remote_post( $hook_url, apply_filters( 'wccf7_post_request_args', $args, $properties, $contact_form ) );

			// If result is a WP Error, throw a Exception woth the message.
			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			}

			/**
			 * Action: wccf7_post_request_result
			 *
			 * You can perform a action with the result of the request.
			 * By default we do nothing but you can throw a Exception in webhook errors.
			 *
			 * @since    1.4.0
			 */
			do_action( 'wccf7_post_request_result', $result, $hook_url );
		}

		/**
		 * Run the module.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->define_hooks();
		}

		/**
		 * Get headers to request.
		 *
		 * @param array $custom fetch headers data on submission.
		 * @since 2.3.0
		 */
		public function create_headers( $custom ) {
			$headers      = array( 'Content-Type' => 'application/json' );
			$blog_charset = get_option( 'blog_charset' );
			if ( ! empty( $blog_charset ) ) {
				$headers['Content-Type'] .= '; charset=' . get_option( 'blog_charset' );
			}
			$custom = explode( "\n", $custom );
			foreach ( $custom as $header ) {
				$header = explode( ':', $header, 2 );
				$header = array_map( 'trim', $header );

				if ( count( $header ) === 2 ) {
					$headers[ $header[0] ] = $header[1];
				}
			}
			return $headers;
		}
	}
}
