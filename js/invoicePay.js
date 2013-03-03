function invoicePay() {
	var $this=$(this);
	var $dialog=$('<table>'
		+'<tr><th>Amount Paid</th>'
		+'<td><input type="number" value="'+$this.data('owed').toFixed(2)+'"/></td></tr>'
		+'</table>')
		.dialog({
			'modal':'true',
			'title':'Record a Payment',
			'close':function() {
				$(this).remove();
			},
			'buttons':{
				'Save':function() {
					$.post(
						'/php/payment-create.php',
						{
							'iid':$this.data('iid'),
							'amt':$dialog.find('input').val()
						},
						function(ret) {
							if (ret.error) {
								return alert(ret.error);
							}
							if (bizti.invoicesTable) {
								bizti.invoicesTable.fnDraw(1);
							}
							if (document.getElementById('portlet-outstanding-invoices')) {
								portletOutstandingInvoices();
							}
							$dialog.remove();
						}
					);
				}
			}
		});
	return false;
}
