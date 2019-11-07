$(document).ready(function (){
	$('#rsa-settings-form').on('submit', function(event) {
    	const $btn = $(this).find('#btn-save'),
    		$xf = $(this).find('#_xf.send-ajax'),
    		data = $(this).find('.send-ajax').serialize();

    	event.preventDefault();
    	showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/app/settings/rsa/update',
			success: function(response) {
    			removeLoading($btn, 'Save');
    			try {
    				const settingsRes = JSON.parse(response);
    				
    				showMessage(settingsRes.message, !settingsRes);
    				
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

	$('#rsa-gen-save-form').on('submit', function(event) {
    	const $btn = $(this).find('#btn-gen-save-key'),
    		$xf = $(this).find('#_xfgs.send-ajax'),
    		data = $(this).find('.send-ajax').serialize();

    	event.preventDefault();
    	showLoading($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/generator/rsa/save',
			success: function(response) {
    			removeLoading($btn, 'Generate and Save Key');
    			try {
    				const gskRes = JSON.parse(response);
    				
    				showMessage(gskRes.message, !gskRes.success);
    				
    				$xf.val(gskRes.ntk);
				} catch (e) {
					showMessage('Generation and saving key failed', true);
				}
			},
			failure: function() {
				removeLoading($btn, 'Generate and Save Key');
				showMessage('Problem to contact server', true);
			}
		});
	});
});