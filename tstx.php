<?
// флаг продаж (открыто/закрыто)
$saleOpen = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/saleopen.txt');
$saleOpen = $saleOpen * 1;

if(!$saleOpen)
{
    header("Location: https://web3ton.pro");
    die();
}

// редирект на HTTPS только для домена .PRO
require_once $_SERVER["DOCUMENT_ROOT"] . "/include/https-redirect.php";

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

$timestamp = time();

$metaPic = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/images/web3ton-title.jpg";
//$metaPic = "https://web3ton.pro/images/web3ton-title.jpg";
$currentPage = $_SERVER['REQUEST_SCHEME'] . '://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
//$destWallet = "EQCNyEcHg5I7YR_PhtuPye7yDMs9Imnm22MY5CiZPyCALBA4";
$destWallet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/walletaddr.txt');

$configArray = Array(
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3nixie1", "DB_PASS" => "d6mWhXE7"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/3nixie_antarctic.txt",
        "NFT_NAME" => "3Nixie Antarctic",
        "NFT_PRICE" => 50
    ),
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3nixie2", "DB_PASS" => "gMyB6dkm"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/3nixie_arctic.txt",
        "NFT_NAME" => "3Nixie Arctic",
        "NFT_PRICE" => 100
    ),
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3nixie3", "DB_PASS" => "XzP6gPkj"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/3nixie_indian.txt",
        "NFT_NAME" => "3Nixie Indian",
        "NFT_PRICE" => 150
    ),
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3nixie4", "DB_PASS" => "Sy7WizpK"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/3nixie_atlantic.txt",
        "NFT_NAME" => "3Nixie Atlantic",
        "NFT_PRICE" => 185
    ),
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3nixie5", "DB_PASS" => "eCraKY2v"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/3nixie_pacific.txt",
        "NFT_NAME" => "3Nixie Pacific",
        "NFT_PRICE" => 245
    ),
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3ton1", "DB_PASS" => "S7aTj5hd"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/web3ton_antarctic.txt",
        "NFT_NAME" => "Web3TON Antarctic",
        "NFT_PRICE" => 275
    ),
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3ton2", "DB_PASS" => "BT1ukwZg"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/web3ton_arctic.txt",
        "NFT_NAME" => "Web3TON Arctic",
        "NFT_PRICE" => 390
    ),
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3ton3", "DB_PASS" => "M9BixD6D"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/web3ton_indian.txt",
        "NFT_NAME" => "Web3TON Indian",
        "NFT_PRICE" => 560
    ),
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3ton4", "DB_PASS" => "T9XNY2sj"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/web3ton_atlantic.txt",
        "NFT_NAME" => "Web3TON Atlantic",
        "NFT_PRICE" => 1500
    ),
    Array(
        "mainDB" => Array("DB_NAME" => "cc17533_3ton5", "DB_PASS" => "pRnnAZ7x"),
        "queuePath" => $_SERVER["DOCUMENT_ROOT"] . "/cfg/web3ton_pacific.txt",
        "NFT_NAME" => "Web3TON Pacific",
        "NFT_PRICE" => 15000
    )
);

// функции генерации уникального токена
function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function getToken($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
    }

    return $token;
}

