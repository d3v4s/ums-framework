$(document).ready(function() {
	/* submit event on login form to send XML HTTP request */
	$('#login-form').on('submit', function(event) {
		/* get button and token */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-login'),
			actionUrl = $(this).attr('action'),
			$cryptElem = $(this).find('.send-ajax-crypt');
		/* serialize data */
		var data = $(this).find('.send-ajax').serialize();

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		try {
			/* crypt password and append on data */
			data += '&' + cryptSerialize($cryptElem);
		} catch (e) {
			removeLoading($btn, 'Login');
			showMessage('Login failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Login');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				console.log(e);
				showMessage('Login failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Login');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});