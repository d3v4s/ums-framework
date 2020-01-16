<?php if (isset($_SESSION['message'])): ?>
    <div id="message" class="p-4 text-center fade-out alert alert-<?=($_SESSION['success'] ?? TRUE) ? 'success' : 'danger'?>">
    	<h3><strong><?=$_SESSION['message']?></strong></h3>
    </div>
<?php unset($_SESSION['message'], $_SESSION['success']);
    endif;
?>