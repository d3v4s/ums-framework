<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">User: <?=${USER}->{USERNAME}?></th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle">ID</td>
        			<td class="align-middle"><?=${USER}->{USER_ID}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Full name</td>
        			<td class="align-middle"><?=${USER}->{NAME}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Username</td>
        			<td class="align-middle"><?=${USER}->{USERNAME}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Email</td>
        			<td class="align-middle">
        				<a href="<?=${SEND_EMAIL_LINK}.${USER}->{EMAIL}?>">
	        				<?=${USER}->{EMAIL}?>
        				</a>
        			</td>
    			</tr>
    			<?php if (${VIEW_ROLE}): ?>
        			<tr>
            			<td class="text-primary align-middle">Role</td>
            			<td class="align-middle"><?=${USER}->{ROLE}?></td>
        			</tr>
    			<?php endif; ?>
    			<tr>
    				<td class="text-primary align-middle">Registration date</td>
    				<td class="align-middle"><?=${USER}->{REGISTRATION_DATETIME}?></td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">Lock expiration date</td>
    				<td class="align-middle <?=${IS_LOCK} ? 'text-danger' : ''?>"><?=(${USER}->{EXPIRE_LOCK} ?? 'NULL').'<br>'.${MESSAGE_LOCK_ACC}?></td>
    			</tr>
    			<tr>
    				<td colspan="2" class="align-middle <?=${USER}->{ENABLED} ? 'text-success' : 'text-danger'?>"><?=${MESSAGE_ENABLE_ACC}?></td>
    			</tr>
    			<tr>
    				<td colspan="2" class="align-middle" <?=${USER}->{ENABLED} ? '' : ''?>>
    					<a
    						class="btn btn-primary m-2"
    						href="/ums/search/advance/<?=PENDING_USER_ID.'/'.DESC.'/1/'.DEFAULT_ROWS_FOR_PAGE.'?'.TABLE.'='.PENDING_EMAILS_TABLE.'&'.USER_ID_FRGN.'='.${USER}->{USER_ID} ?>"
						>
							Pending Emails
						</a>
						<a
    						class="btn btn-primary m-2"
    						href="/ums/search/advance/<?=SESSION_ID.'/'.DESC.'/1/'.DEFAULT_ROWS_FOR_PAGE.'?'.TABLE.'='.SESSIONS_TABLE.'&'.USER_ID_FRGN.'='.${USER}->{USER_ID} ?>"
						>
							Sessions
						</a>
						<a
    						class="btn btn-primary m-2"
    						href="/ums/search/advance/<?=PASSWORD_RESET_REQ_ID.'/'.DESC.'/1/'.DEFAULT_ROWS_FOR_PAGE.'?'.TABLE.'='.PASSWORD_RESET_REQ_TABLE.'&'.USER_ID_FRGN.'='.${USER}->{USER_ID} ?>"
						>
							Password Reset Requests 
						</a>
    				</td>
    			</tr>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
    	    <?php if (${CAN_UPDATE_USER}): ?>
    	    	<a class="btn btn-warning mx-3 my-1" href="/ums/table/action/<?=USERS_TABLE.'/update/'.${USER}->{USER_ID}?>"><i class="fa fa-pen fa-xs"></i> Update</a>
    	    <?php endif; ?>
    	    <?php if (${CAN_DELETE_USER}): ?>
    	    	<form id="delete-user-form" action="/ums/table/action/<?=USERS_TABLE?>/delete" method="post">
    	    		<button id="btn-delete-user" class="btn btn-danger mx-3 my-1" type="submit">
    	    			<i class="ico-btn fa fa-trash-alt fa-xs"></i>
	    				<span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  						<span class="text-btn">Delete</span>
	    			</button>
    		    	<input id="<?=TOKEN?>" name="<?=CSRF_DELETE_USER?>" value="<?=${TOKEN}?>" type="hidden">
    		    	<input name="<?=USER_ID?>" value="<?=${USER}->{USER_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
	    	<a class="btn btn-primary mx-3 my-1" href="/ums/table/get/<?=USER_LOCK_TABLE.'/'.${USER}->{USER_ID}?>"><i class="fas fa-user-lock"></i> View Locks</a>
	    	<?php if (${IS_LOCK} && ${CAN_UNLOCK_USER}): ?>
	    		<form id="lock-user-reset-form" action="/ums/table/action/<?=USER_LOCK_TABLE?>/reset" method="post">
    	    		<button id="btn-lock-user-reset" class="btn btn-danger mx-3 my-1" type="submit">
    	    			<i class="ico-btn fas fa-unlock fa-xs"></i>
	    				<span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  						<span class="text-btn">Unlock</span>
	    			</button>
    		    	<input id="<?=LOCKS_USER_RESET_TOKEN?>" name="<?=CSRF_LOCK_USER_RESET?>" value="<?=${LOCKS_USER_RESET_TOKEN}?>" type="hidden">
    		    	<input name="<?=USER_ID?>" value="<?=${USER}->{USER_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
	    	<?php endif; ?>
    	</div>
    </div>
</div>