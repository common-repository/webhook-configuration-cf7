<?php
/**
 * WCCF7_Module_CF7
 *
 * @package         Webhook_Configuration_CF7
 * @subpackage      WCCF7_Module_CF7
 * @since           1.0.0
 */

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( 'WCCF7_Module_CF7' ) ) {
	/**
	 * Module class for cf7.
	 */
	class WCCF7_Module_CF7 {

		/**
		 * The Module Indentify
		 *
		 * @since    1.0.0
		 */
		const MODULE_SLUG = 'cf7';

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
			add_filter( 'wpcf7_editor_panels', array( $this, 'wccf7_editor_panels' ) );
			add_action( 'wpcf7_save_contact_form', array( $this, 'wccf7_save_contact_form' ) );
			add_action( 'wpcf7_mail_sent', array( $this, 'wccf7_mail_sent' ), 10, 1 );

			add_filter( 'wpcf7_contact_form_properties', array( $this, 'wccf7_contact_form_properties' ), 10, 2 );
			add_filter( 'wpcf7_pre_construct_contact_form_properties', array( $this, 'wccf7_contact_form_properties' ), 10, 2 );

			// Admin Hooks.
			add_action( 'admin_notices', array( $this, 'check_cf7_plugin' ) );
		}

		/**
		 * Check Contact Form 7 Plugin is active
		 * It's a dependency in this version
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		// @codingStandardsIgnoreStart
		public function check_cf7_plugin() {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
				return;
			}
			echo '<p>'. esc_html__( 'You need to install/activate', 'webhook-configuration-cf7' );
			echo '<a href='.esc_url( 'http://contactform7.com/' ).' target="_blank"> Contact Form 7</a>';
			echo esc_html__( 'plugin to use ', 'webhook-configuration-cf7' );
			echo '<Strong>'. esc_html__( 'Webhook Configuration CF7 ', 'webhook-configuration-cf7' ). '</strong></p>';

			$screen = get_current_screen();
			if ( $screen->id == 'plugins' ) {
				echo '.</p></div>';
				return;
			}

			if ( file_exists( WP_PLUGIN_DIR  . '/contact-form-7/wp-contact-form-7.php' ) ) {
				$url = 'plugins.php';
			} else {
				$url = 'plugin-install.php?tab=search&s=Contact+form+7';
			}

			echo '. <a href="' . esc_url( admin_url( $url  )) . '">' . esc_html__( 'Do it now?', 'webhook-configuration-cf7' ) . '</a></p>';
			echo '</div>';
			// @codingStandardsIgnoreStart

		}

		/**
		 * Filter the 'wpcf7_editor_panels' to add necessary tabs
		 *
		 * @since    1.0.0.
		 * @param    array $panels     Panels in CF7 Administration.
		 */
		public function wccf7_editor_panels( $panels ) {
			$panels['webhook-html'] = array(
				'title'    => __( 'Webhook Configuration', 'webhook-configuration-cf7' ),
				'callback' => array( $this, 'webhook_config_panel_html' ),
			);

			return $panels;
		}

		/**
		 * Add Webhook Panel HTML.
		 *
		 * @since    1.0.0
		 * @param    WPCF7_ContactForm $contactform    Current ContactForm Obj.
		 */
		// @codingStandardsIgnoreStart
		public function webhook_config_panel_html( WPCF7_ContactForm $contactform ) {
			require plugin_dir_path( __FILE__ ) . 'admin/webhook-html.php';
		// @codingStandardsIgnoreEnd
		}

		/**
		 * Action 'wpcf7_save_contact_form' to save properties do Contact Form Post
		 *
		 * @since    1.0.0.
		 * @method   param   WPCF7_ContactForm $contactform    Current ContactForm Obj.
		 * @param array $contact_form Current ContactForm Obj.
		 */
		// @codingStandardsIgnoreStart
		public function wccf7_save_contact_form( $contact_form ) {
			$new_properties = array();
			if ( isset( $_POST['wcc-webhook-activate'] ) && $_POST['wcc-webhook-activate'] == '1' ) {
				// Verify nonce.
				if ( isset( $_POST['wcc_webhook_activation_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['wcc_webhook_activation_nonce'] ) ), 'wcc_webhook_activation' ) ) {
					$new_properties['activate'] = '1';
				} else {
					// Nonce is invalid, handle the error or exit.
					wp_die( 'Security check failed', 'Security Error' );
				}
			} else {
				$new_properties['activate'] = '0';
			}
			// Nonce verification
			if ( isset( $_POST['wcc_webhook_hook_url_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcc_webhook_hook_url_nonce'] ) ), 'wcc_webhook_hook_url' ) ) {
				// Nonce is valid, process the form data
				$hook_urls = array_map( function($url) {
					return esc_url_raw(trim($url));
				}, explode(PHP_EOL,  esc_url( sanitize_url( $_POST['wcc-webhook-hook-url'] ) ) ) );
				$new_properties['hook_url'] = $hook_urls;
			} else {
				// Nonce is invalid, handle the error or exit
				wp_die( 'Security check failed', 'Security Error' );
			}
		
			if ( isset( $_POST['wcc_special_mail_tags_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcc_special_mail_tags_nonce'] ) ), 'wcc_special_mail_tags_field' ) ) {
					$new_properties['special_mail_tags'] = sanitize_textarea_field( $_POST['wcc-special-mail-tags'] );
			} else {
				// Nonce is invalid, handle the error or exit
				wp_die( 'Security check failed', 'Security Error' );
			}
			
			if ( isset( $_POST['wcc_skip_tag_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['wcc_skip_tag_nonce'] ) ), 'wcc_skipping_tags' ) ) {
				$new_properties['wccf7_skip_tags'] = sanitize_textarea_field( $_POST['wwcc-skip-tag'] );
			} else {
				// Nonce is invalid, handle the error or exit
				wp_die( 'Security check failed', 'Security Error' );
			}
			
			if ( isset( $_POST['wcc_custom_headers_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcc_custom_headers_nonce'] ) ), 'wcc_custom_headers_field' ) ) {
				$new_properties['custom_headers'] = sanitize_textarea_field( $_POST['wcc-custom-headers'] );
			} else {
				// Nonce is invalid, handle the error or exit
				wp_die( 'Security check failed', 'Security Error' );
			}
			
			$properties                   = $contact_form->get_properties();
			$old_properties               = $properties[ self::METADATA ];
			$properties[ self::METADATA ] = array_merge( $old_properties, $new_properties );
			$contact_form->set_properties( $properties );
			// @codingStandardsIgnoreStart
		}

		/**
		 * Filter the 'wpcf7_contact_form_properties' to add necessary properties
		 *
		 * @since    1.0.0
		 * @param    array $properties     ContactForm Obj Properties.
		 * @param    obj   $instance       ContactForm Obj Instance.
		 */
		public function wccf7_contact_form_properties( $properties, $instance ) {
			if ( ! isset( $properties[ self::METADATA ] ) ) {
				$properties[ self::METADATA ] = array(
					'activate'          => '0',
					'wccf7_skip_tags'     => '',
					'hook_url'          => array(),
					'special_mail_tags' => '',
					'custom_headers'    => '',
				);
			}

			return $properties;
		}
	
		/**
		 * Action 'wpcf7_mail_sent' to send data to webhook.
		 *
		 * @since    1.0.0
		 * @param    obj $contact_form   ContactForm Obj.
		 */
		public function wccf7_mail_sent( $contact_form ) {
			$properties = $contact_form->prop( self::METADATA );

			if ( ! $this->submission_to_webhook( $contact_form ) ) {
				return;
			}

			$smt_data  = $this->get_data_from_special_mail_tags( $contact_form );
			$cf_data   = $this->get_data_from_contact_form( $contact_form );
			$skip_data = $this->skip_from_contact_form( $contact_form );
			$diff_data = array_diff_key( $cf_data, $skip_data );	
			$data  = array_merge( $smt_data, $diff_data );
			$errors = array();

			foreach ( (array) $properties['hook_url'] as $hook_url ) {
				// Try/Catch to support exception on request.
				try {
					/**
					 * Action: wccf7_trigger_webhook
					 *
					 * You can add your own actions to process the hook.
					 * We send it using WCCf7_Module_Webhook::pull_the_triggerred_data().
					 *
					 * @since  1.0.0
					 */
					do_action( 'wccf7_trigger_webhook', $data, $hook_url, $properties, $contact_form );
				} catch ( Exception $exception ) {
					$errors[] = array(
						'webhook'   => $hook_url,
						'exception' => $exception,
					);

					/**
					 * Filter: wccf7_trigger_webhook_error_message
					 *
					 * The 'wccf7_trigger_webhook_error_message' filter change the message in case of error.
					 * Default is CF7 error message, but you can access exception to create your own.
					 *
					 * You can ignore errors returning false:
					 * add_filter( 'wccf7_trigger_webhook_error_message', '__return_empty_string' );
					 *
					 * @since 1.4.0
					 */
					$error_message = apply_filters( 'wccf7_trigger_webhook_error_message', $contact_form->message( 'mail_sent_ng' ), $exception );

					// If empty ignore.
					if ( empty( $error_message ) ) {
						continue;
					}

					// Submission error.
					$submission = WPCF7_Submission::get_instance();
					$submission->set_status( 'mail_failed' );
					$submission->set_response( $error_message );
					break;
				}
			}

			// If empty ignore.
			if ( empty( $errors ) ) {
				return;
			}

			/**
			 * Action: wccf7_trigger_webhook_errors
			 *
			 * If we have errors, we skiped them in 'wccf7_trigger_webhook_error_message' filter.
			 * You can now submit your own error.
			 *
			 * @since  2.4.0
			 */
			do_action( 'wccf7_trigger_webhook_errors', $errors, $contact_form );
		}
		/**
		 * Skip data for webhook
		 * 
		 */
		private function skip_from_contact_form( $contact_form ) {
			$tags = array();
			$data = array();

			$properties = $contact_form->prop( self::METADATA );
			if ( ! empty( $properties['wccf7_skip_tags'] ) ) {
				$tags = self::get_wccf7_skip_tags_from_config_string( $properties['wccf7_skip_tags'] );
			}
			
			foreach ( $tags as $key => $tag ) {
				$mail_tag = new WPCF7_MailTag( sprintf( '[%s]', $tag ), $tag, '' );
				$value    = '';
				// Support to "_raw_" values. @see WPCF7_MailTag::__construct().
				if ( $mail_tag->get_option( 'do_not_heat' ) ) {
					if ( isset( $_POST['wcc_skip_tag_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcc_skip_tag_nonce'] ) ), 'wcc_skipping_tags' ) ) {
						$value = apply_filters( 'wccf7_special_mail_tags', '', $mail_tag->tag_name(), false, $mail_tag );
					} else {
						// Nonce is invalid, handle the error or exit
						wp_die( 'Security check failed', 'Security Error' );
					}
					$value = esc_html( sanitize_text_field( $_POST[ $mail_tag->field_name() ]) ) ?? '';
				}

				$value        = apply_filters( 'wccf7_special_mail_tags', $value, $mail_tag->tag_name(), false, $mail_tag );
				$data[ $key ] = $value;
			}

			/**
			 * You can filter data retrieved from Special Mail Tags with 'wccf7_skip_from_contact_form'.
			 *
			 * @param $data             Array 'field => data'
			 * @param $contact_form     ContactForm obj from 'wccf7_mail_sent' action
			 */
			return apply_filters( 'wccf7_skip_from_contact_form', $data, $contact_form );
		}
		/**
		 * Special Mail Tags from a configuration string.
		 *
		 * @since    1.3.1.
		 * @param    string $string.
		 * @return   array   $data  Array { key => tag }
		 */
		public static function get_wccf7_skip_tags_from_config_string( $string ) {
			$data = array();
			$tags = array();
			
			preg_match_all( '/\[[^\]]*]/', $string, $tags );
			$tags = ( ! empty( $tags[0] ) ) ? $tags[0] : $tags;
			
			foreach ( $tags as $tag_data ) {
				if ( ! is_string( $tag_data ) || empty( $tag_data ) ) {
					continue;
				}

				$tag_data = substr( $tag_data, 1, -1 );
				$tag_data = explode( ' ', $tag_data );

				if ( empty( $tag_data[0] ) ) {
					continue;
				}

				$tag = $tag_data[0];
				$key = ( ! empty( $tag_data[1] ) ) ? $tag_data[1] : $tag;

				if ( empty( $key ) ) {
					continue;
				}

				$data[ $key ] = $tag;
			}

			return $data;
		}
		/**
		 * Retrieve a array with data from Contact Form data
		 *
		 * @since    1.0.0
		 * @param    obj $contact_form   ContactForm Obj.
		 */
		private function get_data_from_contact_form( $contact_form ) {
			$data = array();
			if ( ! empty( $properties['wccf7_skip_tags'] ) ) {
				$wccf7_skip_tags = $properties['wccf7_skip_tags'];
			}
			// Submission.
			$submission     = WPCF7_Submission::get_instance();
			$uploaded_files = ( ! empty( $submission ) ) ? $submission->uploaded_files() : array();

			// Upload Info.
			$wp_upload_dir = wp_get_upload_dir();
			$upload_path   = WCCF7_UPLOAD_DIR . '/' . $contact_form->id() . '/' . uniqid();

			$upload_url = $wp_upload_dir['baseurl'] . '/' . $upload_path;
			$upload_dir = $wp_upload_dir['basedir'] . '/' . $upload_path;
			$tags = $contact_form->scan_form_tags();
			foreach ( $tags as $tag ) {
				if ( empty( $tag->name ) ) {
					continue;
				}
				// Regular Tags.
				$value =  ! empty( $_POST[ $tag->name ] ) ?  $_POST[$tag->name] : '';
				if ( isset( $_POST['wcc_all_tag_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcc_all_tag_nonce'] ) ), 'wcc_all_tags' ) ) {
					if ( is_array( $value ) ) {	
					foreach ( $value as $key => $v ) {
								$value[ $key ] = stripslashes( $v );
							}
					}  else {
						// Nonce is invalid, handle the error or exit
						wp_die( 'Security check failed', 'Security Error' );
					}
				}

				if ( is_string( $value ) ) {
					$value = stripslashes( $value );
				}

				// Files.
				if ( $tag->basetype === 'file' && ! empty( $uploaded_files[ $tag->name ] ) ) {
					$files = $uploaded_files[ $tag->name ];

					$copied_files = array();
					foreach ( (array) $files as $file ) {
						wp_mkdir_p( $upload_dir );
						$target_dir = $wp_upload_dir['basedir'] . '/' . $upload_path;
						if ( ! file_exists( $target_dir ) ) {
							mkdir( $target_dir, 0755, true );
					   	}
						// Define the target directory within the uploads directory.
						$filename = wp_unique_filename( $upload_dir, $tag->name . '-' . basename( $file ) );

						if ( ! copy( $file, $upload_dir . '/' . $filename ) ) {
							$submission = WPCF7_Submission::get_instance();
							$submission->set_status( 'mail_failed' );
							$submission->set_response( $contact_form->message( 'upload_failed' ) );
							continue;
						}

						$copied_files[] = $upload_url . '/' . $filename;
					}

					$value = $copied_files;

					if ( count( $value ) === 1 ) {
						$value = $value[0];
					}
				}

				// Support to Pipes.
				$pipes = $tag->pipes;
				if ( WPCF7_USE_PIPE && $pipes instanceof WPCF7_Pipes && ! $pipes->zero() ) {
					if ( is_array( $value ) ) {
						$new_value = array();
						foreach ( $value as $v ) {
							$new_value[] = $pipes->do_pipe( wp_unslash( $v ) );
						}
						$value = $new_value;
					} else {
						$value = $pipes->do_pipe( wp_unslash( $value ) );
					}
				}
				
				// Support to Free Text on checkbox and radio.
				if ( $tag->has_option( 'free_text' ) && in_array( $tag->basetype, array( 'checkbox', 'radio' ) ) ) {
					$free_text_label = end( $tag->values );
					$free_text_name  = wp_verify_nonce( $tag->name . '_free_text' );
					$free_text_value = ( ! empty( $_POST[ $free_text_name ] ) ) ? $_POST[ $free_text_name ] : '';

					if ( is_array( $value ) ) {
						foreach ( $value as $key => $v ) {
							if ( $v !== $free_text_label ) {
								continue;
							}
							$value[ $key ] = stripslashes( $free_text_value );
						}
					}

					if ( is_string( $value ) && $value === $free_text_label ) {
						$value = stripslashes( $free_text_value );
					}
				}
				// Support to "webhook" option (rename field value).
				$key         = $tag->name;
				$webhook_key = $tag->get_option( 'Webhook_Configuration_CF7' );
				if ( ! empty( $webhook_key ) && ! empty( $webhook_key[0] && $tags != $wccf7_skip_tags) ) {
					$key = $webhook_key[0];
				}
				$data[ $key ] = $value;
			}
			/**
			 * You can filter data retrieved from Contact Form tags with 'wccf7_get_data_from_contact_form'
			 *
			 * @param $data             Array 'field => data'
			 * @param $contact_form     ContactForm obj from 'wccf7_mail_sent' action
			 */
			return apply_filters( 'wccf7_get_data_from_contact_form', $data, $contact_form );
		}

		/**
		 * Retrieve a array with data from Special Mail Tags
		 *
		 * @link https://contactform7.com/special-mail-tags
		 *
		 * @since    1.3.0
		 * @param    obj $contact_form   ContactForm Obj.
		 */
		private function get_data_from_special_mail_tags( $contact_form ) {
			$tags = array();
			$data = array();
			$properties = $contact_form->prop( self::METADATA );
			if ( ! empty( $properties['special_mail_tags'] ) ) {
				$tags = self::get_special_mail_tags_from_config_string( $properties['special_mail_tags'] );
			}

			foreach ( $tags as $key => $tag ) {
				$mail_tag = new WPCF7_MailTag( sprintf( '[%s]', $tag ), $tag, '' );
				$value    = '';

				// Support to "_raw_" values. @see WPCF7_MailTag::__construct().
				if ( $mail_tag->get_option( 'do_not_heat' ) ) {
					if ( isset( $_POST['wcc_special_mail_tags_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wcc_special_mail_tags_nonce'] ) ), 'wcc_special_mail_tags_field' ) ) {
						// Contact form 7 filter to get special mail tag data.
						$value = apply_filters( 'wpcf7_special_mail_tags', '', $mail_tag->tag_name(), false, $mail_tag );
						$value = esc_html( sanitize_text_field( $_POST[ $mail_tag->field_name() ] ) ) ?? '';
					} else {
						// Nonce is invalid, handle the error or exit
						wp_die( 'Security check failed', 'Security Error' );
					}
				}
				// Contact form 7 filter to get special mail tag data value.
				$value        = apply_filters( 'wpcf7_special_mail_tags', $value, $mail_tag->tag_name(), false, $mail_tag );
				$data[ $key ] = $value;
			}

			/**
			 * You can filter data retrieved from Special Mail Tags with 'wccf7_get_data_from_special_mail_tags'.
			 *
			 * @param $data             Array 'field => data'
			 * @param $contact_form     ContactForm obj from 'wccf7_mail_sent' action
			 */
			return apply_filters( 'wccf7_get_data_from_special_mail_tags', $data, $contact_form );
		}

		/**
		 * Check we can submit a form to Zapier.
		 *
		 * @since    1.0.0
		 * @param    obj $contact_form   ContactForm Obj.
		 */
		private function submission_to_webhook( $contact_form ) {
			$properties = $contact_form->prop( self::METADATA );

			if ( empty( $properties ) || empty( $properties['activate'] ) || empty( $properties['hook_url'] ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Special Mail Tags from a configuration string.
		 *
		 * @since    1.3.1.
		 * @param    string $string.
		 * @return   array   $data  Array { key => tag }
		 */
		public static function get_special_mail_tags_from_config_string( $string ) {
			$data = array();
			$tags = array();
			
			preg_match_all( '/\[[^\]]*]/', $string, $tags );
			$tags = ( ! empty( $tags[0] ) ) ? $tags[0] : $tags;

			foreach ( $tags as $tag_data ) {
				if ( ! is_string( $tag_data ) || empty( $tag_data ) ) {
					continue;
				}

				$tag_data = substr( $tag_data, 1, -1 );
				$tag_data = explode( ' ', $tag_data );

				if ( empty( $tag_data[0] ) ) {
					continue;
				}

				$tag = $tag_data[0];
				$key = ( ! empty( $tag_data[1] ) ) ? $tag_data[1] : $tag;

				if ( empty( $key ) ) {
					continue;
				}

				$data[ $key ] = $tag;
			}

			return $data;
		}

		/**
		 * Run the module.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->define_hooks();
		}
	}
}
