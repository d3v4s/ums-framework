<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">Pending User ID: <?=${USER}->{PENDING_USER_ID}?></th>
        		</tr>
    			<tr>
        			<td class="text-primary align-middle">User ID</td>
        			<td class="align-middle">
        				<a href="/<?=UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.${USER}->{USER_ID_FRGN}?>">
        					<?=${USER}->{USER_ID_FRGN}?>
    					</a>
    				</td>
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
    				<td class="text-primary align-middle">Expiration date</td>
    				<td class="align-middle <?=${IS_EXPIRED} ? 'text-danger' : ''?>"><?=(${USER}->{EXPIRE_DATETIME} ?? 'NULL').'<br>'.${MESSAGE_EXPIRE}?></td>
    			</tr>
    			<tr>
    				<td class="align-middle" colspan="2">
        				<?php if (isset(${USER}->{ENABLER_TOKEN})): ?>
        					<span class="text-primary">TOKEN</span>
    					<?php else: ?>
    						<span class="text-danger">NO TOKEN</span>
						<?php endif; ?>
    				</td>
    			</tr>
        	</tbody>
        </table>
    </div>
    <div class="text-center container-fluid mx-auto my-3">
    	<div class="row justify-content-center">
    		<?php if (${CAN_REMOVE_ENABLER_TOKEN} && ${IS_VALID}): ?>
    	    	<form id="invalidate-form" action="/<?=UMS_TABLES_ROUTE.'/'.ACTION_ROUTE.'/'.PENDING_USERS_TABLE.'/'.INVALIDATE_ROUTE?>" method="post">
    	    		<button id="btn-invalidate" class="btn btn-danger mx-3 my-1" type="submit">
    	    			<i class="ico-btn fa fa-user-times"></i>
	    				<span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  						<span class="text-btn">Invalidate Pending User</span>
	    			</button>
    		    	<input id="<?=INVALIDATE_TOKEN?>" name="<?=CSRF_INVALIDATE_PENDING_USER?>" value="<?=${INVALIDATE_TOKEN}?>" type="hidden">
    		    	<input name="<?=PENDING_USER_ID?>" value="<?=${USER}->{PENDING_USER_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
    	    <?php if (${CAN_SEND_EMAIL} && ${IS_VALID}): ?>
    	    	<form id="resend-email-form" action="/<?=UMS_TABLES_ROUTE.'/'.ACTION_ROUTE.'/'.PENDING_USERS_TABLE.'/'.RESEND_ROUTE?>" method="post">
    	    		<button id="btn-resend-email" class="btn btn-primary mx-3 my-1" type="submit">
    	    			<i class="ico-btn fa fa-paper-plane"></i>
	    				<span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  						<span class="text-btn">Resend Enabler Email</span>
	    			</button>
    		    	<input id="<?=RESEND_ENABLER_EMAIL_TOKEN?>" name="<?=CSRF_RESEND_ENABLER_ACC?>" value="<?=${RESEND_ENABLER_EMAIL_TOKEN}?>" type="hidden">
    		    	<input name="<?=PENDING_USER_ID?>" value="<?=${USER}->{PENDING_USER_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
    	</div>
    </div>
</div>