<h2 class="text-center">PENDING USERS LIST</h2>
<div class="container-fluid">
    <header class="p-3">
    	<nav class="navbar navbar-expand-md justify-content-center">
    		<form id="search-form" class="form-inline" action="<?=${SEARCH_ACTION}?>" method="get">
                <input 	
                		class="form-control mr-sm-2"
                		name="<?=SEARCH?>" id="search"
                		type="text"
                		placeholder="Search"
                		value="<?=${SEARCH}?>"
                		aria-label="Search"
                		autofocus="autofocus"
        		>

                <button class="btn btn-outline-success m-2 my-sm-0" type="submit">Search</button>
                <a href="/<?=UMS_TABLES_ROUTE.'/'.PENDING_USERS_TABLE?>" class="btn btn-outline-warning m-2 my-sm-0">Reset</a>
    		</form>
    	</nav>
		<?php require_once ROWS_FOR_PAGE_TEMPLATE; ?>
    </header>
    <div class="table-responsive col-md-10 mx-auto">
        <table class="table table-striped" id="pending-users-table">
        	<thead>
        		<tr>
        			<th colspan="9" class="text-center">
        				<span>TOTAL USERS <?=${TOT_USERS}?> - Page <?=${PAGE}?>/<?=${MAX_PAGES}?></span>
    				</th>
    			</tr>
        		<tr>
        			<th class="w-5">
        				<a href="<?=${LINK_HEAD.PENDING_USER_ID}?>">#</a>
        				<i class="<?=${CLASS_HEAD.PENDING_USER_ID}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.USER_ID_FRGN}?>">USER ID</a>
        				<i class="<?=${CLASS_HEAD.USER_ID_FRGN}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.USERNAME}?>">USERNAME</a>
        				<i class="<?=${CLASS_HEAD.USERNAME}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.NAME}?>">NAME</a>
        				<i class="<?=${CLASS_HEAD.NAME}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.EMAIL}?>">EMAIL</a>
        				<i class="<?=${CLASS_HEAD.EMAIL}?>"></i>
        			</th>
					<th>
        				<a href="<?=${LINK_HEAD.ENABLER_TOKEN}?>">TOKEN</a>
        				<i class="<?=${CLASS_HEAD.ENABLER_TOKEN}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.REGISTRATION_DATETIME}?>">REGISTRATION DATETIME</a>
        				<i class="<?=${CLASS_HEAD.REGISTRATION_DATETIME}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.EXPIRE_DATETIME}?>">EXPIRE DATETIME</a>
        				<i class="<?=${CLASS_HEAD.EXPIRE_DATETIME}?>"></i>
        			</th>
        			<?php if (${VIEW_ROLE}): ?>
            			<th>
            				<a href="<?=${LINK_HEAD.ROLE}?>">ROLE</a>
            				<i class="<?=${CLASS_HEAD.ROLE}?>"></i>
            			</th>
        			<?php endif; ?>
        		</tr>
        	</thead>
        	<tbody>
        	<?php
        	if (!empty(${USERS})):
        	   foreach (${USERS} as $user):
            	    ?>
            	        <tr>
            	        	<td class="align-middle">
            	        		<a href="/<?=UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.PENDING_USERS_TABLE.'/'.$user->{PENDING_USER_ID}?>">
	            	        		<?=$user->{PENDING_USER_ID}?>
            	        		</a>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if (isset($user->{USER_ID_FRGN})): ?>
            	        			<a href="/<?=UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.$user->{USER_ID_FRGN}?>">
                	        			<?=$user->{USER_ID_FRGN}?>
            	        			</a>                	        			
            	        		<?php else: ?>
            	        			<span class="text-danger">NULL</span>
            	        		<?php endif; ?>
    	        			</td>
            	        	<td class="align-middle"><?=$user->{USERNAME}?></td>
            	        	<td class="align-middle"><?=$user->{NAME}?></td>
            	        	<td class="align-middle">
            	        		<a href="<?=${SEND_EMAIL_LINK}.$user->{EMAIL}?>">
            	        			<?=$user->{EMAIL}?>
            	        		</a>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if (isset($user->{ENABLER_TOKEN})): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
        	        		</td>
        	        		<td class="align-middle"><?=$user->{REGISTRATION_DATETIME}?></td>
        	        		<td class="align-middle"><?=$user->{EXPIRE_DATETIME}?></td>
            	        	<?php if (${VIEW_ROLE}): ?>
            	        		<td class="align-middle"><?= $user->{ROLE}?></td>
            	        	<?php endif;?>
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