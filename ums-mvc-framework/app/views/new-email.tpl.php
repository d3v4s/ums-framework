<div class="container col-md-7 text-center">
    <h1>New Email</h1>
    <form action="/ums/email/send" method="POST">
    	<div class="form-group text-md-left">
    		<label for="to">To</label>
    		<input placeholder="To" class="form-control" type="text" name="to" id="to" required="required" autofocus="autofocus">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="subject">Subject</label>
    		<input placeholder="Subject" class="form-control" type="text" name="subject" id="subject">
    	</div>
    	<div class="form-group text-md-left">
    		<label for="editor">Text</label>
    		<textarea id="editor" name="content" class="form-control" rows="10"></textarea>
    	</div>
    	<input type="hidden" name="_xf" value="<?=$token?>">
    	<input type="hidden" name="from" value="<?=getUserLoggedEmail()?>">
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button class="btn btn-success px-3 py-1" type="submit">Send</button>
    	</div>
    </form>
</div>
<script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
	ClassicEditor.create(document.querySelector('#editor')).catch(error => {
    	console.error(error);
	});
</script>

