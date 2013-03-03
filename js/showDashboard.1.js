function showDashboard() {
	var priorities=['not important', 'normal', 'important'];
	var statii=['to do', 'done'];
	var $wrapper=$('#dashboard').empty();
	$.post(
		'/php/portlets-get.php',
		{
			'page':'dashboard'
		},
		function(ret) {
			if (ret.length==[]) {
				ret=[
					{
						'width':350, 'height':200,
						'type':'Outstanding Invoices'
					},
					{
						'width':350, 'height':200,
						'type':'Tasks'
					}
				];
			}
			$.each(ret, function(k, p) {
				var html='<div style="width:'+p.width+'px;height:'+p.height+'px;"'
					+' class="portlet">'
					+'<h3>'+p.type+'</h3>'
					+'<div class="body"/></div>';
				var $portlet=$(html)
					.appendTo($wrapper)
					.resizable({
						'maxWidth':750,
						'minWidth':200,
						'minHeight':120,
						'stop':save
					});
				switch(p.type) {
					case 'Outstanding Invoices':
						return portletOutstandingInvoices($portlet);
					case 'Tasks':
						return portletTasks($portlet);
					default:
						console.log('unknown portlet: '+p.type);
				}
			});
			$wrapper
				.sortable({
					'handle':'h3',
					'stop':save,
					'placeholder':'placeholder'
				})
				.disableSelection();
		}
	);
	function portletOutstandingInvoices($p) {
		$.post('/php/invoices-outstanding-get.php', function(ret) {
			var html='<table>';
			for (var i=0;i<ret.length;++i) {
				html+='<tr data-id="'+ret[i].id+'">'
					+'<td class="invoice clickable">'+ret[i].name+'</td>'
					+'<td class="currency">'+currency(ret[i].amt)+'</td></tr>';
			}
			html+='</table>';
			$p.find('.body').html(html);
			$p.find('.invoice').click(function() {
				var id=+$(this).closest('tr').data('id');
				$.post(
					'/php/invoice-get.php',
					{
						'iid':id
					},
					showInvoiceForm
				);
			});
		});
	}
	function portletTasks($p) {
		$.post('/php/tasks-portlet-get.php', function(ret) {
			var html='<table>';
			for (var i=0;i<ret.length;++i) {
				var name=ret[i].name||'';
				html+='<tr><td>'+ret[i].description+'</td>'
					+'<td>'+name+'</td></tr>';
			}
			html+='</table>';
			$p.find('.body').html(html);
		});
	}
	function save() {
		var $portlets=$wrapper.find('>div');
		var portlets=[];
		$portlets.each(function() {
			var $this=$(this);
			portlets.push({
				'width':Math.ceil($this.width()),
				'height':Math.ceil($this.height()),
				'type':$this.find('>h3').text()
			});
		})
		$.post('/php/portlets-save.php', {
			'portlets':JSON.stringify(portlets),
			'page':'dashboard'
		});
	}
}
