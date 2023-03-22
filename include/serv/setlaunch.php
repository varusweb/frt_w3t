<?
// получаем данные о времени розыгрыша из файла
$launchTime = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/launch.txt');
$launchTime = $launchTime * 1;

// устанавливаем метку времени розыгрыша следующего тиража
$launchTimeNext = $launchTime + 604800;

file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/launch.txt', $launchTimeNext);

echo "Новое время запуска задано успешно!";