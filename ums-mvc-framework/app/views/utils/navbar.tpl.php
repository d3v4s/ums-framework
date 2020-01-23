<nav class="navbar navbar-expand-md navbar-dark bg-dark">
	<a class="navbar-brand" href="/">DevAS</a>
	<button id="collapse-navbar" class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-ums" aria-controls="#navbar-ums" aria-expanded="false" aria-label="Toggle navigation">
		<i class="fas fa-bars"></i>
	</button>
	<div id="navbar-ums" class="collapse navbar-collapse">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item <?=$this->isHome ? 'active' : ''?>">
				<a class="nav-link" href="/<?=HOME_ROUTE?>">Home</a>
			</li>
			<?php if ($this->loginSession): ?>
				<?php if (!$this->isSimpleUser()): ?>
					<li class="nav-item <?=$this->isUmsHome ? 'active' : ''?>">
						<a class="nav-link" href="/<?=UMS_HOME_ROUTE?>">UMS</a>
					</li>
				<?php
				endif;
				require_once ACCOUNT_POPUP_NAVBAR_TEMPLATE;
				?>
				
			<?php else: ?>
				<li class="nav-item <?=$this->isLogin ? 'active' : ''?>">
					<a class="nav-link" href="/<?=LOGIN_ROUTE?>">Login</a>
				</li>
				<li class="nav-item <?=$this->isSignup ? 'active' : ''?>">
					<a class="nav-link" href="/<?=SIGNUP_ROUTE?>">Signup</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</nav>
