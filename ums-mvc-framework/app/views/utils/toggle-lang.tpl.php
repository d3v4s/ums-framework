<div class="dropdown m-2">
	<a class="dropdown-toggle rounded" href="#" data-toggle="dropdown" data-target="#dropdwon-lang">
		<img class="flag" src="/img/flags/<?=$this->langCli?>_64.png" alt="<?=$this->langCli?> flag">
	</a>
	<ul id="dropdown-lang" class="dropdown-menu bg-dark">
		<?php foreach (ACCEPT_LANG_LIST as $lang): ?>
			<li class="p-2 text-xl-left text-lg-left text-md-left text-sm-center">
    			<button class="rounded btn btn-link" value="<?=$lang?>">
            		<img class="flag" src="/img/flags/<?=$lang?>_64.png" alt="<?=$lang?> flag">
            	</button>
        	</li>
		<?php endforeach; ?>
	</ul>
</div>