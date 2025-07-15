<?php
/**
 * Slot settings template.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form action="options.php" method="post">
		<?php
		settings_fields( 'hivepress_timeslot_booking' );
		do_settings_sections( 'hivepress_timeslot_booking' );
		submit_button();
		?>
	</form>
</div>
