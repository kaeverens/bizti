function showTasks() {
	var priorities=['not important', 'normal', 'important'];
	var statii=['to do', 'done'];
	function editTask(task) {
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
							$tasksTable.fnDraw(1);
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
	var $wrapper=$('#tasks').empty();
	var html='<table><thead><tr><th>ID</th><th>Description</th>'
		+'<th>Customer</th><th>Priority</th><th>Status</th>'
		+'<th>Actions</th></tr></thead>'
		+'<tbody/></table>';
	var $tasksTable=$(html).appendTo($wrapper).dataTable({
		'bJQueryUI':true,
		'aaSorting':[[1, 'asc']],
		'sAjaxSource':'/php/tasks-get-dt.php',
		'sScrollY':'400px',
		'sDom':'frtip',
		'aoColumns':[
			{'bVisible':false}, null, null, null, null, null
		],
		'bDeferRender':true,
		'bProcessing':true,
		'bServerSide':true,
		'fnRowCallback':function(nRow, aData, iDisplayIndex) {
			// { actions
			var $actions=$('td:nth-child(5)', nRow).empty();
			$('td:nth-child(3)', nRow).html(priorities[+aData[3]+1]);
			$('td:nth-child(4)', nRow).html(statii[+aData[4]]);
			// { edit
			var $edit=$('<a href="#" class="edit">[edit]</a>')
				.click(function() {
					$.post(
						'/php/task-get.php',
						{
							'id':aData[0]
						},
						editTask
					);
				});
			$actions.append($edit);
			// }
			// }
			return nRow;
		}
	});
	var $addTask=$(
		'<button id="task-add">Create Task</button>'
	)
		.click(function() {
			editTask({
				'id':0,
				'status':0,
				'meta':'{}',
				'description':'',
				'priority':0,
				'customer_id':0
			});
			return false;
		})
		.insertBefore(
			$wrapper.find('>div.dataTables_wrapper>div:first-child')
		);
}
