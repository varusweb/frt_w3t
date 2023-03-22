<?
/*
 * Скрипт парсинга транзакций из блокчейна
 * Получение входящих транзакций на указанный кошелек и запись в БД
 * Запускается по CRON 1 раз в минуту
 * */

function convNanoToTon($numNano)
{
    $num = floor($numNano / 1000000000);
    return $num;
}
function convNanoToDecimalTon($numNano)
{
    if(strlen($numNano) < 6)
    {
        $num = $numNano / 1000000000;
        $num = number_format($num, 20);
        $num = rtrim($num, '0');
    }
    else
    {
        $num = $numNano / 1000000000;
    }
    return $num;
}
function convTonToNano($ton)
{
    $num = floor($ton * 1000000000);
    return $num;
}
// получаем список транзакций и записываем их в таблицу БД, проверяя на уникальность

$destWallet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/walletaddr.txt');

//$ch = curl_init('https://toncenter.com/api/v2/getTransactions?address=' . $addr);
//$ch = curl_init('https://toncenter.com/api/v2/getTransactions?address=' . $addr . "&limit=100");

//$ch = curl_init('https://toncenter.com/api/v2/getTransactions?address=' . $destWallet . "&limit=100&archival=true&api_key=5a7d22c38f64de9e88144e8d9878dd9ea4b344245e5a6d66247a856a6059e002");

// TESTNET
$ch = curl_init('https://testnet.toncenter.com/api/v2/getTransactions?address=' . $destWallet);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$data = curl_exec($ch);
curl_close($ch);

$dataObj = json_decode($data);
$resArr = json_decode(json_encode($dataObj), true);

/*echo "<pre>";
print_r($resArr);
echo "</pre>";
echo "<hr><hr><hr>";
exit;*/

// подключаемся к базе
require_once($_SERVER["DOCUMENT_ROOT"] . "/dbc.inc.php");

foreach($resArr["result"] as $transaction)
{
	unset($ton);
	unset($timestamp);
	unset($lt);
	unset($hash);
	unset($received_from);
	unset($received_nanoton);
	unset($received_message);
	unset($comment);
	
	// пишем только входящие транзакции, исходящие пропускаем
	if($transaction["in_msg"]["value"] > 0)
	{
		// проверяем, есть ли уже в базе эта транзакция
	    $sth = $dbh->prepare("SELECT * FROM `x_transactions` WHERE `hash` = :hash");
		$sth->execute(array('hash' => $transaction["transaction_id"]["hash"]));
		$array = $sth->fetch(PDO::FETCH_ASSOC);
		
		if(!$array)
		{
			// переводим нанотоны в тоны
			// проверяем, есть ли целые тоны, либо это дробь меньше 1
            $ton = convNanoToTon($transaction["in_msg"]["value"]);
            if(!$ton)
            {
                // имеем дробь меньше 1
                $ton = convNanoToDecimalTon($transaction["in_msg"]["value"]);
            }

			$timestamp = $transaction["utime"];
			$lt = $transaction["transaction_id"]["lt"];
			$hash = $transaction["transaction_id"]["hash"];
			$received_from = $transaction["in_msg"]["source"];
			$received_nanoton = $transaction["in_msg"]["value"];
			$received_message = $transaction["in_msg"]["message"];
			
			$comment = strtolower(trim($received_message));
			
			/* echo "ton = $ton <br>";
			echo "timestamp = $timestamp <br>";
			echo "lt = $lt <br>";
			echo "hash = $hash <br>";
			echo "received_from = $received_from <br>";
			echo "received_nanoton = $received_nanoton <br>";
			echo "received_message = $received_message <br>";
			echo "<hr>"; */
			
			
			// транзакции с таким хешем нет, добавляем в базу в таблицу x_transactions
			$sth = $dbh->prepare("INSERT INTO `x_transactions` SET `timestamp` = :timestamp, `lt` = :lt, `hash` = :hash, `received_from` = :received_from, `received_nanoton` = :received_nanoton, `received_ton` = :received_ton, `received_message` = :received_message");
			$sth->execute(array('timestamp' => $timestamp, 'lt' => $lt, 'hash' => $hash, 'received_from' => $received_from, 'received_nanoton' => $received_nanoton, 'received_ton' => $ton, 'received_message' => $received_message));
			 
			// Получаем id вставленной записи
			$insert_id = $dbh->lastInsertId();
			

		}
	}
}

unset($sth);
unset($ch);
unset($data);
unset($dataObj);
unset($resArr);

