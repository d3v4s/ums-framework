$(document).ready(function() {
	/* submit event on confirm signup form to send XML HTTP request */
	$('#signup-confirm-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf'),
			actionUrl = $(this).attr('action'),
			$btn = $(this).find('#btn-resend-email'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
    	showLoading($btn);

    	/* success function */
    	funcSuccess = function(response) {
    		console.log(response);
			removeLoading($btn, 'Resend email');
			try {
				if (response.ntk !== undefined) $xf.val(response.ntk);
				showMessage(response.message, !response.success);
			} catch (e) {
				showMessage('Resend email failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Resend email');
			showMessage('Problem to contact server', true);
		};

    	sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});