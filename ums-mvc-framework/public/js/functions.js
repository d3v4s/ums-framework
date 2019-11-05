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

function focusError(jsonRes) {
	removeEvidence($('.evidence-error'));
	if (jsonRes.error != null) {
		var $error = $('#' + jsonRes.error + '.evidence-error');
		evidenceError($error);
		$error.focus();
	}
}

function evidenceError($elem) {
	if ($elem[0].tagName !== 'SELECT') $elem.css('border', '1px solid red');
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
	$btn.attr("disabled", "disabled");
	$btn.children('#ico-btn').addClass('d-none');
	$btn.children('#spinner').removeClass('d-none');
	$btn.children('#text-btn').text(' Loading...');
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