// пишем в лог время выполнения скрипта
$logFile = $_SERVER["DOCUMENT_ROOT"] . "/grabtransactionslog.txt";
$dateTime = date("d.m.Y H:i:s");
$logStr = "done @ " . $dateTime . "\r\n";
file_put_contents($logFile, $logStr, FILE_APPEND);

// пауза
sleep(13);

$ch = curl_init('https://toncenter.com/api/v2/getTransactions?address=' . $destWallet . "&limit=100&archival=true&api_key=5a7d22c38f64de9e88144e8d9878dd9ea4b344245e5a6d66247a856a6059e002");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$data = curl_exec($ch);
curl_close($ch);

$dataObj = json_decode($data);
$resArr = json_decode(json_encode($dataObj), true);

foreach($resArr["result"] as $transaction)
{
	unset($ton);
	unset($timestamp);
	unset($lt);
	unset($hash);
	unset($received_from);
	unset($received_nanoton);
	unset($received_message);
	unset($comment);
	
	// пишем только входящие транзакции, исходящие пропускаем
	if($transaction["in_msg"]["value"] > 0)
	{
		// проверяем, есть ли уже в базе эта транзакция
	    $sth = $dbh->prepare("SELECT * FROM `x_transactions` WHERE `hash` = :hash");
		$sth->execute(array('hash' => $transaction["transaction_id"]["hash"]));
		$array = $sth->fetch(PDO::FETCH_ASSOC);
		
		if(!$array)
		{
			// переводим нанотоны в тоны
			// проверяем, есть ли целые тоны, либо это дробь меньше 1
            $ton = convNanoToTon($transaction["in_msg"]["value"]);
            if(!$ton)
            {
                // имеем дробь меньше 1
                $ton = convNanoToDecimalTon($transaction["in_msg"]["value"]);
            }

			$timestamp = $transaction["utime"];
			$lt = $transaction["transaction_id"]["lt"];
			$hash = $transaction["transaction_id"]["hash"];
			$received_from = $transaction["in_msg"]["source"];
			$received_nanoton = $transaction["in_msg"]["value"];
			$received_message = $transaction["in_msg"]["message"];
			
			$comment = strtolower(trim($received_message));
			
			// транзакции с таким хешем нет, добавляем в базу в таблицу x_transactions
			$sth = $dbh->prepare("INSERT INTO `x_transactions` SET `timestamp` = :timestamp, `lt` = :lt, `hash` = :hash, `received_from` = :received_from, `received_nanoton` = :received_nanoton, `received_ton` = :received_ton, `received_message` = :received_message");
			$sth->execute(array('timestamp' => $timestamp, 'lt' => $lt, 'hash' => $hash, 'received_from' => $received_from, 'received_nanoton' => $received_nanoton, 'received_ton' => $ton, 'received_message' => $received_message));
			 
			// Получаем id вставленной записи
			$insert_id = $dbh->lastInsertId();
			

		}
	}
}

unset($sth);
unset($ch);
unset($data);
unset($dataObj);
unset($resArr);

// пишем в лог время выполнения скрипта
$logFile = $_SERVER["DOCUMENT_ROOT"] . "/grabtransactionslog.txt";
$dateTime = date("d.m.Y H:i:s");
$logStr = "done @ " . $dateTime . "\r\n";
file_put_contents($logFile, $logStr, FILE_APPEND);

// пауза
sleep(13);

$ch = curl_init('https://toncenter.com/api/v2/getTransactions?address=' . $destWallet . "&limit=100&archival=true&api_key=5a7d22c38f64de9e88144e8d9878dd9ea4b344245e5a6d66247a856a6059e002");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$data = curl_exec($ch);
curl_close($ch);

$dataObj = json_decode($data);
$resArr = json_decode(json_encode($dataObj), true);

