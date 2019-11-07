 <h1 class="text-center p-3 mx-auto my-auto">ERROR</h1>
 <?php if($showMessageException): ?>
 	<div class="container-fluid">
        <div class="table-responsive col-md-4  mx-auto">
            <table class="table table-striped" id="users-table">
            	<tbody>
            		<tr>
            			<th class="text-center" colspan="2"><?=$exception['toString']?></th>
            		</tr>
            		<tr>
            			<td class="text-primary">Code</td>
            			<td><?=$exception['code']?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">Message</td>
            			<td><?=$exception['message']?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">File</td>
            			<td><?=$exception['file']?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">Line</td>
            			<td><?=$exception['line']?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">Previous</td>
            			<td><?=$exception['previous']?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">Trace</td>
            			<td><?=$exception['traceString']?></td>
        			</tr>
            	</tbody>
            </table>
        </div>
    </div>
 <?php endif; ?>
 