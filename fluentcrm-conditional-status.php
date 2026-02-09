<?php
/**
 * Plugin Name: FluentCRM Subscriber Status for FluentForms
 * Plugin URI: https://github.com/Nicscott01/fluentcrm-conditional-status
 * Description: Conditionally set FluentCRM subscriber status based on FluentForms field values. Set subscribers to Transactional, Pending, or Subscribed status based on form inputs like opt-in checkboxes.
 * Version: 1.0.0
 * Author: Nic Scott
 * Author URI: https://www.crearewebsolutions.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fluentcrm-conditional-status
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Requires Plugins: fluentform, fluent-crm
 *
 * @package FluentCRM_Conditional_Status
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'FLUENTCRM_CONDITIONAL_STATUS_VERSION', '1.0.0' );
define( 'FLUENTCRM_CONDITIONAL_STATUS_FILE', __FILE__ );
define( 'FLUENTCRM_CONDITIONAL_STATUS_PATH', plugin_dir_path( __FILE__ ) );
define( 'FLUENTCRM_CONDITIONAL_STATUS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class.
 */
class FluentCRM_Conditional_Status {

	/**
	 * Single instance of the class.
	 *
	 * @var FluentCRM_Conditional_Status
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return FluentCRM_Conditional_Status
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'admin_notices', array( $this, 'check_dependencies' ) );
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		// Check if required plugins are active.
		if ( ! $this->are_dependencies_active() ) {
			return;
		}

		// Load plugin files.
		$this->load_files();

		// Load text domain.
		load_plugin_textdomain( 'fluentcrm-conditional-status', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Check if dependencies are active.
	 *
	 * @return bool
	 */
	private function are_dependencies_active() {
		return defined( 'FLUENTFORM' ) && defined( 'FLUENTCRM' );
	}

	/**
	 * Display admin notice if dependencies are not met.
	 */
	public function check_dependencies() {
		if ( $this->are_dependencies_active() ) {
			return;
		}

		$missing = array();
		if ( ! defined( 'FLUENTFORM' ) ) {
			$missing[] = '<strong>Fluent Forms</strong>';
		}
		if ( ! defined( 'FLUENTCRM' ) ) {
			$missing[] = '<strong>FluentCRM</strong>';
		}

		if ( ! empty( $missing ) ) {
			$message = sprintf(
				/* translators: %s: comma-separated list of missing plugin names */
				__( 'FluentCRM Conditional Status requires %s to be installed and activated.', 'fluentcrm-conditional-status' ),
				implode( ' ' . __( 'and', 'fluentcrm-conditional-status' ) . ' ', $missing )
			);
			?>
			<div class="notice notice-error">
				<p><?php echo wp_kses_post( $message ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Load required files.
	 */
	private function load_files() {
		require_once FLUENTCRM_CONDITIONAL_STATUS_PATH . 'includes/class-feed-settings.php';
		require_once FLUENTCRM_CONDITIONAL_STATUS_PATH . 'includes/class-submission-handler.php';

		// Initialize classes.
		FluentCRM_Conditional_Status_Feed_Settings::instance();
		FluentCRM_Conditional_Status_Submission_Handler::instance();
	}
}

/**
 * Initialize the plugin.
 *
 * @return FluentCRM_Conditional_Status
 */
function fluentcrm_conditional_status() {
	return FluentCRM_Conditional_Status::instance();
}

// Start the plugin.
fluentcrm_conditional_status();
