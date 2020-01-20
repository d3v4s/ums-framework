$(document).ready(function() {
	/* submit event on update password form to send XML HTTP request */
	$('#update-pass-form').on('submit', function(event) {
		/* get button and token */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-change'),
			actionUrl = $(this).attr('action'),
			$cryptData = $(this).find('.send-ajax-crypt');

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);
		
		try {
			/* serialize data */
			var data = $(this).find('.send-ajax').serialize();
			/* crypt password and append on data */
			data += '&' + cryptSerialize($cryptData);

		} catch (e) {
			removeLoading($btn, 'Change');
			showMessage('Settings update failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Change');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Password update failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Change');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});