<!DOCTYPE html>
<html lang="it">
	<head>
		<?php if ($this->setCSPHeader): ?>
			<meta http-equiv="Content-Security-Policy" content="<?=$this->cspContent?>">
		<!-- 
			<meta http-equiv="X-Content-Security-Policy" content="< ?=$this->cspContent?>">
    		<meta http-equiv="X-WebKit-CSP" content="< ?=$this->cspContent?>">
		-->
		<?php endif; ?>
		<meta charset="utf-8"/>
		<meta http-equiv="Content-Type" content="<?=$this->contentType?>"/>

		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
		<meta name="description" content="<?=$this->description?>"/>
		<meta name="keywords" content="<?=$this->keywords?>"/>
		<meta name="robots" content="<?=$this->robots?>"/>
		<meta name="googlebot" content="<?=$this->googlebot?>"/>
		
		<!-- Tells Google not to show the sitelinks search box -->
		<!-- <meta name="google" content="nositelinkssearchbox"/> -->

		<!--  Tells Google that you don't want us to provide a translation for this page -->
		<!-- <meta name="google" content="notranslate"/> -->

		<!-- Confirm your site ownership -->
		<!-- <meta name="google-site-verification" content=""/> -->

		<!-- For adult site - Filtered by SafeSearch -->
		<!-- <meta name="rating" content="adult"/>
		<meta name="rating" content="RTA-5042-1996-1400-1577-RTA"/> -->
		

		<meta name="author" content="Andrea Serra"/>
		<link rel="icon" href="/favicon.ico" nonce="<?=$this->CSPImgNonce?>"/>
		<title><?=$this->title?></title>

		<!-- USE INTERNAL FILE -->
		<link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css" nonce="<?=$this->CSPStyleNonce?>"/>
		<link rel="stylesheet" href="/fontawesome/css/all.css" nonce="<?=$this->CSPStyleNonce?>"/>
		<link rel="stylesheet" href="/msg-box/messagebox.min.css" nonce="<?=$this->CSPStyleNonce?>"/>
		<!-- USE EXETERNAL FILE -->
		<!--
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-message-box@3.2.1/dist/messagebox.min.css">
		-->

		<link rel="stylesheet" href="/css/default.css" nonce="<?=$this->CSPStyleNonce?>"/>

		<?php foreach ($this->cssSrcs as $css): ?>
        	<link rel="stylesheet" <?=$this->getAttributeCss($css)?>/>
        <?php endforeach; ?>
	</head>
	<body>
		<div id="message-box" class="rounded-bottom text-white container-fluid jutify-content-center bg-blue text-center position-absolute z-index-1 row over-top mx-auto">
			<div class="col-1 container-fluid mx-0">
			</div>
			<div id="txt-message" class="col-8 container-fluid"></div>
			<div class="col-1 text-black text-right justify-content-right container-fluid mx-0">
				<button id="close-message-box" class="btn btn-link p-0 btn-close-msg" type="button"><i class="far fa-times-circle"></i></button>
			</div>
		</div>
		<nav class="navbar navbar-expand-md navbar-dark bg-dark">
			<a class="navbar-brand" href="/">UMS</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-ums" aria-controls="navbar-ums" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div id="navbar-ums" class="collapse navbar-collapse">
				<ul class="navbar-nav mr-auto">
					<li class="nav-item <?=$this->isHome ? 'active' : ''?>">
						<a class="nav-link" href="/">Home</a>
					</li>
					<?php if (isUserLoggedin()): ?>
						<?php if (userCanUpdate()): ?>
							<li class="nav-item <?=$this->isUsersList ? 'active' : ''?>">
								<a class="nav-link" href="/ums/users/">Users List</a>
							</li>
						<?php endif; ?>
						<?php if (userCanChangeSettings()): ?>
        					<li class="nav-item <?=$this->isSettings ? 'active' : ''?>">
        						<a class="nav-link" href="/ums/app/settings">App Settings</a>
        					</li>
    					<?php endif; ?>
    					<?php if (userCanCreate()): ?>
    						<li class="nav-item <?=$this->isNewUser ? 'active' : ''?>">
        						<a class="nav-link" href="/ums/user/new">New User</a>
        					</li>
    					<?php endif; ?>
    					<?php if (userCanSendEmail()): ?>
    						<li class="nav-item <?=$this->isNewEmail ? 'active' : ''?>">
        						<a class="nav-link" href="/ums/email/new">Send Email</a>
        					</li>
    					<?php endif; ?>
    					<li class="nav-item dropdown">
    						<a class="nav-link dropdown-toggle" href="/user/settings" id="account" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Account</a>
    						<div id="dropdown-account" class="dropdown-menu mx-auto" aria-labelledby="account">
    							<h4 class="text-center"><a href="/user/info">	<?=getUserLoggedUsername()?></a></h4>
    							<div class="justify-content-center text-left p-4 mx-auto">
        							<p>
        								Full name: <span class="text-primary"><?=getUserLoggedFullName()?></span><br>
        								Email: <span class="text-primary"><?=getUserLoggedEmail()?></span>
        								<?php if (isNotSimpleUser()): ?>
        									<br>
        									Role: <span class="text-primary"><?=getUserLoggedRole()?></span>
        								<?php endif;?>
        							</p>
    							</div>
    							<div class="container justify-content-center text-center p-2 row mx-auto">
    								<a href="/user/settings" class="btn btn-warning m-2"><i class="fas fa-cog"></i> Settings</a>
    								<form id="logout-form" action="/auth/logout" method="post">
    									<input id="_xf-out" type="hidden" name="_xf-out" value="<?=$this->tokenLogout?>" class="send-ajax">
    	    							<button id="btn-logout" class="btn btn-danger m-2" type="submit"><i id="ico-btn" class="fas fa-sign-out-alt"></i> Logout</button>
    								</form>
    							</div>
    						</div>
    					</li>
					<?php else : ?>
    					<li class="nav-item <?=$this->isLogin ? 'active' : ''?>">
    						<a class="nav-link" href="/auth/login">Login</a>
    					</li>
    					<li class="nav-item <?=$this->isSignup ? 'active' : ''?>">
    						<a class="nav-link" href="/auth/signup">Signup</a>
    					</li>
					<?php endif; ?>
				</ul>
			</div>
		</nav>
		<?php if ($this->isSettings): ?>
    		<nav class="navbar navbar-expand navbar-dark bg-dark">
    			<div id="navbar-settings" class="collapse navbar-collapse">
    				<label class="text-white p-2 my-auto">Settings</label>
    				<ul class="navbar-nav mr-auto">
    					<?php foreach ($this->appSectionsList as $key => $appSection): ?>
    						<li class="nav-item <?=$appSection === $this->section ? 'active' : ''?>">
        						<a class="nav-link" href="/ums/app/settings/<?=$appSection?>"><?=$key?></a>
        					</li>
    					<?php endforeach; ?>
					</ul>
    			</div>
    		</nav>
		<?php endif; ?>
		<main role="main">
			<div class="container-fluid p-3 my-2 justify-content-center text-center">
				<?php if (isset($_SESSION['message'])): ?>
                    <div id="message" class="p-4 text-center fade-out alert alert-<?=($_SESSION['success'] ?? TRUE) ? 'success' : 'danger'?>">
                    	<h3><strong><?=$_SESSION['message']?></strong></h3>
                    </div>
                <?php unset($_SESSION['message'], $_SESSION['success']);
                    endif;
                ?>
				<div class="mx-auto justify-content-center text-center p-4">
					<?=$this->content?>
				</div>
			</div>
		</main>
		<footer class="footer py-3">
			<div class="text-light container-fluid row py-3 my-auto">
				<div class="container col-3 text-left">
					<span></span>
				</div>
				<div class="container col-7 text-right my-auto">
					<span>
    					<a href="#">Back to top</a>
    					<br><br>
    					UMS - User Management System &bull; by Andrea Serra &bull; <a target="_blank" href="https:/github.com/d3v4s">Github</a>
					</span>
				</div>
			</div>
		</footer>
		<!-- USE INTERNAL FILE -->
        <script type="text/javascript" src="/js/jquery/jquery-3.4.1.min.js" nonce="<?=$this->CSPScriptNonce?>"></script>
        <script type="text/javascript" src="/bootstrap/js/bootstrap.min.js" nonce="<?=$this->CSPScriptNonce?>"></script>
        <script type="text/javascript" src="/bootstrap/js/popper.min.js" nonce="<?=$this->CSPScriptNonce?>"></script>
    	<script type="text/javascript" src="/msg-box/messagebox.min.js" nonce="<?=$this->CSPScriptNonce?>"></script>
        <!-- USE EXTERNAL FILE -->
    	<!--
    	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o" crossorigin="anonymous"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-message-box@3.2.1/dist/messagebox.min.js"></script>
        -->

    	<script type="text/javascript" src="/js/functions.js" nonce="<?=$this->CSPScriptNonce?>"></script>

        <?php foreach ($this->jsSrcs as $js): ?>
        	<script type="text/javascript" <?=$this->getAttributeJS($js)?>></script>
        <?php endforeach; ?>
	</body>
</html>