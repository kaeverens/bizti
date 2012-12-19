function showPayments() {
	var $wrapper=$('#payments').empty();
	var html='<table><thead><tr><th>ID</th><th>Date</th>'
		+'<th>Invoice</th><th>Customer</th>'
		+'<th>Amount</th><th>Actions</th></tr></thead>'
		+'<tbody/></table>';
	var $paymentsTable=$(html).appendTo($wrapper).dataTable({
		'bJQueryUI':true,
		'aaSorting':[[1, 'asc']],
		'sAjaxSource':'/php/payments-get-dt.php',
		'sScrollY':'400px',
		'sDom':'frtiS',
		'aoColumns':[
			{'bVisible':false}, null, null, {'sWidth':'200px'}, null, null
		],
		'bDeferRender':true,
		'bProcessing':true,
		'bServerSide':true,
		'fnRowCallback':function(nRow, aData, iDisplayIndex) {
			// { date
			$('td:nth-child(1)', nRow).text(aData[1].replace(/ .*/, ''));
			// }
			// { customer
			$('td:nth-child(3)', nRow).text(getCustomerName(+aData[3]));
			// }
			// { amount
			aData[4]=+aData[4];
			$('td:nth-child(4)', nRow).addClass('price').html(currency(aData[4]));
			// }
			// { actions
			var $actions=$('td:nth-child(5)', nRow).empty();
			// { delete
			$actions.append(' ');
			var $delete=$('<a href="#" class="delete">[x]</a>')
				.click(function() {
					$.post(
						'/php/payment-delete.php',
						{
							'pid':aData[0]
						},
						function(ret) {
							if (ret.error) {
								return alert(ret.error);
							}
							$paymentsTable.fnDraw(1);
						}
					);
				});
			$actions.append($delete);
			// }
			// }
			return nRow;
		}
	});
}
