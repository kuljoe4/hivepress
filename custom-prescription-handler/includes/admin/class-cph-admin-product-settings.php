<?php
/**
 * Admin Product Settings for Custom Prescription Handler.
 *
 * @package CustomPrescriptionHandler
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPH_Admin_Product_Settings Class.
 */
class CPH_Admin_Product_Settings {

	private $meta_key = '_cph_prescription_options';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_meta_box_data' ) );
		// Action to load admin scripts for this specific functionality if not already globally enqueued
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue admin scripts and styles for the product settings page.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		global $post_type;

		if ( 'product' === $post_type && ( 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix ) ) {
			wp_enqueue_style( 'cph-admin-product-settings-style', CPH_PLUGIN_URL . 'assets/css/admin-product-settings.css', array(), CPH_VERSION );
			wp_enqueue_script( 'cph-admin-product-settings-script', CPH_PLUGIN_URL . 'assets/js/admin-product-settings.js', array( 'jquery', 'jquery-ui-sortable' ), CPH_VERSION, true );

			// Pass data to script, like nonces for AJAX or existing settings for JS to render
			// wp_localize_script('cph-admin-product-settings-script', 'cph_admin_params', array(
			//    'nonce' => wp_create_nonce('cph_admin_nonce')
			// ));
		}
	}

	/**
	 * Adds the meta box.
	 */
	public function add_meta_box() {
		add_meta_box(
			'cph_prescription_options_meta_box', // ID
			__( 'Prescription & Lens Options', 'custom-prescription-handler' ), // Title
			array( $this, 'render_meta_box_content' ), // Callback
			'product', // Screen (post type)
			'normal', // Context (normal, side, advanced)
			'high' // Priority
		);
	}

	/**
	 * Renders the meta box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
		// Add a nonce field
		wp_nonce_field( 'cph_save_prescription_options_data', 'cph_prescription_options_nonce' );

		// Retrieve existing data if any
		$options_data = get_post_meta( $post->ID, $this->meta_key, true );
		if ( empty( $options_data ) || !is_array($options_data) ) {
			// Initialize with a default structure if empty or malformed
			$options_data = array( 'option_groups' => array() );
		}

		echo '<div id="cph_option_groups_wrapper">';

		// Button to add a new option group
		echo '<p><button type="button" id="cph_add_option_group_button" class="button">' . __( 'Add Option Group', 'custom-prescription-handler' ) . '</button></p>';

		echo '<div id="cph_option_groups_container">';

		if ( ! empty( $options_data['option_groups'] ) && is_array($options_data['option_groups']) ) {
			foreach ( $options_data['option_groups'] as $group_index => $group ) {
				$this->render_option_group_html( $group_index, $group );
			}
		} else {
			// Optionally render one empty group to start with via JS, or a message.
			echo '<p>' . __('No option groups defined yet. Click "Add Option Group" to start.', 'custom-prescription-handler') . '</p>';
		}

		echo '</div>'; // #cph_option_groups_container
		echo '</div>'; // #cph_option_groups_wrapper

		// JavaScript will handle adding new groups and fields dynamically.
		// A template for a new group and a new field will be defined in JS.
		$this->render_js_templates();
	}

	/**
	 * Renders HTML for a single option group and its fields.
	 * This function will be called by render_meta_box_content for existing groups,
	 * and its structure will be replicated by JavaScript for new groups.
	 */
	private function render_option_group_html( $group_index, $group_data = array() ) {
		$group_name = isset( $group_data['group_name'] ) ? esc_attr( $group_data['group_name'] ) : '';
		$fields     = isset( $group_data['fields'] ) && is_array($group_data['fields']) ? $group_data['fields'] : array();

		echo '<div class="cph_option_group postbox">';
		echo '<div class="handlediv" title="' . __('Click to toggle') . '"><br></div>';
		echo '<h3 class="hndle ui-sortable-handle"><span>';
		echo '<input type="text" name="' . $this->meta_key . '[option_groups][' . $group_index . '][group_name]" value="' . $group_name . '" placeholder="' . __( 'Group Name (e.g., Lens Details)', 'custom-prescription-handler' ) . '" class="cph_group_name_input" />';
		echo '</span></h3>';
		echo '<div class="inside">';
		echo '<button type="button" class="button button-link-delete cph_remove_option_group_button" style="float:right;">' . __( 'Remove Group', 'custom-prescription-handler' ) . '</button>';

		echo '<div class="cph_fields_container">';
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field_index => $field_data ) {
				$this->render_field_html( $group_index, $field_index, $field_data );
			}
		} else {
			echo '<p>' . __('No fields in this group yet.', 'custom-prescription-handler') . '</p>';
		}
		echo '</div>'; // .cph_fields_container

		echo '<p><button type="button" class="button cph_add_field_button">' . __( 'Add Field to this Group', 'custom-prescription-handler' ) . '</button></p>';
		echo '</div>'; // .inside
		echo '</div>'; // .cph_option_group
	}

	/**
	 * Renders HTML for a single field within an option group.
	 * This structure will be replicated by JavaScript for new fields.
	 */
	private function render_field_html( $group_index, $field_index, $field_data = array() ) {
		$field_label = isset( $field_data['field_label'] ) ? esc_attr( $field_data['field_label'] ) : '';
		$field_name_id = isset( $field_data['field_name_id'] ) ? esc_attr( $field_data['field_name_id'] ) : 'field_' . time() . '_' . rand(100,999); // Ensure unique default
		$field_type  = isset( $field_data['field_type'] ) ? esc_attr( $field_data['field_type'] ) : 'text';
		// ... other field properties like choices, price, conditional logic rules ...

		$base_name_path = $this->meta_key . '[option_groups][' . $group_index . '][fields][' . $field_index . ']';

		echo '<div class="cph_field options_group">'; // 'options_group' for WC styling
		echo '<p class="form-field">';
		echo '<label>' . __( 'Field Label:', 'custom-prescription-handler' ) . ' </label>';
		echo '<input type="text" name="' . $base_name_path . '[field_label]" value="' . $field_label . '" placeholder="' . __( 'e.g., Sphere (Right Eye)', 'custom-prescription-handler' ) . '" />';
		echo '</p>';

		echo '<p class="form-field">';
		echo '<label>' . __( 'Field Name/ID:', 'custom-prescription-handler' ) . ' </label>';
		echo '<input type="text" name="' . $base_name_path . '[field_name_id]" value="' . $field_name_id . '" placeholder="' . __( 'Unique ID, e.g., od_sphere', 'custom-prescription-handler' ) . '" class="cph_field_name_id_input" />';
		echo '<span class="description">' . __('A unique ID for this field (letters, numbers, underscores). Used for conditional logic and data handling.', 'custom-prescription-handler') . '</span>';
		echo '</p>';

		echo '<p class="form-field">';
		echo '<label>' . __( 'Field Type:', 'custom-prescription-handler' ) . ' </label>';
		echo '<select name="' . $base_name_path . '[field_type]" class="cph_field_type_select">';
		$field_types = array(
			'text'       => __( 'Text Input', 'custom-prescription-handler' ),
			'number'     => __( 'Number Input', 'custom-prescription-handler' ),
			'select'     => __( 'Select Dropdown', 'custom-prescription-handler' ),
			'radio'      => __( 'Radio Buttons', 'custom-prescription-handler' ),
			'checkbox'   => __( 'Checkboxes (Multiple Choices)', 'custom-prescription-handler' ),
			// 'file'    => __( 'File Upload', 'custom-prescription-handler' ), // File upload needs more complex handling
		);
		foreach ( $field_types as $val => $label ) {
			echo '<option value="' . esc_attr( $val ) . '" ' . selected( $field_type, $val, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
		echo '</p>';

		// Placeholder for choices (if select, radio, checkbox)
		echo '<div class="cph_field_choices_wrapper" style="' . (in_array($field_type, ['select', 'radio', 'checkbox']) ? '' : 'display:none;') . '">';
		echo '<h4>' . __('Choices', 'custom-prescription-handler') . '</h4>';
		echo '<div class="cph_choices_container">';
		$choices = isset($field_data['choices']) && is_array($field_data['choices']) ? $field_data['choices'] : array();
		if(!empty($choices)){
			foreach($choices as $choice_idx => $choice_data){
				$this->render_choice_html($base_name_path, $choice_idx, $choice_data);
			}
		}
		echo '</div>'; // .cph_choices_container
		echo '<button type="button" class="button cph_add_choice_button">' . __('Add Choice', 'custom-prescription-handler') . '</button>';
		echo '</div>'; // .cph_field_choices_wrapper

		// Placeholder for conditional logic
		echo '<div class="cph_field_conditional_logic_wrapper">';
		echo '<h4>' . __('Conditional Logic (Advanced - Placeholder)', 'custom-prescription-handler') . '</h4>';
		echo '<p class="description">' . __('Define rules to show/hide this field based on other selections. UI for this will be built out with JavaScript.', 'custom-prescription-handler') . '</p>';
		// Example: Show this field if [Field Name/ID] is [Value]
		echo '</div>';

		echo '<button type="button" class="button button-link-delete cph_remove_field_button">' . __( 'Remove Field', 'custom-prescription-handler' ) . '</button>';
		echo '<hr/>';
		echo '</div>'; // .cph_field
	}

	/**
	 * Renders HTML for a single choice within a field.
	 */
	private function render_choice_html($base_field_name_path, $choice_index, $choice_data = array()){
		$choice_label = isset($choice_data['label']) ? esc_attr($choice_data['label']) : '';
		$choice_value = isset($choice_data['value']) ? esc_attr($choice_data['value']) : '';
		$price_adj    = isset($choice_data['price_adjustment']) ? esc_attr($choice_data['price_adjustment']) : '';

		$base_name = $base_field_name_path . '[choices][' . $choice_index . ']';

		echo '<div class="cph_choice">';
		echo '<input type="text" name="' . $base_name . '[label]" value="' . $choice_label . '" placeholder="' . __('Choice Label (e.g., Red)', 'custom-prescription-handler') . '" />';
		echo '<input type="text" name="' . $base_name . '[value]" value="' . $choice_value . '" placeholder="' . __('Choice Value (e.g., red)', 'custom-prescription-handler') . '" />';
		echo '<input type="text" name="' . $base_name . '[price_adjustment]" value="' . $price_adj . '" placeholder="' . __('Price Adj. (+/- 10 or 5%)', 'custom-prescription-handler') . '" />';
		echo '<button type="button" class="button button-link-delete cph_remove_choice_button">' . __('Remove', 'custom-prescription-handler') . '</button>';
		echo '</div>';
	}


	/**
	 * Renders JS templates for new groups and fields.
	 * These will be cloned by JavaScript.
	 */
	private function render_js_templates() {
		?>
		<script type="text/html" id="tmpl-cph-option-group">
			<div class="cph_option_group postbox">
				<div class="handlediv" title="<?php esc_attr_e('Click to toggle', 'custom-prescription-handler'); ?>"><br></div>
				<h3 class="hndle ui-sortable-handle"><span>
					<input type="text" name="<?php echo $this->meta_key; ?>[option_groups][{{{ group_index }}}][group_name]" value="" placeholder="<?php esc_attr_e( 'Group Name (e.g., Lens Details)', 'custom-prescription-handler' ); ?>" class="cph_group_name_input" />
				</span></h3>
				<div class="inside">
					<button type="button" class="button button-link-delete cph_remove_option_group_button" style="float:right;"><?php esc_html_e( 'Remove Group', 'custom-prescription-handler' ); ?></button>
					<div class="cph_fields_container">
						<p><?php esc_html_e('No fields in this group yet.', 'custom-prescription-handler'); ?></p>
					</div>
					<p><button type="button" class="button cph_add_field_button"><?php esc_html_e( 'Add Field to this Group', 'custom-prescription-handler' ); ?></button></p>
				</div>
			</div>
		</script>

		<script type="text/html" id="tmpl-cph-field">
			<div class="cph_field options_group">
				<p class="form-field">
					<label><?php esc_html_e( 'Field Label:', 'custom-prescription-handler' ); ?> </label>
					<input type="text" name="<?php echo $this->meta_key; ?>[option_groups][{{{ group_index }}}][fields][{{{ field_index }}}][field_label]" value="" placeholder="<?php esc_attr_e( 'e.g., Sphere (Right Eye)', 'custom-prescription-handler' ); ?>" />
				</p>
				<p class="form-field">
					<label><?php esc_html_e( 'Field Name/ID:', 'custom-prescription-handler' ); ?> </label>
					<input type="text" name="<?php echo $this->meta_key; ?>[option_groups][{{{ group_index }}}][fields][{{{ field_index }}}][field_name_id]" value="field_{{{ new_field_id }}}" placeholder="<?php esc_attr_e( 'Unique ID, e.g., od_sphere', 'custom-prescription-handler' ); ?>" class="cph_field_name_id_input" />
					<span class="description"><?php esc_html_e('A unique ID for this field (letters, numbers, underscores). Used for conditional logic and data handling.', 'custom-prescription-handler'); ?></span>
				</p>
				<p class="form-field">
					<label><?php esc_html_e( 'Field Type:', 'custom-prescription-handler' ); ?> </label>
					<select name="<?php echo $this->meta_key; ?>[option_groups][{{{ group_index }}}][fields][{{{ field_index }}}][field_type]" class="cph_field_type_select">
						<?php
						$field_types = array(
							'text'       => __( 'Text Input', 'custom-prescription-handler' ),
							'number'     => __( 'Number Input', 'custom-prescription-handler' ),
							'select'     => __( 'Select Dropdown', 'custom-prescription-handler' ),
							'radio'      => __( 'Radio Buttons', 'custom-prescription-handler' ),
							'checkbox'   => __( 'Checkboxes (Multiple Choices)', 'custom-prescription-handler' ),
						);
						foreach ( $field_types as $val => $label ) {
							echo '<option value="' . esc_attr( $val ) . '">' . esc_html( $label ) . '</option>';
						}
						?>
					</select>
				</p>
				<div class="cph_field_choices_wrapper" style="display:none;">
					<h4><?php esc_html_e('Choices', 'custom-prescription-handler'); ?></h4>
					<div class="cph_choices_container"></div>
					<button type="button" class="button cph_add_choice_button"><?php esc_html_e('Add Choice', 'custom-prescription-handler'); ?></button>
				</div>
				<div class="cph_field_conditional_logic_wrapper">
					<h4><?php esc_html_e('Conditional Logic (Advanced - Placeholder)', 'custom-prescription-handler'); ?></h4>
					 <p class="description"><?php esc_html_e('Define rules to show/hide this field based on other selections. UI for this will be built out with JavaScript.', 'custom-prescription-handler'); ?></p>
				</div>
				<button type="button" class="button button-link-delete cph_remove_field_button"><?php esc_html_e( 'Remove Field', 'custom-prescription-handler' ); ?></button>
				<hr/>
			</div>
		</script>

		<script type="text/html" id="tmpl-cph-choice">
			<div class="cph_choice">
				<input type="text" name="<?php echo $this->meta_key; ?>[option_groups][{{{ group_index }}}][fields][{{{ field_index }}}][choices][{{{ choice_index }}}][label]" value="" placeholder="<?php esc_attr_e('Choice Label', 'custom-prescription-handler'); ?>" />
				<input type="text" name="<?php echo $this->meta_key; ?>[option_groups][{{{ group_index }}}][fields][{{{ field_index }}}][choices][{{{ choice_index }}}][value]" value="" placeholder="<?php esc_attr_e('Choice Value', 'custom-prescription-handler'); ?>" />
				<input type="text" name="<?php echo $this->meta_key; ?>[option_groups][{{{ group_index }}}][fields][{{{ field_index }}}][choices][{{{ choice_index }}}][price_adjustment]" value="" placeholder="<?php esc_attr_e('Price Adj.', 'custom-prescription-handler'); ?>" />
				<button type="button" class="button button-link-delete cph_remove_choice_button"><?php esc_html_e('Remove', 'custom-prescription-handler'); ?></button>
			</div>
		</script>
		<?php
	}


	/**
	 * Saves the meta box data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_meta_box_data( $post_id ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['cph_prescription_options_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['cph_prescription_options_nonce'], 'cph_save_prescription_options_data' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if the CPH options data is set in POST.
		if ( ! isset( $_POST[ $this->meta_key ] ) || ! is_array( $_POST[ $this->meta_key ] ) ) {
			// If nothing is set, maybe delete existing meta or save an empty structure.
			// For now, let's assume if it's not there, we might want to clear it.
			// Or, only save if it's a valid array to prevent accidental deletion on other save_post actions.
			// For robustness, only proceed if our specific data structure is passed.
			delete_post_meta( $post_id, $this->meta_key ); // Or update with empty if that's desired.
			return;
		}

		// Sanitize and prepare the data.
		// This needs a recursive sanitization function.
		$sanitized_data = $this->sanitize_options_data( $_POST[ $this->meta_key ] );

		// Update the meta field in the database.
		update_post_meta( $post_id, $this->meta_key, $sanitized_data );
	}

	/**
	 * Recursively sanitize the options data.
	 *
	 * @param array $data The data to sanitize.
	 * @return array The sanitized data.
	 */
	private function sanitize_options_data( $data ) {
		$sanitized_data = array();

		if ( ! is_array( $data ) ) {
			return $sanitized_data; // Should always be an array at the top level.
		}

		// Sanitize option_groups - expected to be an array of groups
		if ( isset( $data['option_groups'] ) && is_array( $data['option_groups'] ) ) {
			$sanitized_data['option_groups'] = array();
			foreach ( $data['option_groups'] as $group_key => $group_value ) {
				if (is_array($group_value)) { // Each group should be an array
					$sanitized_group = array();
					$sanitized_group['group_name'] = isset( $group_value['group_name'] ) ? sanitize_text_field( $group_value['group_name'] ) : '';

					// Sanitize fields within the group
					if ( isset( $group_value['fields'] ) && is_array( $group_value['fields'] ) ) {
						$sanitized_group['fields'] = array();
						foreach ( $group_value['fields'] as $field_key => $field_value ) {
							if(is_array($field_value)){
								$sanitized_field = array();
								$sanitized_field['field_label'] = isset( $field_value['field_label'] ) ? sanitize_text_field( $field_value['field_label'] ) : '';
								$sanitized_field['field_name_id'] = isset( $field_value['field_name_id'] ) ? sanitize_key( $field_value['field_name_id'] ) : ''; // sanitize_key for IDs
								$sanitized_field['field_type'] = isset( $field_value['field_type'] ) ? sanitize_text_field( $field_value['field_type'] ) : 'text';
								// Sanitize choices
								if (isset($field_value['choices']) && is_array($field_value['choices'])) {
									$sanitized_field['choices'] = array();
									foreach ($field_value['choices'] as $choice_val) {
										if(is_array($choice_val)){
											$sanitized_choice = [];
											$sanitized_choice['label'] = isset($choice_val['label']) ? sanitize_text_field($choice_val['label']) : '';
											$sanitized_choice['value'] = isset($choice_val['value']) ? sanitize_text_field($choice_val['value']) : ''; // Value could be numeric or text
											$sanitized_choice['price_adjustment'] = isset($choice_val['price_adjustment']) ? sanitize_text_field($choice_val['price_adjustment']) : ''; // Needs more specific sanitization for price
											$sanitized_field['choices'][] = $sanitized_choice;
										}
									}
								}
								// Sanitize conditional logic rules (placeholder for now)
								// $sanitized_field['conditional_logic'] = isset( $field_value['conditional_logic'] ) ? $this->sanitize_conditional_logic( $field_value['conditional_logic'] ) : array();
								$sanitized_group['fields'][] = $sanitized_field;
							}
						}
					}
					$sanitized_data['option_groups'][] = $sanitized_group;
				}
			}
		}
		return $sanitized_data;
	}
}

// Instantiate the class
// new CPH_Admin_Product_Settings(); // This will be instantiated by the main plugin file.

?>
