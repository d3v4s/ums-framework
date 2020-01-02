<div class="container col-md-7 text-center p-3">
	<h2>YOU ARE SIGNUP</h2>
	<h3>Now confirm your email and login</h3>
    <form id="signup-confirm-form" action="/auth/signup/confirm/email/resend" method="post">
    	<a class="btn btn-success mx-2 my-2" href="/auth/login">Login</a>
    	<button id="btn-resend-email" class="btn btn-primary mx-2 my-2" type="submit">
				<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Resend email</span>
			</button>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
	</form>
</div>
