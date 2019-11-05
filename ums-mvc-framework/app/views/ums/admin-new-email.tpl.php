<div class="container col-md-7 text-center">
    <h1>New Email</h1>
    <form id="send-email-form" action="/ums/email/send" method="post">
    	<div class="form-group text-md-left">
    		<label for="to">To</label>
    		<input id="to" placeholder="To" class="form-control evidence-error send-ajax-crypt validate-email" type="email" name="to" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="subject">Subject</label>
    		<input id="subject" placeholder="Subject" class="form-control send-ajax-crypt" type="text" name="subject">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="content">Text</label>
    		<textarea id="content" name="content" class="form-control send-ajax-crypt" rows="10"></textarea>
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>" class="send-ajax">
    	<noscript>
    		<div class="container-fluid">
    			<h3 class="text-danger">ENABLE JAVASCRIPT TO SEND EMAIL</h3>
    		</div>
    	</noscript>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button id="btn-send" class="btn btn-success px-3 py-1" type="submit">
	    		<i id="ico-btn" class="fas fa-paper-plane"></i>
	    		<span id="spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  				<span id="text-btn">Send</span>
	    	</button>
    	</div>
    </form>
</div>