<div class="container col-md-7 text-center p-3">
<!-- 	<div class="container-fluid justify-content-right text-right"> -->
<!--     	<form action="/auth/logout" method="post"> -->
<!-- 			<input id="_xf-out" type="hidden" name="_xf-out" value="< ?=$tokenLogout?>" class="send-ajax"> -->
<!-- 			<button id="btn-logout" class="btn btn-danger m-2" type="submit"><i id="ico-btn" class="fas fa-sign-out-alt"></i> Logout</button> -->
<!-- 		</form> -->
<!-- 	</div> -->
    <form id="user-update-form" action="/<?=ACCOUNT_SETTINGS_ROUTE.'/'.UPDATE_ROUTE?>" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=NAME?>">Full name</label>
    		<input id="<?=NAME?>" name="<?=NAME?>" value="<?=${USER}->{NAME}?>" placeholder="Full name" class="form-control validate-name evidence-error send-ajax" type="text" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=USERNAME?>">Username</label>
    		<input id="<?=USERNAME?>" name="<?=USERNAME?>" value="<?=${USER}->{USERNAME}?>" placeholder="Username" class="form-control validate-username evidence-error send-ajax" type="text" required="required">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="<?=EMAIL?>">Email</label>
    		<input id="<?=EMAIL?>" name="<?=EMAIL?>" value="<?=${USER}->{EMAIL}?>" placeholder="Email" class="form-control validate-email evidence-error send-ajax" type="email" required="required">
    	</div>
    	<?php if (${WAIT_EMAIL_CONFIRM}): ?>
    		<div class="form-group text-md-left">
    			<label for="new-email">New email</label>
    			<input id="new-email" value="<?=${WAIT_EMAIL_CONFIRM}->{NEW_EMAIL}?>" class="form-control" type="email" readonly="readonly">
    			<div class="row ">
    				<div class="col-6 text-left">
            			<button id="btn-resend-email" value="/<?=ACCOUNT_SETTINGS_ROUTE.'/'.RESEND_EMAIL_ROUTE?>" class="btn btn-link link-primary p-0" type="button">Resend email</button>
    				</div>
    				<div class="col-6 text-right">
            			<button id="btn-delete-new-email" value="/<?=ACCOUNT_SETTINGS_ROUTE.'/'.DELETE_EMAIL_ROUTE?>" class="btn btn-link link-danger p-0" type="button">Delete</button>
    				</div>
    			</div>
    		</div>
    	<?php endif; ?>
    	<?php if (${USER}->{ROLE_ID_FRGN} !== DEFAULT_ROLE): ?>
        	<div class="form-group text-left my-3">
        		<label for="role">Role</label>
        		<select id="role" disabled="disabled">
        			<?php foreach (${ROLES} as $role): ?>
    	    			<option <?=$role[ROLE_ID] === ${USER}->{ROLE_ID_FRGN} ? 'selected="selected"' : ''?> value="<?=$role[ROLE_ID]?>"><?=ucfirst($role[ROLE])?></option>
        			<?php endforeach; ?>
        		</select>
        	</div>
    	<?php endif; ?>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
    		<a id="btn-delete" class="btn btn-danger px-3 py-1 mx-2 my-2" href="/<?=ACCOUNT_SETTINGS_ROUTE.'/'.DELETE_ROUTE.'/'.CONFIRM_ROUTE?>">
    			<i id="ico-btn" class="fas fa-trash-alt"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Delete Account</span>
			</a>
	    	<a class="btn btn-warning px-3 py-1 mx-2 my-2" href="/<?=ACCOUNT_SETTINGS_ROUTE.'/'.PASS_UPDATE_ROUTE?>"><i class="fas fa-key"></i> Change Password</a>
	    	<button id="btn-update" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i id="ico-btn" class="fas fa-check"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Update</span>
	    	</button>
    	</div>
<!--     	<input id="< ?=DELETE_TOKEN?>" type="hidden" name="< ?=CSRF_DELETE_ACCOUNT?>" value="< ?=${DELETE_TOKEN}?>"> -->
    	<input id="<?=UPDATE_TOKEN?>" type="hidden" name="<?=CSRF_UPDATE_ACCOUNT?>" value="<?=${UPDATE_TOKEN}?>">
    	<input id="<?=DELETE_NEW_EMAIL_TOKEN?>" type="hidden" name="<?=CSRF_DELETE_NEW_EMAIL?>" value="<?=${DELETE_NEW_EMAIL_TOKEN}?>">
    	<input id="<?=RESEND_ENABLER_EMAIL_TOKEN?>" type="hidden" name="<?=CSRF_RESEND_ENABLER_EMAIL?>" value="<?=${RESEND_ENABLER_EMAIL_TOKEN}?>">
    </form>
</div>
