<?php
//ini_set("max_execution_time", "5000");
header('Content-Type: text/html; charset=utf-8');

/*
1)Данный скрипт (Сервер A) сначала запрашивает публичный ключ у сервера B, которому нужно передать сообщение, передавая ему идентификатор нового шифрованного соединения.
Сервер В, получив запрос генерирует публичный P и приватный R RSA ключи для этого соединения. 
На этом этапе производится легкая проверка Сервера A по md5 от конкатинации Подписи и соли (далее Подпись K), которая одинакова у обоих серверов, которая хранится в файле sign/sign.php и которую они меняют раз в пол года

Если Подпись K Сервера В с переданным для соединения от Сервера А совпадает - возвращается публичный ключ P

2)Получив публичный ключ P Сервер А генерирует синхронный ключ S и сообщение D, которое включает в себя a) тело письма M которое нужно передать и b) опять же  Подпись K для текущей передачи пакета. 
Сообщение D шифруется синхронным ключем S. 
Сам синхронный ключ S объединяется с Подписю K и шифруется публичным ключем P, который передал Сервер В.
Эти данные отправляеются на Сервер В

3)Сервер В расшифровывает данные с синхронным ключом S с помощью приватного ключа R, сравнивает  Подпись K и если все впорядке считает синхронный ключ S действительным.
После расшифровываются данные D с помощью полученного синхронного ключа S.
Сервер В получает исходное сообщение M


*/

function new_event($id, $notice, $server_b)
{
	global $SIGNATURE_KEY;
	
	//Генерим цифровую подпись, которую сможем проверить на месте после расшифровки глобального сообщения
	$digital_signature = md5($SIGNATURE_KEY . ' ' . date('Y') . '_' . $id);
	//Отсылаем запрос на приемник чтобы нам сгенерили открытый ключ для этого id
	
	//echo 'SIGNATURE_KEY=' . $SIGNATURE_KEY . '<br>';
	//echo 'id=' . $id . '<br>';
	//echo 'digital_signature=' . $digital_signature . '<br>';
	
	$public_key = file_get_contents($server_b . '/get_public_key.php?id=' . $id . '&digital_signature=' . $digital_signature);
	
	
	$pub = <<<SOMEDATA777
$public_key
SOMEDATA777;


	//echo $pub . '<br>';
	
	$sinc_key = md5($digital_signature  . '_' . date('Y.m.d')); 

	$data = $digital_signature . "@@@" . $sinc_key;

	
	//echo 'sinc_key=' . $sinc_key . '<br>';
	
	$pk  = openssl_get_publickey($pub);
	openssl_public_encrypt($data, $encrypted, $pk);
	$data_key_crypt = chunk_split(base64_encode($encrypted));		

	//echo 'data_key_crypt=' . $data_key_crypt . '<br>';
	
	$data_key_crypt = str_replace("+", "@@p@@", $data_key_crypt);
	
	$key_crypt_urlencode = urlencode($data_key_crypt);
	
	//echo 'key_crypt_urlencode=' . $key_crypt_urlencode . '<br>';
	
	//=============
	
	/* encode */
	
	$return_key = md5($digital_signature  . '_' . date('Y.m.d') . '_' . $id); 
	
	//echo 'return_key=' . $return_key . '<br>';
	
	$notice_text = $return_key . '@@@' . str_replace("+", "@@p@@", $notice);
	
	//echo 'notice_text=' . $notice_text . '<br>';
	
	$encrypted_data = urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $sinc_key, $notice_text, MCRYPT_MODE_ECB)));

	//echo 'Зашифровали синхронным ключом=' . $encrypted_data . '<br>';
	
	$encrypted_data_urlencode = urlencode($encrypted_data);
	
	//echo 'urlencode текста=' . $encrypted_data_urlencode . '<br>';
	
	
	
	//=============
	
	$send = file_get_contents($server_b . '/crypt_target.php?id=' . $id . '&digital_signature=' . $digital_signature . '&key_crypt_urlencode=' . $key_crypt_urlencode . '&encrypted_data_urlencode=' . $encrypted_data_urlencode);

	
	//echo 'send=' . $send . '<br>';
	
	if($send == $return_key)
		return true;
	else
		return false;
	
	
}

include_once('sign/sign.php');//Путь к файлу с подписью (знают оба сервера)



?>