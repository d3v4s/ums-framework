<div class="container col-md-7 text-center">
    <h1>Login</h1>
    <form id="form-login" action="/auth/login" method="POST">
    	<div class="form-group text-md-left">
    		<label for="email">Email/Username</label>
    		<input id="user" placeholder="Email or Username" class="form-control" type="text" name="user" required="required" autofocus="autofocus">
    	</div>
    	<input id="_xf" type="hidden" name="_xf" value="<?=$token?>">
    	<div class="form-group text-md-left">
    		<label for="pass">Password</label>
    		<input id="pass" placeholder="Password" class="form-control" type="password" name="pass" required="required">
    	</div>
    	<div class="form-group text-md-right mr-md-4 mt-md-4">
	    	<button class="btn btn-success px-3 py-1" type="submit">Login</button>
    	</div>
    </form>
</div>
<script type="text/javascript" src="/js/crypt/jsbn.js"></script>
<script type="text/javascript" src="/js/crypt/prng4.js"></script>
<script type="text/javascript" src="/js/crypt/rng.js"></script>
<script type="text/javascript" src="/js/crypt/rsa.js"></script>
<script type="text/javascript">
    $(function (){
    	$('#form-login').on('submit', function (event) {
    		event.preventDefault();

    		var rsa = new RSAKey();
            rsa.setPublic('<?=$keyN?>', '<?=$keyE?>');

    		var pass = $('#pass').val();
    		var user = $('#user').val();

            pass = rsa.encrypt(pass);
            user = rsa.encrypt(user);

        	const data = 'pass=' + pass + '&user=' + user + '&_xf=' + $('#_xf').val();
//     		const data = $(this).serialize();
// 			alert(data);

    		$.ajax({
    			method: 'post',
    			data: data,
    			url: '/auth/login',
    			success: function(response) {
    				const userRes = JSON.parse(response);

    				if(userRes.success) location.href = '/';
    				else {
        				alert(userRes.message);
        				location.href = '/auth/login';
    				}
    			},
    			failure: function() {
    				alert('PROBLEM CONTACT SERVER');
    			}
    		});
    	});
    });

    function login(){
            var rsa = new RSAKey();
            rsa.setPublic('<?=$keyN?>', '<?=$keyE?>');

    		var pass = $('#pass').val();
    		var user = $('#user').val();

            pass = rsa.encrypt(pass);
            user = rsa.encrypt(user);
            $('#pass').val(pass);
            $('#user').val(user);
	}
</script>