<nav class="p-2">
    <ul class="pagination pg-darkgrey justify-content-center">
        <li class="page-item <?=${CLASS_PAGIN_ARROW_LEFT}?>">
            <a class="page-link" href="<?=${LINK_PAGIN_ARROW_LEFT}?>" aria-label="Previous">
            	<i class="fas fa-arrow-circle-left"></i>
            </a>
        </li>
        <?php for ($i = ${START_PAGE}; $i <= ${STOP_PAGE}; $i++): ?>
        	<li class="page-item <?=${PAGE} === $i ? 'active disabled' : ''?>">
        		<a class="page-link" href="<?=${BASE_LINK_PAGIN}.$i.${CLOSE_LINK_PAGIN}?>"><?=$i?></a>
    		</li>
        <?php endfor; ?>
        <li class="page-item  <?=${CLASS_PAGIN_ARROW_RIGHT}?>">
            <a class="page-link" href="<?=${LINK_PAGIN_ARROW_RIGHT}?>" aria-label="Next">
            	<i class="fas fa-arrow-circle-right"></i>
            </a>
        </li>
    </ul>
</nav>