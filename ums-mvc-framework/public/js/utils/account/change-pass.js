$(document).ready(function (){
	/* submit event on change password form to send XML HTTP request */
	$('#change-pass-form').on('submit', function(event) {
		/* get button and token */
		const $btn = $(this).find('#btn-change'),
			actionUrl = $(this).attr('action'),
			$cryptData = $(this).find('.send-ajax-crypt'),
			$xf = $(this).find('#_xf');

		/* block default submit form and show loading */
		event.preventDefault();
		var txtBttn = showLoading($btn);

		try {
			/* crypt and serialize passwords */
			var data = cryptSerialize($cryptData);
			console.log(data);
		} catch (e) {
			removeLoading($btn, txtBttn);
			showMessage('Change passord failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, txtBttn);
			try {
				showMessage(response.message, !response.success);
				if (response.success) setTimeout(redirect, 2000, response.redirect_to);
				else {
					focusError(response);
					if (response.ntk !== undefined) $xf.val(response.ntk);
				}
			} catch (e) {
				showMessage('Change passord failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, txtBttn);
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});
