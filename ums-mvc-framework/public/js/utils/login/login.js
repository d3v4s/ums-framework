$(document).ready(function() {
	/* submit event on login form to send XML HTTP request */
	$('#login-form').on('submit', function(event) {
		/* get button and token */
		const $btn = $(this).find('#btn-login'),
			$xf = $(this).find('#_xf');

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		try {
			/* init rsa, pass and serialize data */
			var rsa = new RSAKey(),
				pass = $(this).find('#pass.send-ajax-crypt').val(),
				data = $(this).find('.send-ajax').serialize();

			/* crypt password and append on data */
			rsa.setPublic(window.keyN, window.keyE);
			pass = rsa.encrypt(pass);
			data += '&pass=' + pass;
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
				if (response.success) setTimeout(redirect, 2000, '/');
				else {
					focusError(response);
					$xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Login failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Login');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/auth/login', data, $xf.val(), funcSuccess, funcFail);
	});
});