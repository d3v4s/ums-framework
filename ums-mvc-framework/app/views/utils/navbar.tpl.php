<nav class="navbar navbar-expand-md navbar-dark bg-dark">
	<a class="navbar-brand" href="/">DevAS</a>
	<button id="collapse-navbar" class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-ums" aria-controls="#navbar-ums" aria-expanded="false" aria-label="Expand navbar">
		<i class="fas fa-bars"></i>
	</button>
	<div id="navbar-ums" class="collapse navbar-collapse">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item <?=$this->isHome ? 'active' : ''?>">
				<a class="nav-link" href="/">Home</a>
			</li>
			<?php if ($this->loginSession): ?>
				<?php if (!$this->isSimpleUser()): ?>
					<li class="nav-item <?=$this->isUmsHome ? 'active' : ''?>">
						<a class="nav-link" href="/ums"><?=$this->lang[DATA]['menagement']?></a>
					</li>
				<?php
				endif;
				require_once ACCOUNT_POPUP_NAVBAR_TEMPLATE;
				?>
				
			<?php else: ?>
				<li class="nav-item <?=$this->isLogin ? 'active' : ''?>">
					<a class="nav-link" href="/auth/login">Login</a>
				</li>
				<li class="nav-item <?=$this->isSignup ? 'active' : ''?>">
					<a class="nav-link" href="/auth/signup"><?=$this->lang[DATA][SIGNUP]?></a>
				</li>
			<?php endif; ?>
		</ul>
		<?php require_once TOGGLE_LANG_TEMPLATE; ?>
	</div>
</nav>
