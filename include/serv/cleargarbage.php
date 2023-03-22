<?
/* Скрипт очистки старых картинок с QR-кодами */
$now = time();
function list_files($path)
{
    if ($path[mb_strlen($path) - 1] != '/') {
        $path .= '/';
    }

    $files = array();
    $dh = opendir($path);
    while (false !== ($file = readdir($dh))) {
        if ($file != '.' && $file != '..' && !is_dir($path.$file) && $file[0] != '.') {
            $files[] = $file;
        }
    }

    closedir($dh);
    return $files;
}

// получаем массив с файлами metadata.json в директории загрузки
$dirPath = $_SERVER["DOCUMENT_ROOT"]."/images/qr/";
$filesArray = list_files($dirPath);

foreach($filesArray as $file)
{
    if($file !== "qrlogo.png")
    {
        $fileChangeTime = filemtime($dirPath . $file);
        if($now - $fileChangeTime > 86400)
        {
            unlink($dirPath . $file);
        }
    }
}
echo "done @ " . date("d.m.Y H:i:s");