$(document).ready(function() {
	reqPublicKey();
	/* submit event on login form to send XML HTTP request */
	$('#double-login-form').on('submit', function(event) {
		/* get button and token */
		const $form = $(this),
			$xf = $(this).find('#_xf'),
			actionUrl = $(this).attr('action'),
			$btn = $(this).find('#btn-double-login'),
			$cryptElem = $(this).find('.send-ajax-crypt');

		/* block default submit form and show loading */
		event.preventDefault();
		var txtBttn = showLoading($btn);

		try {
			/* crypt password and serialize */
			var data = cryptSerialize($cryptElem);
		} catch (e) {
			removeLoading($btn, txtBttn);
			showMessage('Double login failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, txtBttn);
			try {
				showMessage(response.message, !response.success);
				if (response.success) {
					$redrct = $('#redirect_to');
					var redirectTo = $redrct.length ? $redrct.val() : window.location.pathname;
					setTimeout(redirect, 2000, redirectTo);
				}
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Double login failed', true);
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
