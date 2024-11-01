<?php
/**
 * WCCF7_Webhook_Configuration_CF7
 *
 * @package         Webhook_Configuration_CF7
 * @subpackage      WCCF7_Webhook_Configuration_CF7
 * @since           1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Create class for the webhook configuration.
if ( ! class_exists( 'WCCF7_Webhook_Configuration_CF7' ) ) {
	/**
	 * Webhook class for the configuration method.
	 */
	class WCCF7_Webhook_Configuration_CF7 {
		/**
		 * The array of modules of plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      array    $modules    The modules to be used in this plugin.
		 */
		protected $modules = array();

		/**
		 * Define the core functionality of the plugin.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			$this->add_modules();
		}

		/**
		 * Load all the plugins modules.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function add_modules() {
			// Require module files.
			require_once plugin_dir_path( __DIR__ ) . 'modules/cf7/class-wcc-module-cf7.php';
			require_once plugin_dir_path( __DIR__ ) . 'modules/class-wcc-module-webhook.php';
			// Instantiate the Module's classes.
			$this->modules['cf7']     = new WCCF7_Module_CF7( $this );
			$this->modules['webhook'] = new WCCF7_Module_Webhook( $this );
		}
		/**
		 * Run the plugin.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			// Definitions to plugin.
			define( 'WCCF7_VERSION', '1.0.0' );
			define( 'WCCF7_PLUGIN_FILE', __FILE__ );

			// Definition of upload_dir.
			if ( ! defined( 'WCCF7_UPLOAD_DIR' ) ) {
				define( 'WCCF7_UPLOAD_DIR', 'webhook-configuration-cf7' );
			}

			// Definition of text domain.
			if ( ! defined( 'WCCF7_TEXTDOMAIN' ) ) {
				define( 'WCCF7_TEXTDOMAIN', 'webhook-configuration-cf7' );
			}
			// Running Modules (first of all).
			foreach ( $this->modules as $module ) {
				$module->run();
			}
		}
	}
}

/**
 * Activating the things with backend.
 */
$wcc_core = new WCCF7_Webhook_Configuration_CF7();
$wcc_core->run();
