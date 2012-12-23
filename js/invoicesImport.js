function invoicesImport() {
	var html='<div><p>Currently we only provide imports from Simple Invoices.</p>'
		+'<p>If you are interested in importing from a different invoicing system'
		+', please <a href="//github.com/kaeverens/bizti/issues">fill in an issue'
		+' report here</a>.</p></div>';
	var $dialog=$(html).dialog({
		'modal':true,
		'close':function() {
			$dialog.remove();
		},
		'buttons':{
			'Simple Invoices':function() {
				$dialog.remove();
				invoicesImportSimpleInvoices();
			}
		}
	});
}
function invoicesImportSimpleInvoices() {
	var html='<div><p>To import from Simple Invoices, you will need to'
		+' export your database in TSV format and zip it up.</p>'
		+'<p>To do this, please log into your server using SSH and run the'
		+' following commands:</p>'
		+'<pre>mkdir dump &amp;&amp; chmod 777 dump<br/>'
		+'mysqldump -u <b>[username]</b> -p -t -Tdump <b>[database]</b><br/>'
		+'rm -f dump.zip dump/*sql<br/>'
		+'zip dump.zip dump -r &amp;&amp; rm -rf dump</pre>'
		+'<p>Next, download the created dump.zip file to your computer,'
		+' and then upload it to Bizti.</p>'
		+'<table><tr><th>Upload the file</th><td>'
		+'<input id="dump" type="file"/></td></tr></table>'
		+'</div>';
	var $dialog=$(html).dialog({
		'width':'450px',
		'modal':true,
		'close':function() {
			$dialog.remove();
		}
	});
	$('#dump').uploadify({
		'swf':'/js/uploadify-3.2/uploadify.swf',
		'uploader':'/php/invoices-import-simpleinvoices.php',
		'formData':{
			'session_id':window.session_id
		},
		'buttonText':'Select dump.zip',
		'fileSizeLimit':'2MB',
		'multi':false,
		'fileTypeExts':'*.zip',
		'fileTypeDesc':'Your dump.zip file. Max 2MB in size',
		'onUploadSuccess':function(file, ret) {
			ret=JSON.parse(ret);
			if (ret.error) {
				return alert(ret.error);
			}
			$dialog.remove();
			invoicesImportSimpleInvoicesRun();
		}
	});
}
function invoicesImportSimpleInvoicesRun() {
	var html='<div><p>Importing "<span id="popup-name"/>"</p>'
		+'<div id="progressbar"/></div>';
	var $dialog=$(html).dialog({
		'modal':true,
		'close':function() {
			$dialog.remove();
		}
	});
	var tables=[
		'customers', 'invoices', 'tax', 'products', 'payment', 'invoice_items'
		, 'invoice_item_tax'
		, 'user_domain', 'invoice_type', 'cron_log'
		, 'user', 'user_role', 'sql_patchmanager', 'preferences', 'log'
		, 'cron', 'payment_types'
		, 'custom_fields', 'system_defaults', 'inventory', 'index'
		, 'extensions', 'biller'
	];
	var i=0;
	function runOne() {
		$('#progressbar').progressbar({
			value:100/tables.length*(i+1)
		});
		$('#popup-name').text(tables[i]);
		$.post('/php/invoices-import-simpleinvoices-table.php', {
			'table':tables[i]
		}, function(ret) {
			if (ret.error) {
				console.log(ret.error);
			}
			i++;
			if (i<tables.length) {
				setTimeout(runOne, 1);
			}
			else {
				$dialog.remove();
				$.post('/php/update-totals.php', function() {
					document.location='/';
				});
			}
		});
	}
	runOne();
}
