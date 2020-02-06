<div class="container col-md-7 text-center">
	<h3><?=${LANG}['double_login']?></h3>
    <h4><?=${LANG}['insert_password']?></h4>
    <form id="double-login-form" action="/<?=DOUBLE_LOGIN_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=PASSWORD?>">Password</label>
    		<input id="<?=PASSWORD?>" name="<?=PASSWORD?>" placeholder="Password" class="form-control evidence-error send-ajax-crypt mb-1" type="password" required="required" autofocus="autofocus">
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_DOUBLE_LOGIN?>" value="<?=${TOKEN}?>">
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger"><?=${LANG}[ENABLE_JAVASCRIPT]?></h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-double-login" class="btn btn-success px-3 py-1" type="submit">
	    		<i class="fas fa-sign-in-alt ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn"><?=${LANG}[CONFIRM]?></span>
	    	</button>
    	</div>
    	<?php if (isset(${REDIRECT_TO})): ?>
    		<input id="<?=REDIRECT_TO?>" value="<?=${REDIRECT_TO}?>" type="hidden">
    	<?php endif; ?>
    </form>
</div>