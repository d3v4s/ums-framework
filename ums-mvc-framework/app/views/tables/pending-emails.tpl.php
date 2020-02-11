<h2 class="text-center">PENDING EMAILS LIST</h2>
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
                <a href="/ums/table/<?=PENDING_EMAILS_TABLE?>" class="btn btn-outline-warning m-2 my-sm-0">Reset</a>
    		</form>
    	</nav>
		<?php require_once ROWS_FOR_PAGE_TEMPLATE; ?>
    </header>
    <div class="table-responsive col-md-10 mx-auto">
        <table class="table table-striped" id="pending-emails-table">
        	<thead>
        		<tr>
        			<th colspan="5" class="text-center">
        				<span>TOTAL EMAILS <?=${TOT_PENDING_MAILS}?> - Page <?=${PAGE}?>/<?=${MAX_PAGES}?></span>
    				</th>
    			</tr>
        		<tr>
        			<th class="w-5">
        				<a href="<?=${LINK_HEAD.PENDING_EMAIL_ID}?>">#</a>
        				<i class="<?=${CLASS_HEAD.PENDING_EMAIL_ID}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.USERNAME}?>">USERNAME</a>
        				<i class="<?=${CLASS_HEAD.USERNAME}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.NEW_EMAIL}?>">NEW EMAIL</a>
        				<i class="<?=${CLASS_HEAD.NEW_EMAIL}?>"></i>
        			</th>
					<th>
        				<a href="<?=${LINK_HEAD.ENABLER_TOKEN}?>">TOKEN</a>
        				<i class="<?=${CLASS_HEAD.ENABLER_TOKEN}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.EXPIRE_DATETIME}?>">EXPIRE DATETIME</a>
        				<i class="<?=${CLASS_HEAD.EXPIRE_DATETIME}?>"></i>
        			</th>
        		</tr>
        	</thead>
        	<tbody>
        	<?php
        	if (!empty(${EMAILS})):
        	   foreach (${EMAILS} as $email):
            	    ?>
            	        <tr>
            	        	<td class="align-middle">
            	        		<a href="/ums/table/get/<?=PENDING_EMAILS_TABLE.'/'.$email->{PENDING_EMAIL_ID}?>">
            	        			<?=$email->{PENDING_EMAIL_ID}?>
            	        		</a>
        	        		</td>
            	        	<td class="align-middle">
            	        		<?php if (isset($email->{USERNAME})): ?>
                	        		<a href="/ums/table/get/<?=USERS_TABLE.'/'.$email->{USER_ID}?>">
        		        	        	<?=$email->{USERNAME}?>
                	        		</a>
            	        		<?php else: ?>
            	        			<a href="/ums/table/get/<?=DELETED_USER_TABLE.'/'.$email->{USER_ID_FRGN}?>" class="text-danger">
        		        	        	DELETE: <?= $email->{USER_ID_FRGN}?>
                	        		</a>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle">
            	        		<a href="<?=${SEND_EMAIL_LINK}.$email->{NEW_EMAIL}?>">
            	        			<?=$email->{NEW_EMAIL}?>
            	        		</a>
            	        	</td>
            	        	<td class="align-middle">
            	        		<?php if (isset($email->{ENABLER_TOKEN})): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle"><?=$email->{EXPIRE_DATETIME}?></td>
            	        </tr>
            	    <?php endforeach;
                else: ?>
            	    <tr><td colspan="5" class="text-center"><h2>ERR 404!!<br>No records found</h2></td></tr>
            <?php endif; ?>
        	</tbody>
        </table>
    </div>
    <?php require PAGINATION_TEMPLATE;?>
</div>