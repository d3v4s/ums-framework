<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">Password Reset Request ID: <?=${REQUEST}->{PASSWORD_RESET_REQ_ID}?></th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle">User ID</td>
        			<td class="align-middle"><?=${REQUEST}->{USER_ID_FRGN}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Username</td>
        			<td class="align-middle">
        				<?php if (isset(${REQUEST}->{USERNAME})): ?>
        					<a href="/ums/table/get/<?=USERS_TABLE.'/'.${REQUEST}->{USER_ID_FRGN}?>"><?=${REQUEST}->{USERNAME}?></a>
    					<?php else: ?>
    						<a href="/ums/table/get/<?=DELETED_USER_TABLE.'/'.${REQUEST}->{USER_ID_FRGN}?>" class="text-danger">DELETE: <?=${REQUEST}->{USER_ID_FRGN}?></a>
    					<?php endif; ?>
					</td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Email</td>
        			<td class="align-middle">
        				<a href="<?=${SEND_EMAIL_LINK}.${REQUEST}->{EMAIL}?>">
	        				<?=${REQUEST}->{EMAIL}?>
        				</a>
        			</td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">IP address</td>
    				<td class="align-middle"><?=${REQUEST}->{IP_ADDRESS}?></td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">Expire datetime</td>
    				<td class="align-middle">
    					<?=${REQUEST}->{EXPIRE_DATETIME}?>
    					<br>
        				<?php if (${IS_EXPIRED}): ?>
        					<span class="text-danger"><?=${MESSAGE_EXPIRE}?></span>
        				<?php else: ?>
        					<span class="text-success"><?=${MESSAGE_EXPIRE}?></span>
        				<?php endif;?>
    				</td>
    			</tr>
    			<tr>
    				<td class="align-middle" colspan="2">
        				<?php if (isset(${REQUEST}->{PASSWORD_RESET_TOKEN})): ?>
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
    	    	<form id="invalidate-form" action="/ums/table/action/<?=PASSWORD_RESET_REQ_TABLE?>/invalidate" method="post">
    	    		<button id="btn-invalidate" class="btn btn-danger mx-3 my-1" type="submit">
    	    			<i class="fa fa-user-times ico-btn"></i>
	    				<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  						<span class="text-btn">Invalidate Request</span>
	    			</button>
    		    	<input id="<?=INVALIDATE_TOKEN?>" name="<?=CSRF_INVALIDATE_PASS_RES_REQ?>" value="<?=${INVALIDATE_TOKEN}?>" type="hidden">
    		    	<input name="<?=PASSWORD_RESET_REQ_ID?>" value="<?=${REQUEST}->{PASSWORD_RESET_REQ_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
    	    <?php if (${CAN_SEND_EMAIL} && ${IS_VALID}): ?>
    	    	<form id="resend-email-form" action="/ums/table/action/<?=PASSWORD_RESET_REQ_TABL?>resend" method="post">
    	    		<button id="btn-resend-email" class="btn btn-primary mx-3 my-1" type="submit">
    	    			<i class="fa fa-paper-plane ico-btn"></i>
	    				<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
  						<span class="text-btn">Resend Password Reset Email</span>
	    			</button>
    		    	<input id="<?=RESEND_ENABLER_EMAIL_TOKEN?>" name="<?=CSRF_RESEND_PASS_RES_REQ?>" value="<?=${RESEND_ENABLER_EMAIL_TOKEN}?>" type="hidden">
    		    	<input name="<?=PASSWORD_RESET_REQ_ID?>" value="<?=${REQUEST}->{PASSWORD_RESET_REQ_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
    	</div>
    </div>
</div>