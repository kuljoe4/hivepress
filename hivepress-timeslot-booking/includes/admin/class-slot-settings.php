<?php
/**
 * Slot settings.
 */

namespace HivePress\Admin;

use HivePress\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slot settings class.
 *
 * @class Slot_Settings
 */
class Slot_Settings {

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_filter( 'hivepress/v1/meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'hivepress/v1/models/listing/create', [ $this, 'save_listing_settings' ] );
		add_action( 'hivepress/v1/models/listing/update', [ $this, 'save_listing_settings' ] );
	}

	/**
	 * Add meta box.
	 *
	 * @param array $meta_boxes
	 * @return array
	 */
	public function add_meta_box( $meta_boxes ) {
		$meta_boxes['timeslot_settings'] = [
			'title'    => 'Time Slot Settings',
			'screen'   => 'listing',
			'context'  => 'side',
			'priority' => 'default',
			'fields'   => [
				'enable_time_slots' => [
					'label' => 'Enable Time Slots',
					'type'  => 'checkbox',
				],
				'slot_duration'     => [
					'label'   => 'Slot Duration',
					'type'    => 'select',
					'options' => [
						'15' => '15 minutes',
						'30' => '30 minutes',
						'45' => '45 minutes',
						'60' => '1 hour',
					],
				],
				'start_time'        => [
					'label' => 'Start Time',
					'type'  => 'time',
				],
				'end_time'          => [
					'label' => 'End Time',
					'type'  => 'time',
				],
				'days_of_week'      => [
					'label'   => 'Days of the Week',
					'type'    => 'checkboxes',
					'options' => [
						'sunday'    => 'Sunday',
						'monday'    => 'Monday',
						'tuesday'   => 'Tuesday',
						'wednesday' => 'Wednesday',
						'thursday'  => 'Thursday',
						'friday'    => 'Friday',
						'saturday'  => 'Saturday',
					],
				],
			],
		];

		return $meta_boxes;
	}

	/**
	 * Save listing settings.
	 *
	 * @param int $listing_id
	 */
	public function save_listing_settings( $listing_id ) {
		if ( isset( $_POST['enable_time_slots'] ) ) {
			update_post_meta( $listing_id, '_enable_time_slots', sanitize_text_field( $_POST['enable_time_slots'] ) );
		}

		if ( isset( $_POST['slot_duration'] ) ) {
			update_post_meta( $listing_id, '_slot_duration', sanitize_text_field( $_POST['slot_duration'] ) );
		}

		if ( isset( $_POST['start_time'] ) ) {
			update_post_meta( $listing_id, '_start_time', sanitize_text_field( $_POST['start_time'] ) );
		}

		if ( isset( $_POST['end_time'] ) ) {
			update_post_meta( $listing_id, '_end_time', sanitize_text_field( $_POST['end_time'] ) );
		}

		if ( isset( $_POST['days_of_week'] ) ) {
			update_post_meta( $listing_id, '_days_of_week', array_map( 'sanitize_text_field', $_POST['days_of_week'] ) );
		}
	}
}
