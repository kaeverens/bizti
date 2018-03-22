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
					+'<h3>'+htmlspecialchars(p.type)+'</h3>'
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
						$portlet.attr('id', 'portlet-outstanding-invoices');
						return portletOutstandingInvoices();
					case 'Tasks':
						$portlet.attr('id', 'portlet-tasks');
						return portletTasks();
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
