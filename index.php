<?php 
	include dirname(__FILE__) .'/include/header.php';
	// if (!$application->isLoggedIn()) {
	// redirect('login.php',$url);
	// }
	
	// if (!$application->isActivate()) {
	// redirect('confirmation.php',$url);
	// }
	
	
	
?>
<div class="row border-bottom">
	<nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
		<div class="navbar-header">
			<a class="minimalize-styl-2 btn btn-primary " href="javascript:void(1)">
			<span style="padding: 8px;">User ID: <?php echo Session::get("store_id"); ?></span> </a>
		</div>
		<ul class="nav navbar-left">
			<li>
				<h1 class="text-info" id="time_now"></h1>
			</li>
		</ul>
		<ul class="nav navbar-top-links navbar-right">
			<li class="text-info">
				<span class="poststatus">Last Update: <?php echo date('y-m-d h:m:s') ?></span>
			</li>
			<li>
				<a  class="test" href="javascript:void(0);" onclick="GetMenualUpdate()">
					<span class="btn btn-info btn-sm"> <i class="fa fa-check"></i> Update</span>
				</a>
			</li>
			<li>
				<a  class="test" onclick="window.open('', '_self', ''); window.close();" >
					<span class="btn btn-warning btn-sm"> <i class="fa fa-close"></i> Close</span>
				</a>
			</li>
			<li>
				<a  class="test" href="logout.php" >
					<span class="btn btn-danger btn-sm"> <i class="fa fa-sign-out"></i> Logout</span>
				</a>
			</li>
		</ul>
		
	</nav>
</div>
<div class="wrapper wrapper-content">
	<div class="row AddPosAdvance">
		<form id="PosSalesForm">
			<input type="hidden" name="sales_id" value="" readonly />
			<div class="col-sm-7">
				<div class="ibox">
					<div class="ibox-title">
						<h3>Pos Terminal | Sales ID : <?php 
							$GetSettingUpdate = $application->getapidata('GetSettingUpdate',null,true);
							print_r($GetSettingUpdate); 
						?><span class="sales_id_show"></span></h3>
						<div class="pull-right" style="margin-top:-30px;">
							<a href="javascript:void(0)" class="last_receipt" data-toggle="modal">
								<i class="fa fa-receipt" title="Last Receipt"></i>
							</a>
						</div>
					</div>
					<div class="ibox-content">
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon"> <i class="fa fa-user"></i> </span>
										<input type="text" class="form-control customer_name_code_search" name="customer_code" id="customer_code" placeholder="Enter Customer Name or Customer ID" value="" />
										<div class="input-group-btn">
											<button type="button" class="btn btn-default OpenContactModal" title="New Contact"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group typeahead__container">
										<span class="input-group-addon">
											<i class="fa fa-barcode"></i>
										</span>
										<input type="text" class="form-control barcode_type_search" autocomplete="off" placeholder="Enter Product Name / Scan Barcode" id="sales_barcode" name="sales_barcode" autofocus />
									</div>
								</div>
							</div>
						</div>
						<div class="row full">
							<div class="col-sm-12 table-responsive">
								<table class="table table-striped table-sm" id="SalesTable" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>
												Product Name
											</th>
											<th>
												Unit Cost
											</th>
											<th>
												Quantity
											</th>
											<th>
												Subtotal
											</th>
											<th><i class="fa fa-trash"></i></th>
										</tr>
									</thead>
									<tbody>
										
										<tr id="">
											<td>
												
												<input type="hidden" value="" name="sub_product_id[]">
												<input type="hidden" value="" name="product_id[]">
												<input type="hidden" value="" name="product_stock_id[]">
												<input type="hidden" value="" name="product_vat[]" data-id="" id="product_vat_">
												<input type="hidden" data-id="" id="total_product_vat_" name="total_product_vat[]" value="">
											</td>
											<td>
												<input type="text" value="" name="product_price[]" data-id="<" id="product_price_" class="form-control input-sm onchange_sales_cal" placeholder="">
											</td>
											<td>
												<div class="input-group m-b">
													<input type="number" value="" name="product_quantity[]" data-id="" id="product_quantity_" class="form-control input-sm onchange_sales_cal onchange_sales_qty" placeholder="">
													<span class="input-group-addon">PC</span>
												</div>
											</td>
											<td>
												<input type="text" readonly="" value="" name="product_subtotal[]" data-id="" id="product_subtotal_" class="form-control input-sm onchange_sales_cal onchange_sales_subtotal" placeholder="">
											</td>
											<td>
												<a href="javascript:void(0);" data-stock-id="" data-id="" class="btn btn-danger btn-xs sales_product_delete"><i class="fa fa-trash"></i></a>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12 ">
								<div class="panel panel-default">
									<div class="panel-body">
										<div class="row">
											<div class="col-sm-3">
												<div class="form-group">
													<label class="control-label">
													Subtotal:</label>
													<br>
													<input class="form-control input-sm" type="text" readonly name="sales_sub_total" value="">
												</div>
											</div>
											<div class="col-sm-3">
												
												<div class="form-group">
													<label class="control-label">
													Vat:</label>
													<br>
													<input class="form-control input-sm" type="text" readonly name="sales_vat" value="">
												</div>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<div class="row">
														<div class="col-sm-12">
															<label class="control-label"><small>Discount </small></label>
															<span class="discount_amount_value pull-right"></span>
														</div>
														<div class="col-sm-5">
															<select class="input-sm onchange_discount_type_update" name="sales_discount_type">
																<option value="percent">%</option>
																<option value="fixed">Tk</option>
															</select>
														</div>
														<div class="col-sm-7">
															<input class="form-control input-sm onchange_sales_final_cal" type="text" name="salesdiscount_type_amount" value="0">
															<input type="hidden" name="sales_discount" value="0">
														</div>
														
													</div>
												</div>
											</div>
											<div class="col-sm-3">
												
												<div class="form-group">
													<label class="control-label">
													Total:</label>
													<br>
													<input class="form-control input-sm onchange_sales_final_cal" type="text" readonly name="sales_total" value="">
												</div>
											</div>
											<hr>
											<div class="col-sm-3">
												
												<div class="form-group">
													<label class="control-label">
													Need To Pay:</label>
													<br>
													<input class="form-control input-sm transaction-total-bill" type="text" readonly name="sales_need_to_pay" value="">
												</div>
											</div>
											
											<div class="col-sm-6">
												
												<div class="form-group">
													<label class="control-label">
													Receive Amount:</label>
													<br>
													<input class="form-control input-sm payment-total-value onchange_sales_final_cal" type="text" name="sales_receive_amount" value="">
												</div>
											</div>
											<div class="col-sm-3 pay_change_label">
												<div class="form-group">
													<label class="control-label">
													Pay Change:</label>
													<br>
													<input class="form-control input-sm" type="text" readonly name="sales_pay_change" value="">
												</div>
											</div>
											<div class="col-sm-3 due_amount_label due hidden">
												<div class="form-group">
													<label class="control-label">
													Due Amount:</label>
													<br>
													<input class="form-control input-sm" type="text" readonly name="due_amount" value="">
												</div>
											</div>
										</div>
										<div class="clearfix"></div>
										<div class="row">
											<div class="col-sm-2">
												<button type="button" class="btn btn-danger btn-block SalesReseatNow"><i class="fa fa-delete"></i>
													Reset
												</button>
												<button type="submit" value="sales_update" class="btn btn-info btn-block"><i class="fa fa-update"></i>
													Update
												</button>
											</div>
											
											<div class="col-sm-4">
												<button type="button" class="btn btn-primary btn-block btn-lg multipay"><i class="fa fa-check"></i>
													Multipay
												</button>
											</div>
											
											<div class="col-sm-3">
												<button type="submit" value="cash_payment" class="btn btn-success btn-block btn-lg" data-original-content="cash"><i class="fa fa-check"></i>
													Cash
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
					</div>
				</div>
			</div>
			<div class="modal_status"></div>
			<div class="modal fade in show_payment_modal">
				<div class="modal-dialog modal-lg full-width-modal-dialog" role="document">
					<div class="modal-content full-width-modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
							<h4 class="modal-title">Multiple Payment</h4>
						</div>
						<div class="modal-body">
							<div id="payment_div" data-total-bill=""></div>
						</div>
						<div class="modal-footer">
							<button type="submit" value="multi_payment" data-original-content="Finalize Amount" class="btn btn-primary">
								Finalize Amount
							</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">
								Close
							</button>
						</div>
					</div>
				</div>
			</div>
			
		</form>
		<div class="col-sm-5">
			<div class="ibox">
				<div class="ibox-content">
					<div class="row">
						<div class="m-b-sm  col-md-4">
							<input type="text" class="form-control product_search_by_name_code" placeholder="Search By Product" id="product_name_search">
						</div>
						<div class="m-b-sm  col-md-4">
							<select class="selectpicker form-control product_search" data-show-subtext="true" data-live-search="true" style="height: 150px;" id="product_brand">
								<option value="">
									Search By Brand
								</option>
							</select>
						</div>
						<div class="m-b-sm  col-md-4">
							<select class="selectpicker form-control product_search" data-show-subtext="true" data-live-search="true" style="height: 150px;" id="product_category">
								<option value="">
									Search By Category
								</option>
								
							</select>
						</div>
					</div>
					<div class="row " id="product_list"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="outside_modal_status"></div>
	<div class='last_receipt_view hidden'></div>
	
	<div class="showError"></div>
