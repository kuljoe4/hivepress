<?php
/**
 * Booking model.
 */

namespace HivePress\Models;

use HivePress\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking class.
 *
 * @class Booking
 */
class Booking extends Model {

	/**
	 * The post type name.
	 *
	 * @var string
	 */
	protected $post_type = 'hp_booking';

	/**
	 * The singular name.
	 *
	 * @var string
	 */
	protected $singular_name = 'Booking';

	/**
	 * The plural name.
	 *
	 * @var string
	 */
	protected $plural_name = 'Bookings';

	/**
	 * The capabilities.
	 *
	 * @var array
	 */
	protected $capabilities = [
		'edit_post'          => 'edit_hp_booking',
		'read_post'          => 'read_hp_booking',
		'delete_post'        => 'delete_hp_booking',
		'edit_posts'         => 'edit_hp_bookings',
		'edit_others_posts'  => 'edit_others_hp_bookings',
		'publish_posts'      => 'publish_hp_bookings',
		'read_private_posts' => 'read_private_hp_bookings',
		'create_posts'       => 'create_hp_bookings',
	];

	/**
	 * The fields.
	 *
	 * @var array
	 */
	protected $fields = [
		'customer'   => [
			'type'     => 'user',
			'label'    => 'Customer',
			'required' => true,
		],
		'listing'    => [
			'type'     => 'listing',
			'label'    => 'Listing',
			'required' => true,
		],
		'start_time' => [
			'type'     => 'datetime',
			'label'    => 'Start Time',
			'required' => true,
		],
		'end_time'   => [
			'type'     => 'datetime',
			'label'    => 'End Time',
			'required' => true,
		],
		'status'     => [
			'type'    => 'select',
			'label'   => 'Status',
			'options' => [
				'pending'   => 'Pending',
				'confirmed' => 'Confirmed',
				'cancelled' => 'Cancelled',
			],
			'default' => 'pending',
		],
	];

	/**
	 * The meta boxes.
	 *
	 * @var array
	 */
	protected $meta_boxes = [
		'details' => [
			'label'  => 'Booking Details',
			'fields' => [
				'customer',
				'listing',
				'start_time',
				'end_time',
				'status',
			],
		],
	];

	/**
	 * The admin columns.
	 *
	 * @var array
	 */
	protected $admin_columns = [
		'title'      => 'Booking',
		'listing'    => 'Listing',
		'customer'   => 'Customer',
		'start_time' => 'Start Time',
		'end_time'   => 'End Time',
		'status'     => 'Status',
	];
}
