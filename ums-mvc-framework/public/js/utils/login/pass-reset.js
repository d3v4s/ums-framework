$(document).ready(function () {
	/* submit event on reset password form to send XML HTTP request */
	$('#reset-pass-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $btn = $(this).find('#btn-reset-pass'),
			$xf = $(this).find('#_xf');

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		try {
			/* init rsa, pass and serialize data */
			var rsa = new RSAKey(),
			pass = $(this).find('#pass.send-ajax-crypt').val(),
			cpass = $(this).find('#cpass.send-ajax-crypt').val(),
			data = $(this).find('.send-ajax').serialize();

			/* crypt password and append on data */
			rsa.setPublic(window.keyN, window.keyE);
			pass = rsa.encrypt(pass);
			cpass = rsa.encrypt(cpass);
			data += '&pass=' + pass + '&cpass=' + cpass;
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
				if (response.success) setTimeout(redirect, 2000, '/auth/login');
				else {
					focusError(response);
					$xf.val(response.ntk);
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

		sendAjaxReq('/user/reset/password', data, $xf.val(), funcSuccess, funcFail);
	});
});