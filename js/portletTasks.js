function portletTasks() {
	var $p=$('#portlet-tasks');
	$.post('/php/tasks-portlet-get.php', function(ret) {
		var html='<table>';
		for (var i=0;i<ret.length;++i) {
			var name=ret[i].name||'';
			html+='<tr><td><a href="#" data-id="'+ret[i].id+'">'
				+ret[i].description+'</a></td>'
				+'<td>'+name+'</td></tr>';
		}
		html+='</table>';
		$p.find('.body').html(html)
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
	});
}
