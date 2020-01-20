$(document).ready(function() {
	/* click event on delete user button to send XML HTTP request */
	$('#lock-user-reset-form').on('submit', function(event) {
		/* get button, token, url and data */
		const $xf = $(this).find('#_xf_lck_rst'),
			actionUrl = $(this).attr('action'),
			$btn = $(this).find('#btn-lock-user-reset'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		var textBtn = showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, textBtn);
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else if (response.ntk !== undefined) $xf.val(response.ntk);
				
			} catch (e) {
				showMessage('Lock user reset failed', true);
			}
		};
		
		/* fail function */
		funcFail = function() {
			removeLoading($btn, textBtn);
			showMessage('Problem to contact server', true);
		};
		
		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});