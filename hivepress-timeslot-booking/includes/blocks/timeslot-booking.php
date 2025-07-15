<?php
/**
 * Timeslot booking block.
 */

namespace HivePress\Blocks;

use HivePress\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timeslot booking block class.
 *
 * @class Timeslot_Booking_Block
 */
class Timeslot_Booking_Block extends Block {

	/**
	 * The block name.
	 *
	 * @var string
	 */
	protected $name = 'timeslot-booking';

	/**
	 * The block title.
	 *
	 * @var string
	 */
	protected $title = 'Time Slot Booking';

	/**
	 * The block description.
	 *
	 * @var string
	 */
	protected $description = 'A block for booking time slots.';

	/**
	 * The block category.
	 *
	 * @var string
	 */
	protected $category = 'hivepress';

	/**
	 * The block keywords.
	 *
	 * @var array
	 */
	protected $keywords = [ 'booking', 'time', 'slot' ];

	/**
	 * The block attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'listing_id' => [
			'type' => 'number',
		],
	];

	/**
	 * Render the block.
	 *
	 * @param array $attributes
	 * @return string
	 */
	public function render( $attributes ) {
		$listing_id = $attributes['listing_id'] ?? get_the_ID();

		if ( ! $listing_id ) {
			return '';
		}

		$listing = hivepress()->listing->get_by_id( $listing_id );

		if ( ! $listing ) {
			return '';
		}

		$settings = \HivePress\Classes\Slot_Helper::get_listing_settings( $listing_id );

		if ( ! $settings['enable_time_slots'] ) {
			return '';
		}

		ob_start();
		?>

		<div class="hp-booking-form">
			<h3>Book a Time Slot</h3>

			<form action="" method="post">
				<div class="hp-form__field">
					<label for="hp-booking-date">Date</label>
					<input type="date" id="hp-booking-date" name="booking_date" required>
				</div>

				<div class="hp-form__field">
					<label for="hp-booking-time">Time</label>
					<select id="hp-booking-time" name="booking_time" required>
						<option value="">Select a time</option>
					</select>
				</div>

				<div class="hp-form__field">
					<input type="submit" name="submit_booking" value="Book Now">
				</div>
			</form>
		</div>

		<script>
			jQuery( document ).ready( function( $ ) {
				$( '#hp-booking-date' ).on( 'change', function() {
					var date = $( this ).val();
					var listing_id = <?php echo esc_js( $listing_id ); ?>;

					$.ajax( {
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						type: 'post',
						data: {
							action: 'get_available_slots',
							date: date,
							listing_id: listing_id
						},
						success: function( response ) {
							var slots = JSON.parse( response );
							var options = '<option value="">Select a time</option>';

							slots.forEach( function( slot ) {
								options += '<option value="' + slot + '">' + slot + '</option>';
							} );

							$( '#hp-booking-time' ).html( options );
						}
					} );
				} );
			} );
		</script>

		<?php
		return ob_get_clean();
	}
}
