<?php
/**
 * Submission Handler Class
 *
 * Replaces FluentCRM's default feed runtime so mapped status values are authoritative.
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
		// Run after FluentCRM init has registered its integration callback.
		add_action( 'init', array( $this, 'swap_default_feed_handler' ), 50 );
		add_action( 'fluentform/integration_notify_fluentcrm_feeds', array( $this, 'handle_feed_notification' ), 10, 4 );
	}

	/**
	 * Remove FluentCRM's default notify callback for Fluent Forms feed execution.
	 *
	 * @return void
	 */
	public function swap_default_feed_handler() {
		static $swapped = false;

		if ( $swapped ) {
			return;
		}

		global $wp_filter;

		$hook_name = 'fluentform/integration_notify_fluentcrm_feeds';
		if ( empty( $wp_filter[ $hook_name ] ) || ! isset( $wp_filter[ $hook_name ]->callbacks ) ) {
			$swapped = true;
			return;
		}

		foreach ( $wp_filter[ $hook_name ]->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( empty( $callback['function'] ) || ! is_array( $callback['function'] ) ) {
					continue;
				}

				$target = $callback['function'];
				if (
					isset( $target[0], $target[1] ) &&
					is_object( $target[0] ) &&
					'notify' === $target[1] &&
					is_a( $target[0], 'FluentCrm\App\Services\ExternalIntegrations\FluentForm\Bootstrap' )
				) {
					remove_action( $hook_name, $target, (int) $priority );
				}
			}
		}

		$swapped = true;
	}

	/**
	 * Handle FluentCRM feed notification with status-aware runtime logic.
	 *
	 * @param array  $feed      Feed data.
	 * @param array  $form_data Submitted form data.
	 * @param object $entry     Parsed entry object.
	 * @param object $form      Form object.
	 * @return bool
	 */
	public function handle_feed_notification( $feed, $form_data, $entry, $form ) {
		if ( ! isset( $feed['processedValues'] ) || ! is_array( $feed['processedValues'] ) ) {
			return false;
		}

		// Keep parity with FluentCRM behavior for payment-event-only feed settings.
		if (
			isset( $feed['settings']['run_events_only'] ) &&
			! empty( $feed['settings']['run_events_only'] ) &&
			class_exists( '\FluentForm\App\Modules\Form\FormFieldsParser' ) &&
			\FluentForm\App\Modules\Form\FormFieldsParser::getPaymentFields( $form, array( 'element' ) )
		) {
			return false;
		}

		return $this->run_feed( $feed, $form_data, $entry, $form );
	}

	/**
	 * Execute rewritten feed runtime.
	 *
	 * @param array  $feed      Feed data with processed values.
	 * @param array  $form_data Submitted form data.
	 * @param object $entry     Parsed entry object.
	 * @param object $form      Form object.
	 * @return bool
	 */
	private function run_feed( $feed, $form_data, $entry, $form ) {
		$data = $feed['processedValues'];

		$contact = array(
			'first_name' => isset( $data['first_name'] ) ? (string) $data['first_name'] : '',
			'last_name'  => isset( $data['last_name'] ) ? (string) $data['last_name'] : '',
			'email'      => isset( $data['email'] ) ? (string) $data['email'] : '',
		);

		if ( ! is_email( $contact['email'] ) && ! empty( $form_data[ $contact['email'] ] ) ) {
			$contact['email'] = (string) $form_data[ $contact['email'] ];
		}

		if ( '' === $contact['first_name'] && '' === $contact['last_name'] && ! empty( $data['full_name'] ) ) {
			$name_parts = preg_split( '/\s+/', trim( (string) $data['full_name'] ) );
			if ( ! empty( $name_parts ) ) {
				if ( count( $name_parts ) > 1 ) {
					$contact['last_name']  = array_pop( $name_parts );
					$contact['first_name'] = implode( ' ', $name_parts );
				} else {
					$contact['first_name'] = $name_parts[0];
				}
			}
		}

		$mapped_status = '';
		$other_fields  = isset( $data['other_fields'] ) && is_array( $data['other_fields'] ) ? $data['other_fields'] : array();
		foreach ( $other_fields as $field ) {
			$label = isset( $field['label'] ) ? sanitize_key( (string) $field['label'] ) : '';
			if ( '' === $label ) {
				continue;
			}

			$raw_value = isset( $field['item_value'] ) ? $field['item_value'] : '';
			if ( is_string( $raw_value ) ) {
				$value = trim( str_replace( '<br />', ' ', $raw_value ) );
			} elseif ( is_scalar( $raw_value ) ) {
				$value = trim( (string) $raw_value );
			} else {
				$value = '';
			}

			if ( '' === $value ) {
				continue;
			}

			if ( 'status' === $label ) {
				$mapped_status = $this->normalize_status( $value );
				continue;
			}

			$contact[ $label ] = $value;
		}

		if ( ! empty( $entry->ip ) ) {
			$contact['ip'] = $entry->ip;
		}

		if ( ! is_email( $contact['email'] ) ) {
			$this->add_log(
				isset( $feed['settings']['name'] ) ? $feed['settings']['name'] : 'FluentCRM',
				'failed',
				__( 'FluentCRM API call skipped because no valid email was available', 'fluentcrm-conditional-status' ),
				isset( $form->id ) ? $form->id : 0,
				isset( $entry->id ) ? $entry->id : 0
			);
			return false;
		}

		if ( isset( $contact['country'] ) && class_exists( '\FluentCrm\App\Services\Funnel\FunnelHelper' ) ) {
			$country = \FluentCrm\App\Services\Funnel\FunnelHelper::getCountryShortName( $contact['country'] );
			if ( $country ) {
				$contact['country'] = $country;
			} else {
				unset( $contact['country'] );
			}
		}

		$subscriber = \FluentCrm\App\Models\Subscriber::where( 'email', $contact['email'] )->first();

		if ( $subscriber && $this->is_true( $data, 'skip_if_exists' ) ) {
			$this->add_log(
				isset( $feed['settings']['name'] ) ? $feed['settings']['name'] : 'FluentCRM',
				'info',
				__( 'Contact update skipped because contact already exists', 'fluentcrm-conditional-status' ),
				isset( $form->id ) ? $form->id : 0,
				isset( $entry->id ) ? $entry->id : 0
			);
			return false;
		}

		if ( ! empty( $contact['avatar'] ) && ! $this->is_valid_avatar_url( $contact['avatar'] ) ) {
			unset( $contact['avatar'] );
		}

		if ( $subscriber ) {
			if ( ! empty( $subscriber->ip ) && isset( $contact['ip'] ) ) {
				unset( $contact['ip'] );
			}

			if ( $this->is_true( $data, 'skip_primary_data' ) && ! empty( $subscriber->first_name ) ) {
				unset( $contact['first_name'] );
				unset( $contact['last_name'] );
			}
		}

		$user = get_user_by( 'email', $contact['email'] );
		if ( $user ) {
			$contact['user_id'] = $user->ID;
		}

		$tags = $this->get_selected_tag_ids( $data, $form_data, 'tag_ids' );
		if ( ! empty( $tags ) ) {
			$contact['tags'] = $tags;
		}

		$list_id = isset( $data['list_id'] ) ? $data['list_id'] : '';
		if ( '' !== $list_id ) {
			$contact['lists'] = array( $list_id );
		}

		$target_status = $this->resolve_target_status( $mapped_status, $data, $subscriber );
		$has_explicit_status = ( '' !== $mapped_status );
		if ( ! $has_explicit_status && isset( $data['fcs_force_status'] ) ) {
			$has_explicit_status = ( '' !== $this->normalize_status( $data['fcs_force_status'] ) );
		}

		if ( '' !== $target_status ) {
			$contact['status'] = $target_status;
		}

		if ( ! $subscriber ) {
			if ( empty( $contact['source'] ) ) {
				$contact['source'] = 'FluentForms';
			}

			$subscriber = FluentCrmApi( 'contacts' )->createOrUpdate( $contact, false, false );
			if ( ! $subscriber ) {
				return false;
			}

			if ( ! $has_explicit_status && isset( $entry->status ) && 'confirmed' === $entry->status && 'subscribed' !== $subscriber->status ) {
				$subscriber = $subscriber->updateStatus( 'subscribed' );
			}

			if ( 'pending' === $subscriber->status ) {
				$subscriber->sendDoubleOptinEmail();
			}

			do_action( 'fluent_crm/contact_added_by_fluentform', $subscriber, $entry, $form, $feed );

			$this->add_log(
				isset( $feed['settings']['name'] ) ? $feed['settings']['name'] : 'FluentCRM',
				'success',
				__( 'Contact has been created in FluentCRM. Contact ID: ', 'fluentcrm-conditional-status' ) . $subscriber->id,
				isset( $form->id ) ? $form->id : 0,
				isset( $entry->id ) ? $entry->id : 0
			);

			return true;
		}

		$has_double_opt_in = $this->is_true( $data, 'double_opt_in' );
		$force_subscribed  = false;
		$force_update      = false;

		if ( $has_explicit_status ) {
			$force_update = true;
		} else {
			$force_subscribed = ! $has_double_opt_in && ( 'subscribed' !== $subscriber->status );
			if ( ! $force_subscribed ) {
				$force_subscribed = $this->is_true( $data, 'force_subscribe' );
			}

			if ( $force_subscribed ) {
				$contact['status'] = 'subscribed';
				$force_update      = true;
			}
		}

		$subscriber = FluentCrmApi( 'contacts' )->createOrUpdate( $contact, $force_update, false );
		if ( ! $subscriber ) {
			return false;
		}

		if ( ! $has_explicit_status && isset( $entry->status ) && 'confirmed' === $entry->status && 'subscribed' !== $subscriber->status ) {
			$subscriber = $subscriber->updateStatus( 'subscribed' );
		}

		if ( $has_explicit_status ) {
			if ( 'pending' === $target_status && 'pending' === $subscriber->status ) {
				$subscriber->sendDoubleOptinEmail();
			}
		} elseif ( $has_double_opt_in && in_array( $subscriber->status, array( 'pending', 'unsubscribed' ), true ) ) {
			$subscriber->sendDoubleOptinEmail();
		}

		do_action( 'fluent_crm/contact_updated_by_fluentform', $subscriber, $entry, $form, $feed );

		$remove_tags = isset( $feed['settings']['remove_tags'] ) && is_array( $feed['settings']['remove_tags'] ) ? $feed['settings']['remove_tags'] : array();
		if ( ! empty( $remove_tags ) ) {
			$subscriber->detachTags( $remove_tags );
		}

		$this->add_log(
			isset( $feed['settings']['name'] ) ? $feed['settings']['name'] : 'FluentCRM',
			'success',
			__( 'Contact has been updated in FluentCRM. Contact ID: ', 'fluentcrm-conditional-status' ) . $subscriber->id,
			isset( $form->id ) ? $form->id : 0,
			isset( $entry->id ) ? $entry->id : 0
		);

		return true;
	}

	/**
	 * Resolve target status to apply.
	 *
	 * @param string $mapped_status Mapped status.
	 * @param array  $data          Feed processed values.
	 * @param object $subscriber    Existing subscriber model or null.
	 * @return string
	 */
	private function resolve_target_status( $mapped_status, $data, $subscriber ) {
		if ( '' !== $mapped_status ) {
			return $mapped_status;
		}

		$forced_status = '';
		if ( isset( $data['fcs_force_status'] ) ) {
			$forced_status = $this->normalize_status( $data['fcs_force_status'] );
		}
		if ( '' !== $forced_status ) {
			return $forced_status;
		}

		if ( ! $subscriber ) {
			return $this->is_true( $data, 'double_opt_in' ) ? 'pending' : 'subscribed';
		}

		return '';
	}

	/**
	 * Normalize mapped status value.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private function normalize_status( $value ) {
		$value = strtolower( trim( (string) $value ) );
		if ( '' === $value ) {
			return '';
		}

		$aliases = array(
			'subscribe'      => 'subscribed',
			'confirmed'      => 'subscribed',
			'double_opt_in'  => 'pending',
			'double-opt-in'  => 'pending',
			'double opt in'  => 'pending',
			'double opt-in'  => 'pending',
			'unsubscribe'    => 'unsubscribed',
			'complaint'      => 'complained',
			'spam'           => 'spammed',
			'transaction'    => 'transactional',
		);
		if ( isset( $aliases[ $value ] ) ) {
			$value = $aliases[ $value ];
		}

		$allowed_statuses = function_exists( 'fluentcrm_subscriber_editable_statuses' )
			? fluentcrm_subscriber_editable_statuses()
			: array( 'subscribed', 'pending', 'unsubscribed', 'transactional' );

		return in_array( $value, $allowed_statuses, true ) ? $value : '';
	}

	/**
	 * Validate avatar URL.
	 *
	 * @param string $url Avatar URL.
	 * @return bool
	 */
	private function is_valid_avatar_url( $url ) {
		if ( false === filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		$path_info = pathinfo( wp_parse_url( $url, PHP_URL_PATH ) );
		$ext       = isset( $path_info['extension'] ) ? strtolower( $path_info['extension'] ) : '';
		return in_array( $ext, array( 'png', 'jpg', 'jpeg', 'webp', 'gif' ), true );
	}

	/**
	 * Add feed log item.
	 *
	 * @param string $title       Feed title.
	 * @param string $status      Status.
	 * @param string $description Description.
	 * @param int    $form_id     Form ID.
	 * @param int    $entry_id    Entry ID.
	 * @return void
	 */
	private function add_log( $title, $status, $description, $form_id, $entry_id ) {
		$payload = array(
			'title'            => $title,
			'status'           => $status,
			'description'      => $description,
			'parent_source_id' => $form_id,
			'source_id'        => $entry_id,
			'component'        => 'fluentcrm',
			'source_type'      => 'submission_item',
		);

		do_action( 'fluentform/log_data', $payload );
		do_action( 'ff_log_data', $payload );
	}

	/**
	 * Resolve selected tags from simple or routed settings.
	 *
	 * @param array  $data       Feed settings.
	 * @param array  $input_data Submission data.
	 * @param string $simple_key Simple tags key.
	 * @param string $routing_id Routing mode key.
	 * @param string $routers_key Routers key.
	 * @return array
	 */
	private function get_selected_tag_ids( $data, $input_data, $simple_key = 'tag_ids', $routing_id = 'tag_ids_selection_type', $routers_key = 'tag_routers' ) {
		$routing = isset( $data[ $routing_id ] ) ? $data[ $routing_id ] : 'simple';
		if ( ! $routing || 'simple' === $routing ) {
			return isset( $data[ $simple_key ] ) && is_array( $data[ $simple_key ] ) ? $data[ $simple_key ] : array();
		}

		$routers = isset( $data[ $routers_key ] ) && is_array( $data[ $routers_key ] ) ? $data[ $routers_key ] : array();
		if ( empty( $routers ) || ! class_exists( '\FluentForm\App\Services\ConditionAssesor' ) ) {
			return array();
		}

		return $this->evaluate_routings( $routers, $input_data );
	}

	/**
	 * Evaluate dynamic routing rules.
	 *
	 * @param array $routings   Routing rules.
	 * @param array $input_data Input data.
	 * @return array
	 */
	private function evaluate_routings( $routings, $input_data ) {
		$valid_inputs = array();
		foreach ( $routings as $routing ) {
			$input_value = isset( $routing['input_value'] ) ? $routing['input_value'] : '';
			if ( '' === $input_value ) {
				continue;
			}

			$condition = array(
				'conditionals' => array(
					'status'     => true,
					'is_test'    => true,
					'type'       => 'any',
					'conditions' => array( $routing ),
				)
			);

			if ( \FluentForm\App\Services\ConditionAssesor::evaluate( $condition, $input_data ) ) {
				$valid_inputs[] = $input_value;
			}
		}

		return $valid_inputs;
	}

	/**
	 * Match FluentForms truthy check behavior.
	 *
	 * @param array  $data Feed settings.
	 * @param string $key  Setting key.
	 * @return bool
	 */
	private function is_true( $data, $key ) {
		$value = isset( $data[ $key ] ) ? $data[ $key ] : null;
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( 'false' === $value || '0' === $value || ! $value ) {
			return false;
		}
		return true;
	}
}
