$(document).ready(function() {
	$('#rsa-generator-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-generate'),
			$privK = $(this).find('#priv-key'),
			$publK = $(this).find('#publ-key'),
			$xf = $(this).find('#_xf.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);
		$.ajax({
			method: 'post',
			url: '/ums/generator/rsa/get',
			data: data,
			success: function(response) {
				removeLoading($btn, 'Generate');
				try {
					const rsaKeyRes = JSON.parse(response);
					
					if (rsaKeyRes.success) {
						const privKey = rsaKeyRes.keyPair.privKey,
							publKey = rsaKeyRes.keyPair.publKey;
						
						$privK.attr('rows', privKey.lineCount());
						$publK.attr('rows', publKey.lineCount());
						$privK.val(privKey);
						$publK.val(publKey);
					}
					showMessage(rsaKeyRes.message, !rsaKeyRes.success);
					
					$xf.val(rsaKeyRes.ntk);
				} catch (e) {
					showMessage('Key pair generation failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Generate');
				showMessage('Problem to contact server', true);
			}
		});
	});
});