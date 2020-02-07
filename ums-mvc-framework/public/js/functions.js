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

/* function to reuqest the public key */
function reqPublicKey() {
	/* sucess function XML HTTP req */
	funcSuccess = function(response) {
		/* if success */
		if (response.success) {
			/* set public key */
			window.keyN = response.keyN;
			window.keyE = response.keyE;
		/* else show error message */
		} else showMessage(response.message, true);
	};

	/* send req */
	sendAjaxReq('/app/config/get/key/json', '', $('#_kxt'), funcSuccess);
}


/* function to crypt and serialize data of input list */
function cryptSerialize($inputList) {
	/* init vars */
	var rsa = new RSAKey(),
		out = '',
		val
	; 

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
	var funcSuccAjax = function(response) {
		if (response.dbl_lgn_rq !== undefined && response.dbl_lgn_rq) {
			showMessage(response.message, !response.success);
			var reqData = {
				'url': url,
				'data': data,
				'$token': $token,
				'funcSuccess': funcSuccess,
				'funcFail': funcFail
			}
			showDoubleLogin(reqData, response.dbl_lgn_dt);
			return;
		}
		funcSuccess(response);
	}
	$.ajax({
		method: 'post',
		data: data,
		url: url,
		beforeSend: function(request) {
			/* set csrf token header */
			request.setRequestHeader($token.attr('name'), $token.val());
		},
		success: funcSuccAjax,
		failure: funcFail
	});
}

