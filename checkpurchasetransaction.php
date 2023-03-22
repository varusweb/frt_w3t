<?
/* Скрипт проверки наличия транзакции покупки в БД в таблице транзакций x_transactions и осуществления процесса покупки */
// входные данные
$buyerWallet = $_REQUEST['buyerWallet']; // кошелек покупателя и без кошелька даже не суйся сюда, дружок
$purchaseAmount = $_REQUEST['purchaseAmount'] * 1; // сумма покупки, джолжна быть больше или равной стоимости одного билета, иначе - нахуй!
$purchaseToken = $_REQUEST['purchaseToken']; // мемо. не заполнил мемо, умник? пойди ка ты нахуй!

// получаем стоимость одного билета
$ticketCost = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/ticketcost.txt');
$ticketCost = $ticketCost * 1;

// вычисляем количество билетов
$purchaseQuantity = $purchaseAmount / $ticketCost;
if($purchaseQuantity >= 1)
{
    $param = true;
}
else
{
    $param = false;
}
// количество приобретаемых билетов, натуральное целое положительное число, иначе - нахуй!
// что-то тут хотел написать и забыл. похуЮ!

$now = time(); // текущая метка времени, куда ж без неё

// получаем номер текущего тиража, без лишних нулей слева
$cycleNum = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/cycle.txt');
$cycleNum = $cycleNum * 1;

// функции, очевидно, да?) это желчь и злоба, потому что все эти тритоны уже просто поперек горла, наимутнейшая и убыточная хуета, давно пора соскамить, закрыть и забыть - моё такое мнение
function logProcess($str)
{
    $processLogFile = $_SERVER["DOCUMENT_ROOT"] . '/cfg/process.txt';
    file_put_contents($processLogFile, $str, FILE_APPEND);
}
$t = "first run [" . date('d.m.Y H:i:s') . "]\n";
logProcess($t);

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

