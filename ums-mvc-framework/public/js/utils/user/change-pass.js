$(document).ready(function (){
	/* submit event on change password form to send XML HTTP request */
	$('#change-pass-form').on('submit', function(event) {
		/* get button and token */
		const $btn = $(this).find('#btn-change'),
			$xf = $(this).find('#_xf');
		
		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		try {
			/* init rsa and get passwords */
			var rsa = new RSAKey(),
			oldPass = $('#old-pass.send-ajax-crypt').val()
			pass = $('#pass.send-ajax-crypt').val(),
			cpass = $('#cpass.send-ajax-crypt').val();

			/* crypt password */
			rsa.setPublic(window.keyN, window.keyE);
			oldPass = rsa.encrypt(oldPass);
			pass = rsa.encrypt(pass);
			cpass = rsa.encrypt(cpass);
		} catch (e) {
			removeLoading($btn, 'Change');
			showMessage('Change passord failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Change');
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, '/user/settings');
				else {
					focusError(response);
					$xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Change passord failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Change');
			showMessage('Problem to contact server', true);
		};

		/* set data to be sended */
		data = {
			"old-pass": oldPass,
			"pass": pass,
			"cpass": cpass,
			"_xf": $xf.val()
		};

		sendAjaxReq('/user/settings/pass/update', data, $xf.val(), funcSuccess, funcFail);
//		$.ajax({
//			method: 'post',
//			data: {
//    			'old-pass': oldPass,
//    			pass: pass,
//    			cpass: cpass,
//    			_xf: $xf.val()
//			},
//			url: '/user/settings/pass/update',
//			success: function(response) {
//    			
//			},
//			failure: function() {
//				removeLoading($btn, 'Change');
//				showMessage('Problem to contact server', true);
//			}
//		});
	});
});