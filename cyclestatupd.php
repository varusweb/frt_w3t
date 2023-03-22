<?
/*
 * Скрипт обновления статистики о текущем тираже
 * Запуск по крону 1 раз в 1-2 минуты
 * */

// получаем данные о номере тиража из файла
$cycleNum = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/cycle.txt');
$cycleNum = $cycleNum * 1;

// адрес кошелька для приема средств
$destWallet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/walletaddr.txt');

// подключаемся к базе
require_once($_SERVER["DOCUMENT_ROOT"] . "/dbc.inc.php");

// удаляем предыдущий файл lottery_data.json
$unlinkRes = unlink($_SERVER["DOCUMENT_ROOT"] . '/lottery_data.json');

if(!$unlinkRes)
{
    // удаление файла не состоялось, ошибка
    echo "unlink file 'lottery_data.json' failed! ERROR!<br>";
    exit;
}

// запускаем парсинг данных со смарт контракта в файл
// запускаем в темную, как черныый ящик
// успешность результата отслеживаем по наличию файла lottery_data.json
$buf = shell_exec("node get_method.js $destWallet");


sleep(6);

if(file_exists($_SERVER["DOCUMENT_ROOT"] . '/lottery_data.json'))
{
    // файл существует, сторонний скрипт отработал, крутим фарш дальше
    $data = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/lottery_data.json");

    $dataArray = json_decode($data, true);

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

    // извлечь данные из json файла в массив
    // данные из массива поместить в БД по номеру тиража (если тираж уже есть - UPDATE, если нет - INSERT)

    /*echo "<pre>";
    print_r($dataArray);
    echo "</pre>";
    exit;*/

    // делаем запрос к БД, проверяем, есть ли запись о текущем тираже
    $sth = $dbh->prepare("SELECT `id`, `cycleNum`, `drawTime` FROM `x_cycle_stat` WHERE `cycleNum` = :cycleNum");
    $sth->execute(array('cycleNum' => $cycleNum));
    $array = $sth->fetch(PDO::FETCH_ASSOC);

    if($array["id"])
    {
        // запись о текущем тираже есть, делаем UPDATE данных
        $sth = $dbh->prepare("UPDATE `x_cycle_stat` SET `prizePool` = :prizePool, `activeTickets` = :activeTickets, `coinPrizes` = :coinPrizes  WHERE `cycleNum` = :cycleNum");
        $sth->execute(array('prizePool' => $dataArray["prizePool"], 'activeTickets' => $dataArray["activeTickets"], 'coinPrizes' => $dataArray["coinPrizes"], 'cycleNum' => $cycleNum));
    }
    else
    {
        // записи о текущем тираже нет, делаем INSERT

        $nftCount = count($dataArray["prizeNfts"]);
        $nftItems = implode("|", $dataArray["prizeNfts"]);

        $sth = $dbh->prepare("INSERT INTO `x_cycle_stat` SET `cycleNum` = :cycleNum, `drawTime` = :drawTime, `price` = :price, `prizePool` = :prizePool, `activeTickets` = :activeTickets, `coinPrizes` = :coinPrizes, `prizeNftsCount` = :prizeNftsCount, `prizeNfts` = :prizeNfts");
        $sth->execute(array('cycleNum' => $cycleNum, 'drawTime' => $dataArray["drawTime"], 'price' => $dataArray["price"], 'prizePool' => $dataArray["prizePool"], 'activeTickets' => $dataArray["activeTickets"], 'coinPrizes' => $dataArray["coinPrizes"], 'prizeNftsCount' => $nftCount, 'prizeNfts' => $nftItems));
    }

}
else
{
    // файла нет, опять всё пошло по пизде...
    echo "'lottery_data.json' does not exists! ERROR!<br>";
    exit;
}