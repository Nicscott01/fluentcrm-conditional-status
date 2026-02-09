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
			);
			array_splice( $fields_array, $other_fields_index + 1, 0, array( $mapping_info ) );
			$mapping_info_pos = $other_fields_index + 1;
		}

		$force_status_key = 'fcs_force_status';
		$force_status_index = $this->find_setting_index( $fields_array, $force_status_key );
		if ( false === $force_status_index ) {
			$force_status_field = array(
				'key'          => $force_status_key,
				'require_list' => false,
				'label'        => __( 'Fallback / Forced Status', 'fluentcrm-conditional-status' ),
				'component'    => 'select',
				'placeholder'  => __( 'Choose a status', 'fluentcrm-conditional-status' ),
				'options'      => $this->get_status_options(),
				'tips'         => __( 'Optional. If mapped status is empty/invalid, this status is applied. Useful with multiple FluentCRM feeds + feed-level conditional logic.', 'fluentcrm-conditional-status' ),
			);
			$force_status_index = (int) $mapping_info_pos + 1;
			array_splice( $fields_array, $force_status_index, 0, array( $force_status_field ) );
		}

		// Remove conflicting native status toggles to avoid ambiguous behavior.
		$this->remove_setting_by_key( $fields_array, 'double_opt_in' );
		$this->remove_setting_by_key( $fields_array, 'force_subscribe' );

		$status_controls_info_key = 'fcs_status_controls_info';
		if ( false === $this->find_setting_index( $fields_array, $status_controls_info_key ) ) {
			$status_controls_info = array(
				'key'          => $status_controls_info_key,
				'require_list' => false,
				'label'        => __( 'Status Controls', 'fluentcrm-conditional-status' ),
				'component'    => 'html_info',
				'html_info'    => '<p>' . esc_html__( 'This plugin controls status via "Subscriber Status (Mapped Value)" and "Fallback / Forced Status". To send double opt-in, set status to "pending".', 'fluentcrm-conditional-status' ) . '</p>',
			);
			$insert_at = false !== $force_status_index ? (int) $force_status_index + 1 : (int) $mapping_info_pos + 1;
			array_splice( $fields_array, $insert_at, 0, array( $status_controls_info ) );
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
	 * Remove a setting from the fields list by key.
	 *
	 * @param array  $settings Fields array (by reference).
	 * @param string $key      Setting key.
	 * @return void
	 */
	private function remove_setting_by_key( &$settings, $key ) {
		$index = $this->find_setting_index( $settings, $key );
		if ( false !== $index ) {
			array_splice( $settings, (int) $index, 1 );
		}
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
