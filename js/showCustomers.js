function showCustomers() {
	function customerForm(ret) {
		var html='<table>'
			+'<tr><th>Name</th><td><input id="dialog-name"/></td>'
			+'<th>Email</th><td><input type="email" id="dialog-email"/></td></tr>'
			+'<tr><th>Phone</th><td><input id="dialog-phone"/></td>'
			+'<th rowspan="2">Notes</th><td rowspan="2">'
			+'<textarea style="height:100%" id="dialog-notes"/></td></tr>'
			+'<tr><th>Address</th><td><textarea id="dialog-address"/></td></tr>'
			+'</table>';
		var $dialog=$(html).dialog({
			'title':'Edit Customer',
			'width':'640px',
			'modal':true,
			'close':function() {
				$dialog.remove();
			},
			'buttons':{
				'Save':function() {
					$.post(
						'/php/customer-edit.php',
						{
							'id':ret.id,
							'name':$('#dialog-name').val(),
							'phone':$('#dialog-phone').val(),
							'email':$('#dialog-email').val(),
							'notes':$('#dialog-notes').val(),
							'address':$('#dialog-address').val()
						},
						function(ret) {
							$dialog.remove();
							$customersTable.fnDraw(1);
						}
					);
				}
			}
		});
		$('#dialog-name').val(ret.name);
		$('#dialog-phone').val(ret.meta.phone);
		$('#dialog-address').val(ret.meta.address);
		$('#dialog-email').val(ret.meta.email);
		$('#dialog-notes').val(ret.meta.notes);
	}
	var $wrapper=$('#customers').empty();
	var html='<table><thead><tr><th>ID</th><th>Name</th>'
		+'<th>Invoices</th><th>Total Due</th><th>Amt Owed</th>'
		+'<th>Actions</th></tr></thead>'
		+'<tbody/></table>';
	var $customersTable=$(html).appendTo($wrapper).dataTable({
		'bJQueryUI':true,
		'aaSorting':[[1, 'asc']],
		'sAjaxSource':'/php/customers-get-dt.php',
		'sScrollY':'400px',
		'sDom':'frtiS',
		'aoColumns':[
			{'bVisible':false}
			, {'sWidth':'200px'}, null, null, null, null
		],
		'bDeferRender':true,
		'bProcessing':true,
		'bServerSide':true,
		'fnRowCallback':function(nRow, aData, iDisplayIndex) {
			aData[2]=+aData[2];
			if (aData[3]===null) {
				aData[3]='0';
			}
			aData[3]=+aData[3];
			$('td:nth-child(3)', nRow).addClass('price').html(currency(aData[3]));
			if (aData[4]===null) {
				aData[4]='0';
			}
			aData[4]=+aData[4];
			$('td:nth-child(4)', nRow).addClass('price').html(currency(aData[4]));
			// { actions
			var $actions=$('td:nth-child(5)', nRow).empty();
			// { edit
			var $edit=$('<a href="#" class="edit">[edit]</a>')
				.click(function() {
					$.post(
						'/php/customer-get.php',
						{
							'cid':aData[0]
						},
						customerForm
					);
				});
			$actions.append($edit);
			// }
			// { delete
			if (aData[2]<=0) {
				$actions.append(' ');
				var $delete=$('<a href="#" class="delete">[x]</a>')
					.click(function() {
						$.post(
							'/php/customer-delete.php',
							{
								'cid':aData[0]
							},
							function(ret) {
								if (ret.error) {
									return alert(ret.error);
								}
								$.post('/php/get-customer-names.php', function(ret) {
									customerNames=ret;
									$customersTable.fnDraw(1);
								});
							}
						);
					});
				$actions.append($delete);
			}
			// }
			// }
			return nRow;
		}
	});
	var $addCustomer=$(
		'<button id="customer-add">Create Customer</button>'
	)
		.click(function() {
			customerForm({
				'id':0,
				'name':'',
				'meta':{}
			});
			return false;
		})
		.insertBefore(
			$wrapper.find('>div.dataTables_wrapper>div:first-child')
		);
}
