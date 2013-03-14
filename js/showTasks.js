function showTasks() {
	if(typeof(window.counter)=='undefined') window.counter={};
	counter.counts={};
	counter.rows={};
	var priorities=['not important', 'normal', 'important'];
	var statii=['to do', 'done'];
	var $wrapper=$('#tasks').empty();
	var html='<table><thead><tr><th>ID</th><th>Description</th>'
		+'<th>Customer</th><th>Priority</th><th>Status</th>'
		+'<th>Counter</th><th>Actions</th></tr></thead>'
		+'<tbody/></table>';
	bizti.tasksTable=$(html).appendTo($wrapper).dataTable({
		'bJQueryUI':true,
		'aaSorting':[[1, 'asc']],
		'sAjaxSource':'/php/tasks-get-dt.php',
		'sScrollY':'400px',
		'sDom':'frtip',
		'aoColumns':[
			{'bVisible':false}, null, null, null, null, null, null
		],
		'bDeferRender':true,
		'bProcessing':true,
		'bServerSide':true,
		'fnRowCallback':function(nRow, aData, iDisplayIndex) {
			// { actions
			var $actions=$('td:nth-child(6)', nRow).empty();
			$('td:nth-child(3)', nRow).html(priorities[+aData[3]+1]);
			$('td:nth-child(4)', nRow).html(statii[+aData[4]]);
			// { counter
			var $counter=$('<span>[<a href="#" class="counter">'+((aData[7]==1)?'stop':'start')+'</a>] </span>')
				.click(function(){
					$.post(
						'/php/task-counter.php',
						{
							'id':aData[0],
							'action':$('a',$(this)).html()
						},
						showTasks
					);
				});
			window.counter;
			function format_t(seconds){
				var date=new Date(seconds*1000);
				var hh=date.getHours()-1;
				var mm=date.getMinutes();
				var ss=date.getSeconds();
				if (hh < 10) hh = "0"+hh;
				if (mm < 10) mm = "0"+mm;
				if (ss < 10) ss = "0"+ss;
				return hh+":"+mm+":"+ss;
			}
			if(aData[7]==1){
				if(typeof(counter.interval)=='undefined'){
					counter.interval=setInterval(function(){
						for(var i in window.counter.counts){
							counter.rows[i].html(format_t(counter.counts[i]++));
						}
					},1000);
				}
				counter.counts[aData[0]]=aData[6];
				counter.rows[aData[0]]=$('td:nth-child(5)', nRow);
			}	
			$('td:nth-child(5)', nRow).html((aData[6]=="0")?"00:00:00":format_t(aData[6]));
			// }
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
			$actions.append($counter).append($edit);
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
