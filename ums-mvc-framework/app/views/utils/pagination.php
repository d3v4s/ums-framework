<nav class="p-2">
    <ul class="pagination pg-darkgrey justify-content-center">
        <li class="page-item <?=$classPaginationArrowLeft?>">
            <a class="page-link" href="<?=$linkPaginationArrowLeft?>" aria-label="Previous">
            	<i class="fas fa-arrow-circle-left"></i>
            </a>
        </li>
        <?php for ($i = $startPage; $i <= $stopPage; $i++): ?>
        	<li class="page-item <?=$page === $i ? 'active disabled' : ''?>">
        		<a class="page-link" href="<?=$baseLinkPagination . $i . $closeUrlPagination?>"><?=$i?></a>
    		</li>
        <?php endfor; ?>
        <li class="page-item  <?=$classPaginationArrowRight?>">
            <a class="page-link" href="<?=$linkPaginationArrowRight?>" aria-label="Next">
            	<i class="fas fa-arrow-circle-right"></i>
            </a>
        </li>
    </ul>
</nav>