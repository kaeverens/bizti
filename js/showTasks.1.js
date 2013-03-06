function showTasks() {
	var priorities=['not important', 'normal', 'important'];
	var statii=['to do', 'done'];
	var $wrapper=$('#tasks').empty();
	var html='<table><thead><tr><th>ID</th><th>Description</th>'
		+'<th>Customer</th><th>Priority</th><th>Status</th>'
		+'<th>Actions</th></tr></thead>'
		+'<tbody/></table>';
	bizti.tasksTable=$(html).appendTo($wrapper).dataTable({
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
						taskEdit
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
			taskEdit({
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
