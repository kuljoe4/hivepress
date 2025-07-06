// Custom Prescription Handler Frontend Script
(function ($) {
    'use strict';

    $(document).ready(function () {
        const $wrapper = $('#cph_prescription_options_wrapper');
        if (!$wrapper.length) {
            return; // No options on this page
        }

        const $priceDisplayArea = $('#cph_dynamic_price_display_area');
        const baseProductPrice = parseFloat($('#cph_base_product_price').val());
        // In a real scenario, you'd get currency format settings via wp_localize_script
        const currencySymbol = '$'; // Placeholder, get from WooCommerce settings via localize

        function formatPrice(price) {
            // Basic price formatting. WooCommerce has wc_price() in PHP.
            // For JS, you might use Intl.NumberFormat or a simpler fixed decimal approach.
            return currencySymbol + price.toFixed(2);
        }

        function calculateTotalPrice() {
            let totalOptionPrice = 0;

            // Iterate over selected radio buttons
            $wrapper.find('.cph_radio:checked').each(function () {
                const priceAdjustment = $(this).data('price-adjustment');
                if (priceAdjustment) {
                    totalOptionPrice += parseFloat(priceAdjustment);
                }
            });

            // Iterate over selected options in dropdowns
            $wrapper.find('.cph_select option:selected').each(function () {
                if ($(this).val() === '') return; // Skip placeholder "Choose an option"
                const priceAdjustment = $(this).data('price-adjustment');
                if (priceAdjustment) {
                    totalOptionPrice += parseFloat(priceAdjustment);
                }
            });

            // Iterate over selected checkboxes
            $wrapper.find('.cph_checkbox:checked').each(function () {
                const priceAdjustment = $(this).data('price-adjustment');
                if (priceAdjustment) {
                    totalOptionPrice += parseFloat(priceAdjustment);
                }
            });

            // Text and Number inputs might also have a base price or affect price via complex rules
            // For now, this example focuses on price adjustments from choices.
            // A more complex system might involve data-price-per-unit or similar for number fields.

            const finalPrice = baseProductPrice + totalOptionPrice;
            return finalPrice;
        }

        function updateDisplayedPrice() {
            const totalPrice = calculateTotalPrice();
            if ($priceDisplayArea.length) {
                // Attempt to mimic WooCommerce price display structure if possible
                // This is a simplified version.
                let priceHTML = '<p class="price cph-total-price"><span class="woocommerce-Price-amount amount"><bdi>' + formatPrice(totalPrice) + '</bdi></span></p>';
                $priceDisplayArea.html(priceHTML);
            }
            // Also, update the main product price display if the theme uses a standard WooCommerce hook/class
            // This can be theme-dependent and tricky.
            // Example: $('form.cart .price .woocommerce-Price-amount').html(formatPrice(totalPrice));
        }

        function applyConditionalLogic() {
            // This is a VERY basic example. A full implementation would parse JSON rules.
            // For now, let's assume a simple data attribute setup for demonstration.
            // e.g. data-conditional-target="#cph_field_some_id" data-conditional-value="some_value"
            // This would require the target field to be an input, select, or radio group.

            $('.cph_field_row[data-conditional-target-field-id]').each(function(){
                const $dependentFieldRow = $(this);
                const targetFieldSelector = '#' + $dependentFieldRow.data('conditional-target-field-id');
                const targetValue = $dependentFieldRow.data('conditional-target-value'); // Could be string or array of strings

                let $targetField = $(targetFieldSelector);
                let conditionMet = false;

                if ($targetField.length) {
                    let actualValue;
                    if ($targetField.is(':radio')) {
                        actualValue = $('input[name="' + $targetField.attr('name') + '"]:checked').val();
                    } else if ($targetField.is(':checkbox')) { // For single checkbox as a trigger
                        actualValue = $targetField.is(':checked') ? $targetField.val() : '';
                    } else { // select, input[type=text], input[type=number]
                        actualValue = $targetField.val();
                    }

                    if (Array.isArray(targetValue)) {
                        conditionMet = targetValue.includes(actualValue);
                    } else {
                        conditionMet = (actualValue == targetValue);
                    }
                }

                if (conditionMet) {
                    $dependentFieldRow.slideDown();
                } else {
                    $dependentFieldRow.slideUp();
                     // If a field is hidden, its selected value should ideally not contribute to the price
                     // or its value should be cleared. This adds complexity.
                     // For now, we'll just hide it. Recalculating price after hiding is important.
                }
            });
        }

        // Attach event handlers
        $wrapper.on('change input', '.cph_input, .cph_select, .cph_radio, .cph_checkbox', function () {
            // Order matters: apply logic first, then update price based on visible/selected fields.
            applyConditionalLogic();
            updateDisplayedPrice();
        });

        // Initial setup on page load
        applyConditionalLogic();
        updateDisplayedPrice();

        // If product variations are also present, ensure compatibility or decide on interaction.
        // For example, CPH options might only appear after a variation is fully selected.
        // This example assumes CPH operates independently or on simple products.
        // $(document).on('found_variation', 'form.cart', function(event, variation) {
        //    // Variation selected, perhaps update baseProductPrice and re-calculate CPH
        //    baseProductPrice = parseFloat(variation.display_price);
        //    $('#cph_base_product_price').val(baseProductPrice);
        //    updateDisplayedPrice();
        // });
        // $(document).on('reset_data', 'form.cart', function(event) {
        //    // Variation reset, reset CPH to base product if needed
        // });

    }); // End document ready
})(jQuery);
