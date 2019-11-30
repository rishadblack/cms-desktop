// --------------- Sales Start ----------------------- //
function GetMenualUpdate() {
	jQuery.ajax({
		url: "ajax.php",
		data: {
			action	 : "ManualUpdate"
		},
		type: "POST",
		success:function(data){
			$(".poststatus").html(data);
		},
		error:function (){}
	});
}

$('#api_key_form form').validate({
	rules: {
		api_key: {
			required: true
		}
	},	
	submitHandler: function(form) {
		AS.Http.submit(form, getUserApiKey(form), function (result) {
			if(result.status == "registered"){
				swal({
					title: "Software Activation Successfull",
					text: result.status_mag,
					type: "success",
					confirmButtonText: "Ok"
				},
				function (isConfirm) {
					if (isConfirm) {
						window.location = "index.php";
					} 
				});
				}else{
				swal({
					title: "Software Activation Fail",
					text: result.status_mag,
					type: "error",
					confirmButtonText: "Ok"
				});
			}
		});
		
	}
});

function getUserApiKey(form) {
	return {
		action: "userApiKey",
		data: {
			api_key: form['api_key'].value
		}
	};
}

$('#login-form form').validate({
	rules: {
		username: {
			required: true
		},
		password: {
			required: false
		}
	},	
	submitHandler: function(form) {
		AS.Http.submit(form, getUserLogin(form), function (result) {
			window.location = result.page;
			// alert(result.page);
		});
		
	}
});

function getUserLogin(form) {
	return {
		action: "userLogin",
		data: {
			username: form['username'].value,
			password: AS.Util.hash(form['password'].value)
		}
	};
}

