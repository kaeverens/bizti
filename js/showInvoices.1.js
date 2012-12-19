function showInvoices() {
	function invoiceForm(inv) {
		// { form layout
		var html='<div><table style="width:100%"><tr>'
			+'<th>Customer</th><td><select id="dialog-customer-id"/></td>'
			+'<th>Date</th><td><input type="datepicker" id="dialog-date"/></td>'
			+'<th>Inv. Num</th><td><input type="number" id="dialog-num"/></td>'
			+'</tr></table>'
			+'<table id="dialog-table"><thead><tr><th>Quantity</th><th>Product</th>'
			+'<th>Tax</th><th>Unit Price</th><th>&nbsp;</th></tr></thead><tbody/>'
			+'</table>'
			+'<label>Notes</label><br/>'
			+'<textarea style="width:100%;height:150px" name="dialog-notes"'
			+' id="dialog-notes"/>';
		if (inv.id) {
			html+='<input type="checkbox" id="dialog-delete"/> delete invoice';
		}
		html+='</div>';
		// }
		var $dialog=$(html).dialog({
			'title':'Edit Invoice',
			'width':'90%',
			'modal':true,
			'close':function() {
				$dialog.remove();
			},
			'buttons':{
				'Save':function() {
					var del=$('#dialog-delete:checked');
					if (del.length) {
						var html='<div><p>You have selected to delete this invoice.</p>'
							+'<p>Are you sure you want to delete this invoice?</p></div>';
						var $del=$(html).dialog({
							'title':'Delete invoice',
							'modal':true,
							'close':function() {
								$(this).remove();
							},
							'buttons':{
								'Delete':function() {
									$.post(
										'/php/invoice-delete.php',
										{
											'iid':inv.id
										},
										function() {
											$del.remove();
											$dialog.remove();
											$invoicesTable.fnDraw(1);
										}
									);
								},
								'No! Do not delete!':function() {
									$del.remove();
								}
							}
						});
						return false;
					}
					var customer_id=$('#dialog-customer-id').val(),
						cdate=$('#dialog-date').val(),
						notes=CKEDITOR.instances['dialog-notes'].getData(),
						products=[];
					$productsTable.find('tr').each(function(k, v) {
						var $tr=$(this), product={
							'quantity':+$('.quantity', $tr).val(),
							'product':+$('.product', $tr).val(),
							'tax':+$('.tax', $tr).val(),
							'price':+$('.price', $tr).val()
						};
						if (product.product) {
							products.push(product);
						}
					});
					$.post('/php/invoice-edit.php', {
						'customer_id':customer_id,
						'cdate':cdate,
						'notes':notes,
						'products':products,
						'num':$('#dialog-num').val(),
						'id':inv.id
					}, function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						$dialog.remove();
						$invoicesTable.fnDraw(1);
					});
				}
			}
		});
		// { customers
		function showCustomers(ret) {
			customerNames=ret;
			var opts='<option value="0"> -- choose -- </option>';
			for (var i=0;i<customerNames.length;++i) {
				opts+='<option value="'+customerNames[i].id+'">'
					+customerNames[i].name+'</option>';
			}
			opts+='<option value="-1"> -- Add New Customer -- </option>';
			$('#dialog-customer-id').html(opts).val(inv.customer_id);
		}
		showCustomers(customerNames);
		$('#dialog-customer-id')
			.change(function() {
				var $this=$(this);
				if ($this.val()!='-1') {
					return;
				}
				$this.val('0');
				var html='<table><tr><th>Customer Name</th><td>'
					+'<input id="popup-customer-name"/></td></tr></table>';
				var $customerDialog=$(html).dialog({
					'modal':true,
					'close':function() {
						$customerDialog.remove();
					},
					'buttons':{
						'Create Customer':function() {
							$.post('/php/create-customer.php', {
								'name':$('#popup-customer-name').val()
							}, function(ret) {
								if (ret.error) {
									return alert(ret.error);
								}
								customerNames.push(ret);
								inv.customer_id=+ret.id;
								$customerDialog.remove();
								showCustomers(customerNames);
							});
						}
					}
				});
			});
		// }
		// { date
		$('#dialog-date')
			.val(inv.date)
			.datepicker({
				'dateFormat':'yy-mm-dd',
				'changeMonth':true,
				'changeYear':true
			});
		// }
		// { products
		var $productsTable=$('#dialog-table');
		function addProductRow(r) {
			// { form
			var row='<tr><td><input class="quantity" type="number"/></td>'
				+'<td><select class="product"><option value="0"> -- choose -- </option>'
				+'<option value="-1"> -- Add Product -- </option></select></td>'
				+'<td><select class="tax"><option value="0"> -- choose -- </option>'
				+'<option value="-1"> -- Add Tax -- </option></select></td>'
				+'<td><input class="price" type="number"/></td>'
				+'<td><a href="#" class="ui-icon ui-icon-circle-minus delete">[-]</a>'
				+'</td></tr>';
			// }
			var $row=$(row);
			$productsTable.find('tbody').append($row);
			$row.find('.quantity').append('<option value="'+r.quantity+'"></option>')
				.val(+r.quantity);
			if (r.product) {
				$row.find('.product')
					.append('<option class="remove-me" value="'+r.product+'"></option>')
					.val(+r.product);
			}
			if (r.tax) {
				$row.find('.tax')
					.append('<option class="remove-me" value="'+r.tax+'"></option>')
					.val(+r.tax);
			}
			$row.find('.price').val(+r.price);
			updateTaxes(taxes, showTax);
			updateProducts(products, showProduct());
		}
		function showProduct() {
			var opts='<option value="0"> -- choose -- </option>';
			for (var i=0;i<products.length;++i) {
				opts+='<option value="'+products[i].id+'">'
					+products[i].name+'</option>';
			}
			opts+='<option value="-1"> -- Add Product -- </option>';
			$productsTable.find('.product').each(function() {
				var $this=$(this);
				var val=$this.val();
				$this.html(opts).val(val);
				$this.change(function() {
					var $this=$(this);
					var val=+$this.val();
					if (val!='-1') {
						$this.closest('tr').find('.price').val(productsById[val].price);
						$this.closest('tr').find('.tax').val(productsById[val].tax);
						return;
					}
					$this.val('0');
					// { form
					var html='<table><tr><th>Product Name</th><td>'
						+'<input id="popup-product-name"/></td></tr>'
						+'<tr><th>Unit Price</th><td>'
						+'<input type="number" id="popup-product-price"/></td></tr>'
						+'<tr><th>Default Tax</th><td><select id="popup-product-tax"/>'
						+'</td></tr>'
						+'</table>';
					// }
					var $productDialog=$(html).dialog({
						'title':'Create Product',
						'modal':true,
						'close':function() {
							$productDialog.remove();
						},
						'buttons':{
							'Create Product':function() {
								$.post('/php/product-create.php', {
									'name':$('#popup-product-name').val(),
									'tax':$('#popup-product-tax').val(),
									'price':+$('#popup-product-price').val()
								}, function(ret) {
									if (ret.error) {
										return alert(ret.error);
									}
									products.push(ret);
									$productDialog.remove();
									productsById=[];
									for (var i=0;i<products.length;++i) {
										productsById[products[i].id]=products[i];
									}
									showProduct();
									$this.val(ret.id);
									$this.closest('tr').find('.price').val(ret.price);
									$this.closest('tr').find('.tax').val(ret.tax);
									$this.change();
								});
							}
						}
					});
					var opts=[];
					$.each(taxesById, function(k, v) {
						if (!v) {
							return;
						}
						var $opt=$('<option value="'+k+'"/>')
							.text(v?v.name+' ('+v.percentage+'%)':'');
						opts.push($opt);
					});
					opts.push($('<option value="-1"> -- Add Tax -- </option>'));
					var $tax=$('#popup-product-tax').append(opts);
					$tax.change(function() {
						var $this=$(this);
						var val=+$this.val();
						if (val!='-1') {
							return;
						}
						$this.val('0');
						var html='<table><tr><th>Tax Name</th><td>'
							+'<input id="popup-tax-name"/></td></tr>'
							+'<tr><th>Percentage</th><td>'
							+'<input type="number" id="popup-tax-percentage"/></td></tr>'
							+'</table>';
						var $taxDialog=$(html).dialog({
							'modal':true,
							'close':function() {
								$taxDialog.remove();
							},
							'buttons':{
								'Create Tax':function() {
									$.post('/php/create-tax.php', {
										'name':$('#popup-tax-name').val(),
										'percentage':+$('#popup-tax-percentage').val()
									}, function(ret) {
										if (ret.error) {
											return alert(ret.error);
										}
										taxes.push(ret);
										$taxDialog.remove();
										updateTaxes(taxes, showTax);
										$('<option value="'+ret.id+'">'+ret.name+'</option>')
											.insertBefore($tax.find('option:last-child'));
										$tax.val(ret.id);
									});
								}
							}
						});
					});
				});
			});
		}
		function showTax() {
			var opts='<option value="0"> -- choose -- </option>';
			for (var i=0;i<taxes.length;++i) {
				opts+='<option value="'+taxes[i].id+'">'
					+taxes[i].name+' ('+taxes[i].percentage+'%)</option>';
			}
			opts+='<option value="-1"> -- Add Tax -- </option>';
			$productsTable.find('.tax').each(function() {
				var $this=$(this);
				var val=$this.val();
				$this.html(opts).val(val);
				$this.change(function() {
					var $this=$(this);
					var val=+$this.val();
					if (val!='-1') {
						return;
					}
					$this.val('0');
					var html='<table><tr><th>Tax Name</th><td>'
						+'<input id="popup-tax-name"/></td></tr>'
						+'<tr><th>Percentage</th><td>'
						+'<input type="number" id="popup-tax-percentage"/></td></tr>'
						+'</table>';
					var $taxDialog=$(html).dialog({
						'modal':true,
						'close':function() {
							$taxDialog.remove();
						},
						'buttons':{
							'Create Product':function() {
								$.post('/php/create-tax.php', {
									'name':$('#popup-tax-name').val(),
									'percentage':+$('#popup-tax-percentage').val()
								}, function(ret) {
									if (ret.error) {
										return alert(ret.error);
									}
									taxes.push(ret);
									$taxDialog.remove();
									updateTaxes(taxes, showTax);
									$this.val(ret.id);
								});
							}
						}
					});
				});
			});
		}
		for (var i=0;i<inv.products.length;++i) {
			addProductRow(inv.products[i]);
		}
		$productsTable
			.on('click', '.delete', function() {
				$(this).closest('tr').remove();
				recountTableRows();
			})
			.on('change', recountTableRows);
		function recountTableRows() {
			var $rows=$productsTable.find('tbody>tr');
			var emptyFound=false;
			$rows.each(function(k, v) {
				var $this=$(this);
				if (+$('.quantity', $this).val()<=0 || $('.product', $this).val()=='0') {
					emptyFound++;
				}
			});
			if (emptyFound) {
				return;
			}
			addProductRow({
				'qty':0, 'product_id':0, 'tax':0, 'price':0
			});
		}
		recountTableRows();
		// }
		// { notes
		$('#dialog-notes').val(inv.notes);
		CKEDITOR.replace( 'dialog-notes', {
			'height':100,
			'extraPlugins': 'bbcode',
			'toolbar': [
				[ 'Undo', 'Redo' ],
				[ 'Link', 'Unlink' ],
				[ 'Bold', 'Italic', 'Underline' ],
				[ 'NumberedList', 'BulletedList', '-', 'Blockquote' ],
				[ 'TextColor', 'SpecialChar' ]
			],
		});
		// }
		// { invoice num
		if (+inv.num) {
			$('#dialog-num').val(inv.num);
		}
		else {
			$.post('/php/invoice-num-get-next.php', function(ret) {
				inv.num=ret.num;
				$('#dialog-num').val(ret.num);
			});
		}
		// }
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
		'sScrollY':'400px',
		'sDom':'frtiS',
		'aoColumns':[
			{'bVisible':false}
			, null, null, {'sWidth':'80px'}, null
			, null, null, null, null
		],
		'bDeferRender':true,
		'bProcessing':true,
		'bServerSide':true,
		'fnRowCallback':function(nRow, aData, iDisplayIndex) {
			// { customer
			$('td:nth-child(2)', nRow).html(
				'<span class="customer-name cid-'+aData[2]+'">...</span>'
			);
			// }
			// { total
			var cname=aData[4]-aData[5]?'':' fully-paid';
			$('td:nth-child(4)', nRow).addClass('price').html(currency(aData[4]));
			// }
			// { paid
			$('td:nth-child(5)', nRow).addClass('price').html(currency(aData[5]));
			// }
			// { owing
			var owed=+(aData[4]-aData[5]);
			var $td=$('td:nth-child(6)', nRow).addClass('price')
				.html('<span class="price">'+currency(owed)+'</span>&nbsp;');
			$('<a href="#" class="add-payment'+cname+'">[pay]</a>')
				.click(function() {
					var amt=+$td.find('.price').text();
					var $dialog=$('<table>'
						+'<tr><th>Amount Paid</th><td><input type="number" value="'+amt+'"/></td></tr>'
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
						invoiceForm
					);
				});
			$actions.append($edit);
			// }
			// { print
			var print=' <a href="/php/invoice-print.php?id='+aData[0]+'"'
				+' target="_blank" class="print">[print]</a>';
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
			invoiceForm({
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
}