// если все необходимые данные для осуществления корректной продажи в наличии, запускаем процесс
//if($buyerWallet && $param && $purchaseToken)
if($buyerWallet && $purchaseToken)
{
    $t = "sale start!\n";
    logProcess($t);

    // подключаемся к базе
    try
    {
        $dbh = new PDO('mysql:dbname=fortuna;host=localhost', 'fortuna', 'vI1oX2tU1w');
    }
    catch (PDOException $e)
    {
        die($e->getMessage());
    }

    $sth = $dbh->prepare("SELECT * FROM `x_transactions` WHERE `received_message` = :received_message AND `processed` = :processed");
    $sth->execute(array('received_message' => $purchaseToken, 'processed' => 0));
    $array = $sth->fetch(PDO::FETCH_ASSOC);

    if($array["id"])
    {
        // транзакция на покупку найдена в БД
        $t = "stage 1: transaction found!\n";
        logProcess($t);

        // гасим транзакцию в таблице x_transactions
        $sth = $dbh->prepare("UPDATE `x_transactions` SET `processed` = :processed WHERE `id` = :id");
        $sth->execute(array('processed' => 1, 'id' => $array["id"]));

        $fromWallet = $array["received_from"];

        /*$amountNano = $array["received_ton"] * 1;
        $amount = convNanoToTon($amountNano);*/
        $amount = $array["received_ton"] * 1;

        $t = "stage 1.5: amount: " .$amount. "; received_ton: " .$array["received_ton"]. "; purchaseAmount: " .$purchaseAmount. "; received_from: " .$array["received_from"]. "; buyerWallet: " .$buyerWallet. "\n";
        logProcess($t);

        // делим сумму транзы на стоимость одного билета
        //  если больше единицы, то начисляем билеты (проводим продажу)
        // $amount - сумма, полученная от клиента
        // $ticketCost - стоимость одного билета
        // делим сумму $amount на стоимость одного билета $ticketCost без остатка
        // если полученное число больше 1, то проводим продажу и начисляем билеты
        // $resParam - реальное кол-во билетов за которые уплачено

        // переделать к хуям
        // если сумма не делится без остатка, то клиент мудак и пойдет нахуй (так работает смарт-контракт)
        // отвечаем скрипту json с кодом ошибки. на странице продажи открывается сообщение с ошибкой

        //$resParam = (int) ($amount / $ticketCost);

        $resParam = $amount / $ticketCost;
        if(is_int($resParam) && $resParam !== 0) // проверкана целое число, т.е. сумма точная
        {
            // в этом случае все правильно, продолжаем работу скрипта
        }
        else
        {
            // ошибка, продажа не состоится. отдаем json с кодом ошибки для отображения сообщения клиенту
            echo json_encode(array('approval'=>9, 'errorcode'=>'911', 'errormsg'=>'transaction critical error'));
            exit;
        }

        // проверяем, соответствует ли оплаченная сумма заказанному количеству билетов
        // $purchaseAmount - заказано, сумма итого
        // $amount - оплачено итого
        if($amount == $purchaseAmount)
        {
            $precision = true;
            $precisionFlag = 1;
        }
        else
        {
            $precision = false;
            $precisionFlag = 0;
        }

        if($resParam >= 1 && $array["received_from"] == $buyerWallet)
        {
            // транзакция на покупку найдена, все данные коректны
            // осуществляем процессы продажи NFT
            if($precision)
                $t = "stage 2: transaction absolute correct!\n";
            else
                $t = "stage 2: transaction exist, but another sum!\n";
            logProcess($t);

            // файл блокировки для предотвращения параллельного выполнения скрипта
            $lock = $_SERVER["DOCUMENT_ROOT"] . '/cfg/dirty.lock';
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

                // проводим продажу и начисляем требуемое количество билетов
                // $resParam - реальное количество билетов за которое уплачено
                // именно это количество начисляем покупателю

                // определяем номер билета, который будем начислять
                /*
                 * берем номер текущего тиража
                 * Делаем запрос к БД, таблица x_sales
                 * Если на текущий тираж билетов не продано ни одного, продаем с номера 1
                 * в противном случае, получаем номер последнего проданного билета, продаем со следующего за ним порядкового номера
                */

                /*
                 * НИХУЯ, всё переделать. Определять будем через запрос к скритпу Виктора, получаем activeTickets
                 * */
                $currentTicketNum = 0;

                // делаем запуск скрипта Виктора, чтобы получить значение activeTickets - оно будет равным номеру следующего билета
                // с запутанными путями всё это не работает, поэтому хуярить всё будем из корневой дирекктории и похуй, лишь бы хоть как-то работало уже

                /* врезанный функционал */

                $activeTickets = false;
                // адрес кошелька для приема средств
                $destWallet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/walletaddr.txt');
                if(file_exists($_SERVER["DOCUMENT_ROOT"] . '/sale_data.json'))
                {
                    // удаляем предыдущий файл sale_data.json
                    $unlinkRes = unlink($_SERVER["DOCUMENT_ROOT"] . '/sale_data.json');
                }
                $buf = shell_exec("node get_sale.js $destWallet");
                // не торопимся
                sleep(5);
                if(file_exists($_SERVER["DOCUMENT_ROOT"] . '/sale_data.json'))
                {
                    // файл существует, сторонний скрипт отработал, крутим фарш дальше
                    $data = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/sale_data.json");

                    $dataArray = json_decode($data, true);
                    $activeTickets = $dataArray["activeTickets"] * 1;
                }

                if($activeTickets !== false)
                {
                    $currentTicketNum = $activeTickets;
                }
                else
                {
                    // всё пошло по пизде
                    // запись успешно добавлена в БД, пишем лог
                    $t = "ERROR: Victors script failed!\n";
                    logProcess($t);
                }

                /* END врезанный функционал */

                /*
                               $sth = $dbh->prepare("SELECT `id`, `cycleNum`, `ticketNum` FROM `x_sales` WHERE `cycleNum` = :cycleNum ORDER BY `id` DESC LIMIT 1");
                               $sth->execute(array('cycleNum' => $cycleNum));
                               $array = $s>fetch(PDO::FETCH_ASSOC);

                               /*if(!$array["id"])
                               {
                                   // последний билет на текущий тираж не найден
                                   // значит на текущий тираж не продано ещё ни одного билета
                                   // продаем с №1
                                   $currentTicketNum = 1;
                               }
                               else
                               {
                                   $lastTicketNum = $array["ticketNum"] * 1;
                                   $currentTicketNum = $lastTicketNum + 1;
                               }*/

                // проверяем, куплен 1 билет или больше?
                if($resParam == 1)
                {
                    $tdt = date("d.m.Y H:i:s", $now);
                    // хуярим одну паршивую продажонку
                    $sth = $dbh->prepare("INSERT INTO `x_sales` SET `cycleNum` = :cycleNum, `ticketNum` = :ticketNum, `buyerWallet` = :buyerWallet, `timestamp` = :tstamp, `dateTime` = :dateTime, `ticketPrice` = :ticketPrice");
                    $sth->execute(array('cycleNum' => $cycleNum, 'ticketNum' => $currentTicketNum, 'buyerWallet' => $buyerWallet, 'tstamp' => $now, 'dateTime' => $tdt, 'ticketPrice' => $ticketCost));

                    // id вставленной записи
                    $insert_id = $dbh->lastInsertId();

                    if($insert_id)
                    {
                        // запись успешно добавлена в БД, пишем лог
                        $t = "stage 6: ticket sold successfully!\n";
                        logProcess($t);

                        // здесь надо отдать данные JS скрипту
                        echo json_encode(array('approval'=>1, 'wallet'=>$fromWallet, 'paidTotal'=>$amount, 'ticketsBought'=>$resParam, 'precision'=>$precisionFlag, 'ticketsBoughtNum'=>$currentTicketNum));
                        exit;
                    }
                    else
                    {
                        // запись не добавлена в БД, непонятная ошибка и что-то пошло не так (по пизде)
                        $t = "stage 6: ticket NOT sold! Broken wrecked wasted shit, I hate it 1!!\n";
                        logProcess($t);

                        // возможно здесь надо отдать скрипту сообщение, что всё наебнулось
                    }
                }
                elseif($resParam > 1)
                {
                    // хуярим продажи в цикле for
                    $ticketNumArr = Array(); // массив номеров проданных билетов
                    $tdt = date("d.m.Y H:i:s", $now);

                    for ($n = $resParam; $n > 0; $n--)
                    {
                        $sth = $dbh->prepare("INSERT INTO `x_sales` SET `cycleNum` = :cycleNum, `ticketNum` = :ticketNum, `buyerWallet` = :buyerWallet, `timestamp` = :tstamp, `dateTime` = :dateTime, `ticketPrice` = :ticketPrice");
                        $sth->execute(array('cycleNum' => $cycleNum, 'ticketNum' => $currentTicketNum, 'buyerWallet' => $buyerWallet, 'tstamp' => $now, 'dateTime' => $tdt, 'ticketPrice' => $ticketCost));

                        // id вставленной записи
                        $insert_id = $dbh->lastInsertId();

                        if($insert_id)
                        {
                            // запись успешно добавлена в БД, пишем лог, добавляем номер в массив проданных, увеличиваем номер билета
                            $ticketNumArr[] = $currentTicketNum;
                            $t = "stage 6: ticket sold successfully (num: $currentTicketNum)!\n";
                            logProcess($t);
                            $currentTicketNum++;
                        }
                        else
                        {
                            // запись не добавлена в БД, непонятная ошибка и что-то пошло не так (по пизде)
                            $t = "stage 6: ticket NOT sold! Broken wrecked wasted shit, I hate it 2!!\n";
                            logProcess($t);
                        }
                    }

                    if($ticketNumArr[0])
                    {
                        $ticketNumStr = implode("|", $ticketNumArr);

                        // здесь надо отдать данные JS скрипту
                        echo json_encode(array('approval'=>1, 'wallet'=>$fromWallet, 'paidTotal'=>$amount, 'ticketsBought'=>$resParam, 'precision'=>$precisionFlag, 'ticketsBoughtNum'=>$ticketNumStr));
                        exit;
                    }
                    else
                    {
                        $t = "stage 7: shit WTF?!!\n";
                        logProcess($t);
                    }
                }




            }

        }
        else
        {
            // оплачено менее, чем за 1 билет, либо оплачено по мемо с другого кошелька (несоответствие адреса кошелька покупателя)
            echo json_encode(array('approval'=>0, 'errorcode'=>'37111', 'errormsg'=>'transaction incorrect пиздец хуйня'));
            exit;
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
    //logError($nftType, $buyerWallet, $nftPrice, $purchaseToken, "19", "not enough data");
    echo json_encode(array('approval'=>0, 'errorcode'=>'19', 'errormsg'=>'not enough data'));
    exit;
}
$t = "finish!\n=============================\n";
logProcess($t);
exit;

