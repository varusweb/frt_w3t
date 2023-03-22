<?
$data = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/lottery_data.json");
var_dump($data);

$dataArray = json_decode($data, true);

echo "<hr>";
echo "<pre>";
print_r($dataArray);
echo "</pre>";


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

$nftCount = count($dataArray["prizeNfts"]);
echo "<hr>";

echo "price: " . convNanoToDecimalTon($dataArray["price"]) . "<br>";
echo "prizePool: " . convNanoToDecimalTon($dataArray["prizePool"]) . "<br>";
echo "prizeNfts: " . $nftCount . "<br>";