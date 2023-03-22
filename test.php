<?
/* Скрипт проверки наличия транзакции покупки NFT в БД в таблице транзакций x_transactions и осуществления процесса покупки */
$buyerWallet = $_REQUEST['buyerWallet'];
$nftPrice = $_REQUEST['nftPrice'] * 1;
$purchaseToken = $_REQUEST['purchaseToken'];
$now = time();
$nftType = "";

function logProcess($str)
{
    $processLogFile = $_SERVER["DOCUMENT_ROOT"] . '/cfg/process.txt';
    file_put_contents($processLogFile, $str, FILE_APPEND);
}
$t = "first run [" . date('d.m.Y H:i:s') . "]\n";
logProcess($t);

// массив типов NFT с ценами
$nftArray = Array(
    50 => "n3x1",
    100 => "n3x2",
    150 => "n3x3",
    185 => "n3x4",
    245 => "n3x5",
    270 => "w3t1",
    380 => "w3t2",
    550 => "w3t3",
    1400 => "w3t4"
);

$configArray = Array(
    "n3x1" => Array("DB_NAME" => "cc17533_3nixie1", "DB_PASS" => "d6mWhXE7", "PATH" => "3nixie-antarctic", "NFT_NAME" => "3Nixie Antarctic"),
    "n3x2" => Array("DB_NAME" => "cc17533_3nixie2", "DB_PASS" => "gMyB6dkm", "PATH" => "3nixie-arctic", "NFT_NAME" => "3Nixie Arctic"),
    "n3x3" => Array("DB_NAME" => "cc17533_3nixie3", "DB_PASS" => "XzP6gPkj", "PATH" => "3nixie-indian", "NFT_NAME" => "3Nixie Indian"),
    "n3x4" => Array("DB_NAME" => "cc17533_3nixie4", "DB_PASS" => "Sy7WizpK", "PATH" => "3nixie-atlantic", "NFT_NAME" => "3Nixie Atlantic"),
    "n3x5" => Array("DB_NAME" => "cc17533_3nixie5", "DB_PASS" => "eCraKY2v", "PATH" => "3nixie-pacific", "NFT_NAME" => "3Nixie Pacific"),
    "w3t1" => Array("DB_NAME" => "cc17533_3ton1", "DB_PASS" => "S7aTj5hd", "PATH" => "web3ton-antarctic", "NFT_NAME" => "Web3TON Antarctic"),
    "w3t2" => Array("DB_NAME" => "cc17533_3ton2", "DB_PASS" => "BT1ukwZg", "PATH" => "web3ton-arctic", "NFT_NAME" => "Web3TON Arctic"),
    "w3t3" => Array("DB_NAME" => "cc17533_3ton3", "DB_PASS" => "M9BixD6D", "PATH" => "web3ton-indian", "NFT_NAME" => "Web3TON Indian"),
    "w3t4" => Array("DB_NAME" => "cc17533_3ton4", "DB_PASS" => "T9XNY2sj", "PATH" => "web3ton-atlantic", "NFT_NAME" => "Web3TON Atlantic")
);

if(array_key_exists($nftPrice, $nftArray))
{
    $nftType = $nftArray[$nftPrice];
}
else
{
    //echo "nftPrice is wrong!";
    logError($nftType, $buyerWallet, $nftPrice, $purchaseToken, "13", "send incorrect price");
    echo json_encode(array('approval'=>0, 'errorcode'=>'13', 'errormsg'=>'send incorrect price'));
    exit;
}

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
function clearBufSrt($str)
{
    $str = str_replace(' ', '', $str);
    $str = str_replace('{', '', $str);
    $str = str_replace('}', '', $str);
    $str = str_replace("'", "", $str);
    return $str;
}
function getBufResult($str)
{
    $arr1 = explode(",", $str);
    $arr2 = explode(":", $arr1[0]);
    return $arr2[1];
}
function logPurchase($nftType, $nftID, $nftAddr, $buyerWallet, $amount, $tHash)
{
    $nftLogFile = $_SERVER["DOCUMENT_ROOT"] . '/cfg/' . $nftType . '.log';
    $dt = date("d.m.Y H:i:s");
    $logStr = $dt . " | NFT_ID: " . $nftID . "; NFT address: " . $nftAddr . "; Buyer Wallet: " . $buyerWallet . "; Paid: " . $amount . " TON; trHash: " .$tHash. "\n";
    file_put_contents($nftLogFile, $logStr, FILE_APPEND);
}
function logError($nftType, $buyerWallet, $nftPrice, $purchaseToken, $errorcode, $errormsg)
{
    $errorLogFile = $_SERVER["DOCUMENT_ROOT"] . '/cfg/error.log';
    $dt = date("d.m.Y H:i:s");
    $logStr = $dt . " | buyerWallet: " . $buyerWallet . "; nftPrice: " . $nftPrice . "; purchaseToken: " . $purchaseToken . "; nftType: " . $nftType . "; ERRORCODE: " . $errorcode . "; ERRORMSG: " . $errormsg . "\n";
    file_put_contents($errorLogFile, $logStr, FILE_APPEND);
}


