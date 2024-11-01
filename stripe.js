$(document).ready(function(){
	$('.custom_dollar_amt').keyup(function() {
		if($(this).val()!='') { $('.cardAmount').attr('checked', false); }
	});
	
	$('.cardAmount').mouseup(function() {
		$('.cardAmount').attr('checked', false);
		$(this).attr('checked', true);
		$('.custom_dollar_amt').val('');
	});
	
	// If the keys are not the 'live' ones
	if(isLiveKeys==='false') {
		var dom = '<div class="stripe-payment-form-warning">';
		dom += '<h3>Demo Mode</h3>';
		dom += '<p>Use the the credit card number <strong>4242-4242-4242</strong> and ';
		dom += 'a CVC of <strong>any 3 or 4 digit number</strong> for testing.</p>';
		dom += '</div>';
		$('#stripe-payment-wrap').prepend(dom);
		$('#cardNumber').val('4242424242424242');
		$('#cardCvc').val('123');
		$('#FullName').val('John Doe');
		$('#Zip').val('63116');
		$('#Email').val('john.doe@example.com');
	}

	// Set the public key for use by Stripe.com
	Stripe.setPublishableKey(stripePublishable);

	// Automatically add the autocomplete='off' attribute to all the input fields
	$("input").attr("autocomplete", "off");

	// Sanitize and validate all input elements
	$("input").blur(function(){
		var input = $(this);
		sanitize(input);
		validate(input);
	});

	// Initial validation of the amount
	$('.cardAmount').blur();
	
	// Bind to the submit for the form
	$("#stripe-payment-form").submit(function(e) {
		// Do not submit the form.
		e.preventDefault();
		// Check for configuration errors
		if($('.stripe-payment-config-errors').length>0) {
			alert('Fix the configuration errors before continuing.');
			return false;
		}
		
		// Lock the form so no change or double submission occurs
		lock_form();
		
		// Trigger validation
// 		if(!validateForm()) {
// 			// The form is not valid...exit early
// 			unlock_form();
// 			return false;
// 		}
		
		// Get the form values
		var params = {};
		params['name'] 			= $('#FullName').val();
		params['number'] 		= $('#cardNumber').val();
		params['cvc']				= $('#cardCvc').val();
		params['exp_month'] = $('#cardExpiryMonth').val();
		params['exp_year']	= $('#cardExpiryYear').val();
		
		var amount = 0;
		if($('.custom_dollar_amt').val()!='') {
			// Get the charge amount and convert to cents
			var amount = $('.custom_dollar_amt').val()*100;
		} else {
			var amount = $('.cardAmount:checked').val();
		}
		
		// Validate card information using Stripe.com.
		//	Note: createToken returns immediately. The card
		//	is not charged at this time (only validated).
		//	The card holder info is HTTPS posted to Stripe.com
		//	for validation. The response contains a 'token'
		//	that we can use on our server.
		progress('Validating card data…');
		Stripe.createToken(params, function(status, response){
			if (response.error) {
				// Show the error and unlock the form.
				progress(response.error.message);
				unlock_form();
				return false;
			}
			
			// Collect additional info to post to our server.
			//	Note: We are not posting any card holder info.
			//	We only include the 'token' provided by Stripe.com.
			var charge = {};
			charge['action']	= 'stripe_plugin_process_card';
			charge['token']		= response['id'];
			charge['amount']	= amount;
			charge['email']		= $('#Email').val();
			charge['action']	= 'stripe_plugin_process_card';
			charge['zip']		= $('#Zip').val();
			
			// Our other Data
			charge['fullname']			= $('#FullName').val();
			if($('#Address1').length>0) {
				charge['address1']		= $('#Address1').val();
			}
			if($('#Address2').length>0) {
				charge['address2']		= $('#Address2').val();
			}
			if($('#City').length>0) {
				charge['city']		= $('#City').val();
			}
			if($('#State').length>0) {
				charge['state']		= $('#State').val();
			}
			if($('#Employer').length>0) {
				charge['employer']		= $('#Employer').val();
			}
			if($('#Occupation').length>0) {
				charge['occupation']	= $('#Occupation').val();
			}
			console.log(charge);
			progress('Submitting charge…');
			$.post('/wp-admin/admin-ajax.php', charge, function(response){
				// Try to parse the response (expecting JSON).
				try {
					response = JSON.parse(response);
				} catch (err) {
					// Invalid JSON.
					if(!$.trim(response).length) {
						response = { error: 'Server returned empty response during charge attempt'};
					} else {
						response = {error: 'Server returned invalid response:<br /><br />' + response};
					}
				}

				if(response['success']){
					// Card was successfully charged. Replace the form with a
					// dynamically generated receipt.
					$('form#stripe-payment-form').hide();
					$('#stripe-msgs').html("<h4>Thank You for your donation of <b>$" + response['amount'] + "</b> to our campaign!</h4><p>Your transaction ID receipt is: " + response['id'] + "</p>")
					$("<p><a href='javascript:void(0);' class='red'>Make another charge</a></p>").click(function(){ location.href = location.href; }).appendTo('#stripe-msgs');
					$('#stripe-msgs').slideDown();
					progress('success');
				} else {
					// Show the error.
					progress(response['error']);
				}
				// Unlock the form.
				unlock_form();
			});
		});
	});
});

// Lock and unlock the form. This prevents changes or
//	double submissions during payment processing.
function lock_form() {
	$("#stripe-payment-form input").not('.amount').attr("disabled", "disabled");
	$("#stripe-payment-form select").attr("disabled", "disabled");
	$("#stripe-payment-form button").attr("disabled", "disabled");
}
function unlock_form() {
	$("#stripe-payment-form input").not('.amount').removeAttr("disabled");
	$("#stripe-payment-form select").removeAttr("disabled");
	$("#stripe-payment-form button").removeAttr("disabled");
}

// Helper function to display progress messages.
function progress(msg){
	$('.stripe-payment-form-row-progress span.message').html(msg);
}

// Validation helpers.
function validateForm() {
	var isValid = true;
	$("input").each(function(){
		sanitize($(this));
		isValid = validate($(this)) && isValid;
	});
	return isValid;
}

function sanitize(elem) {
	var value = $.trim(elem.val());
	if(elem.hasClass("number")){
		value = value.replace(/[^\d]+/g, '');
	}
	if(elem.hasClass("amount")){
		value = value.replace(/[^\d\.]+/g, '');
		if(value.length) value = parseFloat(value).toFixed(0);
	}
	elem.val(value);
}
function validate(elem) {
	var row = elem.closest('.stripe-payment-form-row');
	var error = $('.error', row);
	var value = $.trim(elem.val());
	if(elem.hasClass("required") && !value.length){
		error.html('Required.');
		return false;
	}
// 	if(elem.attr('id')=='eligible') {
// 		if(elem.attr('checked')!='checked') {
// 			error.html('Required.');
// 			return false;
// 		}
// 	}
	if(elem.hasClass("amount") && value<0.50){
		error.html('Minimum charge is $0.50');
		return false;
	}
	error.html('');
	return true;
}