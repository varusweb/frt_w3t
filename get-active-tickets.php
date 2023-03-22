<?
/*
 * Скрипт получения количества активных билетов для выяснения номера следующего билета при процессе продажи
 * */

$activeTickets = false;

// адрес кошелька для приема средств
$destWallet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/walletaddr.txt');

if(file_exists($_SERVER["DOCUMENT_ROOT"] . '/sale_data.json'))
{
    // файл sale_data есть
    //echo "есть файл sale_data.json <br>";

    // удаляем предыдущий файл lottery_data.json
    $unlinkRes = unlink($_SERVER["DOCUMENT_ROOT"] . '/sale_data.json');
}
else
{
    // файл sale_data не существует
    //echo "файл sale_data.json не существует <br>";
}

// запускаем модифицированный скрипт Виктора
//$jsScriptToRun = $_SERVER["DOCUMENT_ROOT"] . '/get_sale.js';
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