<div class="container col-md-7 text-center p-3">
    <form id="change-pass-form" action="/<?=ACCOUNT_SETTINGS_ROUTE.'/'.PASS_UPDATE_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=CURRENT_PASS?>"><?=${LANG}['current_pass']?></label>
    		<input id="<?=CURRENT_PASS?>" name="<?=CURRENT_PASS?>"  placeholder="<?=${LANG}['current_pass']?>" class="form-control evidence-error send-ajax-crypt" type="password" required="required" autofocus="autofocus">
    	</div>
		<div class="form-group text-md-left">
    		<label for="<?=PASSWORD?>">Password</label>
    		<input id="<?=PASSWORD?>" name="<?=PASSWORD?>"  placeholder="Password" class="form-control validate-password confirm-password-1 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=CONFIRM_PASS?>"><?=${LANG}[CONFIRM_PASS]?></label>
    		<input id="<?=CONFIRM_PASS?>" name="<?=CONFIRM_PASS?>" placeholder="<?=${LANG}[CONFIRM_PASS]?>" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-change" class="btn btn-success px-3 py-1" type="submit">
	    		<i class="fas fa-check ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn"><?=${LANG}[UPDATE]?></span>
	    	</button>
    	</div>
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_CHANGE_PASS?>" value="<?=${TOKEN}?>">
    </form>
</div>