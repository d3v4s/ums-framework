<h2 class="text-center">ADVANCE SEARCH</h2>
<div class="container-fluid">
    <header class="p-3">
    	<nav class="navbar navbar-expand-md justify-content-center">
    		<form id="advance-search-form" class="form-inline text-center justify-content-center" action="<?=${SEARCH_ACTION}?>" method="get">
				<div class="container-fluid p-3 text-center justify-content-center row">
					<?php foreach(${TABLES_LIST} as $table => $name): ?>
						<div class="form-check form-check-inline text-left m-2">
                			<input id="<?=$table?>" name="<?=TABLE?>" type="radio" class="form-check-input" data-toggle="table" data-target="#table-param-<?=$table?>" value="<?=$table?>" <?=$table === ${TABLE} ? CHECKED : ''?>>
                			<label class="form-check-label" for="<?=$table?>"><?=$name?></label>
                        </div>
					<?php endforeach; ?>
				</div>
				<div class="container-fluid row justify-content-center text-center p-2">
					<?php foreach (${TABLES_LIST} as $table => $tableName): ?>
						<div id="table-param-<?=$table?>" class="container-fluid m-auto row collapse hide table-param">
							<h5 class="col-12"><?=$tableName?></h5>
							<?php foreach (${SEARCH_PARAMS}[$table] as $param => $attr): ?>
								<div class="input-group m-2 text-center justify-content-center mx-auto">
									<div class="input-group-prepend">
										<span class="input-group-text">
											<input type="checkbox" data-toggle="param" data-target="<?="#$table-$param"?>" <?=(${TABLE} === $table && isset(${PARAM_VALUES}[$param])) ? CHECKED :  ''?>>
										</span>
									</div>
									<?php
                                    switch ($attr[TYPE]): 
                                        case 'text':
                                    ?>                                    			
											<input id="<?="$table-$param"?>" type="text" class="form-control search-param" name="<?=$param?>" value="<?=(${TABLE} === $table) ? ${SEARCH_PARAMS}[$param] ?? '' : ''?>">
									<?php
                                            break;
                                        case 'datetime':
                                    ?> 
                                    		<input id="<?="$table-$param"?>" type="datetime" class="form-control search-param" name="<?=$param?>" value="<?=(${TABLE} === $table) ? ${SEARCH_PARAMS}[$param] ?? '' : ''?>">
                            		<?php
                                            break;
                                        case 'select':
                            		?>
                            				<select id="<?="$table-$param"?>" name="<?=$param?>" class="search-param">
                                    			<?php foreach ($attr[SELECT_LIST] as $key => $val): ?>
                                	    			<option <?=(${TABLE} === $table && isset(${PARAM_VALUES}[$param]) && $key === ${PARAM_VALUES}[$param]) ? 'selected="selected"' : ''?> value="<?=$key?>"><?=$val?></option>
                                    			<?php endforeach; ?>
                                    		</select>
									<?php endswitch;?>
									<div class="input-group-append">
										<span class="input-group-text"><?=$attr[VALUE]?></span>
									</div>
                                </div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
				<button class="btn btn-outline-success m-2 my-sm-0" type="submit">Search</button>
				<a href="/<?=ADVANCE_SEARCH_ROUTE?>" class="btn btn-outline-warning m-2 my-sm-0">Reset</a>
    		</form>
    	</nav>
    	<?php require_once ROWS_FOR_PAGE_TEMPLATE; ?>
    </header>
    <div class="table-responsive col-md-10 mx-auto">
        <table class="table table-striped" id="advance-search-table">
        	<thead>
        		<tr>
        			<th colspan="7" class="text-center">
        				<span>TOTAL ROWS <?=${TOT_ROWS}?> - Page <?=${PAGE}?>/<?=${MAX_PAGES}?></span>
    				</th>
    			</tr>
        		<tr>
        			<?php foreach (${HEAD_TABLE_LIST} as $head => $prop):?>
        				<th class="w-5">
            				<a href="<?=$prop[LINK_HEAD]?>"><?=$head?></a>
            				<i class="<?=$prop[CLASS_HEAD]?>"></i>
            			</th>
        			<?php endforeach; ?>
<!--         			<th class="w-5"> -->
<!--         				<a href="< ?=${LINK_HEAD.USER_ID}?>">#</a> -->
<!--         				<i class="< ?=${CLASS_HEAD.USER_ID}?>"></i> -->
<!--         			</th> -->
<!--         			<th> -->
<!--         				<a href="< ?=${LINK_HEAD.USERNAME}?>">USERNAME</a> -->
<!--         				<i class="< ?=${CLASS_HEAD.USERNAME}?>"></i> -->
<!--         			</th> -->
<!--         			<th> -->
<!--         				<a href="< ?=${LINK_HEAD.NAME}?>">NAME</a> -->
<!--         				<i class="< ?=${CLASS_HEAD.NAME}?>"></i> -->
<!--         			</th> -->
<!--         			<th> -->
<!--         				<a href="< ?=${LINK_HEAD.EMAIL}?>">EMAIL</a> -->
<!--         				<i class="< ?=${CLASS_HEAD.EMAIL}?>"></i> -->
<!--         			</th> -->
<!-- 					<th> -->
<!--         				<a href="< ?=${LINK_HEAD.ENABLED}?>">STATE</a> -->
<!--         				<i class="< ?=${CLASS_HEAD.ENABLED}?>"></i> -->
<!--         			</th> -->
<!--         			< ?php if (${VIEW_ROLE}): ?> -->
<!--             			<th> -->
<!--             				<a href="< ?=${LINK_HEAD.ROLE}?>">ROLE</a> -->
<!--             				<i class="< ?=${CLASS_HEAD.ROLE}?>"></i> -->
<!--             			</th> -->
<!--         			< ?php endif; ?> -->
<!--         			<th> -->
<!--         			</th> -->
        		</tr>
        	</thead>
        	<tbody>
        	<?php
        	if (!empty(${RESULT})):
        	   foreach (${RESULT} as $row): 
        	   ?>
       				<tr>
    					<?php foreach (${COLUMN_LIST} as $count => $col): ?>
            	        	<td class="align-middle">
        	        			<?php if ($count === 0): ?>
                	        		<a href="/<?=UMS_TABLES_ROUTE.'/'.GET_ROUTE."/${TABLE}/".$row->$col?>"><?=$row->$col?></a>
        	        			<?php
                                else:
                                    echo $row->$col;
                                endif;
                                ?>
        	        		</td>
                	    <?php endforeach; ?>
       				</tr>
        	    <?php
        	    endforeach;
            else: ?>
        	    <tr><td colspan="9" class="text-center"><h2>No records</h2></td></tr>
            <?php endif; ?>
        	</tbody>
        </table>
    </div>
    <?php require PAGINATION_TEMPLATE; ?>
</div>