<?
// протестировать отправку пост запроса на вызов гет метода смарт-контракта
/*

{
  "address": "string",
  "method": "string",
  "stack": [
    [
      "string"
    ]
  ]
}

 */

/*
$data = array(
    'name'  => 'Маффин',
    'price' => 100.0
);

$ch = curl_init('https://example.com');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$res = curl_exec($ch);
curl_close($ch);

$res = json_encode($res, JSON_UNESCAPED_UNICODE);

echo "<pre>";
print_r($res);
echo "</pre>";
*/

// непонятно что за stack в квадратных скобках и как его кодировать в json
// квадратные скобки в stack появляются при такой структуре массива
$tstArr = Array(
                "address" => "EQBOdMRzd4IQySF_-FTIDTz7_xWwEk4EN-r4hhRH7EQkpkH0",
                "method" => "testFuckingMethod",
                "stack" => Array(
                    Array("1620")
                    /*"EQBOdMRzd4IQySF_-FTIDTz7",
                    "r4hhRH7EQkpkH0",
                    "1620"*/
                )
            );

echo "<pre>";
print_r($tstArr);
echo "</pre>";
echo "<hr>";

$res = json_encode($tstArr, JSON_UNESCAPED_UNICODE);

echo $res;