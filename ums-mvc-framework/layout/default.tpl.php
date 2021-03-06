<!DOCTYPE html>
<html lang="it">
	<head>
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
		<?php
		require_once MESSAGE_BOX_TEMPLATE;
		require_once NAVBAR_TEMPLATE;
		?>
		<noscript>
    		<div class="container-fluid text-center">
    			<h5 class="text-danger p-2"><?=$this->lang[DATA][ENABLE_JAVASCRIPT]?></h5>
    		</div>
    	</noscript>
		<main role="main">
			<div class="container-fluid p-3 my-2 justify-content-center text-center">
				<?php require_once SHOW_SESSION_MESSAGE_TEMPLATE; ?>
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
    					UMS - User Management System &bull; by Andrea Serra (DevAS) &bull; <a target="_blank" href="https:/github.com/d3v4s">Github</a>
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