switch($_GET["nft"])
{
    case "3nixie-antarctic":
        $buyNftName = "3Nixie Antarctic";
        $configIndex = 0;
        $itemVideo = "3nixie-antarctic.mp4";
        $itemPoster = "3nixie-antarctic.jpg";
        break;
    case "3nixie-arctic":
        $buyNftName = "3Nixie Arctic";
        $configIndex = 1;
        $itemVideo = "3nixie-arctic.mp4";
        $itemPoster = "3nixie-arctic.jpg";
        break;
    case "3nixie-indian":
        $buyNftName = "3Nixie Indian";
        $configIndex = 2;
        $itemVideo = "3nixie-indian.mp4";
        $itemPoster = "3nixie-indian.jpg";
        break;
    case "3nixie-atlantic":
        $buyNftName = "3Nixie Atlantic";
        $configIndex = 3;
        $itemVideo = "3nixie-atlantic.mp4";
        $itemPoster = "3nixie-atlantic.jpg";
        break;
    case "3nixie-pacific":
        $buyNftName = "3Nixie Pacific";
        $configIndex = 4;
        $itemVideo = "3nixie-pacific.mp4";
        $itemPoster = "3nixie-pacific.jpg";
        break;
    case "web3ton-antarctic":
        $buyNftName = "Web3TON Antarctic";
        $configIndex = 5;
        $itemVideo = "web3ton-antarctic.mp4";
        $itemPoster = "web3ton-antarctic.jpg";
        break;
    case "web3ton-arctic":
        $buyNftName = "Web3TON Arctic";
        $configIndex = 6;
        $itemVideo = "web3ton-arctic.mp4";
        $itemPoster = "web3ton-arctic.jpg";
        break;
    case "web3ton-indian":
        $buyNftName = "Web3TON Indian";
        $configIndex = 7;
        $itemVideo = "web3ton-indian.mp4";
        $itemPoster = "web3ton-indian.jpg";
        break;
    case "web3ton-atlantic":
        $buyNftName = "Web3TON Atlantic";
        $configIndex = 8;
        $itemVideo = "web3ton-atlantic.mp4";
        $itemPoster = "web3ton-atlantic.jpg";
        break;
    case "web3ton-pacific":
        $buyNftName = "Web3TON Pacific";
        $configIndex = 9;
        $itemVideo = "web3ton-pacific.mp4";
        $itemPoster = "web3ton-pacific.jpg";
        break;
    default:
        $buyNftName = "Web3TON";
        break;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <?
    if($_SERVER['HTTP_HOST'] == "web3ton.pro")
    {
        ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Mulish:wght@400;600;700;900&display=swap" rel="stylesheet">
        <?
    }
    else
    {
        ?>
        <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="/css/fonts.css" type="text/css">
        <?
    }
    ?>
    <link rel="stylesheet" href="/template.css?v=<?=$timestamp?>" type="text/css">
    <link rel="stylesheet" href="/css/template-info.css?v=<?=$timestamp?>" type="text/css">
    <link rel="stylesheet" href="/css/template-rarity.css?v=<?=$timestamp?>" type="text/css">

    <?
    if($buyNftName == "Web3TON")
    {
        ?>
        <title>Web3TON shop. Web3TON NFT project.</title>
        <?
    }
    else
    {
        ?>
        <title>Buy NFT <?=$buyNftName?>. Web3TON shop. Web3TON NFT project.</title>
        <?
    }
    ?>
    <?
    if($_SERVER['HTTP_HOST'] == "web3ton.pro")
    {
        ?>
        <meta name="description" content="Web3TON NFT shop. Buy NFT <?=$buyNftName?>." />
        <meta property="og:locale" content="en_US" />
        <meta property="og:locale:alternate" content="en_US" />
        <meta property="og:url" content="<?=$currentPage?>/" />
        <meta property="og:see_also" content="https://web3ton.pro/"/>
        <?
        if($buyNftName == "Web3TON")
        {
            ?>
            <meta property="og:title" content="Web3TON shop. Web3TON NFT project."/>
            <?
        }
        else
        {
            ?>
            <meta property="og:title" content="Buy NFT <?=$buyNftName?>. Web3TON shop. Web3TON NFT project."/>
            <?
        }
        ?>
        <meta property="og:type" content="article"/>
        <meta property="og:image" content="<?=$metaPic?>"/>
        <meta property="og:site_name" content="Web3TON NFT project"/>
        <meta property="og:description" content="Web3TON NFT shop. Buy NFT <?=$buyNftName?>."/>
        <?
        if($buyNftName == "Web3TON")
        {
            ?>
            <meta name="twitter:title" content="Web3TON shop. Web3TON NFT project."/>
            <?
        }
        else
        {
            ?>
            <meta name="twitter:title" content="Buy NFT <?=$buyNftName?>. Web3TON shop. Web3TON NFT project."/>
            <?
        }
        ?>
        <meta name="twitter:description" content="Web3TON NFT shop. Buy NFT <?=$buyNftName?>."/>
        <meta name="twitter:image:src" content="<?=$metaPic?>"/>
        <?
    }
    ?>
</head>
<body>
<div id="main-bg"></div>
<nav id="header-nav" class="navbar navbar-expand-xl navbar-custom sticky sticky-dark">
    <div class="container">
        <a id="header-logo" class="logo" href="/">
            <picture>
                <source srcset="/images/header-logo.webp" type="image/webp">
                <source srcset="/images/header-logo.png" type="image/png">
                <img src="/images/header-logo.png" alt="Web3TON лого">
            </picture>
            <span>Web3TON</span>
        </a>

        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav ml-auto navbar-center" id="main-nav">
                <li class="nav-item">
                    <a href="/index.php#about" class="nav-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="/index.php#civilizations" class="nav-link rarity-explorer">Civilizations</a>
                </li>
                <li class="nav-item">
                    <a href="/rarity-explorer/" class="nav-link rarity-explorer">Rarity explorer</a>
                </li>
                <li class="nav-item">
                    <a href="/index.php#roadmap" class="nav-link rarity-explorer">Roadmap</a>
                </li>
                <li class="nav-item">
                    <a href="/marga/" class="nav-link rarity-explorer">Token</a>
                </li>
                <li class="nav-item">
                    <?
				$bnft = "";
				if(($_GET["nft"] == "3nixie-antarctic" ||
					$_GET["nft"] == "3nixie-arctic" ||
					$_GET["nft"] == "3nixie-indian" ||
					$_GET["nft"] == "3nixie-atlantic" ||
					$_GET["nft"] == "3nixie-pacific" ||
					$_GET["nft"] == "web3ton-antarctic" ||
					$_GET["nft"] == "web3ton-arctic" ||
					$_GET["nft"] == "web3ton-indian" ||
					$_GET["nft"] == "web3ton-atlantic" ||
					$_GET["nft"] == "web3ton-pacific"))
					{
						$bnft = $_GET["nft"];
					}
				?>
                    <a href="/ru/buy/<?if($bnft){echo "?nft=" . $bnft;}?>" class="nav-link lang-toggle"><i class="icon-globe"></i> Rus</a>
                </li>
            </ul>
        </div>

        <div id="user-icon-wrapper">
            <i id="user-icon" class="icon-user<?if(isset($_COOKIE["wallet"]) && $_COOKIE["wallet"] !== ""){echo " user-unlocked";}?>"></i>
            <?
            if(isset($_COOKIE["wallet"]) && $_COOKIE["wallet"] !== "")
            {
                $walletFull = $_COOKIE["wallet"];
                $walletPart1 = substr($walletFull, 0, 4);
                $walletPart2 = substr($walletFull, -4, 4);
                $walletAbbr = $walletPart1 . "..." . $walletPart2;
                ?>
                <div id="user-controls-wrapper">
                    <div id="user-controls-container">
                        <div class="uc-item uc-wallet">
                            <i class="icon-wallet"></i>
                            <span><?=$walletAbbr?></span>
                        </div>
                        <div class="uc-item uc-portfolio">
                            <a href="/user/">
                                <i class="icon-briefcase"></i>
                                <span>Portfolio</span>
                            </a>
                        </div>
                        <div class="uc-item uc-disconnect">
                            <i class="icon-logout"></i>
                            <span>Logout</span>
                        </div>
                    </div>
                </div>
                <?
            }
            ?>
        </div>

        <button id="mobile-menu-toggler" class="navbar-toggler first-button" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <div class="animated-icon1"><span></span><span></span><span></span></div>
        </button>
    </div>
</nav>
<?
// если есть GET параметр с типом NFT для покупки
// отдаем страницу покупки
// в противном случае отдаем страницу с выбором типа NFT для покупки

//echo $_GET["nft"];

// допустимые варианты GET параметра
// 3nixie-antarctic 3nixie-arctic 3nixie-indian 3nixie-atlantic 3nixie-pacific
// web3ton-antarctic web3ton-arctic web3ton-indian web3ton-atlantic

// также проверяем, что задан кошелек покупателя
$buyerWallet = false;
if($_COOKIE["wallet"])
{
    $wallet = $_COOKIE["wallet"];
    $textLen = mb_strlen($wallet);
    if($textLen == 48)
    {
        $startLetters = substr($wallet, 0, 2);
        if ($startLetters == "EQ")
        {
            $buyerWallet = true;
        }
    }
}

if(($_GET["nft"] == "3nixie-antarctic" ||
        $_GET["nft"] == "3nixie-arctic" ||
        $_GET["nft"] == "3nixie-indian" ||
        $_GET["nft"] == "3nixie-atlantic" ||
        $_GET["nft"] == "3nixie-pacific" ||
        $_GET["nft"] == "web3ton-antarctic" ||
        $_GET["nft"] == "web3ton-arctic" ||
        $_GET["nft"] == "web3ton-indian" ||
        $_GET["nft"] == "web3ton-atlantic" ||
        $_GET["nft"] == "web3ton-pacific") && $buyerWallet)
{
    // готовим страницу покупки определенного вида NFT
    $nftType = $_GET["nft"];

    $nftName = $configArray[$configIndex]["NFT_NAME"];
    $nftPrice = $configArray[$configIndex]["NFT_PRICE"];

    // формируем токен для покупки
    $token = getToken(20);

    // формируем ссылки для покупки
    $payLink1 = "ton://transfer/" . $destWallet . "?amount=" . $nftPrice . "000000000&text=" . $token;
    $payLink2 = "https://app.tonkeeper.com/transfer/" . $destWallet . "?amount=" . $nftPrice . "000000000&text=" . $token;

    $payLinkTonkeeper = "https://app.tonkeeper.com/transfer/" . $destWallet . "?amount=" . $nftPrice . "000000000&text=" . $token;
    $payLinkTonhub = "https://tonhub.com/transfer/" . $destWallet . "?amount=" . $nftPrice . "000000000&text=" . $token;

    // генерируем изображение с QR кодом для оплаты
    require_once $_SERVER["DOCUMENT_ROOT"] . '/include/phpqrcode/qrlib.php';

    QRcode::png($payLink1, $_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $token . ".png", 'H', 6, 2);

    // меняем цвет фона
    $im = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $token . ".png");
    $width = imagesx($im);
    $height = imagesy($im);

    $bg_color = imageColorAllocate($im, 0, 0, 0);
    imagecolortransparent ($im, $bg_color);

    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $color = imagecolorat($im, $x, $y);
            if ($color == 0) {
                imageSetPixel($im, $x, $y, $bg_color);
            }
        }
    }
    imagepng($im, $_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $token . ".png");
    imagedestroy($im);

    // меняем цвет пикселей
    $im = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $token . ".png");
    $width = imagesx($im);
    $height = imagesy($im);

    /* Цвет в RGB */
    $fg_color = imageColorAllocate($im, 255, 255, 255);

    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $color = imagecolorat($im, $x, $y);
            if ($color == 1) {
                imageSetPixel($im, $x, $y, $fg_color);
            }
        }
    }
    imagepng($im, $_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $token . ".png");
    imagedestroy($im);


    // добавляем логотип
    $stamp = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $token . ".png");
    $im = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/images/qr/qrlogo.png");

    $sx = imagesx($stamp);
    $sy = imagesy($stamp);
    $ix = imagesx($im);
    $iy = imagesy($im);

    $marge_right  = ($sx / 2) - ($ix / 2);
    $marge_bottom = ($sy / 2) - ($iy / 2);

    imagecopy($stamp, $im, $marge_right, $marge_bottom, 0, 0, $ix, $iy);

    imagepng($stamp, $_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $token . ".png");
    imagedestroy($stamp);
    imagedestroy($im);

    // отдаем страницу покупки
    $src = "/images/qr/" . $token . ".png";
    ?>
    <section id="buy-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12 breadcrumbs-wrapper">
                    <a href="/">Web3TON</a><span>&rarr;</span><a href="/buy/">Buy NFT</a><span>&rarr;</span><strong><?=$nftName?></strong>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p class="re-sup-header gradient-text-alt"><span id="buy-main-header-1">Buy NFT</span></p>
                    <h2 class="gradient-text"><span id="buy-main-header-2"><?=$nftName?> random</span></h2>
                    <div id="buy-process-description" class="portfolio-description" style="margin-top: -30px; margin-bottom: 30px;">
                        <div class="error-icon" style="width: 30px;"></div>
                        <div class="description-text">
                            <p>After completing the transaction, do not close the page, wait for the purchase process to complete and find out which NFT you got.</p>
                        </div>
                    </div>
                    <div id="buy-image-container">
                        <video poster="/buy/assets/<?=$itemPoster;?>" preload="auto" width="500" height="500" autoplay loop muted>
                            <source src="/buy/assets/<?=$itemVideo;?>" type="video/mp4">
                        </video>
                    </div>
                    <div id="buy-data-container">
                        <div id="buy-price">
                            <span>Price:</span>
                            <strong><?=$nftPrice?> <span>TON</span></strong>
                        </div>
                        <div id="buy-qr">
                            <div class="buy-step-description">
                                Pay by QR code
                            </div>
                            <img src="<?=$src?>" alt="QR код для оплаты" class="img-fluid">
                        </div>
                        <div id="buy-button-link" class="buy-nft-item-block">
                            <div class="buy-nft-item-container">
                                <div class="buy-step-description">
                                    Pay with Tonkeeper
                                </div>
                                <a href="<?=$payLinkTonkeeper?>" class="btn btn-success rew-btn"><i class="icon-x-tonkeeper"></i> Pay</a>
                            </div>
                            <div class="buy-nft-item-container">
                                <div class="buy-step-description">
                                    Pay with Tonhub
                                </div>
                                <a href="<?=$payLinkTonhub?>" class="btn btn-success rew-btn"><i class="icon-x-tonhub"></i> Pay</a>
                            </div>


                        </div>
                        <div id="buy-form">
                            <div class="buy-step-description">
                                Pay by details
                            </div>
                            <div class="buy-form-row-1">
                                <div class="buy-form-col">
                                    <div class="form-group">
                                        <label for="dest-address">Wallet address</label>
                                        <div id="dest-address-container" class="field-container">
                                            <input type="text" class="form-control" id="dest-address" value="<?=$destWallet?>" readonly>
                                            <div id="dest-address-copy" class="icon-copy" data-clipboard-action="copy" data-clipboard-target="#dest-address"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="buy-form-row-2">
                                <div class="buy-form-col">
                                    <div class="form-group">
                                        <label for="buy-amount">Amount</label>
                                        <div id="buy-amount-container" class="field-container">
                                            <input type="text" class="form-control" id="buy-amount" value="<?=$nftPrice?>" readonly>
                                            <div id="buy-amount-copy" class="icon-copy" data-clipboard-action="copy" data-clipboard-target="#buy-amount"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="buy-form-col">
                                    <div class="form-group">
                                        <label for="buy-comment">Comment</label>
                                        <div id="buy-comment-container" class="field-container">
                                            <input type="text" class="form-control" id="buy-comment" value="<?=$token?>" readonly>
                                            <div id="buy-comment-copy" class="icon-copy" data-clipboard-action="copy" data-clipboard-target="#buy-comment"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="buy-description">
                            <div id="error-message-container">
                                <div class="error-icon"></div>
                                <div class="error-text">
                                    <p>A comment on the payment is required. <br>Don't forget to paste a comment.</p>
                                </div>
                            </div>
                        </div>
                        <div id="buy-check">
                            <?/*<button id="buy-check-btn" class="btn btn-info">Проверить оплату</button>*/?>
                            <div id="acp-preloader2"></div>
                            <div class="acp-description2">
                                Waiting for a transaction
                            </div>
                        </div>
                    </div>
                    <div id="buy-result">
                        <div id="buy-result-image-wrapper">
                            <a id="buy-result-rr-link" href="/rarity-explorer/"><img id="buy-result-image" class="img-fluid" src="" alt="<?=$nftName?> random"></a>
                        </div>
                        <div class="re-nft-item-description">
                            <p><span>Item #</span><strong id="br-item-id"></strong></p>
                            <p><span>Rarity rank:</span><strong id="br-item-rrank"></strong></p>
                            <p><span>Rarity score:</span><strong id="br-item-rscore"></strong></p>
                            <p><span>Rarity scale:</span><strong id="br-item-rscale" class=""></strong></p>
                        </div>
                        <div id="buy-result-controls">
                            <div class="brc-item">
                                <a id="brc-rr-link" href="#" target="_blank">Open Rarity explorer</a>
                            </div>
                            <div class="brc-item">
                                <a href="/user/">Portfolio NFT Web3TON</a>
                            </div>
                            <div class="brc-item">
                                <a id="brc-buy-more" href="#">Buy more <?=$nftName?></a>
                            </div>
                            <div class="brc-back">
                                <a href="/buy/">← Go back to choice</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="purchase-data" data-buyer-wallet="<?=$wallet?>" data-nft-price="<?=$nftPrice?>" data-purchase-token="<?=$token?>">
    </section>
    <?
}
else
{
    // отдаем страницу с выбором типа NFT для покупки
    ?>
    <section id="rarity-explorer-main-wrapper" class="buy-router">
        <div class="container">
            <div class="row">
                <div class="col-12 breadcrumbs-wrapper">
                    <a href="/">Web3TON</a><span>&rarr;</span><strong>Buy NFT</strong>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p class="re-sup-header gradient-text-alt"><span>Buy NFT Web3TON</span></p>
                    <p class="text-center text-light-grey">Select the NFT type</p>
                    <?
                    if(!isset($_COOKIE["wallet"]) || $_COOKIE["wallet"] == "")
                    {
                        ?>
                        <div class="portfolio-description" style="margin-top: -30px; margin-bottom: 30px;">
                            <div class="error-icon" style="width: 30px;"></div>
                            <div class="description-text">
                                <p>To make purchases, you need to log in.</p>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                    <h2 class="gradient-text"><span>Web3TON</span></h2>

                    <div class="re-nft-type-list-row" style="justify-content: center;">
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=web3ton-antarctic">
                                <p class="re-list-item-img">
                                    <img src="/upload/content/web3ton_antarctic/pic/1110.jpg" alt="Web3TON Antarctic" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-blue re-list-item-name">
                                    <span>Web3TON Antarctic</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[5]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=web3ton-arctic">
                                <p class="re-list-item-img">
                                    <img src="/upload/content/web3ton_arctic/pic/730.jpg" alt="Web3TON Arctic" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-orange re-list-item-name">
                                    <span>Web3TON Arctic</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[6]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=web3ton-indian">
                                <p class="re-list-item-img">
                                    <img src="/upload/content/web3ton_indian/pic/154.jpg" alt="Web3TON Indian" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-green re-list-item-name">
                                    <span>Web3TON Indian</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[7]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=web3ton-atlantic">
                                <p class="re-list-item-img">
                                    <img src="/upload/content/web3ton_atlantic/pic/82.jpg" alt="Web3TON Atlantic" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-red re-list-item-name">
                                    <span>Web3TON Atlantic</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[8]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=web3ton-pacific">
                                <p class="re-list-item-img">
                                    <img src="/upload/content/web3ton_pacific/pic/16.jpg" alt="Web3TON Pacific" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-red re-list-item-name">
                                    <span>Web3TON Pacific</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[9]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                        <?/*<div class="re-nft-type-list-item">
                        <a href="#">
                            <p class="re-list-item-img">
                                <img src="https://web3ton.pro/rarity-explorer/img/pacific-soon.jpg" alt="Web3TON Pacific" title="coming soon" class="img-fluid">
                            </p>
                            <p class="h3 gradient-text-cream re-list-item-name">
                                <span>Web3TON Pacific</span>
                            </p>
                        </a>
                    </div>*/?>
                    </div>

                    <h2 class="gradient-text-alt2"><span>3Nixie</span></h2>

                    <div class="re-nft-type-list-row">
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=3nixie-antarctic">
                                <p class="re-list-item-img">
                                    <img src="/upload/content/3nixie_antarctic/pic/3245.jpg" alt="3Nixie Antarctic" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-blue re-list-item-name">
                                    <span>3Nixie Antarctic</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[0]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=3nixie-arctic">
                                <p class="re-list-item-img">
                                    <img src="/upload/content/3nixie_arctic/pic/592.jpg" alt="3Nixie Arctic" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-orange re-list-item-name">
                                    <span>3Nixie Arctic</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[1]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=3nixie-indian">
                                <p class="re-list-item-img">
                                    <img src="/upload/content/3nixie_indian/pic/617.jpg" alt="3Nixie Indian" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-green re-list-item-name">
                                    <span>3Nixie Indian</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[2]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=3nixie-atlantic">
                                <p class="re-list-item-img">
                                    <img src="/upload/content/3nixie_atlantic/pic/1359.jpg" alt="3Nixie Atlantic" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-red re-list-item-name">
                                    <span>3Nixie Atlantic</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[3]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                        <div class="re-nft-type-list-item">
                            <a class="buy-if-auth" href="/buy/?nft=3nixie-pacific">
                                <p class="re-list-item-img">
                                    <img src="/rarity-explorer/img/nixie-pacific.jpg" alt="3Nixie Pacific" class="img-fluid">
                                </p>
                                <p class="h3 gradient-text-cream re-list-item-name">
                                    <span>3Nixie Pacific</span>
                                </p>
                                <p class="h5 re-list-item-price">
                                    <span>Price:</span>
                                    <strong><?=$configArray[4]["NFT_PRICE"]?> TON</strong>
                                </p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?
}
?>
<footer id="footer-alt">
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-6 footer-left-col">
                <?
                if($_SERVER['HTTP_HOST'] == "web3ton.pro")
                {
                    $pbLink = "https://ton.org/";
                }
                else
                {
                    $pbLink = "http://foundation.ton/";
                }
                ?>
                <a href="<?=$pbLink?>" target="_blank" rel="noopener nofollow">
                    <span class="powered-wrapper">
                        <span>Powered by</span> <i class="ton-symbol"></i> <strong>TON blockchain</strong>
                    </span>
                </a>
            </div>
            <div class="col-12 col-sm-6 footer-right-col">
                <a href="https://tonlab.pro/" id="developedby-link" target="_blank"><img src="/images/developedby.svg" id="developedby" alt="developed by TONLab.Pro Inc."></a>
            </div>
        </div>
    </div>
</footer>

<div id="scroll-to-top"><i class="top-up"></i></div>

<?
if(!isset($_COOKIE["wallet"]) || $_COOKIE["wallet"] == "")
{
    // куки авторизации нет, добавляем pop-up авторизации

    function convNanoToDecimalTon($numNano)
    {
        $num = $numNano / 1000000000;
        return $num;
    }

    $amountNano = 1000000;
    $amount = convNanoToDecimalTon($amountNano);

    // генерируем токен для комментария к транзакции
    $tokenX = getToken(20);
    // формируем ссылку для оплаты
    $payLink = "ton://transfer/" . $destWallet . "?amount=" . $amountNano . "&text=" . $tokenX;
    //$payLinkTonkeeper = "https://app.tonkeeper.com/transfer/" . $destWallet . "?amount=" . $amountNano . "&text=" . $tokenX;
    //$payLinkTonhub = "https://tonhub.com/transfer/" . $destWallet . "?amount=" . $amountNano . "&text=" . $tokenX;

    // генерируем изображение с QR кодом для оплаты
    require_once $_SERVER["DOCUMENT_ROOT"] . '/include/phpqrcode/qrlib.php';
    QRcode::png($payLink, $_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $tokenX . ".png", 'H', 6, 2);

    // меняем цвет фона
    $im = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $tokenX . ".png");
    $width = imagesx($im);
    $height = imagesy($im);

    $bg_color = imageColorAllocate($im, 0, 0, 0);
    imagecolortransparent ($im, $bg_color);

    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $color = imagecolorat($im, $x, $y);
            if ($color == 0) {
                imageSetPixel($im, $x, $y, $bg_color);
            }
        }
    }
    imagepng($im, $_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $tokenX . ".png");
    imagedestroy($im);

    // меняем цвет пикселей
    $im = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $tokenX . ".png");
    $width = imagesx($im);
    $height = imagesy($im);

    /* Цвет в RGB */
    $fg_color = imageColorAllocate($im, 255, 255, 255);

    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $color = imagecolorat($im, $x, $y);
            if ($color == 1) {
                imageSetPixel($im, $x, $y, $fg_color);
            }
        }
    }
    imagepng($im, $_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $tokenX . ".png");
    imagedestroy($im);

    // добавляем логотип
    $stamp = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $tokenX . ".png");
    $im = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/images/qr/qrlogo.png");

    $sx = imagesx($stamp);
    $sy = imagesy($stamp);
    $ix = imagesx($im);
    $iy = imagesy($im);

    $marge_right  = ($sx / 2) - ($ix / 2);
    $marge_bottom = ($sy / 2) - ($iy / 2);

    imagecopy($stamp, $im, $marge_right, $marge_bottom, 0, 0, $ix, $iy);

    imagepng($stamp, $_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $tokenX . ".png");
    imagedestroy($stamp);
    imagedestroy($im);

    // scr на подготовленный qr код
    $src = "/images/qr/" . $tokenX . ".png";
    ?>
    <div class="modal fade" id="authorization" tabindex="-1" role="dialog" aria-labelledby="authorizationLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title gradient-text-alt" id="authorizationLabel"><span>Authorization</span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="icon-close"></i></button>
                </div>
                <div id="auth-select" class="modal-body">
                    <?
                    if($isMobile)
                    {
                        ?>
                        <div id="auth-select-tonkeeper" class="auth-select-item">
                            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0xNiAxM0wwIDdMMTYgMUwzMiA3TDE2IDEzWiIgZmlsbD0iIzQ1QUVGNSIvPgogICAgPHBhdGggb3BhY2l0eT0iMC42IiBkPSJNMTYgMTNMMzIgN0wxNiAzMVYxM1oiIGZpbGw9IiM0NUFFRjUiLz4KICAgIDxwYXRoIG9wYWNpdHk9IjAuOCIgZD0iTTE2IDEzTDAgN0wxNiAzMVYxM1oiIGZpbGw9IiM0NUFFRjUiLz4KPC9zdmc+Cg==">
                            <span>Tonkeeper</span>
                        </div>
                        <?
                    }
                    ?>
                    <div id="auth-select-transaction" class="auth-select-item">
                        <i class="icon-transaction"></i>
                        <span>Transaction</span>
                    </div>
                </div>
                <?
                if($isMobile)
                {
                    ?>
                    <div id="auth-tonkeeper" class="modal-body">
                        <div id="tonkeeper-connect-header">
                            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0xNiAxM0wwIDdMMTYgMUwzMiA3TDE2IDEzWiIgZmlsbD0iIzQ1QUVGNSIvPgogICAgPHBhdGggb3BhY2l0eT0iMC42IiBkPSJNMTYgMTNMMzIgN0wxNiAzMVYxM1oiIGZpbGw9IiM0NUFFRjUiLz4KICAgIDxwYXRoIG9wYWNpdHk9IjAuOCIgZD0iTTE2IDEzTDAgN0wxNiAzMVYxM1oiIGZpbGw9IiM0NUFFRjUiLz4KPC9zdmc+Cg==">
                            <span>Tonkeeper</span>
                        </div>
                        <div id="tonkeeper-connect">
                            <a class="btn btn-info" href="https://app.tonkeeper.com/ton-login/web3ton.pro/authRequest">Connect wallet</a>
                        </div>
                        <div class="auth-goback">
                            <span>← Back</span>
                        </div>
                    </div>
                    <?
                }
                ?>
                <div id="auth-transaction" class="modal-body">
                    <p class="auth-modal-description">Authorization by transaction.<br>
                        Send 0,001 TON to address.<br><span class="warn-text"><i class="error-icon-sm"></i>A comment on the transaction is required.</span></p>
                    <div id="buy-qr">
                        <div class="buy-step-description">
                            Pay by QR code
                            <p style="font-size: 12px;">(Scan with Tonkeeper, Tonhub)</p>
                        </div>
                        <img src="<?=$src?>" alt="QR код для оплаты" class="img-fluid">
                    </div>
                    <div id="buy-button-link">
                        <div class="buy-step-description">
                            Pay with app
                        </div>
                        <a href="<?=$payLink?>" class="btn btn-success rew-btn">Оплатить</a>
                    </div>
                    <div id="buy-form">
                        <div class="buy-step-description">
                            Pay by details
                        </div>
                        <div class="buy-form-row-1">
                            <div class="buy-form-col">
                                <div class="form-group">
                                    <label for="dest-address">Wallet address</label>
                                    <div id="dest-address-container2" class="field-container">
                                        <input type="text" class="form-control" id="dest-address2" value="<?=$destWallet?>" readonly>
                                        <div id="dest-address-copy2" class="icon-copy" data-clipboard-action="copy" data-clipboard-target="#dest-address2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="buy-form-row-2">
                            <div class="buy-form-col">
                                <div class="form-group">
                                    <label for="buy-amount">Amount</label>
                                    <div id="buy-amount-container2" class="field-container">
                                        <input type="text" class="form-control" id="buy-amount2" value="<?=$amount?>" readonly>
                                        <div id="buy-amount-copy2" class="icon-copy" data-clipboard-action="copy" data-clipboard-target="#buy-amount2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="buy-form-col">
                                <div class="form-group">
                                    <label for="buy-comment">Comment</label>
                                    <div id="buy-comment-container2" class="field-container">
                                        <input type="text" class="form-control" id="buy-comment2" value="<?=$tokenX?>" readonly>
                                        <div id="buy-comment-copy2" class="icon-copy" data-clipboard-action="copy" data-clipboard-target="#buy-comment2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="buy-check">
                        <div id="acp-preloader2"></div>
                        <div class="acp-description2">
                            Waiting for a transaction
                        </div>
                    </div>
                    <?/*<div id="auth-check-progress">
                        <div class="acp-inner">
                            <div id="acp-preloader"></div>
                            <div class="acp-description">
                                Идет проверка транзакции, пожалуйста, подождите.
                            </div>
                        </div>
                    </div>*/?>
                    <div id="auth-check-success">
                        <div class="acp-inner">
                            <div id="acp-check"></div>
                            <div class="acp-description">
                                Authorization successful!
                            </div>
                        </div>
                    </div>
                    <div class="auth-goback">
                        <span>← Back</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?
}
?>


<script src="/js/jquery-3.2.1.min.js"></script>
<?
if($_SERVER['HTTP_HOST'] == "web3ton.pro")
{
    ?>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<?
}
else
{
?>
    <script src="/js/popper.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <?
}
?>
<script src="/js/modernizr-custom.js"></script>
<script src="/js/aos.js"></script>
<script src="/js/clipboard.min.js"></script>
<script src="/js/template-buy.js?v=15"></script>
<script>
    $(function()
    {
        let isMobile = "<?=$_SESSION['device']?>";
        console.log(isMobile);

        let uWallet = "<?=$_COOKIE["wallet"]?>";
        console.log(uWallet);
    });
</script>
</body>
</html>
