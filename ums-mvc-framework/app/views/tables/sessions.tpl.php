<h2 class="text-center">SESSIONS LIST</h2>
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
                <a href="/<?=UMS_TABLES_ROUTE.'/'.SESSIONS_TABLE?>" class="btn btn-outline-warning m-2 my-sm-0">Reset</a>
    		</form>
    	</nav>
		<?php require_once ROWS_FOR_PAGE_TEMPLATE; ?>
    </header>
    <div class="table-responsive col-md-10 mx-auto">
        <table class="table table-striped" id="sessions-table">
        	<thead>
        		<tr>
        			<th colspan="9" class="text-center">
        				<span>TOTAL SESSIONS <?=${TOT_SESSIONS}?> - Page <?=${PAGE}?>/<?=${MAX_PAGES}?></span>
    				</th>
    			</tr>
        		<tr>
        			<th class="w-5">
        				<a href="<?=${LINK_HEAD.SESSION_ID}?>">#</a>
        				<i class="<?=${CLASS_HEAD.SESSION_ID}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.USERNAME}?>">USERNAME</a>
        				<i class="<?=${CLASS_HEAD.USERNAME}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.IP_ADDRESS}?>">IP ADDRESS</a>
        				<i class="<?=${CLASS_HEAD.IP_ADDRESS}?>"></i>
        			</th>
					<th>
        				<a href="<?=${LINK_HEAD.SESSION_TOKEN}?>">TOKEN</a>
        				<i class="<?=${CLASS_HEAD.SESSION_TOKEN}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.EXPIRE_DATETIME}?>">EXPIRE DATETIME</a>
        				<i class="<?=${CLASS_HEAD.EXPIRE_DATETIME}?>"></i>
        			</th>
        		</tr>
        	</thead>
        	<tbody>
        	<?php
        	if (!empty(${SESSIONS})):
        	   foreach (${SESSIONS} as $session):
            	    ?>
            	        <tr>
            	        	<td class="align-middle">
            	        		<a href="/<?=SESSION_ROUTE.'/'.$session->{SESSION_ID}?>">
	            	        		<?=$session->{SESSION_ID}?>
            	        		</a>
        	        		</td>
            	        	<td class="align-middle">
            	        		<?php if (isset($session->{USERNAME})): ?>
                	        		<a href="/<?=USER_ROUTE.'/'.$session->{USER_ID}?>">
        		        	        	<?= $session->{USERNAME}?>
                	        		</a>
            	        		<?php else: ?>
            	        			<a href="/<?=DELETED_USER_ROUTE.'/'.$session->{USER_ID_FRGN}?>" class="text-danger">
        		        	        	DELETE: <?= $session->{USER_ID_FRGN}?>
                	        		</a>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle"><?=$session->{IP_ADDRESS}?></td>
            	        	<td class="align-middle">
            	        		<?php if (isset($session->{SESSION_TOKEN})): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle"><?=$session->{EXPIRE_DATETIME}?></td>
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