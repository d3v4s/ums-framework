<div class="container col-md-7 text-center">
    <h1>Login</h1>
    <form id="login-form" action="/auth/login" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=USER?>">Email/Username</label>
    		<input id="<?=USER?>" name="<?=USER?>" placeholder="Email/Username" class="form-control evidence-error send-ajax" type="text" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=PASSWORD?>">Password</label>
    		<input id="<?=PASSWORD?>" name="<?=PASSWORD?>" placeholder="Password" class="form-control evidence-error send-ajax-crypt mb-1" type="password" required="required">
    		<div class="text-left mx-1">
        		<a href="/auth/reset/password" class="link-danger"><?=${LANG}['forgot_password']?></a>
    		</div>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_LOGIN?>" value="<?=${TOKEN}?>">
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-login" class="btn btn-success px-3 py-1" type="submit">
	    		<i class="fas fa-sign-in-alt ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Login</span>
	    	</button>
    	</div>
    </form>
</div>