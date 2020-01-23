<li class="nav-item dropdown">
	<a id="account" class="nav-link dropdown-toggle" href="/<?=ACCOUNT_SETTINGS_ROUTE?>" data-toggle="dropdown" data-target="#dropdown-account" aria-controls="#dropdown-account" aria-haspopup="true" aria-expanded="false">Account</a>
	<div id="dropdown-account" class="dropdown-menu mx-auto">
		<h4 class="text-center"><a href="/<?=ACCOUNT_INFO_ROUTE?>"><?=$this->loginSession->{USERNAME}?></a></h4>
		<div class="justify-content-center text-left p-4 mx-auto">
			<p>
				Full name: <span class="text-primary"><?=$this->loginSession->{NAME}?></span><br>
				Email: <span class="text-primary"><?=$this->loginSession->{EMAIL}?></span>
				<?php if (!$this->isSimpleUser()): ?>
					<br>
					Role: <span class="text-primary"><?=$this->userRole[ROLE]?></span>
				<?php endif;?>
			</p>
		</div>
		<div class="container justify-content-center text-center p-2 row mx-auto">
			<a href="/<?=ACCOUNT_SETTINGS_ROUTE?>" class="btn btn-warning m-2"><i class="fas fa-cog"></i> Settings</a>
			<form id="logout-form" action="/<?=LOGOUT_ROUTE?>" method="post">
				<input id="<?=LOGOUT_TOKEN?>" type="hidden" name="<?=CSRF_LOGOUT?>" value="<?=$this->{LOGOUT_TOKEN}?>">
				<button id="btn-logout" class="btn btn-danger m-2" type="submit"><i id="ico-btn" class="fas fa-sign-out-alt"></i> Logout</button>
			</form>
		</div>
	</div>
</li>