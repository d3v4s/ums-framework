<div class="container col-md-7 text-center">
    <h1>User: <?=${USER}->{USERNAME}?></h1>
    <form id="user-update-form" action="/ums/table/action/<?=USERS_TABLE?>/update" method="post">
    	<div class="form-group text-md-left">
    		<label for="<?=NAME?>">Full Name</label>
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
    	<?php if (${VIEW_ROLE}): ?>
        	<div class="form-group text-left my-3">
        		<label for="<?=ROLE_ID_FRGN?>">Role</label>
        		<select id="<?=ROLE_ID_FRGN?>" name="<?=ROLE_ID_FRGN?>" class="send-ajax evidence-error">
        			<?php foreach (${ROLES} as $role): ?>
	    				<option <?=$role[ROLE_ID] === ${USER}->{ROLE_ID_FRGN} ? 'selected="selected"' : ''?> value="<?=$role[ROLE_ID]?>"><?=ucfirst($role[ROLE])?></option>
        			<?php endforeach; ?>
        		</select>
        	</div>
        	<div class="custom-control custom-switch text-left">
				<input id="<?=ENABLED?>" name="<?=ENABLED?>" type="checkbox" class="custom-control-input send-ajax" value="true" <?=${NO_ESCAPE.ENABLED}?> >
				<label class="custom-control-label" for="<?=ENABLED?>">Enabled</label>
            </div>
    	<?php endif; ?>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
    		<a class="btn btn-primary px-3 py-1 mx-2 my-2" href="/ums/table/get/<?=USERS_TABLE.'/'.${USER}->{USER_ID}?>"><i class="fas fa-info"></i> Info</a>
    		<?php if (${CAN_CHANGE_PASSWORD}): ?>
    			<a class="btn btn-warning px-3 py-1 mx-2 my-2" href="/ums/table/action/<?=USERS_TABLE.'/password_update/'.${USER}->{USER_ID}?>"><i class="fas fa-key"></i> Change Password</a>
			<?php endif; ?>
	    	<button id="btn-update" class="btn btn-success px-3 py-1 mx-2 my-2" type="submit">
	    		<i class="ico-btn fas fa-check"></i>
	    		<span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span class="text-btn">Update</span>
	    	</button>
    	</div>
    	<input id="<?=TOKEN?>" type="hidden" name="<?=CSRF_UPDATE_USER?>" value="<?=${TOKEN}?>">
    	<input id="<?=USER_ID?>" type="hidden" name="<?=USER_ID?>" value="<?=${USER}->{USER_ID}?>" class="send-ajax">
    </form>
</div>