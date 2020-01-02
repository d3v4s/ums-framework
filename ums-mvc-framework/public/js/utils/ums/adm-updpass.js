$(document).ready(function() {
	/* submit event on update password form to send XML HTTP request */
	$('#update-pass-form').on('submit', function(event) {
		/* get button and token */
		const $btn = $(this).find('#btn-change'),
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
			removeLoading($btn, 'Change');
			showMessage('Settings update failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Change');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, '/ums/user/' + response.userId);
				else {
					focusError(response);
					$xf.val(response.ntk);
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

		sendAjaxReq('/ums/user/update/pass', data, $xf.val(), funcSuccess, funcFail);
	});
});