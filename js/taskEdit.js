function taskEdit(task) {
	task.meta=JSON.parse(task.meta);
	var html='<div id="task-popup"><table>'
		+'<tr><th style="width:10%">Description</th>'
		+'<td style="width:10%"><input id="task-description"/></td>'
		+'<th>notes</th></tr>'
		+'<tr><th>Customer</th><td><select id="task-customer"/></td>'
		+'<td rowspan="3"><textarea id="task-notes"></textarea></td></tr>'
		+'<tr><th>Priority</th><td><select id="task-priority">'
		+'<option value="-1">not important</option>'
		+'<option value="0">normal</option>'
		+'<option value="1">important</option>'
		+'</select></td></tr>'
		+'<tr><th>Status</th><td><select id="task-status">'
		+'<option value="0">to do</option><option value="1">done</option>'
		+'</select></td></tr>'
		+'</table></div>';
	$(html).dialog({
		'modal':true,
		'close':function() {
			$(this).remove();
		},
		'buttons':{
			'Save':function() {
				$.post(
					'/php/task-edit.php',
					{
						'id':task.id,
						'description':$('#task-description').val(),
						'notes':$('#task-notes').val(),
						'customer_id':$('#task-customer').val(),
						'priority':$('#task-priority').val(),
						'status':$('#task-status').val()
					},
					function() {
						$('#task-popup').remove();
						if (bizti.tasksTable) {
							bizti.tasksTable.fnDraw(1);
						}
						if ($('#portlet-tasks').length) {
							portletTasks();
						}
					}
				);
			}
		},
		'width':'600'
	});
	$('#task-description').val(task.description);
	$('#task-priority').val(task.priority);
	$('#task-status').val(task.status);
	// { customers
	var opts=['<option value="0"> -- none -- </option>'];
	for (var i=0;i<window.customerNames.length;++i) {
		var cust=window.customerNames[i];
		opts.push('<option value="'+cust.id+'">'+cust.name+'</option>');
	}
	$('#task-customer').html(opts.join('')).val(task.customer_id);
	// }
	$('#task-notes').val(task.meta.notes||'');
}
