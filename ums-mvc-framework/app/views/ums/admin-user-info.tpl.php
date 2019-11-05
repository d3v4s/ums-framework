<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">User: <?=$user->username?></th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle">ID</td>
        			<td class="align-middle"><?=$user->id?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Full name</td>
        			<td class="align-middle"><?=$user->name?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Username</td>
        			<td class="align-middle"><?=$user->username?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Email</td>
        			<td class="align-middle"><?=$user->email?></td>
    			</tr>
    			<?php if (isUserAdmin()): ?>
        			<tr>
            			<td class="text-primary align-middle">Role</td>
            			<td class="align-middle"><?=$user->roletype?></td>
        			</tr>
    			<?php endif; ?>
    			<tr>
    				<td class="text-primary align-middle">Registration date</td>
    				<td class="align-middle"><?=$user->registration_day?></td>
    			</tr>
    			<?php if ($viewNewEmail): ?>
        			<tr>
        				<td class="text-primary align-middle">
        					New email<?=$_messageNewEmail?>
        					<br>
        					<form id="delete-new-email-form" action="/ums/user/delete/new/email" method="post">
        						<button id="btn-delete-new-email" class="btn btn-link link-danger p-0" type="submit">Delete new email</button>
        						<input id="_xf-dnm" name="_xf-dnm" value="<?=$tokenDeleteNewEmail?>" class="send-ajax" type="hidden">
        						<input name="id" value="<?=$user->id?>" class="send-ajax" type="hidden">
        					</form>
    					</td>
        				<td class="align-middle"><?=$user->new_email?></td>
        			</tr>
    			<?php endif; ?>
    			<tr>
    				<td class="text-primary align-middle">
    					N. wrong password<?=$_messageWrongPassword?>
    					<br>
    					<span class="text-secondary">Max wrong passwords: <?=$maxWrongPass?></span>
					</td>
    				<td class="align-middle"><?=$user->n_wrong_password?></td>
				</tr>
    			<?php if ($viewDateTimeResetWrongPass): ?>
    				<tr>
        				<td class="text-primary align-middle">
        					Date time reset wrong passwords
        					<br>
        					<form id="reset-wrong-pass-form" action="/ums/user/update/reset/wrong/pass" method="post">
        						<button id="btn-reset-wrong-pass" class="btn btn-link link-danger p-0" type="submit">Reset wrong passwords</button>
        						<input id="_xf-rwp" name="_xf-rwp" value="<?=$tokenResetWrongPass?>" class="send-ajax" type="hidden">
        						<input name="id" value="<?=$user->id?>" class="send-ajax" type="hidden">
        					</form>
    					</td>
        				<td class="align-middle"><?=$user->datetime_reset_wrong_password?></td>
    				</tr>
    			<?php endif; ?>
    			<tr>
    				<td class="text-primary align-middle">N. locks<?=$_messageLockUser?></td>
    				<td class="align-middle"><?=$user->n_locks?></td>
				</tr>
				<?php if ($viewDateTimeUnlockUser): ?>
    				<tr>
        				<td class="text-primary align-middle">
        					Date time unlock user<?=$_messageUnlockUser?>
        					<br>
        					<form id="reset-lock-user-form" action="/ums/user/update/reset/lock" method="post">
        						<button id="btn-reset-lock" class="btn btn-link link-danger p-0" type="submit">Reset lock user</button>
        						<input id="_xf-rlu" name="_xf-rlu" value="<?=$tokenResetLockUser?>" class="send-ajax" type="hidden">
        						<input name="id" value="<?=$user->id?>" class="send-ajax" type="hidden">
        					</form>
    					</td>
        				<td class="align-middle"><?=$user->datetime_unlock_user?></td>
    				</tr>
    			<?php endif; ?>
    			<tr>
    				<td colspan="2" class="align-middle <?=$classEnabledAccount?>"><?=$_messageEnable?></td>
    			</tr>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
    	    <?php if (userCanUpdate()): ?>
    	    	<a class="btn btn-warning mx-3 my-1" href="/ums/user/<?=$user->id?>/update"><i class="fa fa-pen fa-xs"></i> Update</a>
    	    <?php endif; ?>
    	    <?php if (userCanDelete()): ?>
    	    	<form id="delete-user-form" action="/ums/user/<?=$user->id?>/delete/confirm" method="post">
    	    		<a id="btn-delete-user" href="/ums/user/<?=$user->id?>/delete" class="btn btn-danger mx-3 my-1">
    	    			<i id="ico-btn" class="fa fa-trash-alt fa-xs"></i>
	    				<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  						<span id="text-btn">Delete</span>
	    			</a>
    		    	<input id="_xf-du" name="_xf-du" value="<?=$tokenDeleteUser?>" class="send-ajax" type="hidden">
    		    	<input name="id" value="<?=$user->id?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
    	    <?php if (userCanChangePasswords()): ?>
    	    	<a class="btn btn-primary mx-3 my-1" href="/ums/user/<?=$user->id?>/update/pass"><i class="fas fa-key"></i> Change Password</a>
	    	<?php endif; ?>
    	</div>
    </div>
</div>