/* function to show double login */
function showDoubleLogin(reqData, dblLgnData) {
    /* create all needly element */
	var $container = $(document.createElement('div')),
		$closer = $(document.createElement('a')),
		$iconCloser = $(document.createElement('i')),
        $msgbox = $(document.createElement('div')),
        $title = $(document.createElement('h5')),
        $form = $(document.createElement('form')),
		$token = $(document.createElement('input')),
		$tokenKey = $(document.createElement('input')),
		$contPass = $(document.createElement('div')),
		$labelPass = $(document.createElement('label')),
        $inputPass = $(document.createElement('input')),
		$submitBtn = $(document.createElement('button')),
		$icoBtn = $(document.createElement('i')),
		$spinnerBtn = $(document.createElement('span')),
		$txtBtn = $(document.createElement('span'))
	;

    /* add class and id on container */
    $container.addClass('justify-content-center text-center m-0 row');
    $container.attr('id', 'double-login-container');

    /* add class on message box and append it on container */
    $msgbox.addClass('message-box align-middle m-auto col-5 rounded row border');
    $container.append($msgbox);

	/* add class and icon to closer link and append it on message box */
	$closer.addClass('closer m-0');
	$closer.attr('href', '#');
	$iconCloser.addClass('far fa-times-circle');
	$closer.append($iconCloser);
	$msgbox.append($closer);

    /* add title and append on message box */
	$title.addClass('p-3 m-auto col-12');
	$title.text('Insert your password');
	$msgbox.append($title);
	
	/* set attribute for form and append it message box */
	$form.attr('id', 'double-login-form');
	$form.attr('action', dblLgnData.actn);
	$form.attr('method', 'post');
	$form.addClass('m-auto');
	$msgbox.append($form);

	/* add csrf token on form */
	$token.attr('id', '_xf');
	$token.attr('type', 'hidden');
	$token.attr('name', dblLgnData._xf_nm);
	$token.val(dblLgnData._xf);
	$form.append($token);

	/* add csrf token to get public key */
	$tokenKey.attr('id', '_kxt');
	$tokenKey.attr('type', 'hidden');
	$tokenKey.attr('name', dblLgnData._kxt_nm);
	$tokenKey.val(dblLgnData._kxt);
	$form.append($tokenKey);

	/* create input for password and append it on form */
	$contPass.addClass('form-group text-md-left');
	$labelPass.attr('for', 'dbllgn-pass');
	$labelPass.text('Password');
	$contPass.append($labelPass);
	$inputPass.attr('id', 'dbllgn-pass');
	$inputPass.attr('name', 'password')
	$inputPass.attr('placeholder', 'Password');
	$inputPass.attr('type', 'password');
	$inputPass.attr('required', 'required')
	$inputPass.addClass('form-control send-ajax-crypt evidence-error p-2');
	$contPass.append($inputPass);
	$form.append($contPass);

	/* create button and append it on form */
	$submitBtn.attr('type', 'submit');
	$submitBtn.addClass('btn btn-success px-3 py-1 m-2')
	$icoBtn.addClass('fas fa-check ico-btn');
	$submitBtn.append($icoBtn);
	$spinnerBtn.addClass('spinner-border spinner-border-sm d-none spinner');
	$spinnerBtn.attr('role', 'status');
	$spinnerBtn.attr('aria-hidden', 'true');
	$submitBtn.append($spinnerBtn);
	$txtBtn.addClass('text-btn');
	$txtBtn.text('Confirm');
	$submitBtn.append($txtBtn);
	$form.append($submitBtn);

	/* add submit listener on form */
	$form.on('submit', function() {
		/* get form, button, token and action url */
		const actionUrl = $(this).attr('action');

		/* block default submit form and show loading */
		event.preventDefault();
		var txtBttn = showLoading($submitBtn);

		/* crypt and serialize data */
		data = cryptSerialize($inputPass);

		/* success function */
		funcSuccess = function(response) {
			removeLoading($submitBtn, txtBttn);

			try {
				showMessage(response.message, !response.success);
				if (response.success) {
					sendAjaxReq(reqData.url, reqData.data, reqData.$token, reqData.funcSuccess, reqData.funcFail);
					$container.fadeOut(400);
				} else if (response.ntk !== undefined) $token.val(response.ntk);
			} catch (e) {
				showMessage('Double login failed', true);
			}
		};
		
		/* fail function */
		funcFail = function() {
			removeLoading($submitBtn, txtBttn);
			showMessage('Problem to contact server', true);
		};

		sendAjaxReq(actionUrl, data, $token, funcSuccess, funcFail);
	});
	/* append container on body and fade in it */
	$(document.body).append($container);
	reqPublicKey();
	$container.fadeIn(400);
	$inputPass.focus();

	/* create handle to close message box */
	closeHandler = function(event) {
		event.preventDefault();
		if (event.type === 'click' || (event.type === 'keyup' && event.keyCode === 27)) {
			$container.fadeOut(400);
			reqData.funcFail();
			window.removeEventListener('keyup', closeHandler);
		}
	}
	
	/* add listener on closer */
	$closer.click(closeHandler);
	
	/* add key listener to close msg box */
	window.addEventListener('keyup', closeHandler);
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

/* function to evidence a right input */
function evidenceRight($elem) {
	$elem.css('border', '1px solid green');
	$elem.css('box-shadow', '0 0 5px green');
}

/* function to remove a evidence */
function removeEvidence($elem) {
	$elem.css('border', '');
	$elem.css('box-shadow', '');
}

/* function to show a message box */
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

/* function to close a message box */
function closeMessage() {
	isMessageShow = false;
	$('#message-box').animate({
		top: '-100px'
	});
}

/* function to set position of the message box */
function positionMessageBox() {
	if (isMessageShow) $('#message-box').css('top', $(window).scrollTop() + 'px');
}

/* function to disable a element */
function disableElement($elem) {
	$elem.attr("disabled", "disabled");
}

/* function to enable a element */
function enableElement($elem) {
	$elem.removeAttr("disabled");
}

/* function to redirect */
function redirect(url) {
	location.href = url;
}

/* function to show loading */
function showLoading($btn) {
	textBtn = $btn.children('.text-btn').text()
	$btn.attr("disabled", "disabled");
	$btn.children('.ico-btn').addClass('d-none');
	$btn.children('.spinner').removeClass('d-none');
	$btn.children('.text-btn').text(' Loading...');
	return textBtn;
}

/* function to remove a loading */
function removeLoading($btn, text) {
	$btn.removeAttr("disabled");
	$btn.children('.spinner').addClass('d-none');
	$btn.children('.ico-btn').removeClass('d-none');
	$btn.children('.text-btn').text(text);
}

$(document).ready(function () {
	/* fade out for message */
	$('#message.fade-out').fadeOut(7000);

	/* listenere to close a message box */
	$('#close-message-box.btn-close-msg').click(function(event) {
		event.preventDefault();
		closeMessage();
	});

	$(window).resize(function() {
		positionMessageBox();
	});

	$(window).scroll(function() {
		positionMessageBox();
	});

	$('#dropdown-lang button').each(function(index, elem) {
		console.log(elem);

		var $elem = $(elem);
		$elem.click(function() {
			var lang = $elem.val();
			document.cookie = 'lang='+lang+';path=/';
			redirect(document.location);
		});
	});
});
