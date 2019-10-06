<!DOCTYPE html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<meta name="author" content="Andrea Serra">
		<link rel="icon" href="/favicon.ico">
		<title>UMS - FRAMEWORK</title>
		<link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="/fontawesome/css/all.css" rel="stylesheet">
		<link href="/css/style.css" rel="stylesheet">
		<script type="text/javascript" src="/js/jquery/jquery-3.4.1.min.js"></script>
	</head>
	<body>
		<nav class="navbar navbar-expand-md navbar-dark bg-dark">
			<a class="navbar-brand" href="/ums/users/">UMS</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-ums" aria-controls="navbar-ums" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbar-ums">
				<ul class="navbar-nav mr-auto">
					<li class="nav-item <?=$this->isHome ? '' : 'active'?>">
						<a class="nav-link <?=$this->isHome ? 'disabled' : ''?>" href="/">Home</a>
					</li>
					<?php if (isUserLoggedin()): ?>
						<?php if (isUserAdmin()): ?>
							<?php if ($this->addFakeUsers): ?>
    							<li class="nav-item <?=$this->isAddFakeUsers ? '' : 'active'?>">
            						<a class="nav-link <?=$this->isAddFakeUsers ? 'disabled' : ''?>" href="/ums/users/fake">Add Fake Users</a>
            					</li>
							<?php endif; ?>
    						<li class="nav-item <?=$this->isNewUser ? '' : 'active'?>">
        						<a class="nav-link <?=$this->isNewUser ? 'disabled' : ''?>" href="/ums/user/new">New User</a>
        					</li>
    						<li class="nav-item <?=$this->isNewEmail ? '' : 'active'?>">
        						<a class="nav-link <?=$this->isNewEmail ? 'disabled' : ''?>" href="/ums/email/new">Send Email</a>
        					</li>
    					<?php endif; ?>
    					<li class="nav-item dropdown">
    						<a class="nav-link dropdown-toggle" href="/" id="account" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Account</a>
    						<div class="dropdown-menu" aria-labelledby="account">
    							<h4 class="text-center dropdown-item"><a href="/user/info">	<?=getUserLoggedUsername()?></a></h4>
    							<p class="dropdown-item p-4">
    								Full name: <span class="text-primary"><?=getUserLoggedFullName()?></span><br>
    								Email: <span class="text-primary"><?=getUserLoggedEmail()?></span>
    								<?php if (isUserAdmin() || isUserEditor()): ?>
    									<br>
    									Role: <span class="text-primary"><?=getUserLoggedRole()?></span>
    								<?php endif;?>
    							</p>
    							<div class="text-center p-3">
    								<form action="/auth/logout" method="POST">
    	    							<input class="btn btn-danger" type="submit" value="Logout">
    								</form>
    							</div>
    						</div>
    					</li>
					<?php else : ?>
    					<li class="nav-item <?=$this->isLogin ? '' : 'active'?>">
    						<a class="nav-link <?=$this->isLogin ? 'disabled' : ''?>" href="/auth/login">Login</a>
    					</li>
    					<li class="nav-item <?=$this->isSignup ? '' : 'active'?>">
    						<a class="nav-link <?=$this->isSignup ? 'disabled' : ''?>" href="/auth/signup">Signup</a>
    					</li>
					<?php endif; ?>
				</ul>
<!-- 				<form class="form-inline my-2 my-md-0">
					<input class="form-control" type="text" placeholder="Search">
				</form> -->
			</div>
		</nav>
		<main role="main justify-content-center text-center">
			<div class="container-fluid p-3 mt-3 justify-content-center text-center">
				<?php if (isset($_SESSION['message'])): ?>
                    <div id="message" class="p-4 text-center alert alert-<?=array_key_exists('success', $_SESSION) && $_SESSION['success'] ? 'success' : 'danger'?>">
                    	<h3><strong><?=$_SESSION['message']?></strong></h3>
                    </div>
                <?php unset($_SESSION['message'], $_SESSION['success']);
                    endif;
                ?>
                
				<div class="mx-auto justify-content-center text-center">
					<?=$this->content?>
				</div>
			</div>
		</main>
<!-- 		<footer class="footer mb-0 mx-auto p-2">
			<div class="container">
				<span class="text-light">UMS - User Management System &bull; by Andrea Serra &bull; <a target="_blank" href="https:/github.com/z4X0r">Github</a></span>
			</div>
		</footer> -->
    	<!-- <script src="/js/jquery-3.4.1.min.js"></script> -->
    	<script src="/js/jquery/jquery-validate.min.js"></script>
        <script src="/bootstrap/js/bootstrap.min.js"></script>
        <script src="/bootstrap/js/popper.min.js"></script>
    	<script src="/js/functions.js"></script>
    	<script type="text/javascript">
        	$('#message').fadeOut(7000);
        </script>
	</body>
</html>