</div>
<!-- receipt inovice modal -->
<div class="receiptview"></div>
<div class='last_receipt_view hidden'></div>
<!-- end  receipt inovice modal-->

<!-- New Customer START-->
<div id="customer-form" class="modal fade" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<h3 class="m-t-none m-b">New Customer</h3>
						<form>
							<div class="form-group">
								<label>Customer name</label>
								<input type="hidden" name="customer_id" class="form-control" id="customer_id" value="null">
								<input type="text" name="customer_name" class="form-control" id="customer_name" placeholder="Customer name" >
							</div>
							<div class="form-group">
								<label>Customer Address</label>
								<textarea name="customer_address" class="form-control" id="customer_address" placeholder="Customer Address"></textarea>
							</div>
							<div class="form-group">
								<label>Customer Phone</label>
								<input type="text" name="customer_phone" class="form-control" id="customer_phone" placeholder="Customer Phone">
							</div>
							<div class="form-group">
								<label>Customer Email</label>
								<input type="text" name="customer_email" class="form-control" id="customer_email" placeholder="Customer Email">
							</div>
							<div class="form-group">
								<label>Custoemr Due</label>
								<input type="text" name="customer_due" class="form-control" id="customer_due" placeholder="Customer Due">
							</div>
							<button class="btn btn-sm btn-primary pull-right m-t-n-xs" type="submit"><strong>Submit</strong></button>
							<a class="btn btn-sm btn-danger pull-right m-t-n-xs" data-dismiss="modal" ><strong>Close</strong></a>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	// setInterval(function(){
	// jQuery.ajax({
	// url: "ajax.php",
	// data: {
	// action	 : "AutoUpdate"
	// },
	// type: "POST",
	// success:function(data){
	// $(".poststatus").html(data);
	// },
	// error:function (){}
	// });
	// }, 30000);
</script>
<?php include dirname(__FILE__) .'/include/footer.php';?>	

