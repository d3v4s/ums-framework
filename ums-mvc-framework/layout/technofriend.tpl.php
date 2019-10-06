<!DOCTYPE html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="Blog italiano sullo sviluppo software e web, sui sistemi linux e il mondo informatico in generale.">
		<meta name="keywords" content="tecnofriend, technofriend, programmazione, technology, tecnologia, java, c++, c, ++, php, python, programming, linux, gnu, gnulinux, tecno, techno, friend, it, ita, italia, italian, blog, informatica, sistemistica, sistemi, sysadmin, system, administrator, sistemista, system, amministrazione, web, software, developing, sviluppare, sviluppo, app">
		<meta name="author" content="Andrea Serra">
		<link rel="icon" href="/favicon.ico">
		<title>TECHNOFRIEND</title>
		<link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="/fontawesome/css/all.css" rel="stylesheet">
		<link href="/css/technofriend/style.css" rel="stylesheet">
	</head>
    <body>
    	<header class="static-section">
    		 <hgroup>
    			<h1 class="font-game">TECHNOFRIEND</h1>
			</hgroup>
            <nav class="font-game row">
            	<?php if (isUserLoggedin()): ?>
                    <?php if (userCanUpdate()): ?>
                    	<a class="link-nav" href="/ums/users">USERS</a>
                    <?php endif;?>
                    <ul class="navbar-nav mr-auto">
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
                    </ul>
                <?php else: ?>
                    <a class="link-nav" href="/auth/login">LOGIN</a>
                    <a class="link-nav" href="/auth/signup">SIGNUP</a>
                <?php endif;?>
            </nav>
		</header>
    	<section id="content" class="static-section backgorund-content bottom-distance">
    		<?=$this->content?>
    	 </section>
        <footer id="foot" class="static-section">
        	<div class="center-text">
                <h4 class="font-michroma margin-bottom">ITALIAN TECHNOLOGY BLOG</h4>
                <p class="font-michroma">
                    Repo Code: <a class="color-green" target="_blank" href="https://github.com/z4X0r/TechnoFriend">GitHub</a><br>
                    Free Ads Site - Sito Senza Pubblicità<br>
                    Donazioni: BTC xxx - ETH xxx - MNR xxx - PayPal xxx<br>
                    Build by<br>Andrea Serra
                </p>
            </div>
        </footer>
    	<script src="/js/jquery-3.4.1.min.js"></script>
    	<script src="/js/jquery-validate.min.js"></script>
        <script src="/bootstrap/js/bootstrap.min.js"></script>
        <script src="/bootstrap/js/popper.min.js"></script>
	</body>
</html>