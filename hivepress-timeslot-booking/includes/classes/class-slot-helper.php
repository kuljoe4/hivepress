<?php
/**
 * Slot helper.
 */

namespace HivePress\Classes;

use HivePress\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slot helper class.
 *
 * @class Slot_Helper
 */
class Slot_Helper {

	/**
	 * Get available slots.
	 *
	 * @param int    $listing_id
	 * @param string $date
	 * @return array
	 */
	public static function get_available_slots( $listing_id, $date ) {
		$settings = self::get_listing_settings( $listing_id );

		if ( ! $settings['enable_time_slots'] ) {
			return [];
		}

		$start_time   = strtotime( $date . ' ' . $settings['start_time'] );
		$end_time     = strtotime( $date . ' ' . $settings['end_time'] );
		$slot_duration = $settings['slot_duration'] * 60;

		$available_slots = [];
		$current_time    = $start_time;

		while ( $current_time < $end_time ) {
			$available_slots[] = date( 'H:i', $current_time );
			$current_time     += $slot_duration;
		}

		$booked_slots = self::get_booked_slots( $listing_id, $date );

		return array_diff( $available_slots, $booked_slots );
	}

	/**
	 * Get booked slots.
	 *
	 * @param int    $listing_id
	 * @param string $date
	 * @return array
	 */
	public static function get_booked_slots( $listing_id, $date ) {
		$bookings = hivepress()->booking->get(
			[
				'listing__in' => [ $listing_id ],
				'start_time'  => [
					'operator' => '>=',
					'value'    => $date . ' 00:00:00',
				],
				'end_time'    => [
					'operator' => '<=',
					'value'    => $date . ' 23:59:59',
				],
			]
		);

		$booked_slots = [];

		foreach ( $bookings as $booking ) {
			$booked_slots[] = date( 'H:i', strtotime( $booking->start_time ) );
		}

		return $booked_slots;
	}

	/**
	 * Get listing settings.
	 *
	 * @param int $listing_id
	 * @return array
	 */
	public static function get_listing_settings( $listing_id ) {
		$settings = [
			'enable_time_slots' => get_post_meta( $listing_id, '_enable_time_slots', true ),
			'slot_duration'     => get_post_meta( $listing_id, '_slot_duration', true ),
			'start_time'        => get_post_meta( $listing_id, '_start_time', true ),
			'end_time'          => get_post_meta( $listing_id, '_end_time', true ),
			'days_of_week'      => get_post_meta( $listing_id, '_days_of_week', true ),
		];

		return $settings;
	}
}
