<?php
/**
 * Plugin Name: HivePress Timeslot Booking
 * Description: A configurable time slot booking add-on for HivePress.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hivepress-timeslot-booking
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if HivePress is active.
 */
function hivepress_timeslot_booking_is_hivepress_active() {
	return class_exists( 'HivePress\HivePress' );
}

/**
 * Initialize the plugin.
 */
function hivepress_timeslot_booking_init() {
	if ( ! hivepress_timeslot_booking_is_hivepress_active() ) {
		add_action( 'admin_notices', 'hivepress_timeslot_booking_hivepress_inactive_notice' );
		return;
	}

	// Include core files.
	require_once plugin_dir_path( __FILE__ ) . 'includes/models/class-booking.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-slot-settings.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/classes/class-slot-helper.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/blocks/timeslot-booking.php';

	// Register the booking model.
	hivepress()->register_model( 'booking', new \HivePress\Models\Booking() );

	// Initialize admin settings.
	new \HivePress\Admin\Slot_Settings();

	// Register the booking block.
	hivepress()->register_block( 'timeslot-booking', new \HivePress\Blocks\Timeslot_Booking_Block() );
}

add_action( 'plugins_loaded', 'hivepress_timeslot_booking_init' );

/**
 * Display a notice if HivePress is inactive.
 */
function hivepress_timeslot_booking_hivepress_inactive_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: HivePress plugin name. */
				esc_html__( '%s requires the HivePress plugin to be installed and activated.', 'hivepress-timeslot-booking' ),
				'<strong>' . esc_html__( 'HivePress Timeslot Booking', 'hivepress-timeslot-booking' ) . '</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Get available slots AJAX handler.
 */
function hivepress_timeslot_booking_get_available_slots() {
	$listing_id = absint( $_POST['listing_id'] );
	$date       = sanitize_text_field( $_POST['date'] );

	$slots = \HivePress\Classes\Slot_Helper::get_available_slots( $listing_id, $date );

	echo wp_json_encode( $slots );
	wp_die();
}

add_action( 'wp_ajax_get_available_slots', 'hivepress_timeslot_booking_get_available_slots' );
add_action( 'wp_ajax_nopriv_get_available_slots', 'hivepress_timeslot_booking_get_available_slots' );

/**
 * Handle booking submission.
 */
function hivepress_timeslot_booking_submit_booking() {
	if ( ! isset( $_POST['submit_booking'] ) ) {
		return;
	}

	$listing_id = absint( $_POST['listing_id'] );
	$date       = sanitize_text_field( $_POST['booking_date'] );
	$time       = sanitize_text_field( $_POST['booking_time'] );
	$customer_id = get_current_user_id();

	if ( ! $listing_id || ! $date || ! $time || ! $customer_id ) {
		return;
	}

	$listing = hivepress()->listing->get_by_id( $listing_id );

	if ( ! $listing ) {
		return;
	}

	$start_time = $date . ' ' . $time;
	$end_time   = date( 'Y-m-d H:i:s', strtotime( $start_time ) + $listing->get( 'slot_duration' ) * 60 );

	hivepress()->booking->create(
		[
			'post_title'   => 'Booking for ' . get_the_title( $listing_id ),
			'post_status'  => 'publish',
			'customer'     => $customer_id,
			'listing'      => $listing_id,
			'start_time'   => $start_time,
			'end_time'     => $end_time,
			'status'       => 'pending',
		]
	);

	// Redirect to a confirmation page.
	wp_redirect( home_url( '/booking-confirmation/' ) );
	exit;
}

add_action( 'template_redirect', 'hivepress_timeslot_booking_submit_booking' );
