$(document).ready(function () {
	$('#reset-pass-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-reset-pass'),
			$xf = $(this).find('#_xf.send-ajax');

		var rsa = new RSAKey(),
			pass = $(this).find('#pass.send-ajax-crypt').val(),
			cpass = $(this).find('#cpass.send-ajax-crypt').val(),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);

		try {
			rsa.setPublic(window.keyN, window.keyE);
			pass = rsa.encrypt(pass);
			cpass = rsa.encrypt(cpass);
			data += '&pass=' + pass + '&cpass=' + cpass;
		} catch (e) {
			removeLoading($btn, 'Reset');
			showMessage('Reset password failed', true);
			return;
		}

		$.ajax({
			method: 'post',
			data: data,
			url: '/user/reset/password',
			success: function(response) {
    			removeLoading($btn, 'Reset');
    			try {
    				const passRes = JSON.parse(response);
    				
    				showMessage(passRes.message, !passRes.success);
    				if (passRes.success) setTimeout(redirect, 2000, '/auth/login');
    				else {
    					focusError(passRes);
    					$xf.val(passRes.ntk);
    				}
				} catch (e) {
					showMessage('Reset password failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Reset');
				showMessage('Problem to contact server', true);
			}
		});
	});
});