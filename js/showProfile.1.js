function showProfile() {
	var profile={
		'logonum':0
	};
	var $wrapper=$('#profile').empty();
	var html='<table>'
		+'<tr><th>Currency</th><td><input id="profile-currency"/></td>'
		+'<th rowspan="5">Logo</th><td rowspan="5">'
		+'<input id="profile-logo-button" type="file"/>'
		+'<div id="profile-logo-img"/></td></tr>'
		+'<tr><th>Company Name</th><td><input id="profile-name"/></td></tr>'
		+'<tr><th>Company Email</th><td><input id="profile-email" type="email"/></td>'
		+'<tr><th>Company Phone</th><td><input id="profile-phone"/></td></tr>'
		+'<tr><th>Company Address<span>shown at top of invoices</span></th>'
		+'<td><textarea id="profile-address"/></td></tr>'
		+'<tr><th>Payment Details<span>shown at bottom of invoices</span></th>'
		+'<td><textarea id="profile-payment"/></td>'
		+'<th>Options</th><td><ul id="profile-options-wrapper" class="no-bullets">'
		+'<li><input type="checkbox" id="profile-options[0]"/>use purchase orders</li>'
		+'</ul></td></tr>'
		+'<tr><th><button>Save</button></th><td/></tr>'
		+'</table>';
	$wrapper.append(html);
	$.post('/php/profile-get.php', function(ret) {
		profile=ret;
		$('#profile-logo-button').uploadify({
			'swf':'/js/uploadify-3.2/uploadify.swf',
			'uploader':'/php/profile-upload-logo.php',
			'formData':{
				'session_id':window.session_id
			},
			'buttonText':'Select Image',
			'fileSizeLimit':'200KB',
			'multi':false,
			'fileTypeExts':'*.gif; *.jpg; *.png',
			'fileTypeDesc':'Your logo. Max 200KB in size',
			'onUploadComplete':function(file) {
				profile.logonum++;
				$('#profile-logo-button-queue').empty();
				$('#profile-logo-img')
					.html(
						'<img src="/userdata/'+userdata.id+'/logo.png,'
						+profile['logo-num']+'"/>'
					);
			},
			'onUploadStart':function() {
				$('#profile-logo-img').empty();
			}
		});
		if (ret['logo-num']) {
			$('#profile-logo-img')
				.html(
					'<img src="/userdata/'+userdata.id+'/logo.png,'
					+profile['logo-num']+'"/>'
				);
		}
		$('#profile-address').val(ret['company-address']);
		$('#profile-name').val(ret['company-name']);
		$('#profile-email').val(ret['company-email']);
		$('#profile-phone').val(ret['company-phone']);
		$('#profile-payment').val(ret['payment-details']);
		$('#profile-currency').val(ret['currency-symbol']);
		$wrapper.find('button').click(function() {
			var options=0;
			$('#profile-options-wrapper input:checked').each(function(k, v) {
				options+=Math.pow(2, +$(this).attr('id').replace(/.*\[(.*)\]/, '$1'));
			});
			$.post('/php/profile-edit.php', {
				'company-name':$('#profile-name').val(),
				'company-phone':$('#profile-phone').val(),
				'company-email':$('#profile-email').val(),
				'company-address':$('#profile-address').val(),
				'currency-symbol':$('#profile-currency').val(),
				'payment-details':$('#profile-payment').val(),
				'options':options
			}, function(ret) {
				alert('saved');
			});
			return false;
		});
	});
}
