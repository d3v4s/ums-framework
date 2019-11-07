var appConf = null;

function getConf() {
	if (window.appConf == null)  $.getJSON('/app/config/get/json', function(data) {window.appConf = data;});

	return window.appConf;
}

function validatePassword($elem) {
	var pass = $elem.val(),
		conf = getConf(); 
	if (pass.length < conf.minLengthPassword) {
		evidenceError($elem);
		return;
	}
	if (conf.checkMaxLengthPassword && pass.length > conf.maxLengthPassword) {
		evidenceError($elem);
		return;
	}
	if (conf.requireHardPassword) {
		var regHardPass = new RegExp('^((?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[$@!%*?&._\\-]))[A-Za-z\\d$@!%*?&._\\-]{' + conf.minLengthPassword + ',}$');
		if (!regHardPass.test(pass)) {
			evidenceError($elem);
			return;
		}
	}
	if (conf.useRegex) {
		var regPass = new RegExp(conf.regexPassword);
		if (!regPass.test(pass)) {
			evidenceError($elem);
			return;
		}
	}
	evidenceRight($elem);
}

function validatePasswordOld() {
	var $pass = $('#pass.validate'),
		pass = $pass.val(),
		conf = getConf(); 
	if (pass.length < conf.minLengthPassword) {
		evidenceError($pass);
		return;
	}
	if (conf.checkMaxLengthPassword && pass.length > conf.maxLengthPassword) {
		evidenceError($pass);
		return;
	}
	if (conf.requireHardPassword) {
		var regHardPass = new RegExp('^((?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[$@!%*?&._\\-]))[A-Za-z\\d$@!%*?&._\\-]{' + conf.minLengthPassword + ',}$');
		if (!regHardPass.test(pass)) {
			evidenceError($pass);
			return;
		}
	}
	if (conf.useRegex) {
		var regPass = new RegExp(conf.regexPassword);
		if (!regPass.test(pass)) {
			evidenceError($pass);
			return;
		}
	}
	evidenceRight($pass);
}

function confirmPassword($pass, $cpass) {
	var pass = $pass.val(),
		cpass = $cpass.val();
	if (pass !== cpass) evidenceError($cpass);
	else evidenceRight($cpass);
}

function validateFullname($elem) {
	var name = $elem.val(),
		conf = getConf();
	if (name.length < conf.minLengthName) {
		evidenceError($elem);
		return;
	}
	if (name.length > conf.maxLengthName) {
		evidenceError($elem);
		return;
	}
	if (conf.useRegex) {
		var regName = new RegExp(conf.regexName);
		if (!regName.test(name)) {
			evidenceError($elem);
			return;
		}
	}
	evidenceRight($elem);
}

function validateFullnameOld() {
	var $name = $('#name.validate'),
		name = $name.val(),
		conf = getConf();
	if (name.length < conf.minLengthName) {
		evidenceError($name);
		return;
	}
	if (name.length > conf.maxLengthName) {
		evidenceError($name);
		return;
	}
	if (conf.useRegex) {
		var regName = new RegExp(conf.regexName);
		if (!regName.test(name)) {
			evidenceError($name);
			return;
		}
	}
	evidenceRight($name);
}

function validateUsername($elem) {
	var username = $elem.val(),
		conf = getConf();
	if (username.length < conf.minLengthUsername) {
		evidenceError($elem);
		return;
	}
	if (username.length > conf.maxLengthUsername) {
		evidenceError($elem);
		return;
	}
	if (conf.useRegex) {
		var regUsername = new RegExp(conf.regexUsername);
		if (!regUsername.test(username)) {
			evidenceError($elem);
			return;
		}
	}
	evidenceRight($elem);
}

function validateUsernameOld() {
	var $username = $('#username.validate'),
		username = $username.val(),
		conf = getConf();
	if (username.length < conf.minLengthUsername) {
		evidenceError($username);
		return;
	}
	if (username.length > conf.maxLengthUsername) {
		evidenceError($username);
		return;
	}
	if (conf.useRegex) {
		var regUsername = new RegExp(conf.regexUsername);
		if (!regUsername.test(username)) {
			evidenceError($username);
			return;
		}
	}
	evidenceRight($username);
}

function validateEmailOld() {
	var $email = $('#email.validate'),
	email = $email.val(),
	conf = getConf();
	if (conf.useRegexEmail) {
		var regEmail = new RegExp(conf.regexEmail);
		if (!regEmail.test(email)) {
			evidenceError($email);
			return;
		}
	}
	evidenceRight($email);
}

function validateEmail($elem) {
	const email = $elem.val(),
		conf = getConf();
	if (conf.useRegexEmail) {
		var  regEmail = new RegExp(conf.regexEmail);
		if (!regEmail.test(email)) {
			evidenceError($elem);
			return;
		}
	}
	evidenceRight($elem);
}

$(document).ready(function () {
	$('input.validate-email').on('keyup', function() {
		validateEmail($(this));
	});
	$('input.validate-password').on('keyup', function() {
		validatePassword($(this));
	});
	$('input.confirm-password-2').on('keyup', function() {
		$pass = $('input.confirm-password-1');
		confirmPassword($pass, $(this));
	});
	$('input.validate-name').on('keyup', function() {
		validateFullname($(this));
	});
	$('input.validate-username').on('keyup', function() {
		validateUsername($(this));
	});
});