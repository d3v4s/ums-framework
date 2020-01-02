$(document).ready(function (){
	/* submit event on site map update form to send XML HTTP request */
	$('#site-map-update-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $btn = $(this).find('#btn-update'),
			$xf = $(this).find('#_xf.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and disable button */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Update');
			try {
				showMessage(response.message, !response.success);
				if (!response.success) focusError(response);
				
				$xf.val(response.ntk);
			} catch (e) {
				showMessage('Sitemap update failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Update');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/ums/generator/site/map', data, $xf.val(), funcSuccess, funcFail);
	});

});