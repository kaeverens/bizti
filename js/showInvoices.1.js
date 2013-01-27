function showInvoices() {
	function form(inv) {
		$.getScript('/js/showInvoices.form.php', function() {
			form=showInvoices.form;
			form(inv);
		});
	}
	function fillInCustomerNames(ret) {
		customerNames=ret;
		for (var i=0;i<customerNames.length;++i) {
			$('.customer-name.cid-'+customerNames[i].id)
				.html(customerNames[i].name);
		}
	}
	// { form
	var $wrapper=$('#invoices').empty();
	var html='<table><thead><tr><th>ID</th><th>Num</th>'
		+'<th>Customer</th><th>Date</th>'
		+'<th>Total</th><th>Paid</th>'
		+'<th>Owing</th><th>Age</th><th>Actions</th></tr></thead>'
		+'<tbody/></table>';
	// }
	var $invoicesTable=$(html).appendTo($wrapper).dataTable({
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
			$('<a href="#" class="add-payment'+cname+'">[pay]</a>')
				.click(function() {
					var $dialog=$('<table>'
						+'<tr><th>Amount Paid</th>'
						+'<td><input type="number" value="'+owed+'"/></td></tr>'
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
											'iid':aData[0],
											'amt':$dialog.find('input').val()
										},
										function(ret) {
											if (ret.error) {
												return alert(ret.error);
											}
											$invoicesTable.fnDraw(1);
											$dialog.remove();
										}
									);
								}
							}
						});
					return false;
				})
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
						form
					);
				});
			$actions.append($edit);
			// }
			// { print
			var print='&nbsp;<a href="/php/invoice-print.php?id='+aData[0]+'"'
				+' target="_blank" class="print">[print]</a>';
			$actions.append(print);
			// }
			// { pdf
			var print='&nbsp;<a href="/php/invoice-pdf.php?id='+aData[0]+'"'
				+' target="_blank" class="pdf">[pdf]</a>';
			$actions.append(print);
			// }
			// }
			return nRow;
		},
		'fnDrawCallback':function(oSettings) {
			fillInCustomerNames(customerNames);
		}
	});
	// { add invoice button
	var $addInvoice=$(
		'<button id="invoice-add">Create Invoice</button>'
	)
		.click(function() {
			form({
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
