function portletOutstandingInvoices() {
	$.post('/php/invoices-outstanding-get.php', function(ret) {
		var $p=$('#portlet-outstanding-invoices');
		var html='<table>';
		for (var i=0;i<ret.length;++i) {
			html+='<tr data-id="'+ret[i].id+'">'
				+'<td><a href="#" class="invoice">'+htmlspecialchars(ret[i].name)+'</a></td>'
				+'<td class="currency">'+htmlspecialchars(currency(ret[i].amt))+'</td>'
				+'<td style="width:1%"><a href="#" data-iid="'+ret[i].id+'"'
				+' data-owed="'+ret[i].amt+'" class="add-payment">[pay]</a></td>'
				+'</tr>';
		}
		html+='</table>';
		$p.find('.body').html(html);
		$p.find('.add-payment').click(invoicePay);
		$p.find('.invoice').click(function() {
			var id=+$(this).closest('tr').data('id');
			$.post(
				'/php/invoice-get.php',
				{
					'iid':id
				},
				showInvoiceForm
			);
			return false;
		});
	});
}
