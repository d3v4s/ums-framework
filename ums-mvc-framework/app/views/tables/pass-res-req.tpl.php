<h2 class="text-center">PASSWORD RESET REQUESTS LIST</h2>
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
                <a href="/ums/table/<?=PASSWORD_RESET_REQ_TABLE?>" class="btn btn-outline-warning m-2 my-sm-0">Reset</a>
    		</form>
    	</nav>
		<?php require_once ROWS_FOR_PAGE_TEMPLATE; ?>
    </header>
    <div class="table-responsive col-md-10 mx-auto">
        <table class="table table-striped" id="pass-res-req-table">
        	<thead>
        		<tr>
        			<th colspan="5" class="text-center">
        				<span>TOTAL REQUESTS <?=${TOT_REQ}?> - Page <?=${PAGE}?>/<?=${MAX_PAGES}?></span>
    				</th>
    			</tr>
        		<tr>
        			<th class="w-5">
        				<a href="<?=${LINK_HEAD.PASSWORD_RESET_REQ_ID}?>">#</a>
        				<i class="<?=${CLASS_HEAD.PASSWORD_RESET_REQ_ID}?>"></i>
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
        				<a href="<?=${LINK_HEAD.PASSWORD_RESET_TOKEN}?>">TOKEN</a>
        				<i class="<?=${CLASS_HEAD.PASSWORD_RESET_TOKEN}?>"></i>
        			</th>
        			<th>
        				<a href="<?=${LINK_HEAD.EXPIRE_DATETIME}?>">EXPIRE DATETIME</a>
        				<i class="<?=${CLASS_HEAD.EXPIRE_DATETIME}?>"></i>
        			</th>
        		</tr>
        	</thead>
        	<tbody>
        	<?php
        	if (!empty(${REQUESTS})):
        	   foreach (${REQUESTS} as $req):
            	    ?>
            	        <tr>
            	        	<td class="align-middle">
                	        	<a href="/ums/table/get/<?=PASSWORD_RESET_REQ_TABLE.'/'.$req->{PASSWORD_RESET_REQ_ID}?>">
                	        		<?=$req->{PASSWORD_RESET_REQ_ID}?>
                	        	</a>
        	        		</td>
            	        	<td class="align-middle">
            	        		<?php if (isset($req->{USERNAME})): ?>
                	        		<a href="/ums/table/get/<?=USERS_TABLE.'/'.$req->{USER_ID}?>">
        		        	        	<?= $req->{USERNAME}?>
                	        		</a>
            	        		<?php else: ?>
            	        			<a href="/ums/table/get/<?=DELETED_USER_TABLE.'/'.$req->{USER_ID_FRGN}?>" class="text-danger">
        		        	        	DELETE: <?= $req->{USER_ID_FRGN}?>
                	        		</a>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle"><?=$req->{IP_ADDRESS}?></td>
            	        	<td class="align-middle">
            	        		<?php if (isset($req->{PASSWORD_RESET_TOKEN})): ?>
                	        		<i class="fas fa-check-circle"></i>
            	        		<?php else: ?>
            	        			<i class="far fa-circle"></i>
            	        		<?php endif; ?>
            	        	</td>
            	        	<td class="align-middle"><?=$req->{EXPIRE_DATETIME}?></td>
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