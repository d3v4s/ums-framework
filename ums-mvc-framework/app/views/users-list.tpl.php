<h2 class="text-center">USER LIST</h2>
<div class="container-fluid">
    <header class="p-3">
    	<nav class="navbar navbar-expand-md navbar-light justify-content-center">
    		<form id="search-form" class="form-inline" action="<?=$uri?>" method="get">
                <input 	class="form-control mr-sm-2 bg-light"
                		name="search" id="search"
                		type="text"
                		placeholder="Search"
                		value="<?=$search?>"
                		aria-label="Search">
                <button class="btn btn-outline-success m-2 my-sm-0" type="submit">Search</button>
                <button onclick="location.href='<?=$uri?>'"
                		class="btn btn-outline-warning m-2 my-sm-0"
                		type="reset">Reset</button>
    		</form>
    	</nav>
    	<div class="row justify-content-center col-7 mx-auto">
    		<label class="my-auto mx-2">Users for page</label>
    		<?php foreach ($usersForPageList as $ufp): ?>
    			<a class="btn btn-<?=$usersForPage === $ufp ? 'secondary disabled' : 'primary'?> p-1 px-2 m-2" href="/ums/users/<?=$orderBy?>/<?=$orderDir?>/<?=$page?>/<?=$ufp?>"><?=$ufp?></a>
    		<?php endforeach; ?>
    	</div>
    </header>
    <div class="table-responsive col-md-10 mx-auto">
        <table class="table table-striped" id="users-table">
        	<thead>
        		<tr>
        			<th colspan="9" class="text-center">
        				<span>TOTAL USERS <?=$totUsers?> - Page <?=$page?>/<?=$maxPages?></span>
    				</th>
    			</tr>
        		<tr>
        			<th class="w-5">
        				<a href="/ums/users/id/<?=$orderBy === 'id' ? $orderDirRev : 'desc'?>/<?=$page?>/<?=$usersForPage?><?=empty($search) ? '' : "?search=$search"?>">#</a>
        				<i class="<?=$orderBy === 'id' ? "fas fa-sort-$orderDirClass" : ''?>"></i>
        			</th>
        			<th>
        				<a href="/ums/users/name/<?=$orderBy === 'name' ? $orderDirRev : 'desc'?>/<?=$page?>/<?=$usersForPage?><?=empty($search) ? '' : "?search=$search"?>">NAME</a>
        				<i class="<?=$orderBy === 'name' ? "fas fa-sort-$orderDirClass" : ''?>"></i>
        			</th>
        			<th>
        				<a href="/ums/users/username/<?=$orderBy === 'username' ? $orderDirRev : 'desc'?>/<?=$page?>/<?=$usersForPage?><?=empty($search) ? '' : "?search=$search"?>">USERNAME</a>
        				<i class="<?=$orderBy === 'username' ? "fas fa-sort-$orderDirClass" : ''?>"></i>
        			</th>
        			<th>
        				<a href="/ums/users/email/<?=$orderBy === 'email' ? $orderDirRev : 'desc'?>/<?=$page?>/<?=$usersForPage?><?=empty($search) ? '' : "?search=$search"?>">EMAIL</a>
        				<i class="<?=$orderBy === 'email' ? "fas fa-sort-$orderDirClass" : ''?>"></i>
        			</th>
					<th>
        				<a href="/ums/users/enabled/<?=$orderBy === 'enabled' ? $orderDirRev : 'desc'?>/<?=$page?>/<?=$usersForPage?><?=empty($search) ? '' : "?search=$search"?>">STATE</a>
        				<i class="<?=$orderBy === 'enabled' ? "fas fa-sort-$orderDirClass" : ''?>"></i>
        			</th>
        			<?php if (isUserAdmin()): ?>
            			<th>
            				<a href="/ums/users/role/<?=$orderBy === 'role' ? $orderDirRev : 'desc'?>/<?=$page?>/<?=$usersForPage?><?=empty($search) ? '' : "?search=$search"?>">ROLE</a>
            				<i class="<?=$orderBy === 'role' ? "fas fa-sort-$orderDirClass" : ''?>"></i>
            			</th>
        			<?php endif;?>
        			<th>
        			</th>
        		</tr>
        	</thead>
        	<tbody>
        	<?php
            	if (isset($users)) {
            	    foreach ($users as $user):
            	    ?>
            	        <tr>
            	        	<td><?= $user->id?></td>
            	        	<td><?= $user->name?></td>
            	        	<td>
            	        		<a href="/ums/user/<?=$user->id?>">
    		        	        	<?= $user->username?>
            	        		</a>
            	        	</td>
            	        	<td>
            	        		<a href="mailto:<?=$user->email?>">
            	        			<?=$user->email?>
            	        		</a>
            	        	</td>
            	        	<td><?= $user->enabled ? 'enabled' : 'disabled'?></td>
            	        	<?php if (isUserAdmin()): ?>
            	        		<td><?= $user->roletype?></td>
            	        	<?php endif;?>
            	        	<td>
            	        		<div class="row">
            	        			<?php if (userCanUpdate()):?>
                	        			<div class="col-lg-6 col-md-6 col-sm-7 col-xs-8 my-1">
                	        				<a class="btn btn-warning text-cente" href="/ums/user/<?=$user->id?>/update">Update</a>
                	        			</div>
            	        			<?php endif;?> 
            	        		</div>
            	        	</td>
            	        </tr>
            	    <?php endforeach;
            	} else { ?>
            	    <tr><td colspan="9" class="text-center"><h2>ERR 404!!<br>No records found</h2></td></tr>
            <?php } ?>
        	</tbody>
        </table>
    </div>
    <nav class="p-2">
        <ul class="pagination pg-darkgrey justify-content-center">
            <li class="page-item <?= $page === 1 || $maxPages === 1 ? 'disabled': '' ?>">
                <a class="page-link" href="/ums/users/<?=$orderBy?>/<?=$orderDir?>/<?=$page-1?>/<?=$usersForPage?>" aria-label="Previous">
                	<i class="fas fa-arrow-circle-left"></i>
                </a>
            </li>
            <?php for ($i = $startPage; $i <= $stopPage; $i++) : ?>
            	<li class="page-item <?=$page === $i ? 'active disabled' : ''?>"><a class="page-link" href="/ums/users/<?=$orderBy?>/<?=$orderDir?>/<?=$i?>/<?=$usersForPage?>"><?=$i?></a></li>
            <?php endfor; ?>
            <li class="page-item  <?=$page === $maxPages || $maxPages === 1 ? 'disabled': '' ?>">
                <a class="page-link" href="/ums/users/<?=$orderBy?>/<?=$orderDir?>/<?=$page+1?>/<?=$usersForPage?>" aria-label="Next">
                	<i class="fas fa-arrow-circle-right"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>