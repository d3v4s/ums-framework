<div class="container col-md-7 text-center">
    <h1>Insert your password</h1>
    <form id="double-login-form" action="/<?=DOUBLE_LOGIN_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=PASSWORD?>">Password</label>
    		<input id="<?=PASSWORD?>" name="<?=PASSWORD?>" placeholder="Password" class="form-control evidence-error send-ajax-crypt mb-1" type="password" required="required" autofocus="autofocus">
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_DOUBLE_LOGIN?>" value="<?=${TOKEN}?>">
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO CONTINUE</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-double-login" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-sign-in-alt"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Continue</span>
	    	</button>
    	</div>
    	<?php if (isset(${REDIRECT_TO})): ?>
    		<input id="<?=REDIRECT_TO?>" value="<?=${REDIRECT_TO}?>" type="hidden">
    	<?php endif; ?>
    </form>
</div>