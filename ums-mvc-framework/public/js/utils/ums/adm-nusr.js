$(document).ready(function(){
	$('#new-user-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-save'),
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
			removeLoading($btn, 'Save');
			showMessage('Adding new user failed', true);
			return;
		}

		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/user/new',
			success: function(response) {
    			removeLoading($btn, 'Save');
    			try {
    				const userRes = JSON.parse(response);
    				
    				showMessage(userRes.message, !userRes.success);
    				if (userRes.success) setTimeout(redirect, 2000, '/ums/users');
    				else {
    					focusError(userRes);
    					$xf.val(userRes.ntk);
    				}
				} catch (e) {
					showMessage('Adding new user failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Save');
				showMessage('Problem to contact server', true);
			}
		});
	});
});