foreach($resArr["result"] as $transaction)
{
	unset($ton);
	unset($timestamp);
	unset($lt);
	unset($hash);
	unset($received_from);
	unset($received_nanoton);
	unset($received_message);
	unset($comment);
	
	// пишем только входящие транзакции, исходящие пропускаем
	if($transaction["in_msg"]["value"] > 0)
	{
		// проверяем, есть ли уже в базе эта транзакция
	    $sth = $dbh->prepare("SELECT * FROM `x_transactions` WHERE `hash` = :hash");
		$sth->execute(array('hash' => $transaction["transaction_id"]["hash"]));
		$array = $sth->fetch(PDO::FETCH_ASSOC);
		
		if(!$array)
		{
			// переводим нанотоны в тоны
			// проверяем, есть ли целые тоны, либо это дробь меньше 1
            $ton = convNanoToTon($transaction["in_msg"]["value"]);
            if(!$ton)
            {
                // имеем дробь меньше 1
                $ton = convNanoToDecimalTon($transaction["in_msg"]["value"]);
            }

			$timestamp = $transaction["utime"];
			$lt = $transaction["transaction_id"]["lt"];
			$hash = $transaction["transaction_id"]["hash"];
			$received_from = $transaction["in_msg"]["source"];
			$received_nanoton = $transaction["in_msg"]["value"];
			$received_message = $transaction["in_msg"]["message"];
			
			$comment = strtolower(trim($received_message));
			
			// транзакции с таким хешем нет, добавляем в базу в таблицу x_transactions
			$sth = $dbh->prepare("INSERT INTO `x_transactions` SET `timestamp` = :timestamp, `lt` = :lt, `hash` = :hash, `received_from` = :received_from, `received_nanoton` = :received_nanoton, `received_ton` = :received_ton, `received_message` = :received_message");
			$sth->execute(array('timestamp' => $timestamp, 'lt' => $lt, 'hash' => $hash, 'received_from' => $received_from, 'received_nanoton' => $received_nanoton, 'received_ton' => $ton, 'received_message' => $received_message));
			 
			// Получаем id вставленной записи
			$insert_id = $dbh->lastInsertId();
			

		}
	}
}

unset($sth);
unset($ch);
unset($data);
unset($dataObj);
unset($resArr);

// пишем в лог время выполнения скрипта
$logFile = $_SERVER["DOCUMENT_ROOT"] . "/grabtransactionslog.txt";
$dateTime = date("d.m.Y H:i:s");
$logStr = "done @ " . $dateTime . "\r\n";
file_put_contents($logFile, $logStr, FILE_APPEND);

// пауза
sleep(13);

$ch = curl_init('https://toncenter.com/api/v2/getTransactions?address=' . $destWallet . "&limit=100&archival=true&api_key=5a7d22c38f64de9e88144e8d9878dd9ea4b344245e5a6d66247a856a6059e002");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$data = curl_exec($ch);
curl_close($ch);

$dataObj = json_decode($data);
$resArr = json_decode(json_encode($dataObj), true);

foreach($resArr["result"] as $transaction)
{
	unset($ton);
	unset($timestamp);
	unset($lt);
	unset($hash);
	unset($received_from);
	unset($received_nanoton);
	unset($received_message);
	unset($comment);
	
	// пишем только входящие транзакции, исходящие пропускаем
	if($transaction["in_msg"]["value"] > 0)
	{
		// проверяем, есть ли уже в базе эта транзакция
	    $sth = $dbh->prepare("SELECT * FROM `x_transactions` WHERE `hash` = :hash");
		$sth->execute(array('hash' => $transaction["transaction_id"]["hash"]));
		$array = $sth->fetch(PDO::FETCH_ASSOC);
		
		if(!$array)
		{
			// переводим нанотоны в тоны
			// проверяем, есть ли целые тоны, либо это дробь меньше 1
            $ton = convNanoToTon($transaction["in_msg"]["value"]);
            if(!$ton)
            {
                // имеем дробь меньше 1
                $ton = convNanoToDecimalTon($transaction["in_msg"]["value"]);
            }

			$timestamp = $transaction["utime"];
			$lt = $transaction["transaction_id"]["lt"];
			$hash = $transaction["transaction_id"]["hash"];
			$received_from = $transaction["in_msg"]["source"];
			$received_nanoton = $transaction["in_msg"]["value"];
			$received_message = $transaction["in_msg"]["message"];
			
			$comment = strtolower(trim($received_message));
			
			// транзакции с таким хешем нет, добавляем в базу в таблицу x_transactions
			$sth = $dbh->prepare("INSERT INTO `x_transactions` SET `timestamp` = :timestamp, `lt` = :lt, `hash` = :hash, `received_from` = :received_from, `received_nanoton` = :received_nanoton, `received_ton` = :received_ton, `received_message` = :received_message");
			$sth->execute(array('timestamp' => $timestamp, 'lt' => $lt, 'hash' => $hash, 'received_from' => $received_from, 'received_nanoton' => $received_nanoton, 'received_ton' => $ton, 'received_message' => $received_message));
			 
			// Получаем id вставленной записи
			$insert_id = $dbh->lastInsertId();
			

		}
	}
}

unset($sth);
unset($ch);
unset($data);
unset($dataObj);
unset($resArr);

// пишем в лог время выполнения скрипта
$logFile = $_SERVER["DOCUMENT_ROOT"] . "/grabtransactionslog.txt";
$dateTime = date("d.m.Y H:i:s");
$logStr = "done @ " . $dateTime . "\r\n";
file_put_contents($logFile, $logStr, FILE_APPEND);