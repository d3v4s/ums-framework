<div class="container col-md-7 text-center p-3">
    <form id="change-pass-form" action="/<?=ACCOUNT_SETTINGS_ROUTE.'/'.PASS_UPDATE_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=OLD_PASS?>">Old password</label>
    		<input id="<?=OLD_PASS?>" name="<?=OLD_PASS?>"  placeholder="Old password" class="form-control evidence-error send-ajax-crypt" type="password" required="required" autofocus="autofocus">
    	</div>
		<div class="form-group text-md-left">
    		<label for="<?=PASSWORD?>">Password</label>
    		<input id="<?=PASSWORD?>" name="<?=PASSWORD?>"  placeholder="Password" class="form-control validate-password confirm-password-1 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=CONFIRM_PASS?>">Confirm password</label>
    		<input id="<?=CONFIRM_PASS?>" name="<?=CONFIRM_PASS?>" placeholder="Confirm password" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO CHANGE YOUR PASSWORD</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-change" class="btn btn-success px-3 py-1" type="submit">
	    		<i class="fas fa-check ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Change</span>
	    	</button>
    	</div>
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_CHANGE_PASS?>" value="<?=${TOKEN}?>">
    </form>
</div>