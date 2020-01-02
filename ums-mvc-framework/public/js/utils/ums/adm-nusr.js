$(document).ready(function(){
	/* submit event on new user form to send XML HTTP request */
	$('#new-user-form').on('submit', function(event) {
		/* get button and token */
		const $btn = $(this).find('#btn-save'),
			$xf = $(this).find('#_xf');
		
		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		try {
			/* init rsa, get passwords and serilize data S*/
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
			removeLoading($btn, 'Save');
			showMessage('Adding new user failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Save');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, '/ums/users');
				else {
					focusError(response);
					$xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Adding new user failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Save');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/ums/user/new', data, $xf.val(), funcSuccess, funcFail);
	});
});