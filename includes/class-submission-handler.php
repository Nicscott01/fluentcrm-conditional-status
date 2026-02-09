<?php
/**
 * Submission Handler Class
 *
 * Processes form submissions and applies conditional status logic.
 *
 * @package FluentCRM_Conditional_Status
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentCRM Conditional Status Submission Handler.
 */
class FluentCRM_Conditional_Status_Submission_Handler {

	/**
	 * Single instance of the class.
	 *
	 * @var FluentCRM_Conditional_Status_Submission_Handler
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return FluentCRM_Conditional_Status_Submission_Handler
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
		// Hook into FluentCRM integration before subscriber is created/updated.
		add_filter( 'fluentcrm_contact_added_by_fluentform', array( $this, 'modify_subscriber_status' ), 10, 3 );
		add_filter( 'fluentform/fluentcrm_integration_subscriber_data', array( $this, 'modify_subscriber_data' ), 10, 3 );
	}

	/**
	 * Modify subscriber data before it's sent to FluentCRM.
	 *
	 * @param array  $subscriber_data The subscriber data.
	 * @param array  $feed            The feed configuration.
	 * @param object $entry           The form entry.
	 * @return array Modified subscriber data.
	 */
	public function modify_subscriber_data( $subscriber_data, $feed, $entry ) {
		// Check if conditional status is enabled.
		if ( empty( $feed['enable_conditional_status'] ) || 'true' !== $feed['enable_conditional_status'] ) {
			return $subscriber_data;
		}

		// Get the field to check.
		$field_name = isset( $feed['conditional_status_field'] ) ? $feed['conditional_status_field'] : '';
		if ( empty( $field_name ) ) {
			return $subscriber_data;
		}

		// Get the field value from entry.
		$field_value = $this->get_field_value( $entry, $field_name );

		// Determine which status to use.
		$is_truthy    = $this->evaluate_field_value( $field_value );
		$status_key   = $is_truthy ? 'status_if_true' : 'status_if_false';
		$new_status   = isset( $feed[ $status_key ] ) ? $feed[ $status_key ] : '';

		// Apply the status.
		if ( ! empty( $new_status ) ) {
			$subscriber_data['status'] = $new_status;

			// Log for debugging (optional).
			do_action(
				'fluentcrm_conditional_status_applied',
				array(
					'field_name'   => $field_name,
					'field_value'  => $field_value,
					'is_truthy'    => $is_truthy,
					'status'       => $new_status,
					'entry_id'     => isset( $entry->id ) ? $entry->id : 0,
					'form_id'      => isset( $entry->form_id ) ? $entry->form_id : 0,
				)
			);
		}

		return $subscriber_data;
	}

	/**
	 * Modify subscriber status after contact is added (fallback).
	 *
	 * @param object $subscriber The subscriber object.
	 * @param array  $feed       The feed configuration.
	 * @param object $entry      The form entry.
	 * @return object Modified subscriber.
	 */
	public function modify_subscriber_status( $subscriber, $feed, $entry ) {
		// Check if conditional status is enabled.
		if ( empty( $feed['enable_conditional_status'] ) || 'true' !== $feed['enable_conditional_status'] ) {
			return $subscriber;
		}

		// Get the field to check.
		$field_name = isset( $feed['conditional_status_field'] ) ? $feed['conditional_status_field'] : '';
		if ( empty( $field_name ) ) {
			return $subscriber;
		}

		// Get the field value from entry.
		$field_value = $this->get_field_value( $entry, $field_name );

		// Determine which status to use.
		$is_truthy  = $this->evaluate_field_value( $field_value );
		$status_key = $is_truthy ? 'status_if_true' : 'status_if_false';
		$new_status = isset( $feed[ $status_key ] ) ? $feed[ $status_key ] : '';

		// Update the subscriber status if needed.
		if ( ! empty( $new_status ) && $subscriber->status !== $new_status ) {
			$subscriber->status = $new_status;
			$subscriber->save();

			// If status is pending, trigger double opt-in.
			if ( 'pending' === $new_status ) {
				do_action( 'fluentcrm_contact_added_to_lists', $subscriber );
			}
		}

		return $subscriber;
	}

	/**
	 * Get field value from entry.
	 *
	 * @param object $entry      The form entry.
	 * @param string $field_name The field name.
	 * @return mixed The field value.
	 */
	private function get_field_value( $entry, $field_name ) {
		// Try to get from response data.
		if ( isset( $entry->response ) ) {
			$response = is_string( $entry->response ) ? json_decode( $entry->response, true ) : $entry->response;
			if ( isset( $response[ $field_name ] ) ) {
				return $response[ $field_name ];
			}
		}

		// Try to get directly from entry object.
		if ( isset( $entry->{$field_name} ) ) {
			return $entry->{$field_name};
		}

		// Try to get from entry meta.
		if ( isset( $entry->id ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'fluentform_entry_details';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$value = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT field_value FROM {$table_name} WHERE submission_id = %d AND field_name = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$entry->id,
					$field_name
				)
			);
			if ( null !== $value ) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * Evaluate if a field value is "truthy".
	 *
	 * @param mixed $value The field value.
	 * @return bool True if the value is considered truthy.
	 */
	private function evaluate_field_value( $value ) {
		// Handle null or empty.
		if ( null === $value || '' === $value ) {
			return false;
		}

		// Handle arrays (multi-select, checkboxes).
		if ( is_array( $value ) ) {
			return ! empty( $value );
		}

		// Handle strings.
		if ( is_string( $value ) ) {
			$value = trim( strtolower( $value ) );

			// Common "false" values.
			$false_values = array( 'false', 'no', '0', 'off', 'unchecked', '' );
			if ( in_array( $value, $false_values, true ) ) {
				return false;
			}

			// Any other non-empty string is truthy.
			return '' !== $value;
		}

		// Handle boolean.
		if ( is_bool( $value ) ) {
			return $value;
		}

		// Handle numeric.
		if ( is_numeric( $value ) ) {
			return (float) $value !== 0.0;
		}

		// Default to checking if value is "truthy" in PHP sense.
		return ! empty( $value );
	}
}
