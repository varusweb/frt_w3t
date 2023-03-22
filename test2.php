<?
$i = 11;
$m = 2;
//$n = (int) ($i / $m);
$res = $i / $m;

var_dump($res);
echo "<br>";

if(is_int($res) && $res !== 0)
{
    echo "result is int, GOOD!";
}
elseif(is_float($res))
{
    echo "result is float, клиент мудак!";
}
else
{
    echo "а тут ваще поебень какая-то!";
}