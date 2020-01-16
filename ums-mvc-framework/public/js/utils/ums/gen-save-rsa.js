$(document).ready(function (){
	/* submit event on rsa generate and save form to send XML HTTP request */
	$('#rsa-gen-save-form').on('submit', function(event) {
		/* get button, token, and serialize data */
    	const $xf = $(this).find('#_xf_rsa'),
    		actionUrl = $(this).attr('action'),
    		$btn = $(this).find('#btn-gen-save-key'),
    		data = $(this).find('.send-ajax').serialize();

    	/* block default submit form and show loading */
    	event.preventDefault();
    	showLoading($btn);

    	/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Generate and Save Key');
			try {
				showMessage(response.message, !response.success);
				
				if (response.ntk !== undefined) $xf.val(response.ntk);
			} catch (e) {
				showMessage('Generation and saving key failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Generate and Save Key');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});