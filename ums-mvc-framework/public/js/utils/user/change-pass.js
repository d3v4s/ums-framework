$(document).ready(function (){
	$('#change-pass-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-change'),
			$xf = $(this).find('#_xf.send-ajax');

		var rsa = new RSAKey(),
			oldPass = $('#old-pass.send-ajax-crypt').val()
			pass = $('#pass.send-ajax-crypt').val(),
			cpass = $('#cpass.send-ajax-crypt').val();

		event.preventDefault();
		showLoading($btn);

		try {
			rsa.setPublic(window.keyN, window.keyE);
			oldPass = rsa.encrypt(oldPass);
			pass = rsa.encrypt(pass);
			cpass = rsa.encrypt(cpass);
		} catch (e) {
			removeLoading($btn, 'Change');
			showMessage('Change passord failed', true);
			return;
		}

		$.ajax({
			method: 'post',
			data: {
    			'old-pass': oldPass,
    			pass: pass,
    			cpass: cpass,
    			_xf: $xf.val()
			},
			url: '/user/settings/pass/update',
			success: function(response) {
    			removeLoading($btn, 'Change');
    			try {
    				const passRes = JSON.parse(response);
    				
    				showMessage(passRes.message, !passRes.success);
    				if (passRes.success) setTimeout(redirect, 2000, '/user/settings');
    				else {
    					focusError(passRes);
    					$xf.val(passRes.ntk);
    				}
				} catch (e) {
					showMessage('Change passord failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Change');
				showMessage('Problem to contact server', true);
			}
		});
	});
});