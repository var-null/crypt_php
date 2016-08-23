<?php
//ini_set("max_execution_time", "5000");
header('Content-Type: text/html; charset=utf-8');

include_once('sign/sign.php');

$id = $_GET['id'];
$get_digital_signature = $_GET['digital_signature'];
$key_crypt_urlencode = $_GET['key_crypt_urlencode'];
$encrypted_data_urlencode = $_GET['encrypted_data_urlencode'];

$data_urldecode = urldecode($key_crypt_urlencode);


$data_urldecode = str_replace("@@p@@", "+", $data_urldecode);
$encrypted_data_urlencode = str_replace("@@p@@", "+", $encrypted_data_urlencode);

//

$return_key_error = md5(time() + 2);

$digital_signature = md5($SIGNATURE_KEY . ' ' . date('Y') . '_' . $id);

//echo 'SIGNATURE_KEY=' . $SIGNATURE_KEY . '<br>';
//echo 'id=' . $id . '<br>';
//echo 'digital_signature=' . $digital_signature . '<br>';
//echo 'get_digital_signature=' . $get_digital_signature . '<br>';

if($digital_signature == $get_digital_signature && $data_urldecode != "")
{
	$f = fopen("keys/private_" . $id . ".pem", "r");
	
	$private_key = "";
	
	// Читать построчно до конца файла
	while(!feof($f)) { 
		$private_key .= fgets($f);
	}

	fclose($f);	
		
		
	$key = <<<SOMEDATA777
$private_key
SOMEDATA777;

	$pk  = openssl_get_privatekey($key);
	openssl_private_decrypt(base64_decode($data_urldecode), $out, $pk);	
	
	$out_mas = explode('@@@', $out);
	
	$signature_correct_str = $out_mas[0];
	$sinc_key = $out_mas[1];
	
	if($signature_correct_str == $digital_signature)
	{
		//Синхронный ключ корректен ибо мы извлекли нашу хитрую подпись
		
		//echo 'sinc_key target=' . $sinc_key . '<br>';
		//echo 'encrypted_data_urlencode=' . $encrypted_data_urlencode . '<br>';

		
		/* decode */
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$sinc_key, base64_decode(urldecode($encrypted_data_urlencode)),MCRYPT_MODE_ECB);

		$decrypted_mas = explode('@@@', $decrypted);
		
		$return_key = $decrypted_mas[0];
		
		unset($decrypted_mas[0]);
		sort($decrypted_mas);
		
		$msg = implode('@@@', $decrypted_mas);
		
		echo $return_key;	
		
		//echo 'Наше сообщение =' . $msg . '<br>';		
		
		
	}
	else
	{
		//echo "ERROR5";
		echo $return_key_error;
	}
		
	
	
	
}
else
{
	//echo 'ERROR3';
	echo $return_key_error;
}


?>