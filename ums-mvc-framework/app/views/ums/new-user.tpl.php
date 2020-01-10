<div class="container col-md-7 text-center">
    <h1>New User</h1>
    <form id="new-user-form" action="/ums/user/new" method="POST">
    	<div class="form-group text-md-left">
    		<label for="name">Full Name</label>
    		<input id="name" placeholder="Full name" class="form-control validate-name evidence-error send-ajax" type="text" name="name" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="username">Username</label>
    		<input id="username" name="username" placeholder="Username" class="form-control validate-username evidence-error send-ajax" type="text" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="email">Email</label>
    		<input id="email" name="email" placeholder="Email" class="form-control validate-email evidence-error send-ajax" type="email" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="pass">Password</label>
    		<input id="pass" name="pass"  placeholder="Password" class="form-control validate-password confirm-password-1 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="cpass">Confirm password</label>
    		<input id="cpass" name="cpass" placeholder="Confirm password" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-left my-3">
    		<label for="role">Role</label>
    		<select id="role" name="role" class="send-ajax evidence-error">
    			<?php foreach ($userRoles as $key => $role): ?>
	    			<option <?=$key === 0 ? 'selected="selected"' : ''?> value="<?=$role?>"><?=ucfirst($role)?></option>
    			<?php endforeach; ?>
    		</select>
    	</div>
    	<div class="custom-control custom-switch text-left">
			<input id="enabled" name="enabled" type="checkbox" class="custom-control-input send-ajax" value="true" checked="checked">
			<label class="custom-control-label" for="enabled">Enabled</label>
        </div>
        <noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO ADD NEW USER</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-save" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Save</span>
	    	</button>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
    </form>
</div>