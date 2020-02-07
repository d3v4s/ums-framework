<li class="dropdown">
	<a class="nav-link dropdown-toggle" href="/<?=ACCOUNT_SETTINGS_ROUTE?>" data-toggle="dropdown">Account <span class="caret"></span></a>
	<ul id="dropdown-account" class="dropdown-menu mx-auto">
		<li>
			<h4 class="text-center"><a href="/<?=ACCOUNT_INFO_ROUTE?>"><?=$this->loginSession->{USERNAME}?></a></h4>
		</li>
		<li>
    		<div class="justify-content-center text-left p-4 mx-auto">
    			<p>
    				<?=$this->lang[DATA][NAME]?>: <span class="text-primary"><?=$this->loginSession->{NAME}?></span>
    				<br>
    				Email: <span class="text-primary"><?=$this->loginSession->{EMAIL}?></span>
    				<?php if (!$this->isSimpleUser()): ?>
    					<br>
    					<?=$this->lang[DATA][ROLE]?>: <span class="text-primary"><?=$this->userRole[ROLE]?></span>
    				<?php endif;?>
    			</p>
    		</div>
		</li>
		<li>
    		<div class="container justify-content-center text-center p-2 row mx-auto">
    			<a href="/<?=ACCOUNT_SETTINGS_ROUTE?>" class="btn btn-warning m-2"><i class="fas fa-cog"></i> <?=$this->lang[DATA][SETTINGS]?></a>
    			<form id="logout-form" action="/<?=LOGOUT_ROUTE?>" method="post">
    				<input id="<?=LOGOUT_TOKEN?>" type="hidden" name="<?=CSRF_LOGOUT?>" value="<?=$this->{LOGOUT_TOKEN}?>">
    				<button id="btn-logout" class="btn btn-danger m-2" type="submit">
    					<i class="fas fa-sign-out-alt ico-btn"></i>
    					<span class="spinner-border spinner-border-sm d-none spinner" role="status" aria-hidden="true"></span>
      					<span class="text-btn">Logout</span>
    				</button>
    			</form>
    		</div>
		</li>
	</ul>
</li>