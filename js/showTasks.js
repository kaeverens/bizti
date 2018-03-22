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
			$('td:nth-child(1)', nRow).text(aData[1]);
			$('td:nth-child(2)', nRow).text(aData[2]);
			$('td:nth-child(3)', nRow).html(priorities[+aData[3]+1]);
			$('td:nth-child(4)', nRow).html(statii[+aData[4]]);
			// { counter
			var startstop=aData[7]==1?'stop':'start';
			var $counter=$('<span>[<a href="#" class="counter">'+startstop+'</a>]</span>')
				.click(function(){
					$.post(
						'/php/task-counter.php',
						{
							'id':aData[0],
							'action':$('a',$(this)).html()
						},
						function() {
							bizti.tasksTable.fnDraw(1);
						}
					);
				});
			window.counter;
			function format_t(seconds){
				var date=new Date(seconds*1000);
				var hh=date.getHours();
				var mm=date.getMinutes();
				var ss=date.getSeconds();
				if (hh < 10) hh = "0"+hh;
				if (mm < 10) mm = "0"+mm;
				if (ss < 10) ss = "0"+ss;
				return hh+":"+mm+":"+ss;
			}
			var time=aData[6]=="0"?'00:00:00':format_t(aData[6]);
			var timeClass=aData[6]=='0'?'time disabled':'time';
			$('td:nth-child(5)', nRow)
				.html('<i class="'+timeClass+'">'+time+'</i>')
				.append($counter);
			if(aData[7]==1){
				counter.counts[aData[0]]=aData[6];
				counter.rows[aData[0]]=$('td:nth-child(5)', nRow);
				if(typeof(counter.interval)=='undefined'){
					function update_t(){
						for(var i in window.counter.counts){
							$('.time', counter.rows[i])
								.addClass('active')
								.html(format_t(counter.counts[i]++));
						}
					}
					update_t();
					window.counter.interval=setInterval(update_t,1000);
				}
			}	
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
