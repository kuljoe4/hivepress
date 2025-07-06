<?php
/**
 * Frontend Display for Custom Prescription Handler.
 *
 * @package CustomPrescriptionHandler
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPH_Frontend_Display Class.
 */
class CPH_Frontend_Display {

	private $meta_key = '_cph_prescription_options';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Frontend display hooks
		add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'display_prescription_options_form_start' ), 5 );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'render_prescription_options' ), 10 );
		add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'display_prescription_options_form_end' ), 15 );

		// Cart item data handling
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cph_options_to_cart_item' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_cph_options_in_cart' ), 10, 2 );

		// Email item data display
		add_action( 'woocommerce_order_item_meta_start', array( $this, 'display_cph_options_in_emails' ), 10, 4 );
		// For plain text emails, a different approach might be needed if the default meta display isn't sufficient.
		// add_filter( 'woocommerce_order_item_name', array( $this, 'add_cph_options_to_email_item_name' ), 10, 2 );

		// Save data to order item
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_cph_options_to_order_item' ), 10, 4 );

	}

	/**
	 * Outputs the opening tags for our custom options form wrapper.
	 * We wrap the entire block including the add-to-cart button to potentially manage it with JS.
	 */
	public function display_prescription_options_form_start() {
		global $product;
		if ( ! $product || ! is_product() ) {
			return;
		}
		$options_data = get_post_meta( $product->get_id(), $this->meta_key, true );
		if ( empty( $options_data ) || empty( $options_data['option_groups'] ) ) {
			return; // No options configured for this product.
		}
		echo '<div id="cph_prescription_options_wrapper" class="cph-options-wrapper">';
		// We might add a hidden input for the base product price for JS calculations
		echo '<input type="hidden" id="cph_base_product_price" value="' . esc_attr( $product->get_price() ) . '">';
		echo '<div id="cph_dynamic_price_display_area">';
		// Optionally display price here, to be updated by JS.
		// echo '<p class="price cph-total-price">' . $product->get_price_html() . '</p>';
		echo '</div>';
	}

	/**
	 * Outputs the closing tags for our custom options form wrapper.
	 */
	public function display_prescription_options_form_end() {
		 global $product;
		if ( ! $product || ! is_product() ) {
			return;
		}
		$options_data = get_post_meta( $product->get_id(), $this->meta_key, true );
		if ( empty( $options_data ) || empty( $options_data['option_groups'] ) ) {
			return;
		}
		echo '</div>'; // #cph_prescription_options_wrapper
	}


	/**
	 * Renders the prescription options form on the single product page.
	 */
	public function render_prescription_options() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$product_id = $product->get_id();
		$options_data = get_post_meta( $product_id, $this->meta_key, true );

		if ( empty( $options_data ) || empty( $options_data['option_groups'] ) ) {
			return; // No options configured for this product.
		}

		echo '<div class="cph_options_form_inner">';

		foreach ( $options_data['option_groups'] as $group_index => $group ) {
			if ( empty( $group['fields'] ) ) {
				continue;
			}

			echo '<div class="cph_option_group_display" id="cph_group_' . esc_attr( $group_index ) . '">';
			if ( ! empty( $group['group_name'] ) ) {
				echo '<h3 class="cph_group_name">' . esc_html( $group['group_name'] ) . '</h3>';
			}

			echo '<table class="cph_fields_table variations" cellspacing="0">';
			echo '<tbody>';

			foreach ( $group['fields'] as $field_index => $field ) {
				$field_id_attr = 'cph_field_' . esc_attr( $field['field_name_id'] );
				// Use field_name_id for the name attribute to make it easier for JS and backend
				$field_name_attr = 'cph_options[' . esc_attr( $field['field_name_id'] ) . ']';

				// Data attributes for JS
				$data_attrs = '';
				// Example: $data_attrs .= ' data-is-required="' . esc_attr( $field['is_required'] ?? '0' ) . '"';
				// Example: $data_attrs .= ' data-conditional-logic=\'' . json_encode( $field['conditional_logic'] ?? [] ) . '\'';


				echo '<tr class="cph_field_row cph_field_type_' . esc_attr($field['field_type']) . '" id="row_' . $field_id_attr . '">';
				echo '<td class="label"><label for="' . $field_id_attr . '">' . esc_html( $field['field_label'] ) . '</label></td>';
				echo '<td class="value">';

				switch ( $field['field_type'] ) {
					case 'text':
						echo '<input type="text" id="' . $field_id_attr . '" name="' . $field_name_attr . '" class="cph_input cph_text_input input-text" ' . $data_attrs . '/>';
						break;
					case 'number':
						$min = isset( $field['min_value'] ) ? ' min="' . esc_attr( $field['min_value'] ) . '"' : '';
						$max = isset( $field['max_value'] ) ? ' max="' . esc_attr( $field['max_value'] ) . '"' : '';
						$step = isset( $field['step_value'] ) ? ' step="' . esc_attr( $field['step_value'] ) . '"' : '';
						echo '<input type="number" id="' . $field_id_attr . '" name="' . $field_name_attr . '" class="cph_input cph_number_input input-text" ' . $min . $max . $step . $data_attrs . '/>';
						break;
					case 'select':
						echo '<select id="' . $field_id_attr . '" name="' . $field_name_attr . '" class="cph_select cph_option_field" ' . $data_attrs . '>';
						echo '<option value="">' . __( 'Choose an option', 'custom-prescription-handler' ) . '</option>'; // Default empty option
						if ( ! empty( $field['choices'] ) && is_array($field['choices']) ) {
							foreach ( $field['choices'] as $choice ) {
								$price_adj_data = !empty($choice['price_adjustment']) ? ' data-price-adjustment="' . esc_attr($choice['price_adjustment']) . '"' : '';
								echo '<option value="' . esc_attr( $choice['value'] ) . '"' . $price_adj_data . '>' . esc_html( $choice['label'] ) . '</option>';
							}
						}
						echo '</select>';
						break;
					case 'radio':
						if ( ! empty( $field['choices'] ) && is_array($field['choices']) ) {
							echo '<ul class="cph_radio_list">';
							foreach ( $field['choices'] as $choice_idx => $choice ) {
								$choice_id = $field_id_attr . '_' . $choice_idx;
								$price_adj_data = !empty($choice['price_adjustment']) ? ' data-price-adjustment="' . esc_attr($choice['price_adjustment']) . '"' : '';
								echo '<li>';
								echo '<input type="radio" id="' . $choice_id . '" name="' . $field_name_attr . '" value="' . esc_attr( $choice['value'] ) . '" class="cph_radio cph_option_field" ' . $price_adj_data . $data_attrs . '/>';
								echo '<label for="' . $choice_id . '">' . esc_html( $choice['label'] ) . '</label>';
								echo '</li>';
							}
							echo '</ul>';
						}
						break;
					case 'checkbox': // For multiple checkboxes, name should be an array
						$field_name_attr_array = 'cph_options[' . esc_attr( $field['field_name_id'] ) . '][]';
						if ( ! empty( $field['choices'] ) && is_array($field['choices']) ) {
							echo '<ul class="cph_checkbox_list">';
							foreach ( $field['choices'] as $choice_idx => $choice ) {
								$choice_id = $field_id_attr . '_' . $choice_idx;
								$price_adj_data = !empty($choice['price_adjustment']) ? ' data-price-adjustment="' . esc_attr($choice['price_adjustment']) . '"' : '';
								echo '<li>';
								echo '<input type="checkbox" id="' . $choice_id . '" name="' . $field_name_attr_array . '" value="' . esc_attr( $choice['value'] ) . '" class="cph_checkbox cph_option_field" ' . $price_adj_data . $data_attrs . '/>';
								echo '<label for="' . $choice_id . '">' . esc_html( $choice['label'] ) . '</label>';
								echo '</li>';
							}
							echo '</ul>';
						}
						break;
					// case 'file':
						// echo '<input type="file" id="' . $field_id_attr . '" name="' . $field_name_attr . '" class="cph_file_input" ' . $data_attrs . '/>';
						// break;
					default:
						echo __( 'Unsupported field type.', 'custom-prescription-handler' );
				}
				// Add description if available
				// if (!empty($field['description'])) {
				// 	echo '<small class="cph_field_description">' . esc_html($field['description']) . '</small>';
				// }
				echo '</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>'; // .cph_fields_table
			echo '</div>'; // .cph_option_group_display
		}
		echo '</div>'; // .cph_options_form_inner
	}
}

	/**
	 * Add custom prescription options data to the cart item.
	 *
	 * @param array $cart_item_data Current cart item data.
	 * @param int   $product_id     Product ID.
	 * @param int   $variation_id   Variation ID, if applicable.
	 * @return array Modified cart item data.
	 */
	public function add_cph_options_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
		if ( ! isset( $_POST['cph_options'] ) || empty( $_POST['cph_options'] ) ) {
			return $cart_item_data; // No CPH options submitted
		}

		$submitted_options = $_POST['cph_options']; // Not sanitized yet, sanitization happens during processing.
		$product_configuration = get_post_meta( $product_id, $this->meta_key, true );

		if ( empty( $product_configuration ) || empty( $product_configuration['option_groups'] ) ) {
			return $cart_item_data; // Product does not have CPH configuration
		}

		$cph_data_to_store = array();
		$total_options_price = 0;

		// Flatten the product configuration for easier lookup by field_name_id
		$configured_fields = array();
		foreach ( $product_configuration['option_groups'] as $group ) {
			if ( ! empty( $group['fields'] ) && is_array( $group['fields'] ) ) {
				foreach ( $group['fields'] as $field ) {
					if ( ! empty( $field['field_name_id'] ) ) {
						$configured_fields[ $field['field_name_id'] ] = $field;
					}
				}
			}
		}

		foreach ( $submitted_options as $field_name_id => $submitted_value ) {
			// Sanitize submitted value(s)
			if (is_array($submitted_value)) {
				$submitted_value = array_map('sanitize_text_field', $submitted_value);
			} else {
				$submitted_value = sanitize_text_field($submitted_value);
			}

			if ( isset( $configured_fields[ $field_name_id ] ) ) {
				$field_config = $configured_fields[ $field_name_id ];
				$field_label = $field_config['field_label'];
				$option_price = 0;
				$display_value = '';

				switch ( $field_config['field_type'] ) {
					case 'text':
					case 'number':
						$display_value = $submitted_value;
						// Price for text/number fields might be fixed or per character/unit - not implemented yet
						// For now, assume no direct price adjustment from text/number inputs themselves
						break;

					case 'select':
					case 'radio':
						if ( ! empty( $field_config['choices'] ) && is_array( $field_config['choices'] ) ) {
							foreach ( $field_config['choices'] as $choice ) {
								if ( $choice['value'] === $submitted_value ) {
									$display_value = $choice['label'];
									if ( ! empty( $choice['price_adjustment'] ) ) {
										$option_price = $this->parse_price_adjustment( $choice['price_adjustment'] );
									}
									break;
								}
							}
						}
						break;

					case 'checkbox': // Submitted value will be an array of selected choice values
						$selected_labels = array();
						$checkbox_total_price = 0;
						if ( is_array( $submitted_value ) && ! empty( $field_config['choices'] ) && is_array( $field_config['choices'] ) ) {
							foreach ( $field_config['choices'] as $choice ) {
								if ( in_array( $choice['value'], $submitted_value ) ) {
									$selected_labels[] = $choice['label'];
									if ( ! empty( $choice['price_adjustment'] ) ) {
										$checkbox_total_price += $this->parse_price_adjustment( $choice['price_adjustment'] );
									}
								}
							}
						}
						$display_value = implode( ', ', $selected_labels );
						$option_price = $checkbox_total_price;
						break;
				}

				if ( ! empty( $display_value ) || $display_value === '0') { // Allow '0' as a valid display value
					$cph_data_to_store[] = array(
						'field_name_id' => $field_name_id, // Store for potential future logic
						'label'         => $field_label,
						'value'         => $display_value, // This is the human-readable value/label
						'price'         => $option_price,  // Store the calculated price for this option
						'raw_value'     => $submitted_value // Store the actual submitted value
					);
					$total_options_price += $option_price;
				}
			}
		}

		if ( ! empty( $cph_data_to_store ) ) {
			$cart_item_data['cph_options'] = $cph_data_to_store;
			$cart_item_data['cph_options_price_total'] = $total_options_price;

			// Adjust product price in cart
			// Note: WooCommerce Subscriptions might have its own way of handling recurring totals.
			// This primarily affects the initial price.
			$product = wc_get_product( $variation_id ? $variation_id : $product_id );
			$base_price = $product->get_price( 'edit' ); // Get price without taxes etc.
			$new_price = $base_price + $total_options_price;
			$cart_item_data['data']->set_price( $new_price );
		}

		return $cart_item_data;
	}

	/**
	 * Parses a price adjustment string (e.g., "+10", "-5.50", "10%").
	 * For now, only fixed amounts are handled. Percentage would need base price context.
	 *
	 * @param string $adjustment_string The price adjustment string.
	 * @return float The parsed price adjustment.
	 */
	private function parse_price_adjustment( $adjustment_string ) {
		// Remove whitespace
		$adjustment_string = trim( $adjustment_string );

		// For now, only handle fixed amounts (positive or negative)
		// Percentage logic (e.g., "10%") would require passing the base price to calculate against.
		if ( is_numeric( $adjustment_string ) ) {
			return floatval( $adjustment_string );
		}
		// Could add more sophisticated parsing here for "+10", "-5", etc.
		// For simplicity, admin should enter "10" for +10, "-5" for -5.
		return floatval( $adjustment_string );
	}

	/**
	 * Display custom prescription options in the cart and checkout.
	 *
	 * @param array $item_data Default item data array.
	 * @param array $cart_item Cart item data.
	 * @return array Modified item data array.
	 */
	public function display_cph_options_in_cart( $item_data, $cart_item ) {
		if ( isset( $cart_item['cph_options'] ) && is_array( $cart_item['cph_options'] ) ) {
			foreach ( $cart_item['cph_options'] as $option ) {
				$display_value = esc_html( $option['value'] );
				if ( isset( $option['price'] ) && $option['price'] != 0 ) {
					// Use wc_price to format the price with currency symbol
					$price_formatted = wc_price( $option['price'] );
					$display_value .= ' (' . ( $option['price'] > 0 ? '+' : '' ) . $price_formatted . ')';
				}
				$item_data[] = array(
					'key'     => esc_html( $option['label'] ),
					'display' => $display_value,
					'class'   => 'cph-cart-option ' . sanitize_html_class( 'cph-option-' . $option['field_name_id'] ),
				);
			}
		}
		return $item_data;
	}

	/**
	 * Display custom prescription options in order item meta (emails and order view).
	 * This function is hooked to 'woocommerce_order_item_meta_start'.
	 * In Step 7, we will ensure 'cph_options' are saved to order item meta.
	 * For now, this function anticipates that structure.
	 *
	 * @param int             $item_id        Order item ID.
	 * @param WC_Order_Item   $item           Order item object.
	 * @param WC_Order        $order          Order object.
	 * @param bool            $plain_text     Whether this is for plain text email.
	 */
	public function display_cph_options_in_emails( $item_id, $item, $order, $plain_text = false ) {
		// Ensure this is a product item
		if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
			return;
		}

		// The data will be retrieved from order item meta, which we'll save in the next step (Step 7).
		// Let's assume the meta key will be '_cph_options' for the saved array.
		$cph_options = wc_get_order_item_meta( $item_id, '_cph_options', true );

		if ( $cph_options && is_array( $cph_options ) ) {
			if ( $plain_text ) {
				echo "\n"; // Start with a new line for better readability in plain text
				foreach ( $cph_options as $option ) {
					$display_text = esc_html( $option['label'] ) . ': ' . esc_html( $option['value'] );
					if ( isset( $option['price'] ) && $option['price'] != 0 ) {
						// For plain text, wc_price might include HTML, so we format manually or strip tags.
						// Let's assume a simple text representation.
						$price_text = ($option['price'] > 0 ? '+' : '') . strip_tags(wc_price( $option['price'], array('currency' => $order->get_currency()) ));
						$display_text .= ' (' . $price_text . ')';
					}
					echo $display_text . "\n";
				}
			} else { // HTML emails / order view
				echo '<div class="cph-order-item-options" style="margin-top: 5px;">';
				echo '<small>'; // Use small tags for a less prominent display if desired
				foreach ( $cph_options as $option ) {
					$display_html = '<strong>' . esc_html( $option['label'] ) . ':</strong> ' . wp_kses_post( $option['value'] ); // value might contain commas, etc.
					if ( isset( $option['price'] ) && $option['price'] != 0 ) {
						$price_html = wc_price( $option['price'], array('currency' => $order->get_currency()) );
						$display_html .= ' (' . ( $option['price'] > 0 ? '+' : '' ) . $price_html . ')';
					}
					echo '<br />' . $display_html;
				}
				echo '</small>';
				echo '</div>';
			}
		}
	}

	/*
	// Alternative for plain text emails if more control over item name is needed.
	public function add_cph_options_to_email_item_name( $item_name, $item, $is_plain_text = false ) {
		if ( $is_plain_text && is_a( $item, 'WC_Order_Item_Product' ) ) {
			$cph_options = wc_get_order_item_meta( $item->get_id(), '_cph_options', true );
			if ( $cph_options && is_array( $cph_options ) ) {
				$options_string_parts = array();
				foreach ( $cph_options as $option ) {
					$part = esc_html( $option['label'] ) . ': ' . esc_html( $option['value'] );
					if ( isset( $option['price'] ) && $option['price'] != 0 ) {
						$part .= ' (' . ($option['price'] > 0 ? '+' : '') . strip_tags(wc_price( $option['price'], array('currency' => $item->get_order()->get_currency()) )) . ')';
					}
					$options_string_parts[] = $part;
				}
				if ( !empty($options_string_parts) ) {
					$item_name .= "\n" . implode("\n", $options_string_parts);
				}
			}
		}
		return $item_name;
	}
	*/

	/**
	 * Save custom prescription options data to the order item meta.
	 *
	 * @param WC_Order_Item_Product $item          Order item object.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $values        Cart item values (includes our cph_options).
	 * @param WC_Order              $order         Order object.
	 */
	public function save_cph_options_to_order_item( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['cph_options'] ) && is_array( $values['cph_options'] ) ) {
			// Save the entire structured array for easy retrieval and detailed display (as used in Step 6 email display)
			$item->add_meta_data( '_cph_options', $values['cph_options'], true ); // true for unique meta

			// Additionally, you can add each option as a separate meta entry
			// if you want them to be displayed by default WooCommerce meta display functions
			// in some contexts (though Step 6 already handles custom display).
			// Example:
			/*
			foreach ( $values['cph_options'] as $option ) {
				$display_value_for_meta = esc_html( $option['value'] );
				if ( isset( $option['price'] ) && $option['price'] != 0 ) {
					$price_formatted = wc_price( $option['price'], array('currency' => $order->get_currency()) );
					// Note: wc_price can return HTML. If storing for non-HTML contexts, strip tags or format differently.
					$display_value_for_meta .= ' (' . ( $option['price'] > 0 ? '+' : '' ) . strip_tags($price_formatted) . ')';
				}
				$item->add_meta_data( esc_html( $option['label'] ), $display_value_for_meta );
			}
			*/
		}

		// If you stored total options price separately in cart item data, you could save it too.
		// if ( isset( $values['cph_options_price_total'] ) ) {
		//  $item->add_meta_data( '_cph_options_total_price', $values['cph_options_price_total'] );
		// }

		// Note: The item's total price already reflects the CPH options because we used $cart_item_data['data']->set_price()
		// in `add_cph_options_to_cart_item`. So, $item->set_total() or set_subtotal() modification here
		// for the CPH options price is generally not needed unless there's a very specific reason.
		// The line item's price should already be correct.
	}

} // End class CPH_Frontend_Display

// Instantiate the class (this will be done in the main plugin file)
// new CPH_Frontend_Display();
?>
