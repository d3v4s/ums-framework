<div class="container col-md-7 text-center">
    <h1>Reset Password</h1>
    <form id="reset-pass-form" action="/<?=PASS_RESET_ROUTE?>" method="post">
		<div class="form-group text-md-left">
    		<label for="<?=PASSWORD?>">Password</label>
    		<input id="<?=PASSWORD?>" name="<?=PASSWORD?>"  placeholder="Password" class="form-control validate-password confirm-password-1 evidence-error send-ajax-crypt" type="password" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=CONFIRM_PASS?>"><?=${LANG}[CONFIRM_PASS]?></label>
    		<input id="<?=CONFIRM_PASS?>" name="<?=CONFIRM_PASS?>" placeholder="Confirm password" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-reset-pass" class="btn btn-success px-3 py-1" type="submit">
	    		<i class="fas fa-check ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Reset</span>
	    	</button>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_PASS_RESET?>" value="<?=${TOKEN}?>">
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<input type="hidden" name="<?=PASSWORD_RESET_TOKEN?>" value="<?=${PASSWORD_RESET_TOKEN}?>" class="send-ajax">
    </form>
</div>