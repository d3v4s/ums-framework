<nav class="navbar navbar-expand-md navbar-dark bg-dark">
	<a class="navbar-brand" href="/">DevAS</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-ums" aria-controls="navbar-ums" aria-expanded="false" aria-label="Toggle navigation">
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
				<?php endif; ?>
				<?php if ($this->canViewTables()): ?>
					<li class="nav-item <?=$this->isUsersList ? 'active' : ''?>">
						<a class="nav-link" href="/ums/table/<?=USERS_TABLE?>"><?=$this->lang[DATA]['ums_tables']?></a>
					</li>
				<?php endif; ?>
				<?php if ($this->canCreateUser()): ?>
					<li class="nav-item <?=$this->isNewUser ? 'active' : ''?>">
						<a class="nav-link" href="/ums/table/action/<?=USERS_TABLE?>/new"><?=$this->lang[DATA]['new_user']?></a>
					</li>
				<?php endif; ?>
				<?php if ($this->canSendEmails()): ?>
					<li class="nav-item <?=$this->isNewEmail ? 'active' : ''?>">
						<a class="nav-link" href="/ums/email/new"><?=$this->lang[DATA]['send_email']?></a>
					</li>
				<?php endif; ?>
				<?php if ($this->canChangeSettings()): ?>
					<li class="nav-item <?=$this->isSettings ? 'active' : ''?>">
						<a class="nav-link" href="/ums/app/settings"><?=$this->lang[DATA]['app_settings']?></a>
					</li>
				<?php
				endif;
				require_once ACCOUNT_POPUP_NAVBAR_TEMPLATE;
            endif;
            ?>
		</ul>
		<?php require_once TOGGLE_LANG_TEMPLATE; ?>
	</div>
</nav>
