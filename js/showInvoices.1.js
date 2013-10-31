function showInvoices() {
	function fillInCustomerNames(ret) {
		customerNames=ret;
		for (var i=0;i<customerNames.length;++i) {
			$('.customer-name.cid-'+customerNames[i].id)
				.html(customerNames[i].name);
		}
	}
	// { form
	var $wrapper=$('#invoices').empty();
	var html='<table><thead>'
		+'<tr title="search" class="search"><td></td>'
		+'<td><input type="number"/></td><td><input/></td>'
		+'<td><input type="date"/></td><td><input type="number"/></td>'
		+'<td><input type="number"/></td><td><input type="number"/></td>'
		+'<td>&nbsp;</td><td>&nbsp;</td></tr>'
		+'<tr><th>ID</th><th>Num</th>'
		+'<th>Customer</th><th>Date</th>'
		+'<th>Total</th><th>Paid</th>'
		+'<th>Owing</th><th>Age</th><th>Actions</th></tr></thead>'
		+'<tbody/></table>';
	// }
	bizti.invoicesTable=$(html).appendTo($wrapper).dataTable({
		'bJQueryUI':true,
		'aaSorting':[[1, 'desc']],
		'sAjaxSource':'/php/invoices-get-dt.php',
		'sDom':'frtip',
		'aoColumns':[
			{'bVisible':false}
			, null, null, {'sWidth':'80px'}, null
			, null, null, null, null
		],
		'bDeferRender':true,
		'bProcessing':true,
		'bServerSide':true,
		'fnRowCallback':function(nRow, aData, iDisplayIndex) {
			var owed=parseInt((aData[4]-aData[5])*100)/100;
			// { customer
			$('td:nth-child(2)', nRow).html(
				'<span class="customer-name cid-'+aData[2]+'">...</span>'
			);
			// }
			// { total
			var cname=owed?'':' fully-paid';
			$('td:nth-child(4)', nRow).addClass('price').html(currency(aData[4]));
			// }
			// { paid
			$('td:nth-child(5)', nRow).addClass('price').html(currency(aData[5]));
			// }
			// { owing
			var $td=$('td:nth-child(6)', nRow).addClass('price')
				.html('<span class="price">'+currency(owed)+'</span>&nbsp;');
			$('<a href="#" data-iid="'+aData[0]+'" data-owed="'+owed+'"'
				+' class="add-payment'+cname+'">[pay]</a>')
				.click(invoicePay)
				.appendTo($td);
			// }
			// { age
			var str='';
			if (owed) {
				var str='<span class="age-old">OLD!!!</span>';
				var age=+aData[7];
				if (age<7) {
					str='<span class="age-this-week">new</span>';
				}
				else if (age<14) {
					str='<span class="age-fortnight">&gt;1&nbsp;week</span>';
				}
				else if (age<30) {
					str='<span class="age-month">&gt;2&nbsp;weeks</span>';
				}
				else if (age<183) {
					str='<span class="age-six-months">&gt;4&nbsp;weeks</span>';
				}
			}
			$('td:nth-child(7)', nRow).html(str);
			// }
			// { actions
			var $actions=$('td:nth-child(8)', nRow).empty();
			// { edit
			var $edit=$('<a href="#" class="edit">[edit]</a>')
				.click(function() {
					$.post(
						'/php/invoice-get.php',
						{
							'iid':aData[0]
						},
						showInvoiceForm
					);
					return false;
				});
			$actions.append($edit);
			// }
			// { print
			$actions.append('&nbsp;');
			$('<a href="#">[print]</a>')
				.click(function() {
					window.open('/php/invoice-print.php?id='+aData[0]);
					return false;
				})
				.appendTo($actions);
			// }
			// { pdf
			$actions.append('&nbsp;');
			$('<a href="#">[pdf]</a>')
				.click(function() {
					window.open('/php/invoice-pdf.php?id='+aData[0]);
					return false;
				})
				.appendTo($actions);
			// }
			// }
			return nRow;
		},
		'fnDrawCallback':function(oSettings) {
			fillInCustomerNames(customerNames);
		}
	});
	var $searchInputs=$('#invoices .dataTable .search input');
	var searchFunc=function() {
		bizti.invoicesTable.fnFilter(this.value, $searchInputs.index(this) );
	};
	$searchInputs.change(searchFunc);
	// { add invoice button
	var $addInvoice=$(
		'<button id="invoice-add">Create Invoice</button>'
	)
		.click(function() {
			showInvoiceForm({
				'customer_id':0,
				'date':(new Date).toYMD(),
				'notes':'',
				'products':[],
				'id':0
			});
			return false;
		})
		.insertBefore(
			$wrapper.find('>div.dataTables_wrapper>div:first-child')
		);
	// }
	// { import invoices button
	var $importInvoices=$(
		'<button id="invoices-import">Import Invoices</button>'
	)
		.click(function() {
			invoicesImport();
		})
		.insertAfter('#invoice-add');
	// }
}
