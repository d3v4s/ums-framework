<div class="container-fluid">
    <div class="table-responsive col-md-6 mx-auto">
        <table class="table table-striped" id="users-table">
        	<tbody>
        		<tr>
        			<th class="align-middle" colspan="2">Pending Email ID: <?=${PENDING}->{PENDING_EMAIL_ID}?></th>
        		</tr>
        		<tr>
        			<td class="text-primary align-middle">User ID</td>
        			<td class="align-middle"><?=${PENDING}->{USER_ID_FRGN}?></td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Username</td>
        			<td class="align-middle">
        				<?php if (isset(${PENDING}->{USERNAME})): ?>
        					<a href="/<?=UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.${PENDING}->{USER_ID_FRGN}?>"><?=${PENDING}->{USERNAME}?></a>
    					<?php else: ?>
    						<a href="/<?=UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.DELETED_USER_TABLE.'/'.${PENDING}->{USER_ID_FRGN}?>" class="text-danger">DELETE: <?=${PENDING}->{USER_ID_FRGN}?></a>
    					<?php endif; ?>
					</td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">Email</td>
        			<td class="align-middle">
        				<a href="<?=${SEND_EMAIL_LINK}.${PENDING}->{EMAIL}?>">
	        				<?=${PENDING}->{EMAIL}?>
        				</a>
        			</td>
    			</tr>
    			<tr>
        			<td class="text-primary align-middle">New email</td>
        			<td class="align-middle">
        				<a href="<?=${SEND_EMAIL_LINK}.${PENDING}->{NEW_EMAIL}?>">
	        				<?=${PENDING}->{NEW_EMAIL}?>
        				</a>
        			</td>
    			</tr>
    			<tr>
    				<td class="text-primary align-middle">Expire datetime</td>
    				<td class="align-middle">
    					<?=${PENDING}->{EXPIRE_DATETIME}?>
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
        				<?php if (isset(${PENDING}->{ENABLER_TOKEN})): ?>
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
    		<?php if (${CAN_SEND_EMAIL} && !${IS_EXPIRED}): ?>
    	    	<form id="resend-email-form" action="/<?=PENDING_EMAIL_ROUTE.'/'.RESEND_ROUTE?>" method="post">
    	    		<button id="btn-resend-email" class="btn btn-primary mx-3 my-1" type="submit">
    	    			<i id="ico-btn" class="fa fa-paper-plane"></i>
	    				<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  						<span id="text-btn">Resend Enabler Email</span>
	    			</button>
    		    	<input id="<?=RESEND_ENABLER_EMAIL_TOKEN?>" name="<?=CSRF_RESEND_ENABLER_EMAIL?>" value="<?=${RESEND_ENABLER_EMAIL_TOKEN}?>" type="hidden">
    		    	<input name="<?=PENDING_EMAIL_ID?>" value="<?=${PENDING}->{PENDING_EMAIL_ID}?>" class="send-ajax" type="hidden">
    	    	</form>
    	    <?php endif; ?>
    	</div>
    </div>
</div>