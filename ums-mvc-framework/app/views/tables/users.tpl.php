<h2 class="text-center">USERS LIST</h2>
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
                <a href="/<?=UMS_TABLES_ROUTE.'/'.USERS_TABLE?>" class="btn btn-outline-warning m-2 my-sm-0">Reset</a>
    		</form>
    	</nav>
    	<?php require_once ROWS_FOR_PAGE_TEMPLATE; ?>
    </header>
    <div class="table-responsive col-md-10 mx-auto">
        <table class="table table-striped" id="users-table">
        	<thead>
        		<tr>
        			<th colspan="9" class="text-center">
        				<span>TOTAL USERS <?=${TOT_USERS}?> - Page <?=${PAGE}?>/<?=${MAX_PAGES}?></span>
    				</th>
    			</tr>
        		<tr>
        			<th class="w-5">
        				<a href="<?=${LINK_HEAD_ID}?>">#</a>
        				<i class="<?=${CLASS_HEAD_ID}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD_USERNAME}?>">USERNAME</a>
        				<i class="<?=${CLASS_HEAD_USERNAME}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD_NAME}?>">NAME</a>
        				<i class="<?=${CLASS_HEAD_NAME}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD_EMAIL}?>">EMAIL</a>
        				<i class="<?=${CLASS_HEAD_EMAIL}?>"></i>
        			</th>
					<th>
        				<a href="<?=${LINK_HEAD_ENABLED}?>">STATE</a>
        				<i class="<?=${CLASS_HEAD_ENABLED}?>"></i>
        			</th>
        			<?php if (${VIEW_ROLE}): ?>
            			<th>
            				<a href="<?=${LINK_HEAD_ROLE}?>">ROLE</a>
            				<i class="<?=${CLASS_HEAD_ROLE}?>"></i>
            			</th>
        			<?php endif; ?>
        			<th>
        			</th>
        		</tr>
        	</thead>
        	<tbody>
        	<?php
        	if (!empty(${USERS})):
        	   foreach (${USERS} as $user):
            	    ?>
            	        <tr>
            	        	<td class="align-middle"><?=$user->{USER_ID}?></td>
            	        	<td class="align-middle">
            	        		<a href="/<?=USER_ROUTE.'/'.$user->{USER_ID}?>">
    		        	        	<?= $user->{USERNAME}?>
            	        		</a>
            	        	</td>
            	        	<td class="align-middle"><?=$user->{NAME}?></td>
            	        	<td class="align-middle">
            	        		<a href="/<?=NEW_EMAIL_ROUTE.'?to='.$user->{EMAIL}?>">
            	        			<?=$user->{EMAIL}?>
            	        		</a>
            	        	</td>
            	        	<td class="align-middle"><?=$user->{ENABLED} ? 'enabled' : 'disabled'?></td>
            	        	<?php if (${VIEW_ROLE}): ?>
            	        		<td class="align-middle"><?= $user->{ROLE}?></td>
            	        	<?php endif;?>
            	        	<td class="align-middle">
            	        		<div class="row">
            	        			<div class="col-lg-6 col-md-6 col-sm-7 col-xs-8 my-1">
            	        				<a class="btn btn-warning text-cente" href="/<?=USER_ROUTE.'/'.$user->{USER_ID}.'/'.UPDATE_ROUTE?>">Update</a>
            	        			</div>
            	        		</div>
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
    <?php if (FAKE_USERS): ?>
		<div class="container text-left p-5 ml-5">
    		<a class="btn btn-primary ml-5" href="/<?=FAKE_USERS_ROUTE?>">Add Fake Users</a>
    	</div>
    <?php endif; ?>
</div>