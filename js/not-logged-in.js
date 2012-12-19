$(function() {
	$('#login').html('<button>Login / Register</button>');
	$('html').on('click', '.register', function() {
		var html='<table>'
			+'<tr><th>Email</th><td><input type="email" class="email"/></td></tr>'
			+'<tr><th>Preferred Password</th><td><input type="password" class="password"/></td></tr>'
			+'<tr><th>(repeat password)</th><td><input type="password" class="password password2"/></td></tr>'
			+'</table>';
		var $dialog=$(html).dialog({
			'modal':true,
			'width':'400',
			'close':function() {
				$(this).remove();
			},
			'buttons':{
				'Register':function() {
					var email=$dialog.find('.email').val(),
						password=$dialog.find('.password').val(),
						password2=$dialog.find('.password2').val();
					if (password!=password2 || !password) {
						return alert('Please enter your password twice.');
					}
					if (password.length<8) {
						return alert('Password too short.');
					}
					if (!email) {
						return alert('Please enter your email address.');
					}
					$.post('/php/register.php', {
						'email':email,
						'password':password
					}, function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						$dialog.remove();
						$('<p>Please check your email. A verification link has been'
							+' sent to it. You must click the link to activate your'
							+' account.</p>')
							.dialog({
								'close':function() {
									$(this).remove();
								},
								'modal':true
							});
					});
				}
			}
		});
	});
	$('#login button').click(function() {
		// { layout
		var html='<table><tr>'
			+'<th>Use your social network</th>'
			+'<td rowspan="2" style="vertical-align:middle">-or-</td>'
			+'<th>Use your email address</th>'
			+'</tr><tr>'
			+'<td>'
			+'<a href="/php/oauth-api/login_with_facebook.php">'
			+'<img src="//static.bizti.me/images/login-facebook.png"/></a><br/><br/>'
			+'<a href="/php/oauth-api/login_with_twitter.php">'
			+'<img src="//static.bizti.me/images/login-twitter.png"/></a><br/><br/>'
			+'<a href="/php/oauth-api/login_with_google.php">'
			+'<img src="//static.bizti.me/images/login-google.png"/></a><br/><br/>'
			+'</td>'
			+'<td><strong>Email Address:</strong><br/>'
			+'<input id="popup-email" type="email" placeholder="email"/><br/><br/>'
			+'<strong>Password:<br/><input id="popup-password" type="password"/>'
			+'<br/><br/>'
			+'<button style="float:right" class="register">Register</button> '
			+'<button id="popup-login">Login</button>'
			+'</td>'
			+'</tr></table>';
		// }
		$(html).dialog({
			'title':'Login',
			'modal':true,
			'width':'500px',
			'close':function() {
				$(this).remove();
			}
		});
		$('#popup-login').click(function() {
			var email=$('#popup-email').val(),
				password=$('#popup-password').val();
			if (!email || !password) {
				return alert('you need to enter your email address and your password!');
			}
			$.post('/php/login.php', {
				'email':email,
				'password':password
			}, function(ret) {
				if (ret.ok) {
					return document.location='/';
				}
				return alert(ret.error);
			});
			return false;
		});
		return false;
	});
});
