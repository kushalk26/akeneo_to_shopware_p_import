<?php
ini_set('max_execution_time', -1);
require("/config_shopware.php");
require("/config.php");

if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
} else{
	
}


$filename = "identifier_string.txt"; 
$fp = fopen($filename, "r");

$content = fread($fp, filesize($filename));
$lines_data = explode(",", $content);

/*if(!empty($lines)){
	$data_ary = $lines[0];
	
}*/


$max_page = 2000;
$limit = 5;
$productcount =1;

/****akeneo connection information****/

$akeneo_client_id="akeneo_client_id";
$akeneo_client_secret="akeneo_client_secret";
$akeneo_username="test";
$akeneo_password="test";
$akeneo_url = "akeneo_url";

/*****shopware info****/

//store information
$shopware_store_url = "https://test";
$endpoint = "$shopware_store_url/api/oauth/token";
$client_id = "client_id";
$client_secret = "client_secret";
$grant_type = "client_credentials";
$shop_acc_token = '';
$propArr=array("client_id"=>"$client_id","grant_type"=>"$grant_type","client_secret"=>"$client_secret");
$get_token_shopware = shopware_check_curl_request($endpoint,$propArr,$shop_acc_token);

$file = 'import_products_log.txt';
$message = "";
file_put_contents($file, $message);

/****end****/

function check_curl_request($endpoint,$propArr,$access_token){
	$ch = curl_init();
	if($access_token==""){
		$headers = array('Content-Type:application/json');
	} else {
		$headers = array('Content-Type:application/json',"Authorization: Bearer $access_token");
	}
	if(!empty($propArr)){
		$post_json = json_encode($propArr);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_json);
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = @curl_exec($ch);
	$response_json = json_decode($response);
	
	curl_close($ch);
	return $response_json;
}

function check_curl_request_c($endpoint,$propArr,$access_token){ 
	$ch = curl_init();
	if($access_token==""){
		$headers = array('Content-Type:application/json');
	} else {
		$headers = array('Content-Type:application/json',"Authorization: Bearer $access_token");
	}
	if(!empty($propArr)){
		$post_json = json_encode($propArr);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_json);
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = @curl_exec($ch);
	$response_json = json_decode($response);
	
	curl_close($ch);
	return $response_json;
}

function check_curl_request2($endpoint,$propArr,$access_token){
	$ch = curl_init();
	if($access_token==""){
		$headers = array('Content-Type:application/json');
	} else {
		$headers = array('Content-Type:application/json',"Authorization: $access_token");
	}
	
	curl_setopt($ch, CURLOPT_POSTFIELDS,$propArr);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = @curl_exec($ch);
	$response_json = json_decode($response);
	
	curl_close($ch);
	return $response_json;
}

function check_curl_request3($endpoint,$propArr,$access_token){
	
	$ch = curl_init();
	if($access_token==""){
		$headers = array('Content-Type:application/json');
	} else {
		$headers = array('Content-Type:application/json',"Authorization: $access_token");
	}
	
	curl_setopt($ch, CURLOPT_POSTFIELDS,$propArr);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = @curl_exec($ch);
	$response_json = json_decode($response);
	
	curl_close($ch);
	
	return $response_json;
}

/****shopware curl url****/
function shopware_check_curl_request($endpoint,$propArr,$access_token){
	$ch = curl_init();
	if($access_token==""){
		$headers = array('Content-Type:application/json');
	} else {
		$headers = array('Content-Type:application/json',"Authorization: $access_token");
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$post_json = json_encode($propArr);
	if(!empty($propArr)){
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_json);
	}
	
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = @curl_exec($ch);
	$response_json = json_decode($response);
	
	curl_close($ch);
	return $response_json;
}

								
/*****function for db result of akeneo attribute******/

function get_attribute_data($conn,$man_name){
	$result = $conn->query("SELECT pim_catalog_attribute_option_value.locale_code,pim_catalog_attribute_option_value.value FROM pim_catalog_attribute_option_value INNER JOIN pim_catalog_attribute_option ON pim_catalog_attribute_option.id = pim_catalog_attribute_option_value.option_id AND pim_catalog_attribute_option.code='$man_name'");
	$catn_ids=array();
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			
			$locale_code = $row['locale_code'];
			$locale_value = $row['value'];
			if($locale_code=='de_DE'){
				$man_name = $locale_value;
			} else{
				$man_name = $locale_value;
			}
		}
	}

	return $man_name;
	
}

