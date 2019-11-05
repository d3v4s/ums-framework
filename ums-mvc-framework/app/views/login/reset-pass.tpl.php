<div class="container col-md-7 text-center">
    <h1>Reset Password</h1>
    <form id="reset-pass-form" action="/user/reset/password" method="post">
		<div class="form-group text-md-left">
    		<label for="pass">Password</label>
    		<input id="pass" name="pass"  placeholder="Password" class="form-control validate-password confirm-password-1 evidence-error send-ajax-crypt" type="password" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="cpass">Confirm password</label>
    		<input id="cpass" name="cpass" placeholder="Confirm password" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div id="errors"></div>
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO RESET YOUR PASSWORD</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-reset-pass" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Reset</span>
	    	</button>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>" class="send-ajax">
    	<input type="hidden" name="token" value="<?=$tokenReset?>" class="send-ajax">
    </form>
</div>