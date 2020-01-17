$(document).ready(function() {
	/* submit event on rsa key pair generator form to send XML HTTP request */
	$('#rsa-generator-form').on('submit', function(event) {
		/* get button, token, private and public key, and serialize data */
		const $xf = $(this).find('#_xf'),
			$privK = $(this).find('#priv_key'),
			$publK = $(this).find('#publ_key'),
			actionUrl = $(this).attr('action'),
			$btn = $(this).find('#btn-generate');

		/* block default submit form and disable button */
		event.preventDefault();
		showLoading($btn);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($btn, 'Generate');
			try {
				if (response.success) {
					const privKey = response.key_pair.priv_key,
						publKey = response.key_pair.publ_key;
					
					$privK.attr('rows', privKey.lineCount());
					$publK.attr('rows', publKey.lineCount());
					$privK.val(privKey);
					$publK.val(publKey);
				}
				showMessage(response.message, !response.success);
				
				if (response.ntk !== undefined) $xf.val(response.ntk);
			} catch (e) {
				showMessage('Key pair generation failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Generate');
			showMessage('Problem to contact server', true);
		}

		sendAjaxReq(actionUrl, {}, $xf, funcSuccess, funcFail);
	});
});