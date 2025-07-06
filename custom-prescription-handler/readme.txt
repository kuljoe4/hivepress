=== Custom Prescription Handler for WooCommerce ===
Contributors: Jules (AI Assistant)
Tags: woocommerce, product options, custom fields, prescription, lens options, product configurator
Requires at least: 5.5
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A custom WooCommerce extension to add detailed prescription and lens selection options to your products, allowing for dynamic pricing and customized orders.

== Description ==

The Custom Prescription Handler for WooCommerce allows shop administrators or vendors to define complex sets of options for products, specifically tailored for items like eyeglasses that require prescription details and lens customizations.

Key Features:

*   **Flexible Option Configuration:** Define groups of options (e.g., "Prescription", "Lens Type", "Coatings") directly on the WooCommerce product edit page.
*   **Multiple Field Types:** Supports Text Input, Number Input, Select Dropdowns, Radio Buttons, and Checkboxes for choices.
*   **Dynamic Price Adjustments:** Assign price adjustments (fixed amounts) to individual choices, which dynamically update the total product price on the frontend.
*   **Admin Interface:** User-friendly interface for adding, removing, and sorting option groups, fields, and choices.
*   **Frontend Display:** Clearly presents configured options to customers on the single product page.
*   **Cart & Order Integration:** Selected options and their prices are carried through to the cart, checkout, order emails, and admin order view.

This plugin provides a foundation for creating highly configurable products without relying solely on WooCommerce variations or third-party premium add-on plugins for basic option structures.

== Installation ==

1.  Upload the `custom-prescription-handler` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to a WooCommerce product edit screen. You will find a new meta box titled "Prescription & Lens Options" where you can configure options for that product.

== Frequently Asked Questions ==

= How do I add prescription options to a product? =

1.  Edit a WooCommerce product (or create a new one).
2.  Scroll down to the "Prescription & Lens Options" meta box.
3.  Click "Add Option Group" to create a section (e.g., "Right Eye Details").
4.  Enter a name for the group.
5.  Click "Add Field to this Group" within that group.
6.  Configure the field:
    *   **Field Label:** What the customer sees (e.g., "Sphere").
    *   **Field Name/ID:** A unique internal ID (e.g., `od_sphere`).
    *   **Field Type:** Choose from Text, Number, Select, Radio, or Checkbox.
    *   **Choices (for Select, Radio, Checkbox):** Add choices with a Label (customer-facing), Value (internal), and Price Adjustment (e.g., `10` for +$10, `-5` for -$5).
7.  Add more fields and groups as needed.
8.  Save/Update the product.

= What field types are supported? =

Currently, the plugin supports:
*   Text Input
*   Number Input
*   Select Dropdown
*   Radio Buttons
*   Checkboxes (for multiple selections)

File Upload and more advanced fields like date pickers are not yet implemented.

= How are prices for options calculated? =

When you define choices for Select, Radio, or Checkbox field types, you can enter a "Price Adjustment". This is a fixed numerical value (e.g., `20`, `-10`).
*   A positive number adds to the product price.
*   A negative number subtracts from the product price.
The frontend JavaScript dynamically updates the total price as users make selections. The final price calculation is also verified on the server-side when adding to the cart. Percentage-based adjustments are not currently supported.

= Is it compatible with product variations? =

Basic compatibility exists in that the options will appear on variable product pages. However, the pricing logic currently adds to the variation's selected price. More complex interactions, such as having different CPH options per variation or CPH options determining the variation, are not yet implemented. The JavaScript includes commented-out placeholders for potential integration points with variation changes.

= How is conditional logic handled? =

The admin interface includes placeholders for defining conditional logic (showing/hiding a field based on another field's selection). However, the full UI for defining these rules and the frontend JavaScript engine to execute complex conditions are not yet fully implemented. A very basic show/hide capability based on simple data attributes is present in the JS but requires manual setup of these attributes in the field configuration (not yet exposed in UI). This is an area for future development.

== Screenshots ==

1.  **Admin Configuration:** The "Prescription & Lens Options" meta box on the product edit page, showing option groups, fields, and choices being configured.
2.  **Frontend Display:** A single product page displaying the configured options to a customer.
3.  **Dynamic Pricing:** Example of the price updating on the frontend as a user selects different lens options.
4.  **Cart Display:** The cart page showing a product with its selected prescription options listed.
5.  **Admin Order View:** An order in the WordPress admin, showing the selected options as part of the line item details.

(Note: Actual screenshot images would be included in a live plugin repository.)

== Changelog ==

= 1.0.0 - YYYY-MM-DD =
*   Initial release.
*   Feature: Admin interface for defining option groups, fields (text, number, select, radio, checkbox), and choices with price adjustments.
*   Feature: Dynamic frontend display of options on single product pages.
*   Feature: JavaScript for live price updates based on selected options.
*   Feature: Server-side handling to add selected options and adjusted price to cart items.
*   Feature: Display of selected options in cart, checkout, order emails, and admin order view.
*   Feature: Basic styling for frontend and admin components.
*   Feature: Sortable groups, fields, and choices in the admin UI.

== Upgrade Notice ==

(No upgrade notices for the initial version.)
