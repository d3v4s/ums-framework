$(document).ready(function (){
	$('#update-pass-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-change'),
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
			removeLoading($btn, 'Change');
			showMessage('Settings update failed', true);
			return;
		}

		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/user/update/pass',
			success: function(response) {
    			removeLoading($btn, 'Change');
    			try {
    				const passRes = JSON.parse(response);
    				
    				showMessage(passRes.message, !passRes.success);
    				if (passRes.success) setTimeout(redirect, 2000, '/ums/user/' + passRes.userId);
    				else {
    					focusError(passRes);
    					$xf.val(passRes.ntk);
    				}
				} catch (e) {
					showMessage('Password update failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Change');
				showMessage('Problem to contact server', true);
			}
		});
	});
});