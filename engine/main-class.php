<?php
	class MainEngine
	{
		private $db_type = "sqlite"; 
		
		private $db_sqlite_path = "./store.db";
		
		private $db_connection = null;
		
		private $software_version = "1.0.1";
		
		private $root_domain = "http://admin.appsowl.com:8080";
		
		private $user_is_logged_in = false;
		
		public function __construct()
		{
			
		}
		
		private function createDatabaseConnection()
		{
			try {
				$this->db_connection = new PDO($this->db_type . ':' . $this->db_sqlite_path);
				return true;
				} catch (PDOException $e) {
				$this->feedback = "PDO database connection problem: " . $e->getMessage();
				} catch (Exception $e) {
				$this->feedback = "General problem: " . $e->getMessage();
			}
			return false;
		}
		
		public function getUserLogin($data){
			$this->validateLoginFields($data['username'], $data['password']);
			$result = $this->select(
            "SELECT * FROM `users`
			WHERE `username` = :u AND `password` = :p",
            array("u" => $data['username'], "p" => $data['password'])
			);
			
			if (count($result) !== 1) {
				if($this->check_internet_connection()){
					$output = shell_exec("echo | {$_ENV['SYSTEMROOT']}\System32\wbem\wmic.exe path win32_computersystemproduct get uuid");
					$encodekey = preg_replace('/[^\p{L}\p{N}\s]/u', '', $output);
					$encodekey = preg_replace('/\s+/', '', $encodekey);
					$post_data = '{ "action" : "GetLoginAuth", "username" : "'.$data['username'].'", "password" : "'.$data['password'].'", "key" : "'.$encodekey.'", "pc_name" : "'.$_ENV['COMPUTERNAME'].'", "last_ip" : "'.$_SERVER['REMOTE_ADDR'].'" }';
					$ch = curl_init($this->root_domain."/api.php");
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
					curl_setopt($ch, CURLOPT_VERBOSE, true);	  
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($post_data))                                                                       
					);  
					$result = curl_exec($ch);
					curl_close($ch);
					$result = json_decode($result);
					
					if(isset($result->status) && $result->status == "invalide"){
						respond(array(
						'status' => 'error',
						'errors' => array(
						'username' => 'Username dose not match',
						'password' => "Password dose not match"
						)
						), 422);
					}
					
					if(isset($result->api_status) && $result->api_status != "active"){
						respond(array(
						'status' => 'error',
						'errors' => array(
						'username' => $result->api_msg
						)
						), 422);
					}
					
					if($result->status == "active"){
						if($this->getwhere("users","user_id",$result->user_id)){
							$this->update("users" ,  array(
							"username" => $result->username,
							"password" => $result->password,
							"first_name" => $result->first_name,
							"last_name" => $result->last_name,
							"email" => $result->email,
							"phone" => $result->phone,
							"store_id" => $result->store_id,
							),
							"`user_id` = :id ",
							array("id"  => $result->user_id)
							);
							}else{
							$this->insert("users", array(
							"user_id" => $result->user_id,
							"username" => $result->username,
							"password" => $result->password,
							"first_name" => $result->first_name,
							"last_name" => $result->last_name,
							"email" => $result->email,
							"phone" => $result->phone,
							"store_id" => $result->store_id,
							));
						}
						
						if($this->getwhere("setting","id",1)){
							$this->update("setting" ,  array(
							"api_url" => $result->domain_name,
							"last_update_delete" => date('d-m-Y h:i:s'),
							),
							"`id` = :id ",
							array("id"  => 1)
							);
							}else{
							$this->insert("setting", array(
							"id"  => 1,
							"api_url" => $result->domain_name,
							"last_update_delete" => date('d-m-Y h:i:s'),
							));
						}
						Session::set("user_id", $result->user_id);
						Session::set("store_id", $result->store_id);
						
						respond(array(
						"status" => "success",
						"page" => "index.php"
						));
						
					}
					
					}else{
					respond(array(
					'status' => 'error',
					'errors' => array(
					'username' => 'You are not connected to the Internet.',
					'password' => ""
					)
					), 422);
				}
				}else{
				Session::set("user_id", $result[0]['user_id']);
				Session::set("store_id", $result[0]['store_id']);
				respond(array(
				"status" => "success",
				"page" => "index.php"
				));
			}
		}
		
		public function getapidata($action,$bind = null,$decode = null,$jsondata = null)
		{
			if($this->check_internet_connection()){
				
				$getsetting = $this->getwhereid("setting","id",1);
				
				$output = shell_exec("echo | {$_ENV['SYSTEMROOT']}\System32\wbem\wmic.exe path win32_computersystemproduct get uuid");
				$encodekey = preg_replace('/[^\p{L}\p{N}\s]/u', '', $output);
				$encodekey = preg_replace('/\s+/', '', $encodekey);
				
				if($bind){
					$get = '{ '.$bind.' }';
					}elseif($jsondata){
					$get = $jsondata;
					}else{
					$get = '{"action" : "'.$action.'", "key" : "'.$encodekey.'"}';
				}
				
				$ch = curl_init($getsetting['api_url']);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_VERBOSE, true);	  
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$get);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
				'Content-Type: application/json',                                                                                
				'Content-Length: ' . strlen($get))                                                                       
				);  
				$result = curl_exec($ch);
				
				$result_check = json_decode($result);
				
				if(isset($result_check->api_status) && $result_check->api_status != "active"){
					echo "<span style='color:red;'>".$result_check->api_msg."</span>";
					return false;
				}
				
				curl_close($ch);
				
				if(!$decode){
					$result = $this->jsonDecode($result);
				}
				
				return $result;
				
				}else{
				echo "<span style='color:red;'>You are not connected to the Internet.</span>";
				return false;
			}
		}
		
		public function getsetting(){
			return $this->getwhereid("setting","id",1);
		}
		
		public function SyncSettingUpdate(){
			$GetSettingUpdate = $this->getapidata('GetSettingUpdate');
			if($GetSettingUpdate){
				$PosSetting = $GetSettingUpdate->PosSetting;
				$ProductCategory = $GetSettingUpdate->ProductCategory;
				$ProductUnit = $GetSettingUpdate->ProductUnit;
				$Products = $GetSettingUpdate->Products;
				$Customers = $GetSettingUpdate->Customers;
				
				if($this->getwhere("setting","id",1)){
					$this->update("setting" ,  array(
					"company_name" => $PosSetting->company_name,
					"currency" => $PosSetting->currency,
					"address" => $PosSetting->address,
					"email" => $PosSetting->email,
					"phone" => $PosSetting->phone,
					"website" => $PosSetting->website,
					"receipt_footer" => $PosSetting->receipt_footer,
					"vat" => $PosSetting->vat,
					"vat_type" => $PosSetting->vat_type,
					"company_logo" => $PosSetting->company_logo,
					"invoice_logo" => $PosSetting->invoice_logo,
					"nbr_no" => $PosSetting->nbr_no,
					"nbr_unit" => $PosSetting->nbr_unit,
					"pos_active" => $PosSetting->pos_active,
					"pos_renew_date" => $PosSetting->pos_renew_date,
					),
					"`id` = :id ",
					array("id"  => 1)
					);
					}else{
					$this->insert("setting", array(
					"id"  => 1,
					"company_name" => $PosSetting->company_name,
					"currency" => $PosSetting->currency,
					"address" => $PosSetting->address,
					"email" => $PosSetting->email,
					"phone" => $PosSetting->phone,
					"website" => $PosSetting->website,
					"receipt_footer" => $PosSetting->receipt_footer,
					"vat" => $PosSetting->vat,
					"vat_type" => $PosSetting->vat_type,
					"company_logo" => $PosSetting->company_logo,
					"invoice_logo" => $PosSetting->invoice_logo,
					"nbr_no" => $PosSetting->nbr_no,
					"nbr_unit" => $PosSetting->nbr_unit,
					"pos_active" => $PosSetting->pos_active,
					"pos_renew_date" => $PosSetting->pos_renew_date,
					));
				}
				
				foreach($ProductCategory as $GetProductCategory){
					if($this->getwhere("category","category_id",$GetProductCategory->category_id)){
						if(!$this->getwhereand("category","category_id",$GetProductCategory->category_id,"category_name",$GetProductCategory->category_name)){
							$this->update("category" ,  array(
							"category_name" => $GetProductCategory->category_name,
							"user_id" => $GetProductCategory->user_id,
							),
							"`category_id` = :id ",
							array("id"  => $GetProductCategory->category_id)
							);
						}
						}else{
						$this->insert("category", array(
						"category_id" => $GetProductCategory->category_id,
						"category_name" => $GetProductCategory->category_name,
						"user_id" => $GetProductCategory->user_id,
						));
					}
				}
				
				foreach($ProductUnit as $GetProductUnit){
					if($this->getwhere("unit","unit_id",$GetProductUnit->unit_id)){
						if(!$this->getwhereand("unit","unit_id",$GetProductUnit->unit_id,"unit_name",$GetProductUnit->unit_name)){
							$this->update("unit" ,  array(
							"unit_name" => $GetProductUnit->unit_name,
							"user_id" => $GetProductUnit->user_id,
							),
							"`unit_id` = :id ",
							array("id"  => $GetProductUnit->unit_id)
							);
						}
						}else{
						$this->insert("unit", array(
						"unit_id" => $GetProductUnit->unit_id,
						"unit_name" => $GetProductUnit->unit_name,
						"user_id" => $GetProductUnit->user_id,
						));
					}
				}
				
				foreach($Products as $GetProduct){
					if($this->getwhere("product","product_id",$GetProduct->product_id)){
						$this->update("product" ,  array(
						"product_name" => $GetProduct->product_name,
						"sell_price" => $GetProduct->sell_price,
						"product_vat" => $GetProduct->product_vat,
						"size" => $GetProduct->size,
						"unit" => $GetProduct->unit,
						"category_id" => $GetProduct->category_id,
						"supplier_id" => $GetProduct->supplier_id
						),
						"`product_id` = :id ",
						array("id"  => $GetProduct->product_id)
						);
						}else{
						$this->insert("product", array(
						"product_id" => $GetProduct->product_id,
						"product_name" => $GetProduct->product_name,
						"sell_price" => $GetProduct->sell_price,
						"product_vat" => $GetProduct->product_vat,
						"size" => $GetProduct->size,
						"unit" => $GetProduct->unit,
						"category_id" => $GetProduct->category_id,
						"supplier_id" => $GetProduct->supplier_id,
						"stock" => 0,
						"store_id" => Session::get("store_id")
						));
					}
				}
				
				foreach($Customers as $GetCustomer){
					if(!$this->getwhere("customer","customer_id",$GetCustomer->customer_id)){
						$this->insert("customer", array(
						"customer_id" => $GetCustomer->customer_id,
						"store_id" => $GetCustomer->store_id,
						"user_id" => $GetCustomer->user_id,
						"customer_name" => $GetCustomer->customer_name,
						"customer_phone" => $GetCustomer->customer_phone,
						"sync_status" => 1
						));
					}
				}
				
				$this->update("setting", array("last_update_setting" => date('d-m-Y h:i:s')),"`id` = :id ", array("id"  => 1));
				return true;
				}else{
				return false;
			}
		}
		
		
		public function SyncUpdateStock(){
			$getallsales = $this->getwhere('sales','sync_status',0);
			$getallstock = $this->getwhere('stock','sync_status',0);
			$getallcustomer = $this->getwhere('customer','sync_status',0);
			$myObj = new stdClass;
			$myObj->action = "GetStockUpdate";
			$myObj->customer = $getallcustomer;
			$myObj->sales = $getallsales;
			$myObj->stock = $getallstock;
			$myObj->store_id = Session::get("store_id");
			$myObj = json_encode($myObj);
			$GetStockUpdate = $this->getapidata(null,null,null,$myObj);
			if($GetStockUpdate){
				if(isset($GetStockUpdate->sales_ids)){
					if($GetStockUpdate->sales_ids){
						foreach($GetStockUpdate->sales_ids as $sales_id){
							$this->update("sales" ,  array(
							"sync_status" => 1,
							),
							"`sales_id` = :id ",
							array("id"  => $sales_id)
							);
						}
					}
				}
				if(isset($GetStockUpdate->stock_ids)){
					if($GetStockUpdate->stock_ids){
						foreach($GetStockUpdate->stock_ids as $stock_id){
							$this->update("stock" ,  array(
							"sync_status" => 1,
							),
							"`stock_id` = :id ",
							array("id"  => $stock_id)
							);
						}
					}
				}
				if(isset($GetStockUpdate->customer_ids)){
					foreach($GetStockUpdate->customer_ids as $customer_id){
						$this->update("customer" ,  array(
						"sync_status" => 1,
						),
						"`customer_id` = :id ",
						array("id"  => $customer_id)
						);
					}
				}
				if(isset($GetStockUpdate->customer_ids)){
					foreach($GetStockUpdate->product_stock as $product_id => $stock ){
						if(!$this->getwhereand("product","product_id",$product_id,"stock",$stock)){
							$this->update("product" ,  array(
							"stock" => $stock,
							),
							"`product_id` = :id ",
							array("id"  => $product_id)
							);
						}
					}
				}
				$this->update("setting", array("last_update_stock" => date('d-m-Y h:i:s'),),"`id` = :id ", array("id"  => 1));
				return true;
				}else{
				return false;
			}
		}
		
		
		
		
		public function GetSyncDelete(){
			$getallsales = $this->getwhere('sales','sync_status',1);
			$getallstocks = $this->getwhere('stock','sync_status',1);
			
			foreach($getallsales as $getallsale){
				$this->delete("sales", "sales_id = :el", array( "el" => $getallsale['sales_id'] ));
			}
			
			foreach($getallstocks as $getallstock){
				$this->delete("stock", "stock_id = :el", array( "el" => $getallstock['stock_id'] ));
			}
			$this->update("setting", array("last_update_delete" => date('d-m-Y h:i:s'),),"`id` = :id ", array("id"  => 1));
			return true;
		}
		
		
		
		
		
		
		public function check_internet_connection() 
		{
			return (bool) @fsockopen('ssl://appsowl.com', 443, $iErrno, $sErrStr, 5);
		}
		
		public function isActivate()
		{
			$result = $this->getwhereid("setting","id",1);
			
			if (empty($result['api_key'])) {
				return false;
			}
			return true;
		}
		
		public function isLoggedIn()
		{
			if (Session::get("user_id") == null) {
				return false;
			}
			$result = $this->select(
			"SELECT `user_id` FROM `users`
			WHERE `user_id` = :u",
			array("u" => Session::get("user_id"))
			);
			if (count($result)!== 1) {
				$this->logout();
				return false;
			}
			return true;
		}
		
		public function logout()
		{
			Session::destroySession();
		}
		
		private function validateLoginFields($username, $password)
		{
			$errors = array();
			
			if ($username == "") {
				$errors['username'] = "Phone Number Required";
			}
			
			if ($password == "") {
				$errors['password'] = "Password Required";
			}
			
			return $errors;
		}
		
		
		
		public function jsonDecode($jsondata) { 
			$output = shell_exec("echo | {$_ENV['SYSTEMROOT']}\System32\wbem\wmic.exe path win32_computersystemproduct get uuid");
			$encodekey = preg_replace('/[^\p{L}\p{N}\s]/u', '', $output);
			$encodekey = preg_replace('/\s+/', '', $encodekey);
			$firebase = new \Firebase\JWT\JWT;
			try {
				$decoded = $firebase->decode($jsondata, $encodekey, array('HS256'));
				return $decoded;
				} catch (Exception $e) {
				$myObj = new stdClass;
				$myObj->error = $e->getMessage();
				$myObj->status = 404;
				return $myObj;
			}
		}
		
		
		
		public function explode_time($time) { //explode time and convert into seconds
			$time = explode(':', $time);
			$time = $time[0] * 3600 + $time[1] * 60;
			return $time;
		}
		
		public function update($table, $data, $where, $whereBindArray = array())
		{
			$this->createDatabaseConnection();
			ksort($data);
			
			$fieldDetails = null;
			
			foreach ($data as $key => $value) {
				$fieldDetails .= "`$key`=:$key,";
			}
			
			$fieldDetails = rtrim($fieldDetails, ',');
			
			$sth = $this->db_connection->prepare("UPDATE $table SET $fieldDetails WHERE $where");
			
			foreach ($data as $key => $value) {
				$sth->bindValue(":$key", $value);
			}
			
			foreach ($whereBindArray as $key => $value) {
				$sth->bindValue(":$key", $value);
			}
			
			$sth->execute();
		}
		
		public function select($sql, $array = array(), $fetchMode = PDO::FETCH_ASSOC)
		{
			$this->createDatabaseConnection();
			$sth = $this->db_connection->prepare($sql);
			
			foreach ($array as $key => $value) {
				$sth->bindValue(":$key", $value);
			}
			
			$sth->execute();
			
			return $sth->fetchAll($fetchMode);
		}
		
		public function insert($table, array $data)
		{
			$this->createDatabaseConnection();
			ksort($data);
			
			$fieldNames = implode('`, `', array_keys($data));
			$fieldValues = ':' . implode(', :', array_keys($data));
			
			$sth = $this->db_connection->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");
			
			foreach ($data as $key => $value) {
				$sth->bindValue(":$key", $value);
			}
			
			$sth->execute();
		}
		
		public function delete($table, $where, $bind = array(), $limit = null)
		{
			
			$this->createDatabaseConnection();
			$query = "DELETE FROM $table WHERE $where";
			
			if ($limit) {
				$query .= " LIMIT $limit";
			}
			
			$sth = $this->db_connection->prepare($query);
			
			foreach ($bind as $key => $value) {
				$sth->bindValue(":$key", $value);
			}
			
			$sth->execute();
		}
		
		public function getwhereandid($col,$where,$id,$and,$andid)
		{ 
			$result = $this->select("SELECT * FROM `$col` WHERE `$where` = :id AND `$and` = :andid", array( 'id' => $id, 'andid' => $andid));
			return count($result) > 0 ? $result[0] : null;
			
		}
		
		public function getwhereand($col,$where,$id,$and,$andid)
		{ 
			$result = $this->select("SELECT * FROM `$col` WHERE `$where` = :id AND `$and` = :andid", array( 'id' => $id, 'andid' => $andid));
			return $result;
			
		}  
		
		public function getwhereid($col,$where,$id)
		{ 
			$result = $this->select("SELECT * FROM `$col` WHERE `$where` = :id", array( 'id' => $id));
			return count($result) > 0 ? $result[0] : null;
			
		} 
		
		public function getwhere($col,$where,$id)
		{ 
			$result = $this->select("SELECT * FROM `$col` WHERE `$where` = :id", array( 'id' => $id));
			return $result;
		}
		
		public function getall($col)
		{ 
			$result = $this->select("SELECT * FROM `$col` ");
			return $result;
			
		}
		
		public function getsumtotalbywhereand($col,$sum,$where,$id,$and,$andid) {
			$this->createDatabaseConnection();
			$query = $this->db_connection->prepare("SELECT sum($sum) FROM `$col` WHERE `$where` = ? AND `$and` = ? ");
			$query->bindValue(1, $id);
			$query->bindValue(2, $andid);
			try{ $query->execute();     
				$rows =  $query->fetch();
				return $rows[0];
				
			} catch (PDOException $e){die($e->getMessage());}  
		}
		
		//------------Pos-------------//
		
		public function GetcustomerDetails($data)
		{ 
			if($data['customer_id'] != 'null'){
				$result = $this->select("SELECT customer_id,customer_phone FROM `customer` WHERE `customer_id` = :id", array( 'id' => $data['customer_id']));
				foreach($result as $results);
				
				respond(array(
				"status" => "success",
				"cid" => $results['customer_id'],
				"phone" => $results['customer_phone']
				));
				}elseif($data['customer_phone'] != 'null'){
				$result = $this->select("SELECT customer_id,customer_phone FROM `customer` WHERE `customer_phone` = :id", array( 'id' => $data['customer_phone']));
				foreach($result as $results);
				
				respond(array(
				"status" => "success",
				"cid" => $results['customer_id'],
				"phone" => $results['customer_phone']
				));
				}else{
				respond(array(
				"status" => "success",
				"cid" => "",
				"phone" => ""
				));
			}
		}
		
		public function getproductbysearch($where,$id)
		{ 
			$result = $this->select("SELECT * FROM `product` WHERE `$where` LIKE :id", array( 'id' => $id));
			return $result;
		}
		
		public function GetProductStock($product_id)
		{ 
			$totalproductin = $this->getwhereid('product','product_id',$product_id);
			$totalproductout = $this->getsumtotalbywhereand('stock','product_quantity','stock_type','out','product_id',$product_id);
			$totalavaliblestock = $totalproductin['stock'] - $totalproductout;
			return $totalavaliblestock;
		}
		
		public function insertcustomerphone($phone,$userid,$storeid) {
			$customerid = 'CO'.gettoken(8);
			$sql = $this->insert("customer",  array("user_id"=> $userid, "customer_id"=> $customerid, "customer_phone"=> $phone, "sync_status"=> 0, "store_id"=> $storeid, "created_at" => date("Y-m-d H:i:s")));
			return $customerid;
		}
		
		public function GetCustomerOrder($data,$id,$store_id){
			$sales_id = 'SA'.gettoken(8);
			
			$sql = $this->insert("sales",array(
			"sales_id"=> $sales_id,
			"store_id"=> $store_id,
			"customer_id"=> $data['customer_id'],
			"sales_discount"=> $data['discount_amount'],
			"sales_vat"=> $data['order_vat'],
			"sales_subtotal"=> $data['order_subtotal'],
			"sales_status"=> $data['order_status'],
			"sales_total"=> $data['need_to_pay'],
			"pay_cash"=> $data['paid_amount'],
			"pay_change"=> $data['pay_change'],
			"payment_method"=> $data['payment_method'],
			"return_id"=> $data['return_id'],
			"return_total"=> $data['return_amount'],
			"transition_id"=> $data['transition_id'],
			"sales_note"=> $data['sales_note'],
			"sync_status" => 0,
			"user_id"=> $id,
			"created_at"=> date("Y-m-d H:i:s")
			));
			
			return $sales_id;
		}
		
		public function GetcustomerOrderProduct($customer_id,$product_id,$sales_id,$product_price,$product_quantity,$product_subtotal,$user_id,$store_id){
			$stockid = 'ST'.gettoken(8);
			$sql = $this->insert("stock",array(
			"stock_id"=> $stockid,
			"product_id"=> $product_id,
			"customer_id"=> $customer_id,
			"sales_id"=> $sales_id,
			"product_price"=> $product_price,
			"product_quantity"=> $product_quantity,
			"product_subtotal"=> $product_subtotal,
			"store_id"=> $store_id,
			"user_id"=> $user_id,
			"stock_type"=> 'out',
			"stock_category"=> 'sales',
			"stock_status"=> 1,
			"sync_status" => 0,
			"created_at"=> date("Y-m-d H:i:s")
			));
			
			return $stockid;
		}
		
		public function GetLastOrderReceipt($userId = null)
		{ 
			if($userId == null){
				$result = $this->select("SELECT * FROM `sales` ORDER BY `id` DESC LIMIT 1 ");
				}else{
				$result = $this->select("SELECT * FROM `sales` WHERE `user_id` = :ids ORDER BY `id` DESC LIMIT 1 ", array( 'ids' => $userId));
			}
			return count($result) > 0 ? $result[0] : null;
		}
		//------------Pos-------------//
		
		public function getcustomerupdate($data,$userid,$store_id) {
			$customerid = 'CO'.gettoken(8);
			
			$sql = $this->insert("customer",  array("customer_name"=> $data['customer_name'],
			"customer_id"=> $customerid,
			"store_id"=> $store_id,
			"user_id"=> $userid,
			"customer_address"=> $data['customer_address'],
			"customer_phone"=> $data['customer_phone'],
			"customer_email"=> $data['customer_email'],
			"customer_due"=> $data['customer_due'],
			"sync_status"=> 0,
			"created_at"      => date("Y-m-d H:i:s"),
			));
			
			respond(array(
			"status" => "success",
			"message" => "This ".$data['customer_name']."Customer Added Successfully"
			));
			return $customerid;
		}
		
		
	}
