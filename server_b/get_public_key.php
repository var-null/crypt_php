<?php
//ini_set("max_execution_time", "5000");
header('Content-Type: text/html; charset=utf-8');

include_once('sign/sign.php');

$id = $_GET['id'];
$get_digital_signature = $_GET['digital_signature'];

$digital_signature = md5($SIGNATURE_KEY . ' ' . date('Y') . '_' . $id);

if($digital_signature == $get_digital_signature)
{
	$bait = 1024;
	
	$com = "openssl genrsa -out keys/private_" . $id . ".pem " . $bait;		
	$res = exec( $com, $output);

	$com = "openssl rsa -in keys/private_" . $id . ".pem -out keys/public_" . $id . ".pem -outform PEM -pubout";		
	$res = exec( $com, $output);	

	$f = fopen("keys/public_" . $id . ".pem", "r");

	$public_key = "";
	
	// Читать построчно до конца файла
	while(!feof($f)) { 
		$public_key .= fgets($f);
	}

	fclose($f);	
	
	//$public_key_urlencode = urlencode($public_key);
	
	//$data_str = $id . '_' . $public_key;  
	
	echo $public_key;
}
else
{
	echo 'ERROR2';

}


?>