function portletTasks() {
	$.post('/php/tasks-portlet-get.php', function(ret) {
		var $p=$('#portlet-tasks');
		var $body=$p.find('.body').empty();
		// { control
		var $controls=$('<div class="controls"/>');
		var $addNew=$('<button>New Task</button>')
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
			});
		$controls.append($addNew);
		$body.append($controls);
		// }
		// { table
		var table='<table>';
		for (var i=0;i<ret.length;++i) {
			var name=ret[i].name||'';
			console.log(ret[i]);
			table+='<tr><td><a href="#" data-id="'+ret[i].id+'">'
				+htmlspecialchars(ret[i].description)+'</a></td>'
				+'<td>'+htmlspecialchars(name)+'</td></tr>';
		}
		table+='</table>';
		var $table=$(table);
		$table
			.find('a').click(function() {
				console.log($(this).data('id'));
				$.post(
					'/php/task-get.php',
					{
						'id':$(this).data('id')
					},
					taskEdit
				);
				return false;
			});
		$table.appendTo($body);
		// }
	});
}
