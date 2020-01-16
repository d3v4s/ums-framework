<div class="container col-md-7 text-center">
    <h1>User: <?=${USER}->{USERNAME}?></h1>
    <form id="update-pass-form" action="/<?=USER_ROUTE.'/'.PASS_UPDATE_ROUTE?>" method="POST">
		<div class="form-group text-md-left">
    		<label for="<?=PASSWORD?>">Password</label>
    		<input id="<?=PASSWORD?>" name="<?=PASSWORD?>"  placeholder="Password" class="form-control confirm-password-1 send-ajax-crypt" type="password" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=CONFIRM_PASS?>">Confirm password</label>
    		<input id="<?=CONFIRM_PASS?>" name="<?=CONFIRM_PASS?>" placeholder="Confirm password" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO UPDATE PASSWORD</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-change" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Change</span>
	    	</button>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_UPDATE_PASS?>" value="<?=${TOKEN}?>">
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<input id="<?=USER_ID?>" type="hidden" name="<?=USER_ID?>" value="<?=${USER}->{USER_ID}?>" class="send-ajax">
    </form>
</div>