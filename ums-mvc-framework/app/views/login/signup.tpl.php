<div class="container col-md-7 text-center">
    <h1>Signup</h1>
    <form id="signup-form" action="/<?=SIGNUP_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=NAME?>">Full Name <span class="text-red">*</span></label>
    		<input id="<?=NAME?>" name="<?=NAME?>" placeholder="Full name" class="form-control validate-name evidence-error send-ajax" type="text" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?-USERNAME?>">Username <span class="text-red">*</span></label>
    		<input id="<?=USERNAME?>" name="<?=USERNAME?>" placeholder="Username" class="form-control validate-username evidence-error send-ajax" type="text" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=EMAIL?>">Email <span class="text-red">*</span></label>
    		<input id="<?=EMAIL?>" name="<?=EMAIL?>" placeholder="Email" class="form-control validate-email evidence-error send-ajax" type="email" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=PASSWORD?>">Password <span class="text-red">*</span></label>
    		<input id="<?=PASSWORD?>" name="<?=PASSWORD?>"  placeholder="Password" class="form-control validate-password confirm-password-1 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=CONFIRM_PASS?>">Confirm password <span class="text-red">*</span></label>
    		<input id="<?=CONFIRM_PASS?>" name="<?=CONFIRM_PASS?>" placeholder="Confirm password" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_SIGNUP?>" value="<?=${TOKEN}?>">
    	<p class="text-red text-left text-small">* Required</p>
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO SIGNUP</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-signup" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-user-plus"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Signup</span>
	    	</button>
    	</div>
    </form>
</div>