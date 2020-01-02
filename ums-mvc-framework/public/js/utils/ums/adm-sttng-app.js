$(document).ready(function(){
	/* submit event on settings app form to send XML HTTP request */
	$('#app-settings-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-save'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and disable button */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Save');
			try {
				showMessage(response.message , !response.success);
				if (!response.success) focusError(response);
				
				$xf.val(response.ntk);
			} catch (e) {
				showMessage('Settings update failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Save');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/auth/logout', data, $xf.val(), funcSuccess, funcFail);
	});
});