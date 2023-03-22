<?
// подключаемся к базе
try
{
    $dbh = new PDO('mysql:dbname=fortuna;host=localhost', 'root', '');
}
catch (PDOException $e)
{
    die($e->getMessage());
}