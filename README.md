# crypt_php
скрипты на php для передачи шифрованного текста с сервера А серверу В в архиве 2 папки - разносятся по двум машинам. Работает на синтезе синхронного и асинхронного шифрования

Работает в целом так:

1)Сервер A сначала запрашивает публичный ключ у сервера B, которому нужно передать сообщение, передавая ему идентификатор нового шифрованного соединения.
Сервер В, получив запрос генерирует публичный P и приватный R RSA ключи для этого соединения. 
На этом этапе производится легкая проверка Сервера A по md5 от конкатинации Подписи и соли (далее Подпись K), которая одинакова у обоих серверов, которая хранится в файле sign/sign.php и которую они меняют раз в пол года

Если Подпись K Сервера В с переданным для соединения от Сервера А совпадают - возвращается публичный ключ P

2)Получив публичный ключ P Сервер А генерирует синхронный ключ S и сообщение D, которое включает в себя a) тело письма M которое нужно передать и b) опять же  Подпись K для текущей передачи пакета. 
Сообщение D шифруется синхронным ключем S. 
Сам синхронный ключ S объединяется с Подписю K и шифруется публичным ключем P, который передал Сервер В.
Эти данные отправляеются на Сервер В

3)Сервер В расшифровывает данные с синхронным ключом S с помощью приватного ключа R, сравнивает  Подпись K и если все впорядке считает синхронный ключ S действительным.
После расшифровываются данные D с помощью полученного синхронного ключа S.
Сервер В получает исходное сообщение M, трет ключе текущей передачи сообщения и отдает обратно Серверу а еще один ключик, который был передан вместе с сообщением M.

4)Сервер сверяет полученный ключик и если он такой, который он сгенерил - то считает сообщение переданным, после чего функция возвращает true

Таким образом для каждого сообщения выполняется все выше перечисленное, что не оставляет негодяям из АНБ ни малейшего шанса на расшифровку
