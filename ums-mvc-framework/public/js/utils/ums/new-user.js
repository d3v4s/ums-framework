$(document).ready(function(){
	reqPublicKey();
	/* submit event on new user form to send XML HTTP request */
	$('#new-user-form').on('submit', function(event) {
		/* get button and token */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-save'),
			actionUrl = $(this).attr('action'),
			$cryptData = $(this).find('.send-ajax-crypt')
		;
		
		/* block default submit form and show loading */
		event.preventDefault();
		var txtBtn = showLoading($btn);

		try {
			/* serilize data */
			var data = $(this).find('.send-ajax').serialize();
			/* crypt and serialize passwords */
			data += '&' + cryptSerialize($cryptData);

		} catch (e) {
			removeLoading($btn, txtBtn);
			showMessage('Adding new user failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, txtBtn);
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Adding new user failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, txtBtn);
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});