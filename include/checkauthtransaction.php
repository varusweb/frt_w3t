<?
/*
 * Скрипт проверки наличия авторизационной транзакции БД в таблице транзакций x_transactions
 * Запуск AJAXом из JS скрипта
 * */
$token = $_REQUEST['token'];

if($token)
{
    $textLen = mb_strlen($token);
    if($textLen == 20)
    {
        // подключаемся к базе
        require_once($_SERVER["DOCUMENT_ROOT"] . "/dbc.inc.php");

        $sth = $dbh->prepare("SELECT * FROM `x_transactions` WHERE `received_message` = :received_message");
        $sth->execute(array('received_message' => $token));
        $array = $sth->fetch(PDO::FETCH_ASSOC);

        if($array["id"])
        {
            // авторизационная транзакция найдена
            echo json_encode(array('approval'=>1, 'wallet'=>$array["received_from"]));
        }
        else
        {
            // авторизационная транзакция НЕ найдена
            echo json_encode(array('approval'=>0));
        }
    }
}
else
{
    exit;
}