/****function for shopware data api*****/

function shop_data_check($conn,$endpoint,$cat,$access_token,$shopware_acc_token,$akeneo_url,$shopware_store_url) {

	

	$startTime = date("Y.m.d h:i:s"); // Script Excution Time

	if(isset($cat)){
		if(!empty($cat)){
			$endpoint_cat = "$shopware_store_url/api/search/category";
		
				$categories_ary = $cat->categories;
				$categories_parent = $cat->parent;
				
				if(!empty($categories_ary)){
					$cat_string = "'" . implode ( "', '", $categories_ary ) . "'";
				} else{
					$cat_string='';
				}
				
				
				if(isset($cat->values->manufacturer[0]->data)){
					$man_name = $cat->values->manufacturer[0]->data;
				} else{
					$man_name = '';
				}
				
				if($man_name!=''){
					$man_name = get_attribute_data($conn,$man_name);
				
				}
				
				if(isset($cat->values->packing_size[0]->data)){
					$packing_size_name = $cat->values->packing_size[0]->data;
				} else{
					$packing_size_name = '';
				}
				if(isset($cat->values->packing_unit[0]->data)){
					$packing_unit_name = $cat->values->packing_unit[0]->data;
				} else{
					$packing_unit_name = '';
				}
				
				
				if(isset($cat->values->shopware_product_stock[0]->data)){
					$shopware_product_stock_name = $cat->values->shopware_product_stock[0]->data;
				} else{
					$shopware_product_stock_name = '';
				}
				if($shopware_product_stock_name!=''){
					$stock_vl = intval($shopware_product_stock_name);
				} else{
					$stock_vl = 0;
				}
				
				
				if(isset($cat->values->package_sizes[0]->data)){
					$package_sizes_name = $cat->values->package_sizes[0]->data;
				} else{
					$package_sizes_name = '';
				}
				
				if(isset($cat->values->packing_size[0]->data)){
					$packing_size_name = $cat->values->packing_size[0]->data;
				} else{
					$packing_size_name = '';
				}
				if($packing_size_name!=''){
					$packing_size_name = get_attribute_data($conn,$packing_size_name);
				}
				
				if(isset($cat->values->price[0]->data[0]->amount)){
					$price_amount_name = $cat->values->price[0]->data[0]->amount;
					$price_currency_name = $cat->values->price[0]->data[0]->currency;
				} else{
					$price_amount_name = '';
					$price_currency_name = '';
				}
				
				if(isset($cat->values->shopware_net[0]->data[0]->amount)){
					$shopware_net_amount_name = $cat->values->shopware_net[0]->data[0]->amount;
					$shopware_net_currency_name = $cat->values->shopware_net[0]->data[0]->currency;
				} else{
					$shopware_net_amount_name = '';
					$shopware_net_currency_name = '';
				}
				
				
				if(isset($cat->values->shopware_list_net[0]->data[0]->amount)){
					$shopware_list_net_amount_name = $cat->values->shopware_list_net[0]->data[0]->amount;
					$price_currency_name = $cat->values->shopware_list_net[0]->data[0]->currency;
				} else{
					$shopware_list_net_amount_name = '';
					$shopware_list_net_currency_name = '';
				}
				
				
				if(isset($cat->values->regular_price[0]->data[0]->amount)){
					$regular_price_amount_name = $cat->values->regular_price[0]->data[0]->amount;
					$regular_price_currency_name = $cat->values->regular_price[0]->data[0]->currency;
				} else{
					$regular_price_amount_name = '';
					$regular_price_currency_name = '';
				}
				
				if(isset($cat->values->ean[0]->data)){
					$ean_name = $cat->values->ean[0]->data;
				} else{
					$ean_name = '';
				}
				
				if(isset($cat->values->pzn[0]->data)){
					$pzn_name = $cat->values->pzn[0]->data;
				} else{
					$pzn_name = '';
				}
				
				if(isset($cat->values->gtin[0]->data)){
					$gtin_name = $cat->values->gtin[0]->data;
				} else{
					$gtin_name = '';
				}
				
				if(isset($cat->values->name[0]->data)){
					$name = $cat->values->name[0]->data;
				} else{
					$name = '';
				}
				
				if(isset($cat->values->image_url[0]->data)){
					$image_url = $cat->values->image_url[0]->data;
				} else{
					$image_url = '';
				}
				
				if(isset($cat->values->packing_unit[0]->data)){
					$packing_unit = $cat->values->packing_unit[0]->data;
				} else{
					$packing_unit = '';
				}
				
				if(isset($cat->values->shopware_taxId[0]->data)){
					$shopware_taxId = $cat->values->shopware_taxId[0]->data;
					$akeneo_tax_name = get_attribute_data($conn,$shopware_taxId);
				} else{
					$akeneo_tax_name = '';
				} 
				
				$pac_data = str_replace("packing_size_","",$packing_size_name);			
				$packing_unit_data = str_replace("packing_unit_","",$packing_unit);
				$finvar = $pac_data.' '.$packing_unit_data;
				$pval = $categories_parent.$packing_size_name.'_';

				$vid='';
				
				/***check access token for shopware****/
				if($shopware_acc_token){
					//echo $get_token_shopware->access_token;
					$atr_data=array();
					$access_token_shop=$shopware_acc_token;
					
					/*********First step check if product exist in shopware for parent**********/
					$endpoint = "$shopware_store_url/api/search/product";
					$product_number_filter = array("filter"=>[["type"=>"equals","field"=>"productNumber","value"=> "$categories_parent"]]);
					$check_productnumber = check_curl_request($endpoint,$product_number_filter,$access_token_shop);
					
					/* echo"<pre>";
					print_r($check_productnumber);
					die; */
				
					if($cat_string=="'master'"){
						
						$query = "SELECT category_label FROM akeneo_categories WHERE category_url IN ('category_4')";
					} else{
						$query = "SELECT category_label FROM akeneo_categories WHERE category_url IN ($cat_string)";
					}
					
					$result = $conn->query($query);
					$catn_ids=array();
					if($cat_string!=''){
						if ($result->num_rows > 0) {
							while($row = $result->fetch_assoc()) {
								if($cat_string=="'master'"){
									$cat_lbl = 'Hauptkategorien';
								} else{
									$cat_lbl = $row['category_label']; 
								}
								
								$product_parent_filter = array("filter"=>[["type"=>"equals","field"=>"name","value"=> "$cat_lbl"]]);
								$parent_categories = check_curl_request($endpoint_cat,$product_parent_filter,$access_token_shop);
					
								if(isset($parent_categories->data)){
									if(!empty($parent_categories->data)){
										foreach($parent_categories->data as $pdata){
											$catn_id = $pdata->id;
											$catn_ids[] = array("id"=>$catn_id);			
										}
									}
								}
							}
					
						} else {
							
						}	
					}
                    /****check for the manufacturer*****/
					
					$product_number_filter ='{"page":1,"limit":25,"filter":[{"type":"contains","field":"name","value":"'.$man_name.'"}],"total-count-mode":1}';
					$endpoint = "$shopware_store_url/api/search/product-manufacturer";
					$check_manufacture = check_curl_request2($endpoint,$product_number_filter,$access_token_shop);
					
					$manuf_id='';
					
					if(isset($check_manufacture->data)){
					
						if(!empty($check_manufacture->data)){
							
							if(isset($check_manufacture->data[0]->id)){
								$manuf_id = $check_manufacture->data[0]->id;
							}
						}
					}
				
					/*****manufacture end******/
					
					
					/**check for tax*/

					if($akeneo_tax_name=='7% rate'){
						$akeneo_tax_name="Reduced Rate";
					} else{
						$akeneo_tax_name="Standard Rate";
					}

					
					$tax_filter ='{"page":1,"limit":2,"filter":[{"type":"contains","field":"name","value":"'.$akeneo_tax_name.'"}],"total-count-mode":1}';
					$endpointtax = "$shopware_store_url/api/search/tax";
					$check_tax = check_curl_request2($endpointtax,$tax_filter,$access_token_shop);
					$tax_id='';
					
					
					if(isset($check_tax->data)){
					
						if(!empty($check_tax->data)){
							
							if(isset($check_tax->data[0]->id)){
								 $tax_id = $check_tax->data[0]->id;
                                 $tax_name = $check_tax->data[0]->attributes->name;	
                                 $taxRate = $check_tax->data[0]->attributes->taxRate;
								 
								 
							}
						}
						
					}
					else{
						$tax_id='';
					}
					
					/*chek for the tax end */
					

					$product_mid='';
					$key = md5(microtime().rand());

					
					if(isset($check_productnumber->data)){
						if(!empty($check_productnumber->data)){
							
							if(isset($check_productnumber->data[0]->id)){
								$product_mid = $check_productnumber->data[0]->id;
								
							}
							
						} else { 
							
							//print_r($shopware_taxId);
							if($manuf_id==''){
								
							/*$insert_data = array("name" => "$name","manufacturer"=>array("name"=> "$man_name"),"productNumber" => "$categories_parent","stock"=>$stock_vl,
							"price"=>[array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $price_amount_name,"net"=> $shopware_net_amount_name,"listPrice"=>array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $regular_price_amount_name,"net"=> $shopware_list_net_amount_name,"linked" => true),"linked" => true)],"categories"=>$catn_ids,"tax"=>array("name"=> "Standard Rate","taxRate"=>10)); */
							if($tax_id==''){
								$insert_data = array("name" => "$name","manufacturer"=>array("name"=> "$man_name"),"productNumber" => "$categories_parent","stock"=>$stock_vl,
								"price"=>[array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $price_amount_name,"net"=> $shopware_net_amount_name,"listPrice"=>array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $regular_price_amount_name,"net"=> $shopware_list_net_amount_name,"linked" => true),"linked" => true)],"categories"=>$catn_ids,"tax"=>array("name"=> "$tax_name" ,"taxRate"=> $taxRate));
							} else{
								$insert_data = array("name" => "$name","taxId"=>"$tax_id","manufacturer"=>array("name"=> "$man_name"),"productNumber" => "$categories_parent","stock"=>$stock_vl,
								"price"=>[array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $price_amount_name,"net"=> $shopware_net_amount_name,"listPrice"=>array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $regular_price_amount_name,"net"=> $shopware_list_net_amount_name,"linked" => true),"linked" => true)],"categories"=>$catn_ids);
							}
								
							
							} else{
								if($tax_id==''){
									$insert_data = array("name" => "$name","manufacturerId"=>"$manuf_id","productNumber" => "$categories_parent","stock"=>$stock_vl,
								"price"=>[array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $price_amount_name,"net"=> $shopware_net_amount_name,"listPrice"=>array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $regular_price_amount_name,"net"=> $shopware_list_net_amount_name,"linked" => true),"linked" => true)],"categories"=>$catn_ids,"tax"=>array("name"=> "$tax_name","taxRate"=> $taxRate));
								} else{
									$insert_data = array("name" => "$name","taxId"=>"$tax_id","manufacturerId"=>"$manuf_id","productNumber" => "$categories_parent","stock"=>$stock_vl,
								"price"=>[array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $price_amount_name,"net"=> $shopware_net_amount_name,"listPrice"=>array("currencyId" => "b7d2554b0ce847cd82f3ac9bd1c0dfca","gross"=> $regular_price_amount_name,"net"=> $shopware_list_net_amount_name,"linked" => true),"linked" => true)],"categories"=>$catn_ids);
								
								}	
							}
							 							
							
							$post_json = json_encode($insert_data);
						
							
							$endpoint = "$shopware_store_url/api/product";
							//insert product to shopware curl
							$inser_d = check_curl_request($endpoint,$insert_data,$access_token_shop);

							$product_number_filter = array("filter"=>[["type"=>"equals","field"=>"productNumber","value"=> "$categories_parent"]]);
							$endpoint = "$shopware_store_url/api/search/product";
							$check_productnumber = shopware_check_curl_request($endpoint,$product_number_filter,$access_token_shop);

							
							if(isset($check_productnumber->data)){
								if(!empty($check_productnumber->data)){
									if(isset($check_productnumber->data[0]->id)){
										$product_mid = $check_productnumber->data[0]->id;
									}
								}
							}
							
							/*****check product visibility */
							$upd_visibility = "$shopware_store_url/api/_action/sync";
							$updpoint = '[{"key":"write","action":"upsert","entity":"product","payload":[{"id":"'.$product_mid.'","visibilities":[{"productId":"'.$product_mid.'","salesChannelId":"24c31aca3b7f42509a51ad849c80af34","visibility":30}]}]}]';
							$update_engp = check_curl_request2($upd_visibility,$updpoint,$access_token_shop);
							echo "pmid = ".$product_mid; 
							/****visibility end */

							/****update product image*****/
								
							$endpoint_vari = "$shopware_store_url/api/media?_response=true";
							$access_token_shop = $shopware_acc_token;
							$media_data = '{"mediaFolderId":"test"}';
							
							$get_media_data = check_curl_request2($endpoint_vari,$media_data,$access_token_shop);
							$media_id='';
							if(isset($get_media_data->data)){
								if(!empty($get_media_data->data)){
									$media_id = $get_media_data->data->id;
									$num = mt_rand(100000,999999);
									$endpoint_media_u = "$shopware_store_url/api/_action/media/$media_id/upload?extension=jpg&fileName=$num";
								
									$media_data = '{"url":"'.$image_url.'"}';
									$insert_media_data = check_curl_request2($endpoint_media_u,$media_data,$access_token_shop);
									//search media
									
									$endpoint_vari = "$shopware_store_url/api/search/media";
			
									$media_data = '{"ids":"'.$media_id.'"}';
									
									$get_media_data = check_curl_request2($endpoint_vari,$media_data,$access_token_shop); 
									
									$update_pendpoint = "$shopware_store_url/api/_action/sync";
									
									//create key for the media
									$key = md5(microtime().rand());			
									//media data
									$ndata = '[{"key":"write","action":"upsert","entity":"product","payload":[{"id":"'.$product_mid.'","versionId":"0fa91ce3e96a4bc2be4bd9ce752c3425","media":[{"id":"'.$key.'","productId":"'.$product_mid.'","mediaId":"'.$media_id.'","position":1}]}]}]';
									//add image with product
									$upd_d = check_curl_request2($update_pendpoint,$ndata,$access_token_shop);
									
								}
							}
							/****update product image end*****/
							
						}
					}
										
				/******end*******/
				
				/******check if parent key exist of product number in shopware********/
					
					if($product_mid!=''){
						
						/*****variation assign to the product in shopware********/
						
						$akeneo_atr_name = "packing_size";
						$endpoint_group_option = "$shopware_store_url/api/search/property-group/907ed9979f54425a8a4429f8ec0a199b/options";

						$propArr='{"page":1,"limit":10,"term":"'.$finvar.'"}';
						$get_variation = check_curl_request2($endpoint_group_option,$propArr,$access_token_shop);
						$not_found=''; 
						
						if(isset($get_variation->data)) {
							if(!empty($get_variation->data)){
								foreach($get_variation->data as $data){									
									$v_name = $data->attributes->name;
									
									if($v_name==$finvar){	
										$not_found="";									
										$vid = $data->id;
										$cdata = '[{"action":"upsert","entity":"product","payload":[{"parentId":"'.$product_mid.'","options":[{"id":"'.$vid.'"}],"stock":'.$stock_vl.',"productNumber":"'.$pzn_name.'"}]}]';
										$create_p_endpoint = "$shopware_store_url/api/_action/sync";
										$get_variation_option_data = check_curl_request2($create_p_endpoint,$cdata,$access_token_shop);
										
										$key = md5(microtime().rand());	
									} else{
										$not_found="not found";
									}
								}
							} else{
			
								$propArr='{"page":1,"limit":10,"term":"'.$finvar.'"}';
								$get_variation = check_curl_request2($endpoint_group_option,$propArr,$access_token_shop);
								
								if(isset($get_variation->data)) {
									
									if(!empty($get_variation->data)){
									
										foreach($get_variation->data as $data){
											
											$v_name = $data->attributes->name;
											
											if($v_name==$finvar){
												
												$vid = $data->id;
												
												$cdata = '[{"action":"upsert","entity":"product","payload":[{"parentId":"'.$product_mid.'","options":[{"id":"'.$vid.'"}],"stock":'.$stock_vl.',"productNumber":"'.$pzn_name.'"}]}]';
												$create_p_endpoint = "$shopware_store_url/api/_action/sync";
												$get_variation_option_data = check_curl_request2($create_p_endpoint,$cdata,$access_token_shop);
												$key = md5(microtime().rand());	
												$update_pendpoint = "$shopware_store_url/api/_action/sync";
												if($media_id!=''){
													$ndata = '[{"key":"write","action":"upsert","entity":"product","payload":[{"id":"'.$vid.'","versionId":"0fa91ce3e96a4bc2be4bd9ce752c3425","media":[{"id":"'.$key.'","productId":"'.$vid.'","mediaId":"'.$media_id.'","position":1}]}]}]';
													//add image with product
													$upd_d = check_curl_request2($update_pendpoint,$ndata,$access_token_shop);
												}
			
											} else{
												$not_found="not found";
											}
											
										}
									} else{
										$not_found="not found";
									}
								}																
							}
							
							$file = 'import_products_log.txt';
							$message = "$product_mid".PHP_EOL;
							file_put_contents($file, $message, FILE_APPEND);
							
							$productcount=''; 
							
						} else{
							$not_found="not found";
						}
						
						if($not_found!=""){
							$akeneo_atr_name = "packing_size";
							$endpoint_group_option = "$shopware_store_url/api/search/property-group/6fb86b6f6c8f4c19b70fb215aae6c4a5/options";
							$propArr='{"page":1,"limit":10,"term":"'.$finvar.'"}';
							$get_variation = check_curl_request2($endpoint_group_option,$propArr,$access_token_shop);


							if(isset($get_variation->data)) {
								if(!empty($get_variation->data)){
									foreach($get_variation->data as $data){									
										$v_name = $data->attributes->name;
										if($v_name==$finvar){	
											$not_found="";									
											$vid = $data->id;
											$cdata = '[{"action":"upsert","entity":"product","payload":[{"parentId":"'.$product_mid.'","options":[{"id":"'.$vid.'"}],"stock":'.$stock_vl.',"productNumber":"'.$pzn_name.'"}]}]';
											$create_p_endpoint = "$shopware_store_url/api/_action/sync";
											$get_variation_option_data = check_curl_request2($create_p_endpoint,$cdata,$access_token_shop);
											
											$key = md5(microtime().rand());	
										} else{
											$not_found="not found";
										}
									}
								}
							}
							
						}
						
					}	
				}		
			
		}
	}

  
	/****check for next query end*****/
	
}
 
