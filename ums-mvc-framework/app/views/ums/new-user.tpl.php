<div class="container col-md-7 text-center">
    <h1>New User</h1>
    <form id="new-user-form" action="/<?=UMS_TABLES_ROUTE.'/'.ACTION_ROUTE.'/'.USERS_TABLE.'/'.NEW_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=NAME?>">Full Name</label>
    		<input id="<?=NAME?>" name="<?=NAME?>" placeholder="Full name" class="form-control validate-name evidence-error send-ajax" type="text" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=USERNAME?>">Username</label>
    		<input id="<?=USERNAME?>" name="<?=USERNAME?>" placeholder="Username" class="form-control validate-username evidence-error send-ajax" type="text" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=EMAIL?>">Email</label>
    		<input id="<?EMAIL?>" name="<?=EMAIL?>" placeholder="Email" class="form-control validate-email evidence-error send-ajax" type="email" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=PASSWORD?>">Password</label>
    		<input id="<?=PASSWORD?>" name="<?=PASSWORD?>" placeholder="Password" class="form-control validate-password confirm-password-1 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=CONFIRM_PASS?>">Confirm password</label>
    		<input id="<?=CONFIRM_PASS?>" name="<?=CONFIRM_PASS?>" placeholder="Confirm password" class="form-control confirm-password-2 evidence-error send-ajax-crypt" type="password" required="required">
    	</div>
    	<div class="form-group text-left my-3">
    		<label for="<?=ROLE_ID_FRGN?>">Role</label>
    		<select id="<?=ROLE_ID_FRGN?>" name="<?=ROLE_ID_FRGN?>" class="send-ajax evidence-error">
    			<?php foreach (${ROLES} as $role): ?>
	    			<option <?=$role[ROLE_ID] === DEFAULT_ROLE ? 'selected="selected"' : ''?> value="<?=$role[ROLE_ID]?>"><?=ucfirst($role[ROLE])?></option>
    			<?php endforeach; ?>
    		</select>
    	</div>
    	<div class="custom-control custom-switch text-left">
			<input id="<?=PENDING?>" name="<?=PENDING?>" type="checkbox" class="custom-control-input send-ajax" value="true">
			<label class="custom-control-label" for="<?=PENDING?>">Pending</label>
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
    	<input id="<?=GET_KEY_TOKEN?>" type="hidden" name="<?=CSRF_KEY_JSON?>" value="<?=${GET_KEY_TOKEN}?>">
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_NEW_USER?>" value="<?=${TOKEN}?>">
    </form>
</div>