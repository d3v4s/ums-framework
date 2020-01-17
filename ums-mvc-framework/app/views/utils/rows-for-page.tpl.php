<div class="row justify-content-center col-7 mx-auto">
	<label class="my-auto mx-2">Rows for page</label>
    <?php foreach (ROWS_FOR_PAGE_LIST as $rfp): ?>
    	<a
    		class="btn btn-<?=${ROWS_FOR_PAGE} == $rfp ? 'secondary disabled' : 'primary'?> p-1 px-2 m-2"
    		href="<?=${BASE_LINK_ROWS_FOR_PAGE} . $rfp . (${SEARCH_QUERY} ?? '')?>"
    	><?=$rfp?></a>
    <?php endforeach; ?>
</div>