$endpoint = "$akeneo_url/api/oauth/v1/token";
$propArr=array("grant_type"=>"password","username"=>"$akeneo_username","password"=>"$akeneo_password","client_id"=>"$akeneo_client_id","client_secret"=>"$akeneo_client_secret");

$access_token='';
$shopware_acc_token='';

//generate access token

$get_token = check_curl_request($endpoint,$propArr,$access_token);

if($get_token->access_token){
	$access_token=$get_token->access_token;
}
if($get_token_shopware->access_token){
	$shopware_acc_token=$get_token_shopware->access_token;
}


if($access_token!=''){ 
    $product_exist = true;
	$in = 1;

	
	
	//foreach($identifier_ids as $identifier_newd){
	
	if(!empty($lines_data)){
		foreach($lines_data as $identifier_newd){
			
			$identifier_newd=str_replace('"',"",$identifier_newd);
			$identifier_newd = str_replace(' ', '', $identifier_newd);
			
			if ($in % 3000 == 0) { 
				$shop_propArr=array("client_id"=>"$client_id","grant_type"=>"$grant_type","client_secret"=>"$client_secret");
				$shopware_token_endpoint = "$shopware_store_url/api/oauth/token";
				$get_token_shopware = shopware_check_curl_request($shopware_token_endpoint,$shop_propArr,$shop_acc_token);
				if($get_token_shopware->access_token){
					$shopware_acc_token=$get_token_shopware->access_token;
				}
				
				/***for akeneo token regenerate****/
				$endpoint_ak = "$akeneo_url/api/oauth/v1/token";
				$propArr_ak=array("grant_type"=>"password","username"=>"$akeneo_username","password"=>"$akeneo_password","client_id"=>"$akeneo_client_id","client_secret"=>"$akeneo_client_secret");
				$acc_token = '';
				$get_token = check_curl_request($endpoint_ak,$propArr_ak,$acc_token);
				if($get_token->access_token){
					$access_token=$get_token->access_token;
				}
				
				
			}
			
			$endpoint = "$akeneo_url/api/rest/v1/products/$identifier_newd";
			
			
			//$endpoint = "$akeneo_url/api/rest/v1/products/$identifier_newd?with_count=false&pagination_type=search_after&limit=1";
			$get_products = check_curl_request_c($endpoint,$data,$access_token);
			
			$get_total_data = shop_data_check($conn,$endpoint,$get_products,$access_token,$shopware_acc_token,$akeneo_url,$shopware_store_url);

			$in++;
			
		}
	}
	
	
	echo "last";

	
	$endTime = date("Y.m.d h:i:s");
	echo "<br> End Time:  $endTime";
}
?>
