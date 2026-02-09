<?php
/**
 * Feed Settings Class
 *
 * Adds conditional status mapping settings to FluentCRM feed configuration.
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
		// Add settings to FluentCRM feed.
		add_filter( 'fluentform/get_integration_values_FluentCrm', array( $this, 'add_feed_settings' ), 10, 3 );
		add_filter( 'fluentform/get_integration_defaults_FluentCrm', array( $this, 'add_feed_defaults' ), 10, 2 );

		// Add custom CSS for better UI integration.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add custom feed settings fields.
	 *
	 * @param array  $settings     The current feed settings.
	 * @param object $feed         The feed object.
	 * @param int    $form_id      The form ID.
	 * @return array Modified settings.
	 */
	public function add_feed_settings( $settings, $feed, $form_id ) {
		// Get all form fields for the dropdown.
		$form_fields = $this->get_form_fields( $form_id );

		// Add conditional status section after the list selection.
		$list_index = $this->find_setting_index( $settings, 'list_id' );

		if ( false !== $list_index ) {
			$conditional_settings = array(
				array(
					'key'         => 'enable_conditional_status',
					'label'       => __( 'Enable Conditional Status', 'fluentcrm-conditional-status' ),
					'tips'        => __( 'Set subscriber status based on form field values', 'fluentcrm-conditional-status' ),
					'component'   => 'checkbox-single',
					'checkbox_label' => __( 'Enable conditional subscriber status mapping', 'fluentcrm-conditional-status' ),
				),
				array(
					'key'            => 'conditional_status_field',
					'label'          => __( 'Field to Check', 'fluentcrm-conditional-status' ),
					'tips'           => __( 'Select the form field to determine subscriber status', 'fluentcrm-conditional-status' ),
					'component'      => 'select',
					'options'        => $form_fields,
					'dependency'     => array(
						'depends_on' => 'enable_conditional_status',
						'operator'   => '==',
						'value'      => true,
					),
				),
				array(
					'key'        => 'conditional_status_info',
					'label'      => '',
					'component'  => 'html_info',
					'html_info'  => '<div class="ff_card_block" style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
						<h4 style="margin: 0 0 10px 0;">' . __( 'Status Mapping Logic', 'fluentcrm-conditional-status' ) . '</h4>
						<p style="margin: 0 0 10px 0;">' . __( 'Define which subscriber status to use based on the selected field value:', 'fluentcrm-conditional-status' ) . '</p>
						<ul style="margin: 0; padding-left: 20px;">
							<li><strong>Subscribed:</strong> ' . __( 'Contact is fully subscribed (no confirmation needed)', 'fluentcrm-conditional-status' ) . '</li>
							<li><strong>Pending:</strong> ' . __( 'Contact needs to confirm via double opt-in email', 'fluentcrm-conditional-status' ) . '</li>
							<li><strong>Transactional:</strong> ' . __( 'Contact can receive transactional emails only', 'fluentcrm-conditional-status' ) . '</li>
							<li><strong>Unsubscribed:</strong> ' . __( 'Contact is unsubscribed', 'fluentcrm-conditional-status' ) . '</li>
							<li><strong>Bounced:</strong> ' . __( 'Email address has bounced', 'fluentcrm-conditional-status' ) . '</li>
							<li><strong>Complained:</strong> ' . __( 'Contact has marked emails as spam', 'fluentcrm-conditional-status' ) . '</li>
						</ul>
					</div>',
					'dependency' => array(
						'depends_on' => 'enable_conditional_status',
						'operator'   => '==',
						'value'      => true,
					),
				),
				array(
					'key'        => 'status_if_true',
					'label'      => __( 'Status if TRUE/Checked/Has Value', 'fluentcrm-conditional-status' ),
					'tips'       => __( 'Status to set when checkbox is checked, radio/select has a value, or text field is not empty', 'fluentcrm-conditional-status' ),
					'component'  => 'select',
					'options'    => $this->get_subscriber_statuses(),
					'dependency' => array(
						'depends_on' => 'enable_conditional_status',
						'operator'   => '==',
						'value'      => true,
					),
				),
				array(
					'key'        => 'status_if_false',
					'label'      => __( 'Status if FALSE/Unchecked/No Value', 'fluentcrm-conditional-status' ),
					'tips'       => __( 'Status to set when checkbox is unchecked, radio/select is empty, or text field is empty', 'fluentcrm-conditional-status' ),
					'component'  => 'select',
					'options'    => $this->get_subscriber_statuses(),
					'dependency' => array(
						'depends_on' => 'enable_conditional_status',
						'operator'   => '==',
						'value'      => true,
					),
				),
			);

			// Insert after list selection.
			array_splice( $settings, $list_index + 1, 0, $conditional_settings );
		}

		return $settings;
	}

	/**
	 * Add default values for new settings.
	 *
	 * @param array $defaults The default settings.
	 * @param int   $form_id  The form ID.
	 * @return array Modified defaults.
	 */
	public function add_feed_defaults( $defaults, $form_id ) {
		$defaults['enable_conditional_status'] = false;
		$defaults['conditional_status_field']  = '';
		$defaults['status_if_true']            = 'pending';
		$defaults['status_if_false']           = 'transactional';

		return $defaults;
	}

	/**
	 * Get form fields for dropdown.
	 *
	 * @param int $form_id The form ID.
	 * @return array Array of field options.
	 */
	private function get_form_fields( $form_id ) {
		$fields  = array();
		$form    = wpFluent()->table( 'fluentform_forms' )->find( $form_id );

		if ( ! $form ) {
			return $fields;
		}

		$form_fields = json_decode( $form->form_fields, true );

		if ( ! $form_fields || ! isset( $form_fields['fields'] ) ) {
			return $fields;
		}

		// Add empty option.
		$fields[''] = __( '-- Select Field --', 'fluentcrm-conditional-status' );

		// Parse form fields.
		foreach ( $form_fields['fields'] as $field ) {
			$field_type  = isset( $field['element'] ) ? $field['element'] : '';
			$field_name  = isset( $field['attributes']['name'] ) ? $field['attributes']['name'] : '';
			$field_label = isset( $field['settings']['label'] ) ? $field['settings']['label'] : $field_name;

			// Include common input fields.
			$allowed_types = array(
				'input_checkbox',
				'input_radio',
				'select',
				'input_text',
				'input_email',
				'input_number',
				'textarea',
				'gdpr-agreement',
				'terms_and_condition',
			);

			if ( $field_name && in_array( $field_type, $allowed_types, true ) ) {
				$fields[ $field_name ] = $field_label . ' (' . $field_type . ')';
			}
		}

		return $fields;
	}

	/**
	 * Get available subscriber statuses.
	 *
	 * @return array Array of status options.
	 */
	private function get_subscriber_statuses() {
		return array(
			'subscribed'    => __( 'Subscribed', 'fluentcrm-conditional-status' ),
			'pending'       => __( 'Pending (Double Opt-In)', 'fluentcrm-conditional-status' ),
			'transactional' => __( 'Transactional', 'fluentcrm-conditional-status' ),
			'unsubscribed'  => __( 'Unsubscribed', 'fluentcrm-conditional-status' ),
			'bounced'       => __( 'Bounced', 'fluentcrm-conditional-status' ),
			'complained'    => __( 'Complained', 'fluentcrm-conditional-status' ),
		);
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
	 * Enqueue admin assets.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on FluentForms pages.
		if ( false === strpos( $hook, 'fluent_forms' ) ) {
			return;
		}

		// Add inline CSS for better integration.
		wp_add_inline_style(
			'fluent_forms_admin',
			'
			.ff_conditional_status_section {
				background: #f8f9fa;
				padding: 15px;
				border-radius: 4px;
				margin: 15px 0;
			}
			.ff_conditional_status_section h4 {
				margin: 0 0 10px 0;
				color: #23282d;
			}
			'
		);
	}
}
