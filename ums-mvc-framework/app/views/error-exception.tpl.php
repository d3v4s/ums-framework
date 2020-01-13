 <h1 class="text-center p-3 mx-auto my-auto">ERROR</h1>
 <?php if(SHOW_MESSAGE_EXCEPTION): ?>
 	<div class="container-fluid">
        <div class="table-responsive col-md-4  mx-auto">
            <table class="table table-striped" id="users-table">
            	<tbody>
            		<tr>
            			<th class="text-center" colspan="2"><?=${EXCEPTION}[TO_STRING]?></th>
            		</tr>
            		<tr>
            			<td class="text-primary">Code</td>
            			<td><?=${EXCEPTION}[CODE]?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">Message</td>
            			<td><?=${EXCEPTION}[MESSAGE]?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">File</td>
            			<td><?=${EXCEPTION}[FILE]?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">Line</td>
            			<td><?=${EXCEPTION}[LINE]?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">Previous</td>
            			<td><?=${EXCEPTION}[PREVIOUS]?></td>
        			</tr>
        			<tr>
            			<td class="text-primary">Trace</td>
            			<td><?=${EXCEPTION}[TRACE_STRING]?></td>
        			</tr>
            	</tbody>
            </table>
        </div>
    </div>
 <?php endif; ?>
 