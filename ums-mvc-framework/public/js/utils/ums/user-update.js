$(document).ready(function() {
	/* submit event on user update form to send XML HTTP request */
	$('#user-update-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-update'),
			actionUrl = $(this).attr('action'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Update');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('User update failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Update');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});