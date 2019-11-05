$(document).ready(function (){
	$('#login-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-login'),
			$xf = $(this).find('#_xf.send-ajax');

		var rsa = new RSAKey(),
			pass = $(this).find('#pass.send-ajax-crypt').val(),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);

		try {
			rsa.setPublic(window.keyN, window.keyE);
			pass = rsa.encrypt(pass);
			data += '&pass=' + pass;
		} catch (e) {
			removeLoading($btn, 'Login');
			showMessage('Login failed', true);
			return;
		}

		$.ajax({
			method: 'post',
			data: data,
			url: '/auth/login',
			success: function(response) {
    			removeLoading($btn, 'Login');
    			try {
    				const userRes = JSON.parse(response);
    				
    				showMessage(userRes.message, !userRes.success);
    				if(userRes.success) setTimeout(redirect, 2000, '/');
    				else {
    					focusError(userRes);
    					$xf.val(userRes.ntk);
    				}
				} catch (e) {
					showMessage('Login failed', true);
				}
			},
			failure: function() {
    			removeLoading($btn, 'Login');
    			showMessage('Problem to contact server', true);
			}
		});
	});
});