function validatePassword() {
	var $pass = $('#pass');
	var $cpass = $('#cpass');
	var pass = $pass.val();
	var cpass = $cpass.val();
	$pass.css('border', '');
	$cpass.css('border', '');
	if (pass.length < 4) $pass.css('border', '1px solid red');
	else if (pass !== cpass) $cpass.css('border', '1px solid red');
	else {
		$pass.css('border', '1px solid green');
		$cpass.css('border', '1px solid green');
	}
}

function validateFullname() {
	var $name = $('#name');
	var name = $name.val();
	if (name.length < 2) $name.css('border', '1px solid red');
	else $name.css('border', '1px solid green')
}

function validateUsername() {
	var $username = $('#username');
	var username = $username.val();
	if (username.length < 5) $username.css('border', '1px solid red');
	else $username.css('border', '1px solid green');
}

$(document).ready(function () {
	$('#name', '#signup-form').on('keyup', function () {
		validateFullname();
	});
	$('#username', '#signup-form').on('keyup', function () {
		validateUsername();
	});
	$('#cpass', '#signup-form').on('keyup', function () {
		validatePassword();
	});

	$('#name', '#newuser-form').on('keyup', function () {
		validateFullname();
	});
	$('#username', '#newuser-form').on('keyup', function () {
		validateUsername();
	});
	$('#cpass', '#newuser-form').on('keyup', function () {
		validatePassword();
	});

	$('#name', '#user-update-form').on('keyup', function () {
		validateFullname();
	});
	$('#username', '#user-update-form').on('keyup', function () {
		validateUsername();
	});
	$('#cpass', '#user-update-form').on('keyup', function () {
		validatePassword();
	});

	$('#cpass', '#update-pass-form').on('keyup', function () {
		validatePassword();
	});
	
//	$("#signup-form").validate({
//		rules: {
//			name: "required",
//			username: {
//				required: true,
//				minlength: 5
//			},
//			email: {
//				required: true,
//				email: true
//			},
//			pass: {
//				required: true,
//				minlength: 4
//			},
//			cpass: {
//				minlength: 4,
//				equalTo: "#pass"
//			},
//		}
//	});
});


//$("#pass").passwordValidation({"confirmField": "#conf-pass"}, function(element, valid, match, failedCases) {
//  $("#errors").html("<pre>" + failedCases.join("\n") + "</pre>");
//   if(valid) $(element).css("border","2px solid green");
//   if(!valid) $(element).css("border","2px solid red");
//   if(valid && match) $("#conf-pass").css("border","2px solid green");
//   if(!valid || !match) $("#conf-pass").css("border","2px solid red");
//});


//$( document ).ready(function() {
//    console.log( "ready!" );
//    jQuery.validator.setDefaults({
//    	debug: true,
//    	success: "valid"
//	});
//
//	$('#form1').validate({
//		rules:{
//			Email:{
//				required:true,
//				email:true
//			},
//			password:{
//				required:true,
//				minlength:3,
//			},
//			password_again:{
//				equalTo:"#password"
//			}
//		},
//	
//		highlight: function(element) {
//			$(element).closest('.form-group').addClass('has-error');
//		},
//		unhighlight: function(element) {
//			$(element).closest('.form-group').removeClass('has-error');
//		},
//		errorElement: 'span',
//		errorClass: 'help-block',
//		errorPlacement: function(error, element) {
//			if(element.parent('.input-group').length) {
//				error.insertAfter(element.parent());
//			} else {
//				error.insertAfter(element);
//			}
//		}
//	})
//});

//$(document).ready(function () {
//	$("#signup-form").validate( {
//		onkeyup: true,
//		rules: {
//			name: "required",
//			username: {
//				required: true,
//				minlength: 5
//			},
//			email: {
//				required: true,
//				email: true
//			},
//			pass: {
//				required: true,
//				minlength: 4
//			},
//			cpass: {
//				minlength: 4,
//				equalTo: "#pass"
//			},
//		},
//		messages: {
//			name: "Please enter your full name",
//			username: {
//				required: "Please enter a username",
//				minlength: "Your username must consist of at least 5 characters"
//			},
//			email: "Please enter a valid email address",
//			pass: {
//				required: "Please provide a password",
//				minlength: "Your password must be at least 4 characters long"
//			},
//			cpass: {
//				minlength: "Your password must be at least 4 characters long",
//				equalTo: "Please enter the same password as above"
//			},
//		},
//		errorElement: "em",
//		errorPlacement: function (error, element) {
//			// Add the `help-block` class to the error element
//			error.addClass("help-block");
//
//			// Add `has-feedback` class to the parent div.form-group
//			// in order to add icons to inputs
//			element.parents(".col-sm-5").addClass("has-feedback");
//
//			error.insertAfter(element);
////			if (element.prop("type") === "checkbox") {
////				error.insertAfter(element.parent("label"));
////			} else {
////			}
//
//			// Add the span element, if doesn't exists, and apply the icon classes to it.
//			if (!element.next("span")[0]) {
//				$("<span class='glyphicon glyphicon-remove form-control-feedback'></span>").insertAfter(element);
//			}
//		},
//		success: function (label, element) {
//			// Add the span element, if doesn't exists, and apply the icon classes to it.
//			if (!$( element).next("span")[0]) {
//				$("<span class='glyphicon glyphicon-ok form-control-feedback'></span>").insertAfter($(element));
//			}
//		},
//		highlight: function (element, errorClass, validClass) {
//			$(element).parents(".col-sm-5").addClass("has-error").removeClass("has-success");
//			$(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
//		},
//		unhighlight: function (element, errorClass, validClass) {
//			$(element).parents(".col-sm-5").addClass("has-success").removeClass("has-error");
//			$(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
//		}
//	});
//});