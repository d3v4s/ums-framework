<div class="container col-md-7 text-center">
    <h1>Signup</h1>
    <form id="signup-form" action="/auth/signup" method="POST">
    	<div class="form-group text-md-left">
    		<label for="name">Full Name <span class="text-red">*</span></label>
    		<input id="name" placeholder="Full name" class="form-control validate-name evidence-error send-ajax" type="text" name="name" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="username">Username <span class="text-red">*</span></label>
    		<input id="username" name="username" placeholder="Username" class="form-control validate-username evidence-error send-ajax" type="text" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="email">Email <span class="text-red">*</span></label>
    		<input id="email" name="email" placeholder="Email" class="form-control validate-email evidence-error send-ajax" type="email" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="pass">Password <span class="text-red">*</span></label>
    		<input id="pass" name="pass"  placeholder="Password" class="form-control validate-password confirm-password-1 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="cpass">Confirm password <span class="text-red">*</span></label>
    		<input id="cpass" name="cpass" placeholder="Confirm password" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
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
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
    </form>
</div>