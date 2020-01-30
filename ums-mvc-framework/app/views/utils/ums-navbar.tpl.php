<nav class="navbar navbar-expand-md navbar-dark bg-dark">
	<a class="navbar-brand" href="/">DevAS</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-ums" aria-controls="navbar-ums" aria-expanded="false" aria-label="Toggle navigation">
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
				<?php endif; ?>
				<?php if ($this->canViewTables()): ?>
					<li class="nav-item <?=$this->isUsersList ? 'active' : ''?>">
						<a class="nav-link" href="/<?=UMS_TABLES_ROUTE.'/'.USERS_TABLE?>">Tables</a>
					</li>
				<?php endif; ?>
				<?php if ($this->canCreateUser()): ?>
					<li class="nav-item <?=$this->isNewUser ? 'active' : ''?>">
						<a class="nav-link" href="/<?=UMS_TABLES_ROUTE.'/'.ACTION_ROUTE.'/'.USERS_TABLE.'/'.NEW_ROUTE?>">New User</a>
					</li>
				<?php endif; ?>
				<?php if ($this->canSendEmails()): ?>
					<li class="nav-item <?=$this->isNewEmail ? 'active' : ''?>">
						<a class="nav-link" href="/<?=NEW_EMAIL_ROUTE?>">Send Email</a>
					</li>
				<?php endif; ?>
				<?php if ($this->canChangeSettings()): ?>
					<li class="nav-item <?=$this->isSettings ? 'active' : ''?>">
						<a class="nav-link" href="/<?=APP_SETTINGS_ROUTE?>">App Settings</a>
					</li>
				<?php
				endif;
				require_once ACCOUNT_POPUP_NAVBAR_TEMPLATE;
            endif;
            ?>
		</ul>
	</div>
</nav>