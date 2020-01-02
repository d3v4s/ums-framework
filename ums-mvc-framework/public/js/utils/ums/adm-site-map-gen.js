$(document).ready(function (){
	/* submit event on sitemap genrator form to send XML HTTP request */
	$('#site-map-generator-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-generate'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and disable button */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Generate');
			try {
				showMessage(response.message, !response.success);
				if (!response.success) focusError(response);
				else setTimeout(redirect, 2000, '/ums/generator/site/map/update');

				$xf.val(response.ntk);
			} catch (e) {
				showMessage('Generation sitemap failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Generate');
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq('/ums/generator/site/map', data, $xf.val(), funcSuccess, funcFail);
	});

});