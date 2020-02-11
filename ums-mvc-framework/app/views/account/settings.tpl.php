<div class="container col-md-7 text-center p-3">
    <form id="user-update-form" action="/user/settings/update" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=NAME?>"><?=${LANG}[NAME]?></label>
    		<input id="<?=NAME?>" name="<?=NAME?>" value="<?=${USER}->{NAME}?>" placeholder="<?=${LANG}[NAME]?>" class="form-control validate-name evidence-error send-ajax" type="text" required="required" autofocus="autofocus">
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
    			<label for="new-email"><?=${LANG}[NEW_EMAIL]?></label>
    			<input id="new-email" value="<?=${WAIT_EMAIL_CONFIRM}->{NEW_EMAIL}?>" class="form-control" type="email" readonly="readonly">
    			<div class="row ">
    				<div class="col-6 text-left">
            			<button id="btn-resend-email" value="/user/settings/email/resend" class="btn btn-link link-primary p-0" type="button"><?=${LANG}[RESEND_EMAIL]?></button>
    				</div>
    				<div class="col-6 text-right">
            			<button id="btn-delete-new-email" value="/user/settings/email/delete" class="btn btn-link link-danger p-0" type="button"><?=${LANG}[DELETE]?></button>
    				</div>
    			</div>
    		</div>
    	<?php endif; ?>
    	<?php if (!isSimpleUser(${USER}->{ROLE_ID_FRGN})): ?>
        	<div class="form-group text-left my-3">
        		<label for="role"><?=${LANG}[ROLE]?></label>
        		<select id="role" disabled="disabled">
        			<?php foreach (${ROLES} as $role): ?>
    	    			<option <?=$role[ROLE_ID] === ${USER}->{ROLE_ID_FRGN} ? 'selected="selected"' : ''?> value="<?=$role[ROLE_ID]?>"><?=ucfirst($role[ROLE])?></option>
        			<?php endforeach; ?>
        		</select>
        	</div>
    	<?php endif; ?>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
    		<a id="btn-delete" class="btn btn-danger px-3 py-1 mx-2 my-2" href="/user/settings/delete/confirm">
    			<i class="fas fa-trash-alt ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn"><?=${LANG}[DELETE]?> Account</span>
			</a>
			<a class="btn btn-warning px-3 py-1 mx-2 my-2" href="/user/settings/sessions"><i class="far fa-user-circle"></i> <?=${LANG}[SESSIONS]?></a>
	    	<a class="btn btn-warning px-3 py-1 mx-2 my-2" href="/user/settings/password_update"><i class="fas fa-key"></i> <?=${LANG}[CHANGE_PASS]?></a>
	    	<button id="btn-update" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i class="fas fa-check ico-btn"></i>
	    		<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  				<span class="text-btn"><?=${LANG}[UPDATE]?></span>
	    	</button>
    	</div>
    	<input id="<?=UPDATE_TOKEN?>" type="hidden" name="<?=CSRF_UPDATE_ACCOUNT?>" value="<?=${UPDATE_TOKEN}?>">
    	<input id="<?=DELETE_NEW_EMAIL_TOKEN?>" type="hidden" name="<?=CSRF_DELETE_NEW_EMAIL?>" value="<?=${DELETE_NEW_EMAIL_TOKEN}?>">
    	<input id="<?=RESEND_ENABLER_EMAIL_TOKEN?>" type="hidden" name="<?=CSRF_RESEND_ENABLER_EMAIL?>" value="<?=${RESEND_ENABLER_EMAIL_TOKEN}?>">
    </form>
</div>
