<?
// получаем данные о номере тиража из файла
$cycleNum = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/cycle.txt');
$cycleNum = $cycleNum * 1;

// следующий тираж
$cycleNumNext = $cycleNum + 1;

file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/cycle.txt', $cycleNumNext);

echo "Номер следующего тиража задан успешно!";