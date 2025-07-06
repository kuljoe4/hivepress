(function ($) {
    'use strict';

    $(document).ready(function () {
        // Make option groups sortable
        $('#cph_option_groups_container').sortable({
            items: '.cph_option_group',
            handle: '.hndle',
            placeholder: 'ui-sortable-placeholder',
            axis: 'y',
            update: function () {
                updateGroupIndexes();
            }
        });

        // Make fields within groups sortable
        $('body').on('mouseenter', '.cph_fields_container', function() {
            if (!$(this).data('sortable-init')) {
                $(this).sortable({
                    items: '.cph_field',
                    handle: '.form-field:first-child label', // Crude handle, can be improved
                    placeholder: 'ui-sortable-placeholder',
                    axis: 'y',
                    update: function(event, ui) {
                        updateFieldIndexes($(this));
                    }
                }).data('sortable-init', true);
            }
        });

        // Make choices within fields sortable
        $('body').on('mouseenter', '.cph_choices_container', function() {
            if (!$(this).data('sortable-init')) {
                $(this).sortable({
                    items: '.cph_choice',
                    placeholder: 'ui-sortable-placeholder', // Smaller placeholder
                    axis: 'y',
                    update: function(event, ui) {
                       updateChoiceIndexes($(this));
                    }
                }).data('sortable-init', true);
            }
        });


        // Add Option Group
        $('#cph_add_option_group_button').on('click', function () {
            let groupIndex = $('#cph_option_groups_container .cph_option_group').length;
            if ($('#cph_option_groups_container p').length > 0) { // Remove "No groups" message
                $('#cph_option_groups_container p').remove();
                groupIndex = 0; // Start fresh
            }

            let groupTemplate = wp.template('cph-option-group');
            let newGroupHtml = groupTemplate({ group_index: groupIndex });
            $('#cph_option_groups_container').append(newGroupHtml);
            updateGroupIndexes(); // Ensure indexes are correct even if starting from empty
        });

        // Remove Option Group
        $('body').on('click', '.cph_remove_option_group_button', function () {
            if (confirm('Are you sure you want to remove this option group and all its fields?')) {
                $(this).closest('.cph_option_group').remove();
                updateGroupIndexes();
                if ($('#cph_option_groups_container .cph_option_group').length === 0) {
                     $('#cph_option_groups_container').html('<p>No option groups defined yet. Click "Add Option Group" to start.</p>');
                }
            }
        });

        // Add Field to Group
        $('body').on('click', '.cph_add_field_button', function () {
            let $group = $(this).closest('.cph_option_group');
            let $fieldsContainer = $group.find('.cph_fields_container');
            let groupIndex = $group.index(); // More reliable way to get index after sorting
            let fieldIndex = $fieldsContainer.find('.cph_field').length;

            if ($fieldsContainer.find('p').length > 0) { // Remove "No fields" message
                $fieldsContainer.find('p').remove();
                fieldIndex = 0;
            }

            let newFieldId = 'new_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
            let fieldTemplate = wp.template('cph-field');
            let newFieldHtml = fieldTemplate({
                group_index: groupIndex,
                field_index: fieldIndex,
                new_field_id: newFieldId // For default unique ID
            });
            $fieldsContainer.append(newFieldHtml);
            updateFieldIndexes($fieldsContainer);
        });

        // Remove Field from Group
        $('body').on('click', '.cph_remove_field_button', function () {
            if (confirm('Are you sure you want to remove this field?')) {
                let $fieldsContainer = $(this).closest('.cph_fields_container');
                $(this).closest('.cph_field').remove();
                updateFieldIndexes($fieldsContainer);
                 if ($fieldsContainer.find('.cph_field').length === 0) {
                     $fieldsContainer.html('<p>No fields in this group yet.</p>');
                }
            }
        });

        // Add Choice to Field
        $('body').on('click', '.cph_add_choice_button', function () {
            let $field = $(this).closest('.cph_field');
            let $choicesContainer = $field.find('.cph_choices_container');
            let groupIndex = $field.closest('.cph_option_group').index();
            let fieldIndex = $field.index();
            let choiceIndex = $choicesContainer.find('.cph_choice').length;

            let choiceTemplate = wp.template('cph-choice');
            let newChoiceHtml = choiceTemplate({
                group_index: groupIndex,
                field_index: fieldIndex,
                choice_index: choiceIndex
            });
            $choicesContainer.append(newChoiceHtml);
            updateChoiceIndexes($choicesContainer);
        });

        // Remove Choice from Field
        $('body').on('click', '.cph_remove_choice_button', function () {
            let $choicesContainer = $(this).closest('.cph_choices_container');
            $(this).closest('.cph_choice').remove();
            updateChoiceIndexes($choicesContainer);
        });


        // Show/hide choices wrapper based on field type
        $('body').on('change', '.cph_field_type_select', function () {
            let $field = $(this).closest('.cph_field');
            let $choicesWrapper = $field.find('.cph_field_choices_wrapper');
            if (['select', 'radio', 'checkbox'].includes($(this).val())) {
                $choicesWrapper.slideDown();
            } else {
                $choicesWrapper.slideUp();
            }
        });

        // Function to re-index group names
        function updateGroupIndexes() {
            $('#cph_option_groups_container .cph_option_group').each(function (newGroupIndex) {
                $(this).find('[name*="[option_groups]"]').each(function () {
                    let currentName = $(this).attr('name');
                    let newName = currentName.replace(/\[option_groups\]\[\d+\]/, '[option_groups][' + newGroupIndex + ']');
                    $(this).attr('name', newName);
                });
                // Update group_index data in templates for fields inside this group for future additions
                $(this).find('.cph_add_field_button').data('group-index', newGroupIndex);
            });
        }

        // Function to re-index field names within a group
        function updateFieldIndexes($fieldsContainer) {
            let groupIndex = $fieldsContainer.closest('.cph_option_group').index();
            $fieldsContainer.find('.cph_field').each(function (newFieldIndex) {
                $(this).find('[name*="[fields]"]').each(function () {
                    let currentName = $(this).attr('name');
                    // Regex to replace both group and field index for robustness
                    let newName = currentName.replace(/\[option_groups\]\[\d+\]\[fields\]\[\d+\]/, '[option_groups][' + groupIndex + '][fields][' + newFieldIndex + ']');
                    $(this).attr('name', newName);
                });
                 // Update indexes for choices within this field
                updateChoiceIndexes($(this).find('.cph_choices_container'));
            });
        }

        // Function to re-index choice names within a field
        function updateChoiceIndexes($choicesContainer) {
            let $field = $choicesContainer.closest('.cph_field');
            if (!$field.length) return;

            let groupIndex = $field.closest('.cph_option_group').index();
            let fieldIndex = $field.index();

            $choicesContainer.find('.cph_choice').each(function (newChoiceIndex) {
                $(this).find('[name*="[choices]"]').each(function () {
                    let currentName = $(this).attr('name');
                    let newName = currentName.replace(/\[option_groups\]\[\d+\]\[fields\]\[\d+\]\[choices\]\[\d+\]/,
                                                    '[option_groups][' + groupIndex + '][fields][' + fieldIndex + '][choices][' + newChoiceIndex + ']');
                    $(this).attr('name', newName);
                });
            });
        }

        // Make sure PostBox toggles work for dynamically added content
        // WordPress uses this class for toggling.
        // We might need to re-initialize if groups are added dynamically and WP doesn't pick it up.
        // For now, relying on WP's built-in handling for .postbox
         if (typeof postboxes !== 'undefined' && typeof postboxes.add_postbox_toggles !== 'undefined') {
            // This is a bit of a hack, might need more specific targeting if it causes issues.
            // The idea is to re-run WP's toggle setup if we dynamically add postboxes.
            // However, the default event delegation by WP might cover this.
            // If toggles don't work on newly added groups, this is where to look.
        }

        // Initial setup for existing fields (e.g. show/hide choices wrapper)
        $('.cph_field_type_select').trigger('change');

    }); // End document ready
})(jQuery);
