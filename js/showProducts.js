function showProducts() {
	function productForm(ret) {
		var html='<table>'
			+'<tr><th>Name</th><td><input id="dialog-name"/></td></tr>'
			+'<tr><th>Unit Price</th><td><input type="number" id="dialog-price"/>'
			+'</td></tr>'
			+'<tr><th>Default Tax</th><td><select id="dialog-tax"/></td></tr>'
			+'</table>';
		var $dialog=$(html).dialog({
			'title':'Edit Product',
			'modal':true,
			'close':function() {
				$dialog.remove();
			},
			'buttons':{
				'Save':function() {
					$.post(
						'/php/product-edit.php',
						{
							'id':ret.id,
							'name':$('#dialog-name').val(),
							'price':$('#dialog-price').val(),
							'tax':$('#dialog-tax').val()
						},
						function(ret) {
							$dialog.remove();
							$.post('/php/products-get.php', function(ret) {
								updateProducts(ret);
								$productsTable.fnDraw(1);
							});
						}
					);
				}
			}
		});
		$('#dialog-name').val(ret.name);
		$('#dialog-price').val(ret.price);
		// { tax
		var opts=[];
		$.each(taxesById, function(k, v) {
			var $opt=$('<option value="'+k+'"/>')
				.text(v?v.name+' ('+v.percentage+'%)':'');
			opts.push($opt);
		});
		$('#dialog-tax').append(opts).val(ret.tax);
		// }
	}
	var $wrapper=$('#products').empty();
	var html='<table><thead><tr><th>ID</th><th>Name</th>'
		+'<th>Unit Price</th><th>Default Tax</th><th>Actions</th></tr></thead>'
		+'<tbody/></table>';
	var $productsTable=$(html).appendTo($wrapper).dataTable({
		'bJQueryUI':true,
		'aaSorting':[[1, 'asc']],
		'sAjaxSource':'/php/products-get-dt.php',
		'sScrollY':'400px',
		'sDom':'frtip',
		'aoColumns':[
			{'bVisible':false}, {'sWidth':'200px'}, null, null, null
		],
		'bDeferRender':true,
		'bProcessing':true,
		'bServerSide':true,
		'fnRowCallback':function(nRow, aData, iDisplayIndex) {
			// { unit price
			aData[2]=+aData[2];
			$('td:nth-child(2)', nRow).addClass('price').html(currency(aData[2]));
			// }
			// { tax
			var tax_id=+aData[3];
			var t=taxesById[tax_id];
			$('td:nth-child(3)', nRow)
				.html(
					t?t.name+' ('+t.percentage+'%)':'<span class="faded">not set</span>'
				);
			// }
			// { actions
			var $actions=$('td:nth-child(4)', nRow).empty();
			// { edit
			var $edit=$('<a href="#" class="edit">[edit]</a>')
				.click(function() {
					$.post(
						'/php/product-get.php',
						{
							'pid':aData[0]
						},
						productForm
					);
				});
			$actions.append($edit);
			// }
			// { delete
			$actions.append(' ');
			var $delete=$('<a href="#" class="delete">[x]</a>')
				.click(function() {
					$.post(
						'/php/product-delete.php',
						{
							'pid':aData[0]
						},
						function(ret) {
							if (ret.error) {
								return alert(ret.error);
							}
							$.post('/php/products-get.php', function(ret) {
								updateProducts(ret);
								$productsTable.fnDraw(1);
							});
						}
					);
				});
			$actions.append($delete);
			// }
			// }
			return nRow;
		}
	});
	var $addProduct=$(
		'<button id="product-add">Create Product</button>'
	)
		.click(function() {
			productForm({
				'id':0,
				'name':'',
				'price':0,
				'tax':0
			});
			return false;
		})
		.insertBefore(
			$wrapper.find('>div.dataTables_wrapper>div:first-child')
		);
}
