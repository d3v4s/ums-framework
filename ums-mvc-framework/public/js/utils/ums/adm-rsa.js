$(document).ready(function() {
	/* submit event on rsa key pair generator form to send XML HTTP request */
	$('#rsa-generator-form').on('submit', function(event) {
		/* get button, token, private and public key, and serialize data */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-generate'),
			$privK = $(this).find('#priv-key'),
			$publK = $(this).find('#publ-key'),
			data = $(this).find('.send-ajax').serialize();

		/* block default submit form and disable button */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Generate');
			try {
				if (response.success) {
					const privKey = response.keyPair.privKey,
						publKey = response.keyPair.publKey;
					
					$privK.attr('rows', privKey.lineCount());
					$publK.attr('rows', publKey.lineCount());
					$privK.val(privKey);
					$publK.val(publKey);
				}
				showMessage(response.message, !response.success);
				
				$xf.val(response.ntk);
			} catch (e) {
				showMessage('Key pair generation failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Generate');
			showMessage('Problem to contact server', true);
		}

		sendAjaxReq('/ums/generator/rsa/get', data, $xf.val(), funcSuccess, funcFail);
	});
});