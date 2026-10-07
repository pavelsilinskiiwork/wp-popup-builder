/**
 * Pavel Silinskii Popup Builder — popup editor meta boxes.
 *
 * jQuery is used only because wpColorPicker is a jQuery plugin.
 */
jQuery(function ($) {
	'use strict';

	$('.pspb-color-field').wpColorPicker();

	var triggerFields = {
		delay: '.pspb-field-delay',
		scroll: '.pspb-field-scroll',
		click: '.pspb-field-selector'
	};

	function toggleTriggerFields() {
		var current = $('input[name="_pspb_trigger"]:checked').val();
		$.each(triggerFields, function (trigger, selector) {
			$(selector).toggle(trigger === current);
		});
	}

	function toggleSpecificPages() {
		$('.pspb-field-specific-pages').toggle($('select[name="_pspb_display_pages"]').val() === 'specific');
	}

	$(document).on('change', 'input[name="_pspb_trigger"]', toggleTriggerFields);
	$(document).on('change', 'select[name="_pspb_display_pages"]', toggleSpecificPages);

	toggleTriggerFields();
	toggleSpecificPages();
});
