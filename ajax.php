<?php 
	include dirname(__FILE__) .'/engine/autoload.php';
	$application = new MainEngine();
	$action = $_POST['action'];
	
	switch ($action) {
		
		case 'AutoUpdate':
		$getsetting = $application->getsetting();
		$datestock = new DateTime($getsetting['last_update_stock']);
		$datestock->modify('+10 minute');
		$stock_date = $datestock->format('d-m-Y h:i:s');
		
		$stockStatus = true;
		
		if($stock_date < date('d-m-Y h:i:s')){
			$stockStatus = $application->SyncUpdateStock();
		}
		
		$datesetting = new DateTime($getsetting['last_update_stock']);
		$datesetting->modify('+60 minute');
		$setting_date = $datesetting->format('d-m-Y h:i:s');
		
		if($setting_date < date('d-m-Y h:i:s')){
			$stockStatus = $application->SyncSettingUpdate();
		}
		
		$datedelete = new DateTime($getsetting['last_update_stock']);
		$datedelete->modify('+120 minute');
		$delete_date = $datedelete->format('d-m-Y h:i:s');
		
		if($delete_date < date('d-m-Y h:i:s')){
			$application->GetSyncDelete();
		}
		if($stockStatus) echo '<span class="poststatus">Last Update: '.$getsetting['last_update_stock'].'</span>';
		
		break;
		
		case 'ManualUpdate':
		$getsetting = $application->getsetting();
		$stockStatus = $application->SyncUpdateStock();
		if($stockStatus) echo '<span class="poststatus">Last Update: '.$getsetting['last_update_stock'].'</span>';
		break;
		
		case 'userApiKey':
		$application->getuserApiKey($_POST['data']);
		break;
		
		case 'userLogin':
		$application->getUserLogin($_POST['data']);
		break;
		
		case 'GetcustomerSalesComplete':
		
		if(isset($_SESSION["cart_products"])){
			foreach($_POST['data'] as $key => $value){ 
				$data[$key] = filter_var($value, FILTER_SANITIZE_STRING);
			}
			
			if($data['paid_amount'] < $data['need_to_pay']){
				respond(array(
				'status' => 'error',
				'errors' => array(
				'paid_amount' => "Insufficient Cash - Need More ".abs($data['pay_change']).Session::get("user_id")
				)
				), 422);
			}
			
			$customer_id = $data['search_customer_id'];
			$customer_phone = $data['search_customer_phone'];
			
			$check_customer_id = $application->getwhere("customer","customer_id",$customer_id);
			
			
			if ($check_customer_id) {
				$customer = $customer_id;
				}elseif(!empty($customer_phone)) {
				$customer = $application->insertcustomerphone($customer_phone,Session::get("user_id"),Session::get("store_id"));
				}else{
				$customer = 0;
			}
			
			$data['order_status'] = "1";
			$data['customer_id'] = $customer;
			$sales_id = $application->GetCustomerOrder($data,Session::get("user_id"),Session::get("store_id"));
			
			if($sales_id){
				if(isset($_SESSION["cart_products"])){
					foreach ($_SESSION["cart_products"] as $cart_itm){
						$product_id = $cart_itm['product_id']; 
						$product_quantity = $cart_itm['product_quantity']; 
						$sub_total = $cart_itm['sell_price'] * $cart_itm['product_quantity'];
						$application->GetcustomerOrderProduct($customer,$cart_itm['product_id'],$sales_id,$cart_itm['sell_price'],$cart_itm['product_quantity'],$sub_total,Session::get("user_id"),Session::get("store_id"));
					}
				}
				
			}
			
			unset($_SESSION["cart_products"]);
			
			respond(array(
			"status" => "success",
			"order_id" => $sales_id
			));
			
			
			}else{
			respond(array(
			'status' => 'error',
			'errors' => array(
			'product_barcode' => "Add some product first"
			)
			), 422);
			
		}
		
		break;
		
		case 'GetProductByCategory':
		$product = $application->getwhere('product','category_id',$_POST['product_category']);
		if($product){
		?>
		<div id="product_list">
			<?php foreach($product as $Product){ ?>
				<div class="m-b-sm col-md-2">
					<a data-id="<?php echo $Product['id']; ?>" href="javascript:void(0);" class="addcartpurchase">
						<img src="assets/images/avatar.jpg" class="img-lg" hspace="5" vspace="5">
						<p class="font-bold text-left"><?php echo $Product['product_name']; ?></p>
					</a>
				</div>
			<?php } ?>
		</div>
		<?php 
		}
		break;
		
		case 'GetProductByCode':
		$product = $application->getproductbysearch('product_id','%'.$_POST['product_id']);
		if($product){
		?>
		<div id="product_list">
			<?php foreach($product as $Product){ ?>
				<div class="m-b-sm col-md-2">
					<a data-id="<?php echo $Product['id']; ?>" href="javascript:void(0);" class="addcartpurchase">
						<img src="assets/images/avatar.jpg" class="img-lg" hspace="5" vspace="5">
						<p class="font-bold text-left"><?php echo $Product['product_name']; ?></p>
					</a>
				</div>
			<?php } ?>
		</div>
		<?php 
		}
		break;
		
		case 'GetProductByName':
		$product = $application->getproductbysearch('product_name','%'.$_POST['product_name'].'%');
		if($product){
		?>
		<div id="product_list">
			<?php foreach($product as $Product){ ?>
				<div class="m-b-sm col-md-2">
					<a data-id="<?php echo $Product['id']; ?>" href="javascript:void(0);" class="addcartpurchase">
						<img src="assets/images/avatar.jpg" class="img-lg" hspace="5" vspace="5">
						<p class="font-bold text-left"><?php echo $Product['product_name']; ?></p>
					</a>
				</div>
			<?php } ?>
		</div>
		<?php 
		}
		break;
		
		case 'GetcustomerDetails':
		$application->GetcustomerDetails($_POST['data']);
		break;
		
		case 'GetSalesCartCal':
		$total_amount = 0;
		$total = 0;
		$totalvat = 0;
		$vat_label = '';
		$getpossetting = $application->getsetting();
		if(isset($_SESSION["cart_products"]) && !empty($_SESSION["cart_products"])){
			foreach ($_SESSION["cart_products"] as $cart_itm){ 
				$sub_total = $cart_itm['sell_price'] * $cart_itm['product_quantity'];
				$total += $sub_total;
				$totalvat += $sub_total * $cart_itm['product_vat'] / 100;
			}
			
			if($getpossetting['vat_type'] == "global"){
				$totalvat = $total * $getpossetting['vat'] / 100;
				$vat_label = '('.$getpossetting['vat'].'%)';
				}elseif($getpossetting['vat_type'] == "single"){
				$totalvat = $totalvat;
				$vat_label = '';
			}
			$total_amount = $totalvat + $total;
		}
		
		respond(array(
		"status" => "success",
		"product_sub_total" => $total,
		"product_total_vat_label" => $vat_label,
		"product_total_vat" => $totalvat,
		"need_to_pay" => $total_amount,
		));
		
		break;
		
		case 'GetRemoveSalesCart':
		unset($_SESSION["cart_products"][$_POST["id"]]);
		
		if(isset($_SESSION["cart_products"]) && !empty($_SESSION["cart_products"])){
			respond(array(
			"status" => "success",
			"cart_empty_check" => "not_empty",
			"cart_id" => $_POST["id"],
			));
			}else{
			respond(array(
			"status" => "success",
			"cart_empty_check" => "empty",
			"cart_id" => $_POST["id"],
			));
		}
		break;
		
		case 'GetAddCartPurchase':
		$new_product = array();
		if($_POST['id_type'] == "product_id"){
			$get_product = $application->getwhereid('product','product_id',$_POST['id']);
			}else{
			$get_product = $application->getwhereid('product','id',$_POST['id']);
		}
		
		if($get_product){
			$new_product["id"] = $get_product['id']; 
			$new_product["product_id"] = $get_product['product_id']; 
			$new_product["product_name"] = $get_product['product_name']; 
			$new_product["sell_price"] = $get_product['sell_price'];
			$new_product["product_vat"] = ceil($get_product['product_vat']);
			
			
			if(isset($_SESSION["cart_products"][$new_product['id']]) && $_POST['product_new_qty'] != null){
				$new_product["product_quantity"] = $_POST['product_new_qty'];
				}elseif(isset($_SESSION["cart_products"][$new_product['id']])){
				$getsessionproduct = $_SESSION["cart_products"][$new_product['id']];
				$new_product["product_quantity"] = $getsessionproduct['product_quantity'] + 1;
				}else{
				$new_product["product_quantity"] = 1;
			} 
			
			$getproductstock = $application->GetProductStock($get_product['product_id']);
			$addtotalstock = $getproductstock - $new_product["product_quantity"];
			if($addtotalstock  == 5){
				$_SESSION["cart_products"][$new_product['id']] = $new_product; 
				respond(array(
				"status" => "success",
				"product_code" => $_SESSION["cart_products"][$new_product['id']]["product_id"],
				"product_name" => $_SESSION["cart_products"][$new_product['id']]["product_name"],
				"product_price" => $_SESSION["cart_products"][$new_product['id']]["sell_price"],
				"product_sub_total" => $_SESSION["cart_products"][$new_product['id']]["sell_price"] * $_SESSION["cart_products"][$new_product['id']]["product_quantity"],
				"product_qty" => $_SESSION["cart_products"][$new_product['id']]["product_quantity"],
				"cart_id" => $new_product['id'],
				"stock_status" => "low_alart",
				"stock_avaliable" => $addtotalstock,
				));
				}elseif($getproductstock == 0){
				if(isset($_SESSION["cart_products"][$new_product['id']])){
					respond(array(
					"status" => "success",
					"cart_id" => $new_product['id'],
					"product_qty" => $_SESSION["cart_products"][$new_product['id']]["product_quantity"],
					"product_sub_total" => $_SESSION["cart_products"][$new_product['id']]["sell_price"] * $_SESSION["cart_products"][$new_product['id']]["product_quantity"],
					"stock_status" => "out_of_stock",
					));
					}else{
					respond(array(
					"status" => "success",
					"stock_status" => "out_of_stock",
					));
				}
				}elseif($getproductstock >= $new_product["product_quantity"]){
				$_SESSION["cart_products"][$new_product['id']] = $new_product; 
				
				respond(array(
				"status" => "success",
				"product_code" => $_SESSION["cart_products"][$new_product['id']]["product_id"],
				"product_name" => $_SESSION["cart_products"][$new_product['id']]["product_name"],
				"product_price" => $_SESSION["cart_products"][$new_product['id']]["sell_price"],
				"product_sub_total" => $_SESSION["cart_products"][$new_product['id']]["sell_price"] * $_SESSION["cart_products"][$new_product['id']]["product_quantity"],
				"product_qty" => $_SESSION["cart_products"][$new_product['id']]["product_quantity"],
				"cart_id" => $new_product['id'],
				"stock_status" => "avaliable",
				));
				
				}else{
				respond(array(
				"status" => "success",
				"cart_id" => $new_product['id'],
				"product_qty" => $_SESSION["cart_products"][$new_product['id']]["product_quantity"],
				"product_sub_total" => $_SESSION["cart_products"][$new_product['id']]["sell_price"] * $_SESSION["cart_products"][$new_product['id']]["product_quantity"],
				"stock_status" => "out_of_stock",
				));
			}
		}
		
		break;
		
		
		case 'GetReceiptView':
		$numberToWords = new NumberToWords\NumberToWords();
		$numberTransformer = $numberToWords->getNumberTransformer('en');
		$getlastorderid = $application->GetLastOrderReceipt(Session::get("user_id"));
		$getpossetting = $application->getsetting();
		$getcustomerid = $application->getwhereid('customer','customer_id',$getlastorderid['customer_id']);
		$getsalesby = $application->getwhereid('users','user_id',$getlastorderid['user_id']);
		$getsalesproducts = $application->getwhere('stock','sales_id',$getlastorderid['sales_id']);
		if($getlastorderid){
		?>
		<div class="modal inmodal fade" id="resipt" tabindex="-1" role="dialog"  aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content receiptview" style="width: 450px;">
					<div class="modal-body" id="invoice-POS">
						<center id="top">
							<div class="info"> 
								<?php if(!empty($getpossetting['company_logo'])){ ?>
									
									<?php }else{ ?>
									<p class="company_name"><?php echo $getpossetting['company_name']; ?></p>
								<?php } ?>
							</div><!--End Info-->
						</center><!--End InvoiceTop-->
						
						<div id="mid">
							<div class="text_center" style="font-family: -webkit-body;">
								<p> 
									Address : <?php echo $getpossetting['address']; ?><br>
									Email   : <?php echo $getpossetting['email']; ?><br>
									Phone   : <?php echo $getpossetting['phone']; ?><br>
									<?php if(!empty($getpossetting['nbr_no'])){ ?>Vat Reg : <?php echo $getpossetting['nbr_no']; ?> Mushak : <?php echo $getpossetting['nbr_unit']; ?><br> <?php } ?>
								</p>
							</div>
							<div class="info">
								<p class="pull-left" style="font-size:14px;font-family: -webkit-body; float:left;">
									Voucher:  <?php echo $getlastorderid['sales_id']; ?> <br>
									Sale To: <?php if(empty($getlastorderid['customer_id'])){ echo "Walk In Customer"; }else{ if(empty($getcustomerid['customer_name'])){ echo $getcustomerid['customer_phone']; }else{ echo $getcustomerid['customer_name']; }} ?>
									
								</p>
								<p class="pull-right" style="font-size:14px;font-family: -webkit-body; float:right;">
									Date: <?php echo getdatetime($getlastorderid['created_at'], 3); ?> <br>
									Sale By: <?php echo $getsalesby['first_name'].' '.$getsalesby['last_name']; ?>
								</p>
							</div>
						</div><!--End Invoice Mid-->
						
						<div id="bot">
							<div id="table">
								<table class="pdt_table" >
									<tr class="text_center head" style="border: 2px solid #ccc2c2">
										<th style="font-size: 12px;">Item</th>
										<th class="text_center" style="font-size: 13px;">Qty</th>
										<th class="text_center" style="font-size: 13px;">Price</th>
										<th class="text_center" style="font-size: 13px;">Sub Total</th>
									</tr>
									
									<tbody id="body">
										<?php 
											// print_r($getlastorderid);
											foreach($getsalesproducts as $getsalesproduct){ 
												$getproductdetails = $application->getwhereid('product','product_id',$getsalesproduct['product_id']);
											?>
											<tr>
												<td><p style="font-weight:bold;"><?php echo $getproductdetails['product_name']; ?></p></td>
												<td class="text_center"><p style="font-weight:bold;"><?php echo $getsalesproduct['product_quantity']; ?></p></td>
												<td class="text_center"><p style="font-weight:bold;"><?php echo $getsalesproduct['product_price']; ?></p></td>
												<td class="text_center"><p style="font-weight:bold;"><?php echo $getsalesproduct['product_subtotal']; ?></p></td>
											</tr>
										<?php } ?>	
									</tbody>
									<tr class="calculation" style="border-top: 1px solid gray; margin-top: 5px;">
										
										<th colspan="3" style="font-size: 13px;"><span class="float_right">Sub Total =</span></th>
										<th colspan="1" class="text_right" style="font-size: 13px;"><?php echo $getlastorderid['sales_subtotal']; ?></th>
									</tr>
									<?php if($getlastorderid['sales_discount'] != 0){ ?>
										<tr class="calculation">
											
											<th colspan="3" style="font-size: 13px;"><span class="float_right">Discount =</span></th>
											<th colspan="1" class="text_right" style="font-size: 13px;"><?php echo $getlastorderid['sales_discount']; ?></th>
										</tr>
									<?php } ?>
									<tr class="calculation">
										<?php if($getpossetting['vat_type']=='global'){ ?>
											<th colspan="3" style="font-size: 13px;"><span class="float_right">VAT(<?php echo $getpossetting['vat']; ?>%) =</span></th>
											<?php } else{ ?>
											<th colspan="3" style="font-size: 13px;"><span class="float_right">Total VAT =</span></th>
										<?php } ?>
										<th colspan="1" class="text_right" style="font-size: 13px;"><?php echo $getlastorderid['sales_vat']; ?></th>
									</tr>
									<tr class="calculation" style="border-top: 1px solid gray;">
										
										<th colspan="3" style="font-size: 13px;" ><span class="float_right" >Grand Total =</span></th>
										<th colspan="1" style="font-size: 13px;" class="text_right"><?php echo $getlastorderid['sales_total']; ?></th>
									</tr>
									<tr>
										<td colspan='4'><?php echo ucwords($numberTransformer->toWords(ceil($getlastorderid['sales_total'])));  ?>*</td>
									</tr>
									<tr style='border-top:1px dashed'>
										<td colspan='2' class='text_right'>Payment Type</td>
										<td colspan='2' class='text_right' ><?php echo $getlastorderid['payment_method']; ?></td>
									</tr>
									<?php if($getlastorderid['payment_method']!='cash'){ ?>
										<tr>
											<td colspan='2' class='text_right'>Transaction Id</td>
											<td colspan='2' class='text_right' ><?php echo $getlastorderid['transition_id'];?></td>
										</tr>
									<?php }?>
									
									<tr>
										<td colspan='2' class='text_right'>Cash Received BDT</td>
										<td colspan='2' class='text_right' ><?php echo $getlastorderid['pay_cash'];?></td>
									</tr>
									<tr style='border-bottom:1px dashed'>
										<td colspan='2' class='text_right'>Change BDT</td>
										<td colspan='2' class='text_right' ><?php echo $getlastorderid['pay_change'];?></td>
									</tr>
								</table>
							</div><!--End Table-->
							
							<div id="legalcopy">
								
								<?php if(!empty($getpossetting['receipt_footer'])){ ?>
									<p class="text_center"><?php echo $getpossetting['receipt_footer']; ?>
									</p>
								<?php } ?>
								<p class="text_center"><?php $barcodeobj = new TCPDFBarcode($getlastorderid['sales_id'], 'C128'); echo $barcodeobj->getBarcodeSVGcode(1.5, 30, 'black'); ?></p>
								<p class="text_center"><strong>Powered by APPSOWL.COM</strong> 
								</p>
							</div>
							
						</div>
						<div class="modal-footer">
							<a href="javascript:void(0);" class="btn btn-white" data-dismiss="modal">Close</a>
							<a class="btn btn-primary last_receipt_print"  href="javascript:void(0);" ><i class="fa fa-print"></i> Print</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
			}else{
		?>
		<div class="modal inmodal fade" id="resipt" tabindex="-1" role="dialog"  aria-hidden="true">
			<div class="modal-dialog modal-sm">
				<div class="modal-content receiptview" style="width: 450px;">
					<div class="modal-body" id="invoice-POS">
						<h3>No Invoice Avaliable On Offline Version. Please Check Online Version</h3>
						<div class="modal-footer">
							<a href="javascript:void(0);" class="btn btn-white" data-dismiss="modal">Close</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		}
		
		break;
		
		case 'GetReceiptPrint':
		$numberToWords = new NumberToWords\NumberToWords();
		$numberTransformer = $numberToWords->getNumberTransformer('en');
		
		if(isset($_GET['id2'])){
			$getlastorderid = $application->getwhereid('sales','sales_id',$_GET['id2']);
			}else{
			$getlastorderid = $application->GetLastOrderReceipt();
		}
		$getpossetting = $application->getsetting();
		$getcustomerid = $application->getwhereid('customer','customer_id',$getlastorderid['customer_id']);
		$getsalesby = $application->getwhereid('users','user_id',$getlastorderid['user_id']);
		$getsalesproducts = $application->getwhere('stock','sales_id',$getlastorderid['sales_id']);
	?>
	<div id="pos-print" >
		<html>
			<head>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<link href="assets/css/pos-print.css?p=<?php echo time(); ?>" rel="stylesheet">
				<title>Point of Sale</title>
			</head>
			<body id="pos-print">
				<center class="pos-logo">
					<?php if(!empty($getpossetting['company_logo'])){ ?>
						
						<?php }else{ ?>
						<p><?php echo $getpossetting['company_name']; ?></p>
					<?php } ?> 
				</center><!--End InvoiceTop-->
				
				<div class="pos-header">
					<p> 
						Address : <?php echo $getpossetting['address']; ?><br>
						Email   : <?php echo $getpossetting['email']; ?><br>
						Phone   : <?php echo $getpossetting['phone']; ?><br>
						
						<?php if(!empty($getpossetting['nbr_no'])){ ?>Vat Reg : <?php echo $getpossetting['nbr_no']; ?>123456 &nbsp;&nbsp; Mushak : <?php echo $getpossetting['nbr_unit']; ?><?php } ?>
					</p>
					
					<div class="pos-header-info">
						<div class="float-left">
							<p>Voucher:  <?php echo $getlastorderid['sales_id']; ?><br>Sale To: <?php if(empty($getlastorderid['customer_id'])){ echo "Walk In Customer"; }else{ if(empty($getcustomerid['customer_name'])){ echo $getcustomerid['customer_phone']; }else{ echo $getcustomerid['customer_name']; }} ?>
							</p>
						</div>
						<div class="float-right">
							<p>
								Date: <?php echo date('d-m-Y',strtotime($getlastorderid['created_at'])); ?> <br>
								Sale By: <?php echo $getsalesby['first_name'].' '.$getsalesby['last_name']; ?>
							</p>
						</div>
					</div>
				</div>
				<table id="pos-product-table">
					<thead>
						<tr>
							<th>Item</th>
							<th>Qty</th>
							<th>Price</th>
							<th>Sub Total</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($getsalesproducts as $getsalesproduct){ 
							$getproductdetails = $application->getwhereid('product','product_id',$getsalesproduct['product_id']);
						?>
						<tr class="item-row">
							<td class="item-name"><?php echo $getproductdetails['product_name']; ?></td>
							<td><?php echo $getsalesproduct['product_quantity']; ?></td>
							<td><?php echo $getsalesproduct['product_price']; ?></td>
							<td><?php echo $getsalesproduct['product_subtotal']; ?></td>
						</tr>
						<?php } ?>
						<tr>
							<td colspan="3" class="total-line">Sub Total =</td>
							<td class="total-value"><?php echo $getlastorderid['sales_subtotal']; ?></td>
						</tr>
						<tr>
							<td colspan="3" class="total-line"><?php echo $getpossetting['vat_type']=='single' ? 'Total VAT' : 'VAT('.$getpossetting['vat'].'%)'; ?> =</td>
							<td class="total-value"><?php echo $getlastorderid['sales_vat']; ?></td>
						</tr>
						<?php if($getlastorderid['sales_discount'] != 0){ ?>
							<tr>
								<td colspan="3" class="total-line">Discount =</td>
								<td class="total-value"><?php echo $getlastorderid['sales_discount']; ?></td>
							</tr>
						<?php } ?>
						
						<tr>
							<td colspan="3" class="total-line">Grand Total =</td>
							<td class="total-value"><?php echo $getlastorderid['sales_total']; ?></td>
						</tr>
						
						<tr>
							<td colspan='4' class="total-text-value"><?php echo ucwords($numberTransformer->toWords(ceil($getlastorderid['sales_total'])));  ?>*</td>
						</tr>
						
						<tr>
							<td colspan="3" class="total-line">Payment Type</td>
							<td class="total-value"><?php echo $getlastorderid['payment_method']; ?></td>
						</tr>
						
						<?php if($getlastorderid['payment_method']!='cash'){ ?>
							<tr>
								<td colspan="3" class="total-line">Transaction Id</td>
								<td class="total-value" ><?php echo $getlastorderid['transition_id'];?></td>
							</tr>
						<?php }?>
						
						<tr>
							<td colspan="3" class="total-line">Cash Received BDT</td>
							<td class="total-value"><?php echo $getlastorderid['pay_cash'];?></td>
						</tr>
						
						<tr class="total-last-row">
							<td colspan="3" class="total-line">Change BDT</td>
							<td class="total-value"><?php echo $getlastorderid['pay_change'];?></td>
						</tr>
					</tbody>			
				</table>
				<div class="legalcopy">
					<?php if(!empty($getpossetting['receipt_footer'])){ ?>
						<p><?php echo $getpossetting['receipt_footer']; ?>
						</p>
					<?php } ?>
					<p><?php $barcodeobj = new TCPDFBarcode($getlastorderid['sales_id'], 'C128'); echo $barcodeobj->getBarcodeSVGcode(1.5, 30, 'black'); ?></p>
					<p><strong>Powered by APPSOWL.COM</strong> 
					</p>
				</div>
			</body>
		</html>
	</div>
	<?php
		
		break;
		
		case 'updatecustomer':
        $application->getcustomerupdate($_POST['data'],Session::get("user_id"),Session::get("store_id"));
		break;
		
		default:
		break;
	}
	
	
