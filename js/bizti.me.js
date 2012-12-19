$.cachedScript=function(url, options) {
	options=$.extend(options || {}, {
		dataType: "script",
		cache: true,
		url: url
	});
	return $.ajax(options);
};
function currency(num) {
	var bits=(''+num).split('.');
	var l=bits[0]==='null'?0:+bits[0];
	var r=bits.length==2
		?(bits[1].length==1?bits[1]+'0':bits[1])
		:'00';
	return userdata.currency+l+'.'+r;
}
Date.prototype.toYMD =function() {
	var year, month, day;
	year = String(this.getFullYear());
	month = String(this.getMonth() + 1);
	if (month.length == 1) {
		month = "0" + month;
	}
	day = String(this.getDate());
	if (day.length == 1) {
		day = "0" + day;
	}
	return year + "-" + month + "-" + day;
};
CKEDITOR.basePath='/js/ckeditor/';
CKEDITOR.config.contentsCss=CKEDITOR.basePath+"contents.css";
function updateTaxes(ret, callback) {
	taxes=ret;
	window.taxesById=[];
	for (var i=0;i<taxes.length;++i) {
		taxes[i].id=+taxes[i].id;
		taxesById[taxes[i].id]=taxes[i];
	}
	callback && callback();
}
function updateProducts(ret, callback) {
	products=ret;
	window.productsById=[];
	for (var i=0;i<products.length;++i) {
		products[i].id=+products[i].id;
		products[i].price=+products[i].price;
		productsById[products[i].id]=products[i];
	}
	callback && callback();
}
function getCustomerName(id) {
	var name='';
	for (var i=0;i<customerNames.length;++i) {
		if (+customerNames[i].id==id) {
			return customerNames[i].name;
		}
	}
}
$(function() {
	var functionStubs=[
		'showCustomers', 'showInvoices', 'showPayments', 'showProducts',
		'showProfile'
	];
	for (var i=0;i<functionStubs.length;++i) {
		window[functionStubs[i]]=(function(fn) {
			return function() {
				$.cachedScript('/js/'+fn+'.php').done(function() {
					window[fn]();
				});
			}
		})(functionStubs[i]);
	}
	// { logout button
	var $logout=$('<button>Logout</button>').click(function() {
		document.location='/php/logout.php';
		return false;
	});
	$('#login').empty().append($logout);
	// }
	// { main page layout
	var html='<div><ul>'
		+'<li><a href="#invoices">Invoices</a></li>'
		+'<li><a href="#customers">Customers</a></li>'
		+'<li><a href="#products">Products</a></li>'
		+'<li><a href="#payments">Payments</a></li>'
		+'<li><a href="#profile">Your&nbsp;Profile</a></li>'
		+'</ul>'
		+'<div id="invoices"/>'
		+'<div id="customers"/>'
		+'<div id="products"/>'
		+'<div id="payments"/>'
		+'<div id="profile"/>'
		+'</div>';
	function switchMainTab(e, ui) {
		switch (ui.panel===undefined?ui.newPanel.selector:ui.panel.selector) {
			case '#invoices': return showInvoices();
			case '#customers': return showCustomers();
			case '#products': return showProducts();
			case '#payments': return showPayments();
			case '#profile': return showProfile();
			default: return alert('huh?');
		}
	}
	$(html).appendTo($('#main-wrapper').empty()).tabs({
		'activate':switchMainTab,
		'create':switchMainTab,
		'active':window.customerNames.length&&window.products.length?0:4
	});
	// }
	updateTaxes(taxes);
	updateProducts(products);
});
