function showShares() {
	var $wrapper=$('#shares').empty();
	var html='<p>This section lets you share some of your data with other people'
		+'</p>'
		+'<table id="shares-table">'
		+'<thead><th>Type</th><th>Share Address</th><th>Notes</th></tr></thead>'
		+'<tbody><tr><td>Invoices</td>'
		+'<td data-type="invoices" class="todo invoices"/>'
		+'<td>Use this if you need to let someone (an accountant, for example) see'
		+' your invoices. Only invoices, not quotes, will be shared.</td></tr>'
		+'</tbody></table>';
	$wrapper.html(html);
	$('#shares-table').dataTable();
	$.post('/php/shares-get.php', function(ret) {
		var domain=document.location.toString().replace(/\/[^\/]*$/, '/');
		$.each(ret, function(k, v) {
			var url=domain+'?share='+v.type+'/'+v.user_id+'/'+v.md5;
			$('#shares .'+v.type)
				.removeClass('todo')
				.text(url)
				.append('<button class="reset">Reset</button>')
				.append('<button class="delete">Delete</button>');
		});
		$('#shares .todo').each(function() {
			$(this).removeClass('.todo')
				.append('<button class="reset">Create Share</button>');
		});
		$('button.reset').click(function() {
			var type=$(this).closest('td').data('type');
			$.post('/php/share-reset.php?type='+type, showShares);
		});
		$('button.delete').click(function() {
			var type=$(this).closest('td').data('type');
			$.post('/php/share-delete.php?type='+type, showShares);
		});
	});
}
/*
mysql> describe shares;
+---------+-------------+------+-----+---------+-------+
| Field   | Type        | Null | Key | Default | Extra |
+---------+-------------+------+-----+---------+-------+
| user_id | int(11)     | YES  |     | 0       |       |
| type    | varchar(32) | YES  |     | NULL    |       |
| md5     | char(32)    | YES  |     | NULL    |       |
+---------+-------------+------+-----+---------+-------+

*/
