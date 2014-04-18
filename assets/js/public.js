(function ($) {
	$(document).ready(function () {
		var shipping_alt = $("#shipping_alt");
		var billing_alt = $("#billing_alt");

		shipping_alt.val("0");
		billing_alt.val("0");

		shipping_alt.on("change", function () {
			$.post(
				WCMA_Ajax.ajaxurl, {
					action               : 'alt_change',
					id                   : $(this).val(),
					wc_multiple_addresses: WCMA_Ajax.wc_multiple_addresses
				}, function (response) {
					$('#shipping_address_1').val(response.shipping_address_1);
					$('#shipping_address_2').val(response.shipping_address_2);
					$('#shipping_city').val(response.shipping_city);
					$('#shipping_company').val(response.shipping_company);
					$('#shipping_country').val(response.shipping_country);
					$("#shipping_country_chosen").find('span').html(response.shipping_country_text);
					$('#shipping_first_name').val(response.shipping_first_name);
					$('#shipping_last_name').val(response.shipping_last_name);
					$('#shipping_postcode').val(response.shipping_postcode);
					$('#shipping_state').val(response.shipping_state);
				}
			);
			return false;
		});

		billing_alt.on("change", function () {
			$.post(
				WCMA_Ajax.ajaxurl, {
					action               : 'alt_change',
					id                   : $(this).val(),
					wc_multiple_addresses: WCMA_Ajax.wc_multiple_addresses
				}, function (response) {
					$('#billing_address_1').val(response.shipping_address_1);
					$('#billing_address_2').val(response.shipping_address_2);
					$('#billing_city').val(response.shipping_city);
					$('#billing_company').val(response.shipping_company);
					$('#billing_country').val(response.shipping_country);
					$("#billing_country_chosen").find('span').html(response.shipping_country_text);
					$('#billing_first_name').val(response.shipping_first_name);
					$('#billing_last_name').val(response.shipping_last_name);
					$('#billing_postcode').val(response.shipping_postcode);
					$('#billing_state').val(response.shipping_state);
				}
			);
			return false;
		});
	});
})(jQuery);