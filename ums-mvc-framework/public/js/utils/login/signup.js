$(document).ready(function (){
	$('#signup-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-signup')
			$xf = $(this).find('#_xf.send-ajax');

		var rsa = new RSAKey(),
			pass = $('#pass.send-ajax-crypt').val(),
			cpass = $('#cpass.send-ajax-crypt').val(),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);

		try {
			rsa.setPublic(window.keyN, window.keyE);
			pass = rsa.encrypt(pass);
			cpass = rsa.encrypt(cpass);
			data += '&pass=' + pass + '&cpass=' + cpass;
		} catch (e) {
			removeLoading($btn, 'Signup');
			showMessage('Signup failed', true);
			return;
		}

		$.ajax({
			method: 'post',
			data: data,
			url: '/auth/signup',
			success: function(response) {
    			removeLoading($btn, 'Signup');
    			try {
    				const userRes = JSON.parse(response);
    				
    				showMessage(userRes.message, !userRes.success);
    				if (userRes.success) setTimeout(redirect, 2000, '/auth/signup/confirm');
    				else {
    					focusError(userRes);
    					$xf.val(userRes.ntk);
    				}
				} catch (e) {
					showMessage('Signup failed', true);
				}
			},
			failure: function() {
    			removeLoading($btn, 'Signup');
    			showMessage('Problem to contact server', true);
			}
		});
	});
});