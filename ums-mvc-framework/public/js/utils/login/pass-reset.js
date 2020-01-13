$(document).ready(function () {
	/* submit event on reset password form to send XML HTTP request */
	$('#reset-pass-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $cryptElem = $(this).find('.send-ajax-crypt'),
			$btn = $(this).find('#btn-reset-pass'),
			actionUrl = $(this).attr('action'),
			$xf = $(this).find('#_xf');

		/* serialize data */
		var data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		try {
			/* crypt password and append on data */
			data += '&' + cryptSerialize($cryptElem);
		} catch (e) {
			removeLoading($btn, 'Reset');
			showMessage('Reset password failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Reset');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Reset password failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Reset');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});