$('#customer-form form').validate({
	rules: {
		customer_name: {
			required: true
		},
		customer_address: {
			required: false
		},
		customer_phone: {
			required: true,
			number: true
		},
		customer_email: {
			required: false,
		email: true
		},
		customer_due: {
		required: false,
		number: true
		}
		},	
		submitHandler: function(form) {
		AS.Http.submit(form, getcustomerSubmit(form), function (response) {
		swal({
		title: "Customer Added Successfully",
		text: response.message,
		type: "success",
		confirmButtonText: "Ok"
		},
		function (isConfirm) {
		if (isConfirm) {
		$('#customer-form').modal('hide');
		} 
		});
		});
		
		}
		});
		
		function getcustomerSubmit(form) {
		return {
		action: "updatecustomer",
		data: {
		customer_id: form['customer_id'].value,
		customer_name: form['customer_name'].value,
		customer_address: form['customer_address'].value,
		customer_phone: form['customer_phone'].value,
		customer_email: form['customer_email'].value,
		customer_due: form['customer_due'].value
		}
		};
		}
		
		$('#paid_amount').keyup(function(){
		getcartcalupdate();
		});
		
		$('#discount_amount').keyup(function(){
		getcartcalupdate();
		});
		
		function paymentmethodchange() {
		var paidamount = $("#payment_method").val();
		
		if(paidamount != 1){
		$(".transition-id").removeClass("hidden");
		}else{
		$(".transition-id").addClass('hidden');
		}
		}
		
		function getproductbycategory() {
		var productcategory = $("#product_category").val();
		
		jQuery.ajax({
		url: "ajax.php",
		data: {
		action	 : "GetProductByCategory",
		product_category  : productcategory
		},
		type: "POST",
		success:function(data){
		$("#product_list").html(data);
		},
		error:function (){}
		});
		}
		
		$('#product_name_search').keyup(function(){
		var productnamesearch = $("#product_name_search").val();
		
		jQuery.ajax({
		url: "ajax.php",
		data: {
		action	 : "GetProductByName",
		product_name  : productnamesearch
		},
		type: "POST",
		success:function(data){
		$("#product_list").html(data);
		},
		error:function (){}
		});
		});
		
		$('#product_code_search').keyup(function(){
		var productcodesearch = $("#product_code_search").val();
		
		jQuery.ajax({
		url: "ajax.php",
		data: {
		action	 : "GetProductByCode",
		product_id  : productcodesearch
		},
		type: "POST",
		success:function(data){
		$("#product_list").html(data);
		},
		error:function (){}
		});
		});
		
		$(document).on("click",".removecartpurchase", function(){
		var Id = $(this).data("id");
		AS.Http.post({
		action: "GetRemoveSalesCart",
		id  : Id
		}, function (result) {
		$("#cart_id_" + result.cart_id).remove();
		if(result.cart_empty_check == "empty"){
		$(".cart_empty").removeClass("hidden");
		}else if(result.cart_empty_check == "not_empty"){
		$(".cart_empty").addClass('hidden');
		}
		getcartcalupdate();
		});
		});
		
		$(document).on("change",".CartQuantityUpdate", function(){
		var cartid = $(this).data("cart-id");
		var productid = $(this).data("product-id");
		var productqty = $("#product_qty"+cartid).val();
		addcart(productid,"product_id",productqty);
		});
		
		function getproductbybarcode() {
		var productcode = $("#product_barcode").val();
		addcart(productcode,'product_id');
		$("#product_barcode").val('');
		}
		
		$('.SalesOrderForm form').validate({
		rules: {
		paid_amount: {
		required: true
		}
		},	
		submitHandler: function(form) {
		AS.Http.submit(form, GetSalesCompletedetails(form), function (response) {
		getcartcalupdate();
		$("#sales_table tr").remove();
		$(".cart_empty").removeClass("hidden");
		form.reset();
		GetReceiptView();
		$('#resipt').modal('show');
		GetReceiptViews();
		});
		}
		});
		
		function GetReceiptView() {
		jQuery.ajax({
		url: "ajax.php",
		data: {
		action: "GetReceiptView",
		},
		type: "POST",
		success:function(data){
		$(".receiptview").html(data);
		$('#resipt').modal('show');
		},
		error:function (){}
		});
		
		}
		
		function GetReceiptViews() {
		jQuery.ajax({
		url: "ajax.php",
		data: {
		action: "GetReceiptPrint",
		},
		type: "POST",
		success:function(data){
		$(".last_receipt_view").html(data);	
		$.print("#pos-print");
		
		},
		error:function (){}
		});
		
		}
		
		$(document).on("click",".last_receipt_print", function(){
		$("#pos-print").removeClass('hidden');
		GetReceiptViews();
		});
		
		function GetSalesCompletedetails(form) {
		return {
		action: "GetcustomerSalesComplete",
		data: {
		search_customer_id: form['search_customer_id'].value,
		search_customer_phone: form['search_customer_phone'].value,
		paid_amount: form['paid_amount'].value,
		need_to_pay: form['need_to_pay'].value,
		order_vat: form['product_total_vat'].value,
		order_subtotal: form['product_sub_total'].value,
		order_total: form['order_total'].value,
		pay_change: form['pay_change'].value,
		return_id: form['return_id'].value,
		return_amount: form['return_amount'].value,
		payment_method: form['payment_method'].value,
		sales_note: form['sales_note'].value,
		discount_amount: form['discount_amount'].value,
		transition_id: form['transition_id'].value,
		product_barcode: form['product_barcode'].value,
		}
		};
		}
		
		$(document).on("click",".test", function(){
		AS.Http.post({
		action: "Gettest"
		}, function (result) {
		$("#time_now").html(result.test_result);
		});
		});
		
		$(document).on("click",".addcartpurchase", function(){
		var productcode = $(this).data("id");
		addcart(productcode);
		});
		
		
		function addcart(productcode,idtype = "id",productqty = null){
		AS.Http.post({
		action: "GetAddCartPurchase",
		id  : productcode,
		id_type  : idtype,
		product_new_qty : productqty
		}, function (result) {
		if(document.getElementById("cart_id_"+result.cart_id)){
		$('#product_qty'+result.cart_id).val(result.product_qty);
		$('#subtotal_'+result.cart_id).html(result.product_sub_total);
		if(result.stock_status == "out_of_stock"){
		swal({
		title: "Out Of Stock",
		type: "error"
		});
		
		}else if(result.stock_status == "low_alart"){
		swal({
		title: "Low Stock Alart! Avaliable Stock "+result.stock_avaliable,
		type: "error"
		});
		
		}
		}else if(result.stock_status == "low_alart" || result.stock_status == "avaliable"){
		$(".cart_empty").addClass('hidden');
		var salesTable = document.getElementById('sales_table');
		var rowCnt = salesTable.rows.length; 
		var tr = salesTable.insertRow(rowCnt);  
		tr.setAttribute('id',"cart_id_"+result.cart_id);
		tr.setAttribute('class',"text-center");
		for (var c = 0; c < 6; c++) {
		var td = document.createElement('td');      
		td = tr.insertCell(c);
		if(c==0){
		td.append(result.product_code);
		
		}else if(c==1){
		td.append(result.product_name);
		
		}else if(c==2){
		td.append(result.product_price);
		
		}else if(c==3){
		var ele = document.createElement('input');
		ele.setAttribute('type', 'number');
		ele.setAttribute('value', '1');
		ele.setAttribute('class', 'form-control input-sm text-center CartQuantityUpdate');
		ele.setAttribute('size', '2');
		ele.setAttribute('min', '0');
		ele.setAttribute('oninput', 'this.value = Math.abs(this.value);');
		ele.setAttribute('id', "product_qty"+result.cart_id);
		ele.setAttribute('data-product-id', result.product_code);
		ele.setAttribute('data-cart-id', result.cart_id);
		ele.setAttribute('value', result.product_qty);
		ele.setAttribute('style', "display: block;");
		td.appendChild(ele);
		}else if(c==4){
		td.setAttribute('id',"subtotal_"+result.cart_id);
		td.append(result.product_sub_total);
		
		}else if(c==5){
		var ele = document.createElement('a');
		ele.setAttribute('href', 'javascript:void(0);');
		ele.setAttribute('data-id', result.cart_id);
		ele.setAttribute('class', 'removecartpurchase');
		var ele_icon = document.createElement('i');
		ele_icon.setAttribute('class', 'fa fa-trash');
		ele.appendChild(ele_icon);
		td.appendChild(ele);
		}
		}
		
		if(result.stock_status == "low_alart"){
		swal({
		title: "Low Stock Alart! Avaliable Stock "+result.stock_avaliable,
		type: "error"
		});
		
		}
		}else{
		swal({
		title: "Out Of Stock",
		type: "error"
		});
		}
		
		$(document).ready(function(){
		$(".touchspin").TouchSpin({
		buttondown_class: 'btn btn-white btn-sm',
		buttonup_class: 'btn btn-white btn-sm'
		});
		});
		getcartcalupdate();
		});
		}
		
		function getcartcalupdate() {
		var paidamount = $("#paid_amount").val();
		var returnamount = $("#return_amount").val();
		var discountamount = $("#discount_amount").val();
		console.log(returnamount);
		if(!returnamount){
		var returnamount = 0;
		}
		
		if(!discountamount){
		var discountamount = 0;
		}
		
		var return_discount = parseFloat(discountamount) + parseFloat(returnamount);
		
		AS.Http.post({
		action: "GetSalesCartCal"
		}, function (result) {
		$("#product_sub_total").val(result.product_sub_total);
		$("#product_total_vat_label").html("Vat"+' :'+result.product_total_vat_label);
		$("#product_total_vat").val(result.product_total_vat);
		$("#order_total").val(result.need_to_pay);
		var total_need_to_pay = result.need_to_pay - return_discount;
		$("#need_to_pay").val(total_need_to_pay);
		var total_pay_change = paidamount - total_need_to_pay;
		$("#pay_change").val(total_pay_change);
		});
		}
		
		function getcustomerbymobile() {
		AS.Http.post(GetcustomerDetailByMobile(), function (result) {
		$("#search_customer_id").val(result.cid);
		$("#search_customer_phone").val(result.phone);
		});
		}
		
		function GetcustomerDetailByMobile() {
		var search_customer_phone = $("#search_customer_phone").val();
		
		if(search_customer_phone){
		var customerphone = search_customer_phone;
		}else{
		var customerphone = null;
		}
		return {
		action: "GetcustomerDetails",
		data: {
		customer_id: "null",
		customer_phone: customerphone
		}
		};
		}
		
		function getcustomerbyid() {
		AS.Http.post(GetcustomerDetailByid(), function (result) {
		$("#search_customer_id").val(result.cid);
		$("#search_customer_phone").val(result.phone);
		});
		}
		
		function GetcustomerDetailByid() {
		var search_customer_id = $("#search_customer_id").val();
		
		if(search_customer_id){
		var customerid = search_customer_id;
		}else{
		var customerid = null;
		}
		return {
		action: "GetcustomerDetails",
		data: {
		customer_id: customerid,
		customer_phone: "null"
		}
		};
		}				