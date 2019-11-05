$(document).ready(function() {
	$('#user-update-form #btn-delete-new-email').click(function(event) {
		const $form = $('#user-update-form'),
			$btn = $(this);
		
		disableElement($btn);
		$.MessageBox({
			buttonDone  : "Yes",
			buttonFail  : "No",
			message     : "Delete your new email?"
		}).done(function() {
			const $xf = $form.find('#_xf.send-ajax'),
				data = $xf.serialize();
			$.ajax({
				method: 'post',
				data: data,
				url: '/user/settings/new/email/delete',
				success: function(response) {
					enableElement($btn);
					try {
						const userRes = JSON.parse(response);
						
						showMessage(userRes.message, !userRes.success);
						if (userRes.success) setTimeout(function() {location.reload();}, 2000);
						else $xf.val(userRes.ntk);
					} catch (e) {
						showMessage('Delete new email failed', true);
					}
				},
				failure: function() {
					enableElement($btn);
					showMessage('Problem to contact server', true);
				}
			});
		}).fail(function(){
			enableElement($btn);
		});
	});

	$('#user-update-form #btn-resend-email').click(function(event) {
		const $form = $('#user-update-form'),
			$btn = $(this),
			$xf = $form.find('#_xf.send-ajax'),
			data = $xf.serialize();
	
		disableElement($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/user/settings/new/email/resend/validation',
			success: function(response) {
				enableElement($btn);
				try {
					const userRes = JSON.parse(response);
					
					showMessage(userRes.message, !userRes.success);
					$xf.val(userRes.ntk);
				} catch (e) {
					showMessage('Email resend failed', true);
				}
			},
			failure: function() {
				enableElement($btn);
				showMessage('Problem to contact server', true);
			}
		});
	});
});