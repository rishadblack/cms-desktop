<?php
	
	function redirect($url,$domain)
	{
		$isExternal = stripos($url, "http://") !== false || stripos($url, "https://") !== false;
		
		if (! $isExternal) {
			$url = rtrim($domain, '/') . '/' . ltrim($url, '/');
		}
		
		if (! headers_sent()) {
			header('Location: '.$url, true, 302);
			} else {
			echo '<script type="text/javascript">';
			echo 'window.location.href="'.$url.'";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
			echo '</noscript>';
		}
		exit;
	}
	
	function gettoken($length = 8) {
		$getuniquetime =  time(true);
		$key = '';
		$keys = array_merge(range(0, 9), range(0, 9));
		
		for ($i = 0; $i < 5; $i++) {
			$key .= $keys[array_rand($keys)];
		}
		$key = substr($getuniquetime.$key, -10);
		return $key;
	}
	
	function randomtoken($length) {
		$key = '';
		$keys = array_merge(range(0, 9), range('A', 'Z'));
		
		for ($i = 0; $i < $length; $i++) {
			$key .= $keys[array_rand($keys)];
		}
		
		return $key;
	}
	
	function respond(array $data, $statusCode = 200){
		$response = new Response();
		
		$response->send($data, $statusCode);
	}	
	
	function getdatetime($datetime,$type = 0) {
		
		$date = new DateTime($datetime);
		if($type == 1){
			$dati = $date->format('Y-m-d');
			}elseif($type == 2){
			$dati = $date->format('h:i:s A');
			}elseif($type == 3){
			$dati = $date->format('d F Y');
			}elseif($type == 4){
			$dati = $date->format('D d F Y');
			}elseif($type == 5){
			$dati = $date->format('F Y');
			}elseif($type == 6){
			$dati = $date->format('d M Y H:i:s A');
			}elseif($type == 7){
			$dati = $date->format('d M Y H:i:s A');
			}elseif($type == 8){
			$dati = $date->format('d M Y h:i:s A');
			}elseif($type == 9){
			$dati = $date->format('m/d/Y');
			}else{
			$dati = $date->format('Y-m-d H:i:s');
		}
		return $dati;
		
	}