var isMessageShow = false,
	idTimeout = 0;

String.prototype.lines = function() {
	return this.split(/\r*\n/);
}
String.prototype.lineCount = function() {
	return this.lines().length;
}
String.prototype.ucFirst = function() {
    return this.charAt(0).toUpperCase() + this.substr(1);
}

/* function to crypt and serialize data of input list */
function cryptSerialize($inputList) {
	/* init vars */
	var rsa = new RSAKey(),
		out = '',
		val; 

	/* set public key */
	rsa.setPublic(window.keyN, window.keyE);
	/* crypt value of input and serialize it */
	$inputList.each(function(index, input) {
		/* crypt valure */
		val = rsa.encrypt(input.value);
		/* append & */
		out += index === 0 ? '' : '&';
		/* append serialized input */
		out += input.getAttribute('name') + '=' + val;
	});
	return out;
}

/* function to send XML HTTP request */
function sendAjaxReq(url, data, $token, funcSuccess, funcFail=null) {
	/* if function fail is null, then set it */ 
	funcFail = funcFail === null ? function() {
		showMessage('Problem to contact server', true);
	} : funcFail;
	/* send ajax request */
	$.ajax({
		method: 'post',
		data: data,
		url: url,
		beforeSend: function(request) {
			/* set csrf token header */
			request.setRequestHeader($token.attr('name'), $token.val());
		},
		success: funcSuccess,
		failure: funcFail
	});
	
}

/* function to focus and evidence a error on form */
function focusError(jsonRes) {
	removeEvidence($('.evidence-error'));
	if (jsonRes.error != null) {
		var $error = $('#' + jsonRes.error + '.evidence-error');
		evidenceError($error);
		$error.focus();
	}
}

/* fucntion to evidence a error of a element of form */
function evidenceError($elem) {
	if ($elem.tagName !== 'SELECT') $elem.css('border', '1px solid red');
	$elem.css('box-shadow', '0 0 5px red');
}

function evidenceRight($elem) {
	$elem.css('border', '1px solid green');
	$elem.css('box-shadow', '0 0 5px green');
}

function removeEvidence($elem) {
	$elem.css('border', '');
	$elem.css('box-shadow', '');
}

function showMessage(message, isError = false) {
	clearTimeout(idTimeout);
	isMessageShow = true;
	var $messgBox = $('#message-box'),
		scrollTop = $(window).scrollTop();
	$messgBox.find('#txt-message').text(message);
	if (isError) {
		$messgBox.removeClass('bg-blue');
		$messgBox.addClass('bg-red');
	} else {
		$messgBox.removeClass('bg-red');
		$messgBox.addClass('bg-blue');
	}
	$messgBox.animate({top: scrollTop + 'px'});
	idTimeout = setTimeout(closeMessage, 5000);
}

function closeMessage() {
	isMessageShow = false;
	$('#message-box').animate({
		top: '-100px'
	});
}

function positionMessageBox() {
	if (isMessageShow) $('#message-box').css('top', $(window).scrollTop() + 'px');
}

function disableElement($elem) {
	$elem.attr("disabled", "disabled");
}

function enableElement($elem) {
	$elem.removeAttr("disabled");
}

function redirect(url) {
	location.href = url;
}

function showLoading($btn) {
	textBtn = $btn.children('#text-btn').text()
	$btn.attr("disabled", "disabled");
	$btn.children('#ico-btn').addClass('d-none');
	$btn.children('#spinner').removeClass('d-none');
	$btn.children('#text-btn').text(' Loading...');
	return textBtn;
}

function removeLoading($btn, text) {
	$btn.removeAttr("disabled");
	$btn.children('#spinner').addClass('d-none');
	$btn.children('#ico-btn').removeClass('d-none');
	$btn.children('#text-btn').text(text);
}

$(document).ready(function () {
	$('#message.fade-out').fadeOut(7000);

	$('#close-message-box.btn-close-msg').click(function(event) {
		event.preventDefault();
		closeMessage();
	})

	$(window).resize(function() {
		positionMessageBox();
	});

	$(window).scroll(function() {
		positionMessageBox();
	});
});