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
		add_action( 'hivepress/v1/models/listing/save', [ $this, 'save_listing_settings' ] );
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
	 * @param array $values
	 */
	public function save_listing_settings( $listing_id, $values ) {
		if ( isset( $values['enable_time_slots'] ) ) {
			hivepress()->listing->get_by_id( $listing_id )->set( 'enable_time_slots', $values['enable_time_slots'] )->save();
		}

		if ( isset( $values['slot_duration'] ) ) {
			hivepress()->listing->get_by_id( $listing_id )->set( 'slot_duration', $values['slot_duration'] )->save();
		}

		if ( isset( $values['start_time'] ) ) {
			hivepress()->listing->get_by_id( $listing_id )->set( 'start_time', $values['start_time'] )->save();
		}

		if ( isset( $values['end_time'] ) ) {
			hivepress()->listing->get_by_id( $listing_id )->set( 'end_time', $values['end_time'] )->save();
		}

		if ( isset( $values['days_of_week'] ) ) {
			hivepress()->listing->get_by_id( $listing_id )->set( 'days_of_week', $values['days_of_week'] )->save();
		}
	}
}
