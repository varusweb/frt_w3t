<?
/*
 * Проверка работы скрипта из корня подключенного к скрипту в подпапке
 * */

// подключим скрипт, который работает только из корня, посмотрим, что получится

require_once($_SERVER["DOCUMENT_ROOT"] . "/get-active-tickets.php");

echo "here is suka! <br>";

echo "param 2: <br>";

var_dump($activeTickets);