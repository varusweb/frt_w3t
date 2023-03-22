<?
$tonAddress = "EQBINZZji1507MJHqKaXDyXK8b2JQW3fPrm07FOZE5NkC22w";
$buf = shell_exec("node get_method.js $tonAddress");
/*$buf = clearBufSrt($buf);
$res = getBufResult($buf);*/

//var_dump($res);
echo "<hr>";
echo "done @ " . date("d.m.Y H:i:s");

/*phpinfo();*/