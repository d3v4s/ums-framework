$(document).ready(function(){
	/* click event to toogle sidebar */
	$("#sidebar-toggle").click(function(e){
		e.preventDefault();
		$("#sidebar").toggleClass("show");
	});

	/* click evento to close sidebar when click link */
	$('#sidebar a').click(function(){
		$("#sidebar").toggleClass("show");
	});
});
