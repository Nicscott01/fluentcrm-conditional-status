<?php
/**
 * Feed Settings Class
 *
 * Adds status mapping options to FluentCRM feed configuration.
 *
 * @package FluentCRM_Conditional_Status
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentCRM Conditional Status Feed Settings.
 */
class FluentCRM_Conditional_Status_Feed_Settings {

	/**
	 * Single instance of the class.
	 *
	 * @var FluentCRM_Conditional_Status_Feed_Settings
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return FluentCRM_Conditional_Status_Feed_Settings
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
		// Run after FluentCRM registers its default field set so our changes stick.
		add_filter( 'fluentform/get_integration_settings_fields_fluentcrm', array( $this, 'add_status_mapping_fields' ), 20, 2 );
	}

	/**
	 * Add status mapping option into FluentCRM's "Other Fields" mapper.
	 *
	 * @param array $settings_fields Existing settings fields.
	 * @param int   $form_id         Form ID.
	 * @return array
	 */
	public function add_status_mapping_fields( $settings_fields, $form_id ) {
		if ( isset( $settings_fields['fields'] ) && is_array( $settings_fields['fields'] ) ) {
			$fields_array = &$settings_fields['fields'];
		} else {
			$fields_array = &$settings_fields;
		}

		if ( ! is_array( $fields_array ) ) {
			return $settings_fields;
		}

		$other_fields_index = $this->find_setting_index( $fields_array, 'other_fields' );
		if ( false === $other_fields_index ) {
			return $settings_fields;
		}

		if ( empty( $fields_array[ $other_fields_index ]['options'] ) || ! is_array( $fields_array[ $other_fields_index ]['options'] ) ) {
			$fields_array[ $other_fields_index ]['options'] = array();
		}

		if ( ! isset( $fields_array[ $other_fields_index ]['options']['status'] ) ) {
			$fields_array[ $other_fields_index ]['options'] = array_merge(
				array(
					'status' => __( 'Subscriber Status (Mapped Value)', 'fluentcrm-conditional-status' ),
				),
				$fields_array[ $other_fields_index ]['options']
			);
		}

		$mapping_info_key = 'fcs_status_mapping_info';
		$mapping_info_pos = $this->find_setting_index( $fields_array, $mapping_info_key );
		if ( false === $mapping_info_pos ) {
			$mapping_info = array(
				'key'          => $mapping_info_key,
				'require_list' => false,
				'label'        => __( 'Status Mapping', 'fluentcrm-conditional-status' ),
				'component'    => 'html_info',
				'html_info'    => '<p><strong>' . esc_html__( 'Status mapping:', 'fluentcrm-conditional-status' ) . '</strong> ' . esc_html__( 'In "Other Fields", map Contact Property "Subscriber Status (Mapped Value)" to a form field/smartcode that resolves to a valid status slug (e.g. subscribed, pending, transactional).', 'fluentcrm-conditional-status' ) . '</p>'
					. '<p><strong>' . esc_html__( 'Smartcode fallback:', 'fluentcrm-conditional-status' ) . '</strong> ' . esc_html__( 'FluentForms feed smartcodes do not support inline fallback/default syntax in this runtime parser.', 'fluentcrm-conditional-status' ) . '</p>'
					. '<p><strong>' . esc_html__( 'Recommended fallback approach:', 'fluentcrm-conditional-status' ) . '</strong> ' . esc_html__( 'Use multiple FluentCRM feeds with feed-level conditional logic and set a per-feed "Fallback / Forced Status". This avoids relying on hidden-field conditional logic.', 'fluentcrm-conditional-status' ) . '</p>',
			);
			array_splice( $fields_array, $other_fields_index + 1, 0, array( $mapping_info ) );
			$mapping_info_pos = $other_fields_index + 1;
		}

		$force_status_key = 'fcs_force_status';
		if ( false === $this->find_setting_index( $fields_array, $force_status_key ) ) {
			$force_status_field = array(
				'key'          => $force_status_key,
				'require_list' => false,
				'label'        => __( 'Fallback / Forced Status', 'fluentcrm-conditional-status' ),
				'component'    => 'select',
				'placeholder'  => __( 'Choose a status', 'fluentcrm-conditional-status' ),
				'options'      => $this->get_status_options(),
				'tips'         => __( 'Optional. If mapped status is empty/invalid, this status is applied. Useful with multiple FluentCRM feeds + feed-level conditional logic.', 'fluentcrm-conditional-status' ),
			);
			array_splice( $fields_array, (int) $mapping_info_pos + 1, 0, array( $force_status_field ) );
		}

		return $settings_fields;
	}

	/**
	 * Find the index of a setting by key.
	 *
	 * @param array  $settings The settings array.
	 * @param string $key      The setting key to find.
	 * @return int|false The index or false if not found.
	 */
	private function find_setting_index( $settings, $key ) {
		foreach ( $settings as $index => $setting ) {
			if ( isset( $setting['key'] ) && $setting['key'] === $key ) {
				return $index;
			}
		}
		return false;
	}

	/**
	 * Get available status options for feed-level forced status.
	 *
	 * @return array
	 */
	private function get_status_options() {
		$options = array(
			'' => __( 'Disabled (use mapped/default behavior)', 'fluentcrm-conditional-status' ),
		);

		$statuses = function_exists( 'fluentcrm_subscriber_editable_statuses' )
			? fluentcrm_subscriber_editable_statuses()
			: array( 'subscribed', 'pending', 'unsubscribed', 'transactional' );

		foreach ( $statuses as $status ) {
			$options[ $status ] = ucfirst( str_replace( '_', ' ', (string) $status ) );
		}

		return $options;
	}
}
