<div class="container col-md-7 text-center p-3">
	<h2>YOU ARE SIGNUP</h2>
	<h3>Now confirm your email and login</h3>
    <form id="signup-confirm-form" action="/<?=CONFIRM_SIGNUP_ROUTE.'/'.RESEND_EMAIL_ROUTE?>" method="post">
    	<a class="btn btn-success mx-2 my-2" href="/<?=LOGIN_ROUTE?>">Login</a>
    	<button id="btn-resend-email" class="btn btn-primary mx-2 my-2" type="submit">
			<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
			<span id="text-btn">Resend email</span>
		</button>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_RESEND_ENABLER_ACC?>" value="<?=${TOKEN}?>">
	</form>
</div>
