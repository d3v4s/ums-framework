$(document).ready(function(){
	$('#app-settings-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-save'),
			$xf = $(this).find('#_xf.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/app/settings/app/update',
			success: function(response) {
    			removeLoading($btn, 'Save');
    			try {
    				const settingsRes = JSON.parse(response);
    				
    				showMessage(settingsRes.message , !settingsRes.success);
    				
    				if (!settingsRes.success) focusError(settingsRes);
    				
    				$xf.val(settingsRes.ntk);
				} catch (e) {
					showMessage('Settings update failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Save');
				showMessage('Problem to contact server', true);
			}
		});
	});
});