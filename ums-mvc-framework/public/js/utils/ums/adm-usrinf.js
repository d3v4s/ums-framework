$(document).ready(function (){
	$('#delete-user-form #btn-delete-user').click(function(event) {
		const $form = $('#delete-user-form'), 
			$btn = $(this);
	
		event.preventDefault();
		showLoading($btn);
		$.MessageBox({
			buttonDone  : "Yes",
	    	buttonFail  : "No",
	    	message     : "Delete this account?"
		}).done(function(){
			const data = $form.find('.send-ajax').serialize();
	
			$.ajax({
				method: 'post',
				data: data,
				url: '/ums/user/delete/confirm',
				success: function(response) {
					removeLoading($btn, 'Delete');
					try {
						const deleteRes = JSON.parse(response);
						
						showMessage(deleteRes.message, !deleteRes.success);
						if (deleteRes.success) setTimeout(redirect, 2000, '/ums/users');
						else $form.find('#_xf-du.send-ajax').val(deleteRes.ntk);
						
					} catch (e) {
						showMessage('Delete user failed', true);
					}
				},
				failure: function() {
					removeLoading($btn, 'Delete');
					showMessage('Problem to contact server', true);
				}
			});
		}).fail(function(){
			removeLoading($btn, 'Delete');
		});
	});

	$('#delete-new-email-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-delete-new-email'),
			$xf = $(this).find('#_xf-dnm.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		disableElement($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/user/delete/new/email',
			success: function(response) {
				enableElement($btn);
				try {
					const resetRes = JSON.parse(response);
					
					showMessage(resetRes.message, !resetRes.success);
					if (resetRes.success) setTimeout(function() {location.reload();}, 2000);
					else $xf.val(resetRes.ntk);
				} catch (e) {
					showMessage('Delete new email failed', true);
				}
			},
			failure: function() {
				enableElement($btn);
				showMessage('Problem to contact server', true);
			}
		});
	});

	$('#reset-wrong-pass-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-reset-wrong-pass'),
			$xf = $(this).find('#_xf-rwp.send-ajax'),
			data = $(this).find('.send-ajax').serialize();

		event.preventDefault();
		disableElement($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/user/update/reset/wrong/pass',
			success: function(response) {
				enableElement($btn);
				try {
					const resetRes = JSON.parse(response);
					
					showMessage(resetRes.message, !resetRes.success);
					if (resetRes.success) setTimeout(function() {location.reload();}, 2000);
					else $xf.val(resetRes.ntk);
				} catch (e) {
					showMessage('Reset wrong password failed', true);
				}
			},
			failure: function() {
				enableElement($btn);
				showMessage('Problem to contact server', true);
			}
		});
	});

	$('#reset-lock-user-form').on('submit', function(event) {
		const $btn = $(this).find('#btn-reset-lock'),
			$xf = $(this).find('#_xf-rlu.send-ajax'),
			data = $(this).find('.send-ajax').serialize();
		
		event.preventDefault();
		disableElement($btn);
		$.ajax({
			method: 'post',
			data: data,
			url: '/ums/user/update/reset/lock',
			success: function(response) {
				enableElement($btn);
				try {
					const resetRes = JSON.parse(response);
					
					showMessage(resetRes.message, !resetRes.success);
					if (resetRes.success) setTimeout(function() {location.reload();}, 2000);
					else $xf.val(resetRes.ntk);
				} catch (e) {
					showMessage('Reset locks user failed', true);
				}
			},
			failure: function() {
				enableElement($btn);
				showMessage('Problem to contact server', true);
			}
		});
	});
});