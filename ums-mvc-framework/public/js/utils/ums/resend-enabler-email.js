$(document).ready(function() {
	/* click event on delete user button to send XML HTTP request */
	$('#resend-email-form').on('submit', function(event) {
		/* get button, url, token and serialize data */
		const $xf = $(this).find('#_xf_res_ml'),
			actionUrl = $(this).attr('action'),
			$btn = $(this).find('#btn-resend-email'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		var txtBttn = showLoading($btn);
		
		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, txtBttn);
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else if (response.ntk !== undefined) $xf.val(response.ntk);
				
			} catch (e) {
				showMessage('Resend email failed', true);
			}
		};
		
		/* fail function */
		funcFail = function() {
			removeLoading($btn, txtBttn);
			showMessage('Problem to contact server', true);
		};
		
		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});
