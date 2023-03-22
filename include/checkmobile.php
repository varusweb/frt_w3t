<?
session_start();
$isMobile = false;
if(!isset($_SESSION['device']))
{
    require_once $_SERVER["DOCUMENT_ROOT"] . "/include/Mobile_Detect.php";
    $detect = new Mobile_Detect;

    if($detect->isMobile() || $detect->isTablet())
    {
        $isMobile = true;
        $_SESSION['device'] = "mobile";
    }
    else
    {
        $isMobile = false;
        $_SESSION['device'] = "desktop";
    }
}
else
{
    if($_SESSION['device'] == "mobile")
    {
        $isMobile = true;
    }
    elseif($_SESSION['device'] == "desktop")
    {
        $isMobile = false;
    }
}