$(document).ready(function (){
	/* submit event on rsa settings form to send XML HTTP request */
	$('#rsa-settings-form').on('submit', function(event) {
		/* get button, token, and serialize data */
    	const $xf = $(this).find('#_xf'),
    		$btn = $(this).find('#btn-save'),
    		data = $(this).find('.send-ajax').serialize();

    	/* block default submit form and show loading */
    	event.preventDefault();
    	showLoading($btn);

    	/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Save');
			try {
				showMessage(response.message, !response);
				
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

		sendAjaxReq('/ums/app/settings/rsa/update', data, $xf.val(), funcSuccess, funcFail);
	});

	/* submit event on rsa generate and save form to send XML HTTP request */
	$('#rsa-gen-save-form').on('submit', function(event) {
		/* get button, token, and serialize data */
    	const $xf = $(this).find('#_xfgs.send-ajax'),
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
				
				$xf.val(response.ntk);
			} catch (e) {
				showMessage('Generation and saving key failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Generate and Save Key');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/ums/generator/rsa/save', data, $xf.val(), funcSuccess, funcFail, 'XS-TKN-GS');
	});
});