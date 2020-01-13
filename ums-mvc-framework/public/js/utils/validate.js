/* FUNCTION TO VALIDATE A INPUTS OF UMS-FRAMEWORK
 * Develop by Andrea Serra (DevAS) https://devas.info https://github.com/d3v4s
 */

/* global var for app configurations */
window.appConf = null;

/* function to get configurations */ 
function getConf() {
	/* if configuration is null, then send request to get it */
	if (window.appConf == null) $.getJSON('/app/config/get/json', function(data) {window.appConf=data;});
	/* return conf */
	return window.appConf;
}

/* function to validate a password */
function validatePassword($elem) {
	/* get password value and config */
	const pass = $elem.val(),
		conf = getConf();
	/* check lenght */
	if (pass.length < conf.minLengthPassword || pass.length > conf.maxLengthPassword) {
		/* evidence error and return */
		evidenceError($elem);
		return;
	}
	/* exec regex */
	if (conf.useRegexPassword) {
		var regPass = new RegExp(conf.regexPassword);
		if (!regPass.test(pass)) {
			/* evidence error and return */
			evidenceError($elem);
			return;
		}
	}
	/* no error */
	evidenceRight($elem);
}

/* function to compare 2 passwords */
function confirmPassword($pass, $cpass) {
	/* get password values */
	const pass = $pass.val(),
		cpass = $cpass.val();

	/* if not equals, then evidence the error */
	if (pass !== cpass) evidenceError($cpass);
	/* else evidence right */
	else evidenceRight($cpass);
}

/* function to validate the name */
function validateFullname($elem) {
	/* get name value and config */
	var name = $elem.val(),
		conf = getConf();

	/* check lenght */
	if (name.length < conf.minLengthName || name.length > conf.maxLengthName) {
		/* evidence error and return */
		evidenceError($elem);
		return;
	}

	/* exec regex */
	if (conf.useRegexName) {
		var regName = new RegExp(conf.regexName);
		if (!regName.test(name)) {
			/* evidence error and return */
			evidenceError($elem);
			return;
		}
	}
	/* evidence right */
	evidenceRight($elem);
}

/* function to validate a username */
function validateUsername($elem) {
	/* get usrname value and config */
	var username = $elem.val(),
		conf = getConf();

	/* check lenght */
	if (username.length < conf.minLengthUsername || username.length > conf.maxLengthUsername) {
		/* evidence error and return */
		evidenceError($elem);
		return;
	}

	/* exec regex */
	if (conf.useRegexUsername) {
		var regUsername = new RegExp(conf.regexUsername);
		if (!regUsername.test(username)) {
			/* evidence error and return */
			evidenceError($elem);
			return;
		}
	}
	/* evidence right */
	evidenceRight($elem);
}

/* fucntion to validate a password */
function validateEmail($elem) {
	/* get email value and config */
	const email = $elem.val(),
		conf = getConf();

	/* check lenght */
	if (email.length < conf.minLengthEmail || email.length > conf.maxLengthEmail) {
		/* evidence error and return */
		evidenceError($elem);
		return;
	}

	/* exec regex */
	if (conf.useRegexEmail) {
		var  regEmail = new RegExp(conf.regexEmail);
		if (!regEmail.test(email)) {
			/* evidence error and return */
			evidenceError($elem);
			return;
		}
	}
	/* evidence right */
	evidenceRight($elem);
}

/* function to execute when document is ready */
$(document).ready(function () {
	/* keyup event to validate email */
	$('input.validate-email').on('keyup', function() {
		validateEmail($(this));
	});
	/* keyup event to validate password */
	$('input.validate-password').on('keyup', function() {
		validatePassword($(this));
	});
	/* keyup event to confirm password */
	$('input.confirm-password-2').on('keyup', function() {
		$pass = $('input.confirm-password-1');
		confirmPassword($pass, $(this));
	});
	/* keyup event to validate name */
	$('input.validate-name').on('keyup', function() {
		validateFullname($(this));
	});
	/* keyup event to validate username */
	$('input.validate-username').on('keyup', function() {
		validateUsername($(this));
	});
});