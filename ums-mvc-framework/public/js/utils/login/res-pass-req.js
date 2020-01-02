$(document).ready(function (){
	/* submit event on reset password request form to send XML HTTP request */
	$('#reset-pass-req-form').on('submit', function(event) {
		/* get button, token, and serialize data */
    	const $xf = $(this).find('#_xf'),
    		$btn = $(this).find('#btn-reset-pass'),
    		data = $(this).find('.send-ajax').serialize();

    	/* block default submit form and show loading */
    	event.preventDefault();
    	showLoading($btn);

    	/* success function */
    	funcSuccess = function(response) {
    		console.log(response)
			removeLoading($btn, 'Reset Password');
			try {
				showMessage(response.message, !response.success);
				if(response.success) setTimeout(redirect, 2000, '/');
				else {
					focusError(response);
					$xf.val(response.ntk);
				}
			} catch (e) {
				console.log(e)
				showMessage('Request failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Reset Password');
			showMessage('Problem to contact server', true);
		};

    	sendAjaxReq('/auth/reset/password', data, $xf.val(), funcSuccess, funcFail);
	});
});
