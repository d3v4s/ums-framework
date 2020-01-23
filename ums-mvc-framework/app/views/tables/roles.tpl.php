<h2 class="text-center">ROLES LIST</h2>
<div class="container-fluid">
    <header class="p-3">
		<?php require_once ROWS_FOR_PAGE_TEMPLATE; ?>
    </header>
    <div class="table-responsive col-md-12 mx-auto">
        <table class="table table-striped" id="roles-table">
        	<thead>
        		<tr>
        			<th colspan="15" class="text-center">
        				<span>TOTAL ROLES <?=${TOT_ROLES}?> - Page <?=${PAGE}?>/<?=${MAX_PAGES}?></span>
    				</th>
    			</tr>
        		<tr>
        			<th class="w-5">
        				<a href="<?=${LINK_HEAD.ROLE_ID}?>">#</a>
        				<i class="<?=${CLASS_HEAD.ROLE_ID}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.ROLE}?>">ROLE NAME</a>
        				<i class="<?=${CLASS_HEAD.ROLE}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_CREATE_USER}?>">CREATE USER</a>
        				<i class="<?=${CLASS_HEAD.CAN_CREATE_USER}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_UPDATE_USER}?>">UPDATE USER</a>
        				<i class="<?=${CLASS_HEAD.CAN_UPDATE_USER}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_DELETE_USER}?>">DELETE USER</a>
        				<i class="<?=${CLASS_HEAD.CAN_DELETE_USER}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_UNLOCK_USER}?>">UNLOCK USER</a>
        				<i class="<?=${CLASS_HEAD.CAN_UNLOCK_USER}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_RESTORE_USER}?>">RESTORE USER</a>
        				<i class="<?=${CLASS_HEAD.CAN_RESTORE_USER}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_CHANGE_PASSWORD}?>">CHANGE PASSWORD</a>
        				<i class="<?=${CLASS_HEAD.CAN_CHANGE_PASSWORD}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_REMOVE_SESSION}?>">REMOVE SESSION</a>
        				<i class="<?=${CLASS_HEAD.CAN_REMOVE_SESSION}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_REMOVE_ENABLER_TOKEN}?>">REMOVE ENABLER TOKEN</a>
        				<i class="<?=${CLASS_HEAD.CAN_REMOVE_ENABLER_TOKEN}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_GENERATE_RSA}?>">GENERATE RSA</a>
        				<i class="<?=${CLASS_HEAD.CAN_GENERATE_RSA}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_GENERATE_SITEMAP}?>">GENERATE SITEMAP</a>
        				<i class="<?=${CLASS_HEAD.CAN_GENERATE_SITEMAP}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_CHANGE_SETTINGS}?>">CHANGE SETTINGS</a>
        				<i class="<?=${CLASS_HEAD.CAN_CHANGE_SETTINGS}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_SEND_EMAIL}?>">SEND EMAIL</a>
        				<i class="<?=${CLASS_HEAD.CAN_SEND_EMAIL}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.CAN_VIEW_TABLES}?>">VIEW TABLES</a>
        				<i class="<?=${CLASS_HEAD.CAN_VIEW_TABLES}?>"></i>
        			</th>
        		</tr>
        	</thead>
        	<tbody>
        	<?php
        	if (!empty(${ROLES})):
        	   foreach (${ROLES} as $role):
            	    ?>
            	        <tr>
            	        	<td class="align-middle"><?=$role->{ROLE_ID}?></td>
            	        	<td class="align-middle"><?=$role->{ROLE}?></td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_CREATE_USER}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_UPDATE_USER}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_DELETE_USER}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_UNLOCK_USER}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_RESTORE_USER}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_CHANGE_PASSWORD}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_REMOVE_SESSION}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_REMOVE_ENABLER_TOKEN}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_GENERATE_RSA}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_GENERATE_SITEMAP}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_CHANGE_SETTINGS}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_SEND_EMAIL}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if ($role->{CAN_VIEW_TABLES}): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        </tr>
            	    <?php endforeach;
                else: ?>
            	    <tr><td colspan="9" class="text-center"><h2>ERR 404!!<br>No records found</h2></td></tr>
            <?php endif; ?>
        	</tbody>
        </table>
    </div>
    <?php require PAGINATION_TEMPLATE;?>
</div>