if($buyerWallet && $nftPrice && $purchaseToken)
{
    $t = "sale start!\n";
    logProcess($t);
    try
    {
        $dbh = new PDO('mysql:dbname=db_sales;host=localhost', 'db_sales', 'rR2hA5pP3k');
    }
    catch (PDOException $e)
    {
        die($e->getMessage());
    }

    $sth = $dbh->prepare("SELECT * FROM `x_transactions` WHERE `received_message` = :received_message");
    $sth->execute(array('received_message' => $purchaseToken));
    $array = $sth->fetch(PDO::FETCH_ASSOC);

    if($array["id"])
    {
        $t = "stage 1: transaction found!\n";
        logProcess($t);
        /*$amountNano = $array["received_ton"] * 1;
        $amount = convNanoToTon($amountNano);*/
        $amount = $array["received_ton"] * 1;

        $t = "stage 1.5: amount: " .$amount. "; received_ton: " .$array["received_ton"]. "; nftPrice: " .$nftPrice. "; received_from: " .$array["received_from"]. "; buyerWallet: " .$buyerWallet. "\n";
        logProcess($t);

        if($amount == $nftPrice && $array["received_from"] == $buyerWallet)
        {
            // транзакция на покупку найдена, все данные коректны
            // осуществляем процессы продажи NFT
            $t = "stage 2: transaction correct!\n";
            logProcess($t);

            // файл блокировки для предотвращения параллельного выполнения скрипта
            $lock = $_SERVER["DOCUMENT_ROOT"] . '/cfg/' . $nftType . '.lock';
            $f = fopen($lock, 'x');

            if ($f === false)
            {
                // Не удается получить блокировку
                // Скорее всего скрипт работает параллельно, поэтому завершаем работу
                $t = "stage 3: lock file error!\n";
                logProcess($t);
                echo "Не удается получить блокировку!";
                exit;
            }
            else
            {
                $t = "stage 3: lock file done!\n";
                logProcess($t);
                // Блокировка получена, работаем
                // регистрируем завершающую функцию на случай непредвиденных обстоятельнств в работе сервера и скрипта
                function shutdown($lock, $f)
                {
                    // Это наша завершающая функция, здесь выполняем работу, перед тем как скрипт полностью завершится.
                    fclose($f);
                    unlink($lock);
                }
                register_shutdown_function('shutdown', $lock, $f);

                // выбираем из списка соответствующего типа NFT следующую по очереди (псевдорандом)
                // itemID;nftAddress;rarity_scale
                $nftListFile = $_SERVER['DOCUMENT_ROOT'] . "/cfg/" . $nftType . ".csv";
                if(file_exists($nftListFile))
                {
                    $row = 0;
                    $csvDataArr = array();
                    if (($handle = fopen($nftListFile, "r")) !== FALSE)
                    {
                        while (($data = fgetcsv($handle, 200, ";")) !== FALSE)
                        {

                            $csvDataArr[$row]["itemID"] = trim($data[0]);
                            $csvDataArr[$row]["nftAddress"] = trim($data[1]);
                            $csvDataArr[$row]["rarity_scale"] = trim($data[2]);
                            $row++;
                        }
                        fclose($handle);
                        $t = "stage 4: get random NFT from list!\n";
                        logProcess($t);
                    }
                    // получаем следующую по очереди NFT
                    $randomNft = array_shift($csvDataArr);

                    // есть данные: кошелек покупателя ($buyerWallet); nftAddress ($randomNft["nftAddress"])
                    // запускаем скрипт трансфера NFT
                    $nftAddress = $randomNft["nftAddress"];
                    $buf = shell_exec("node index.js $nftAddress $buyerWallet");
                    $buf = clearBufSrt($buf);
                    $res = getBufResult($buf);
                    if($res == "ok")
                    {
                        $t = "stage 5: NFT transfer complete!\n";
                        logProcess($t);
                        // трансфер NFT осуществлено успешно
                        // обрабатываем файл списка NFT (убираем из списка проданную NFT)
                        // если это последняя NFT на продажу, удаляем файл со списком
                        if(count($csvDataArr) === 0)
                        {
                            unlink($nftListFile);
                        }
                        else
                        {
                            foreach($csvDataArr as $key => $item)
                            {
                                $itemDataStr = $item["itemID"] . ";" . $item["nftAddress"] . ";" . $item["rarity_scale"] . "\r\n";
                                if($key === 0)
                                {
                                    file_put_contents($nftListFile, $itemDataStr);
                                }
                                else
                                {
                                    file_put_contents($nftListFile, $itemDataStr, FILE_APPEND);
                                }
                            }
                        }

                        // обновляем данные о NFT в БД (таблица collection) id => $randomNft["itemID"]
                        // подключаемся к соответствующей базе
                        try
                        {
                            $dbh = new PDO('mysql:dbname=' . $configArray[$nftType]["DB_NAME"] . ';host=localhost', $configArray[$nftType]["DB_NAME"], $configArray[$nftType]["DB_PASS"]);
                        }
                        catch (PDOException $e)
                        {
                            die($e->getMessage());
                        }
                        // получаем данные по проданной NFT для отображения пользователю
                        // картинка; Item #; Rarity rank; Rarity score; Rarity scale;
                        $sth = $dbh->prepare("SELECT `id`, `rarity_rank`, `rarity_score`, `rarity_scale` FROM `collection` WHERE `id` = :id");
                        $sth->execute(array('id' => $randomNft["itemID"]));
                        $arrayNftItem = $sth->fetch(PDO::FETCH_ASSOC);

                        // обновляем данные проданного NFT
                        $sth = $dbh->prepare("UPDATE `collection` SET `mint` = :mint, `owner` = :owner, `is_wallet` = :is_wallet, `owner_update` = :owner_update  WHERE `id` = :id");
                        $sth->execute(array('mint' => 1, 'owner' => $buyerWallet, 'is_wallet' => 1, 'owner_update' => $now, 'id' => $arrayNftItem["id"]));

                        $t = "stage 6: NFT data updated!\n";
                        logProcess($t);

                        // записываем данне о покупке в лог
                        logPurchase($nftType, $randomNft["itemID"], $nftAddress, $buyerWallet, $amount, $array["hash"]);

                        // возвращаем данные для завершения процесса покупки
                        $path2 = $str = str_replace('-', '_', $configArray[$nftType]["PATH"]);
                        echo json_encode(array('approval'=>1, 'wallet'=>$array["received_from"], 'nftType'=>$nftType, 'id'=>$arrayNftItem["id"], 'rarityRank'=>$arrayNftItem["rarity_rank"], 'rarityScore'=>$arrayNftItem["rarity_score"], 'rarityScale'=>$arrayNftItem["rarity_scale"], 'path1'=>$configArray[$nftType]["PATH"], 'path2'=>$path2, 'nftName'=>$configArray[$nftType]["NFT_NAME"]));
                    }
                    else
                    {
                        // res не ok
                        // какого-то хуя что-то пошло не так в самом технологически продвинутом блокчейне 21 века The Open Network
                        // ну и хули тут делать - не понятно...
                        // сделаем паузу секунд 15 и попробуем хуйнуть по-новой
                        sleep(15);

                        $buf = shell_exec("node index.js $nftAddress $buyerWallet");
                        $buf = clearBufSrt($buf);
                        $res = getBufResult($buf);

                        if($res == "ok")
                        {
                            $t = "stage 5: NFT transfer complete!\n";
                            logProcess($t);
                            // трансфер NFT осуществлено успешно
                            // обрабатываем файл списка NFT (убираем из списка проданную NFT)
                            // если это последняя NFT на продажу, удаляем файл со списком
                            if(count($csvDataArr) === 0)
                            {
                                unlink($nftListFile);
                            }
                            else
                            {
                                foreach($csvDataArr as $key => $item)
                                {
                                    $itemDataStr = $item["itemID"] . ";" . $item["nftAddress"] . ";" . $item["rarity_scale"] . "\r\n";
                                    if($key === 0)
                                    {
                                        file_put_contents($nftListFile, $itemDataStr);
                                    }
                                    else
                                    {
                                        file_put_contents($nftListFile, $itemDataStr, FILE_APPEND);
                                    }
                                }
                            }

                            // обновляем данные о NFT в БД (таблица collection) id => $randomNft["itemID"]
                            // подключаемся к соответствующей базе
                            try
                            {
                                $dbh = new PDO('mysql:dbname=' . $configArray[$nftType]["DB_NAME"] . ';host=localhost', $configArray[$nftType]["DB_NAME"], $configArray[$nftType]["DB_PASS"]);
                            }
                            catch (PDOException $e)
                            {
                                die($e->getMessage());
                            }
                            // получаем данные по проданной NFT для отображения пользователю
                            // картинка; Item #; Rarity rank; Rarity score; Rarity scale;
                            $sth = $dbh->prepare("SELECT `id`, `rarity_rank`, `rarity_score`, `rarity_scale` FROM `collection` WHERE `id` = :id");
                            $sth->execute(array('id' => $randomNft["itemID"]));
                            $arrayNftItem = $sth->fetch(PDO::FETCH_ASSOC);

                            // обновляем данные проданного NFT
                            $sth = $dbh->prepare("UPDATE `collection` SET `mint` = :mint, `owner` = :owner, `is_wallet` = :is_wallet, `owner_update` = :owner_update  WHERE `id` = :id");
                            $sth->execute(array('mint' => 1, 'owner' => $buyerWallet, 'is_wallet' => 1, 'owner_update' => $now, 'id' => $arrayNftItem["id"]));

                            $t = "stage 6: NFT data updated!\n";
                            logProcess($t);

                            // записываем данне о покупке в лог
                            logPurchase($nftType, $randomNft["itemID"], $nftAddress, $buyerWallet, $amount, $array["hash"]);

                            // возвращаем данные для завершения процесса покупки
                            $path2 = $str = str_replace('-', '_', $configArray[$nftType]["PATH"]);
                            echo json_encode(array('approval'=>1, 'wallet'=>$array["received_from"], 'nftType'=>$nftType, 'id'=>$arrayNftItem["id"], 'rarityRank'=>$arrayNftItem["rarity_rank"], 'rarityScore'=>$arrayNftItem["rarity_score"], 'rarityScale'=>$arrayNftItem["rarity_scale"], 'path1'=>$configArray[$nftType]["PATH"], 'path2'=>$path2, 'nftName'=>$configArray[$nftType]["NFT_NAME"]));
                        }
                    }
                }
                else
                {
                    //echo "Все NFT данного вида проданы!";
                    logError($nftType, $buyerWallet, $nftPrice, $purchaseToken, "14", "sold out");
                    echo json_encode(array('approval'=>0, 'errorcode'=>'14', 'errormsg'=>'sold out'));
                    exit;
                }

            }

        }
    }
    else
    {
        // транзакция НЕ найдена
        echo json_encode(array('approval'=>0, 'errorcode'=>'11', 'errormsg'=>'transaction not found'));
        exit;
    }
}
else
{
    logError($nftType, $buyerWallet, $nftPrice, $purchaseToken, "19", "not enough data");
    echo json_encode(array('approval'=>0, 'errorcode'=>'19', 'errormsg'=>'not enough data'));
    exit;
}
$t = "finish!\n=============================\n";
logProcess($t);
exit;