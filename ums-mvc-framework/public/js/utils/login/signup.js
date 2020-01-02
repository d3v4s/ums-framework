$(document).ready(function () {
	/* submit event on signup form to send XML HTTP request */
	$('#signup-form').on('submit', function(event) {
		/* get button and token */
		const $xf = $(this).find('#_xf');
			$btn = $(this).find('#btn-signup')

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		try {
			/* init rsa, pass and serialize data */
			var rsa = new RSAKey(),
			pass = $('#pass.send-ajax-crypt').val(),
			cpass = $('#cpass.send-ajax-crypt').val(),
			data = $(this).find('.send-ajax').serialize();

			/* crypt password and append on data */
			rsa.setPublic(window.keyN, window.keyE);
			pass = rsa.encrypt(pass);
			cpass = rsa.encrypt(cpass);
			data += '&pass=' + pass + '&cpass=' + cpass;
		} catch (e) {
			removeLoading($btn, 'Signup');
			showMessage('Signup failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			console.log(response)
			removeLoading($btn, 'Signup');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, '/auth/signup/confirm');
				else {
					focusError(response);
					$xf.val(response.ntk);
				}
			} catch (e) {
				console.log(e)
				showMessage('Signup failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Signup');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/auth/signup', data, $xf.val(), funcSuccess, funcFail);
	});
});