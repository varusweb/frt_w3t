<?
// наше изображение
$img = ImageCreateFromJPEG($_SERVER["DOCUMENT_ROOT"] . "/include/ticket-bg.jpg");

// определяем цвет, в RGB
$color = imagecolorallocate($img, 251, 250, 67);

// указываем путь к шрифту
$font = $_SERVER["DOCUMENT_ROOT"] . "/include/frizquadratactt.ttf";



// надо добить номер до 5 знаков левыми нулями
$tNum = (string) $_GET['number'];

$tnumStrLength = strlen($tNum);
$curNumStr = $tNum;
$nullNeeded = 5 - $tnumStrLength;
if($nullNeeded > 0)
{
    for ($n = $nullNeeded; $n > 0; $n--)
    {
        $curNumStr = "0" . $curNumStr;
    }
}

$text = "№" . $curNumStr;

imagettftext($img, 28, 0, 760, 89, $color, $font, $text);
// 28 - размер шрифта
// 0 - угол поворота
// 365 - смещение по горизонтали
// 159 - смещение по вертикали

header('Content-type: image/jpeg');
imagejpeg($img, NULL, 100);