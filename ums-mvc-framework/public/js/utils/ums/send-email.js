$(document).ready(function(){
//	/* init classic editor wysiwyg */ 
//	try {
//		ClassicEditor.create(document.querySelector('#editor')).catch(error => {
//			console.error(error);
//		});
//	} catch (e) {}

	/* submit event on send email form to send XML HTTP request */
	$('#send-email-form').on('submit', function(event) {
		/* get button, token, and serialize data */
		const $xf = $(this).find('#_xf'),
			$btn = $(this).find('#btn-send'),
			actionUrl = $(this).attr('action'),
			$dataCrypt = $(this).find('.send-ajax-crypt');

		/* block default submit form and show loading */
		event.preventDefault();
		showLoading($btn);

		try {
			/* crypte data */
			var data = cryptSerialize($dataCrypt);
//			var rsa = new RSAKey(),
//				to = $(this).find.val(),
//				subject = $(this).find('#subject.send-ajax-crypt').val(),
//				content = $(this).find('#content.send-ajax-crypt').val();
//
//			/* crypt password and append on data */
//			rsa.setPublic(window.keyN, window.keyE);
//			to = rsa.encrypt(to);
//			subject = rsa.encrypt(subject);
//			content = rsa.encrypt(content);
		} catch (e) {
			removeLoading($btn, 'Send');
			showMessage('Send email failed', true);
			return;
		}

		/* success function */
		funcSuccess = function(response) {
    		removeLoading($btn, 'Send');
    		try {
    			showMessage(response.message, !response.success);
    			if (response.success) setTimeout(redirect, 2000, response.redirect_to);
    			else {
    				focusError(response);
    				if (response.ntk !== undefined) $xf.val(response.ntk);
    			}
			} catch (e) {
				showMessage('Send email failed', true);
			}
		};

		/* fail function */
		funcFail = function() {
			removeLoading($btn, 'Send');
			showMessage('Problem to contact server', true);
		};

//		/* data to send on request */
//		data = {
//			to: to,
//			subject: subject,
//			content: content,
//			_xf: $xf.val()
//		}
		sendAjaxReq(actionUrl, data, $xf, funcSuccess, funcFail);
	});
});