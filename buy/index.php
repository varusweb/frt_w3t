<?
// определение мобильного устройства
require_once($_SERVER["DOCUMENT_ROOT"] . "/include/checkmobile.php");

// получаем входящие параметры
$qty = $_REQUEST["quantity"] * 1;

// проверяем, что задан кошелек покупателя
// если кошель не задан - кидать на главную и открывать попап авторизации
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
//  кошелька нет, отправляем [авторизоваться]/[на хуй]
// раскоментить на проде
/*if(!$buyerWallet)
{
    // $_SERVER['REQUEST_SCHEME'] // http-https
    // $_SERVER['HTTP_HOST'] // example.com
    // $_SERVER['SERVER_NAME'] // example.com
    $goTo = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/#auth";
    header("Location: " . $goTo);
    die(); // умри, сука!
}*/

// получаем стоимость одного билета
$ticketCost = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/ticketcost.txt');
$ticketCost = $ticketCost * 1;

// сумма покупки
$buyAmount = $ticketCost * $qty;

// адрес кошелька для приема средств
$destWallet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/walletaddr.txt');

// получаем номер предстоящего тиража
$cycleNum = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/cycle.txt');
// добиваем до 5 значной предшествующими нулями, блеать
$cycleStrLength = strlen($cycleNum);
$curCycleStr = $cycleNum;
$nullNeeded = 5 - $cycleStrLength;
if($nullNeeded > 0)
{
    for ($n = $nullNeeded; $n > 0; $n--)
    {
        $curCycleStr = "0" . $curCycleStr;
    }
}

$now = time();

// генерируем QR код и ссылки на оплату
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
function convNanoToDecimalTon($numNano)
{
    $num = $numNano / 1000000000;
    return $num;
}

/*$amountNano = 10000000000;
$amount = convNanoToDecimalTon($amountNano);*/

// генерируем токен для комментария к транзакции
$token = getToken(20);
// формируем ссылку для оплаты
//$payLink = "ton://transfer/" . $destWallet . "?amount=" . $amountNano . "&text=" . $token;

// формируем ссылки для покупки
$payLink1 = "ton://transfer/" . $destWallet . "?amount=" . $buyAmount . "000000000&text=" . $token;
$payLink2 = "https://app.tonkeeper.com/transfer/" . $destWallet . "?amount=" . $buyAmount . "000000000&text=" . $token;

$payLinkTonkeeper = "https://app.tonkeeper.com/transfer/" . $destWallet . "?amount=" . $buyAmount . "000000000&text=" . $token;
$payLinkTonhub = "https://tonhub.com/transfer/" . $destWallet . "?amount=" . $buyAmount . "000000000&text=" . $token;

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

// scr на подготовленный qr код
$src = "/images/qr/" . $token . ".png";
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="/css/bootstrap.min.css?v=<?=$now?>" type="text/css">
    <link rel="stylesheet" href="/css/lightslider.min.css" type="text/css">
    <link rel="stylesheet" href="/css/fonts.css" type="text/css">
    <link rel="stylesheet" href="/css/style.css?v=<?=$now?>" type="text/css">

    <title>Купить билет Fortuna TON</title>
</head>
<body>

<div id="main-wrapper">
    <header id="header">

        <nav id="header-nav" class="navbar navbar-expand-xl navbar-custom sticky sticky-dark">
            <div class="container">
                <div class="row">
                    <div class="col-12">

                        <div id="header-wrapper">

                            <div id="header-menu">
                                <div class="collapse navbar-collapse" id="navbarCollapse">
                                    <ul class="navbar-nav navbar-center" id="main-nav">
                                        <li class="nav-item">
                                            <a href="/#about" class="nav-link external">Что такое fortuna</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="/#how-it-works" class="nav-link external">Как работает</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="/#hall-of-fame" class="nav-link external">Победители</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div id="header-logo">
                                <a id="header-logo-link" href="<?=$_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST']?>">
                                    <picture>
                                        <source srcset="/images/fortuna-logo-1.webp" type="image/webp">
                                        <source srcset="/images/fortuna-logo-1.png" type="image/png">
                                        <img src="/images/fortuna-logo-1.png" alt="Fortunav logo">
                                    </picture>
                                </a>
                            </div>

                            <div id="header-controls">
                                <div id="header-controls-inner">
                                    <div id="auth-wrapper" class="icon-user-auth-wrapper<?if(isset($_COOKIE["wallet"]) && $_COOKIE["wallet"] !== ""){echo " user-unlocked";}?>">
                                        <i class="icon-auth"></i>
                                        <?
                                        if(isset($_COOKIE["wallet"]) && $_COOKIE["wallet"] !== "")
                                        {
                                            $walletFull = $_COOKIE["wallet"];
                                            $walletPart1 = substr($walletFull, 0, 2);
                                            $walletPart2 = substr($walletFull, -4, 4);
                                            $walletAbbr = $walletPart1 . "..." . $walletPart2;
                                            ?>
                                            <span class="aw-sm"><?=$walletAbbr?></span>
                                            <?
                                        }
                                        else
                                        {
                                            ?>
                                            <span>Войти</span>
                                            <?
                                        }
                                        ?>

                                    </div>
                                    <div class="navbar-btn-wrapper">
                                        <button id="header-buy-btn" class="btn btn-info buy-ticket"><i class="ico ico-cart"></i> Купить<span> билет</span></button>
                                    </div>

                                    <?
                                    /* авторизованным */
                                    ?>
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
                                                <?/*<div class="uc-item uc-wallet">
                                                    <i class="icon-wallet"></i>
                                                    <span><?=$walletAbbr?></span>
                                                </div>*/?>
                                                <div class="uc-item uc-portfolio">
                                                    <a href="/personal/">
                                                        <i class="icon-briefcase"></i>
                                                        <span>Личный кабинет</span>
                                                    </a>
                                                </div>
                                                <div class="uc-item uc-disconnect">
                                                    <i class="icon-logout"></i>
                                                    <span>Выйти</span>
                                                </div>
                                            </div>
                                        </div>
                                        <?
                                    }
                                    ?>


                                    <button id="mobile-menu-toggler" class="navbar-toggler first-button" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                                        <div class="animated-icon1"><span></span><span></span><span></span></div>
                                    </button>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </nav>
    </header>





    <div id="footer-block-wrapper">
        <div id="footer-block-container" class="standalone-block">

            <section id="place-an-order" class="section">
                <div class="container">
                    <div class="row">
                        <div id="buy-page-block-wrapper" class="col-12">

                            <p class="re-sup-header"><span id="buy-main-header-1">Купить билет</span></p>
                            <h2 class="text-gradient-gold-super-vyebon"><span id="buy-main-header-2">FORTUNA</span></h2>
                            <div id="buy-process-description" class="portfolio-description">
                                <div class="error-icon" style="width: 30px; min-width: 30px;"></div>
                                <div class="description-text">
                                    <p>После совершения транзакции не закрывайте страницу, дождитесь завершения процесса покупки.</p>
                                </div>
                            </div>


                            <div id="buy-data-container">
                                <div id="bpb-img" class="buy-page-block">
                                    <? // картинка билета ?>
                                    <picture>
                                        <source srcset="/images/ticket.webp" type="image/webp">
                                        <source srcset="/images/ticket.png" type="image/png">
                                        <img src="/images/ticket.png" alt="Fortuna ticket">
                                    </picture>
                                </div>
                                <div class="buy-page-block dfrc">
                                    <? // цена билета ?>
                                    <p class="mbd-label">Цена билета:</p>
                                    <p class="mbd-value text-gradient-gold-super-vyebon"><span><?=$ticketCost?> TON</span></p>
                                </div>
                                <div class="buy-page-block dfrc">
                                    <? // количество билетов, кнопка зменить ?>
                                    <p class="mbd-label">Билетов:</p>
                                    <span class="field-wrapper"><input type="text" id="ticket-quantity2" class="qc-text" value="<?=$qty?>" disabled="disabled"></span>
                                    <button class="btn btn-info change-order">Изменить</button>
                                </div>
                                <div class="buy-page-block dfrc">
                                    <? // сумма покупки ?>
                                    <p class="mbd-label">Сумма:</p>
                                    <p class="mbd-value text-gradient-gold-super-vyebon"><span><strong><?=$buyAmount?></strong> TON</span></p>
                                </div>
                                <div class="buy-page-block dfcc">
                                    <? // Оплата по QR-коду ?>
                                    <div class="bpb-block-label mb-15">
                                        Оплата по QR-коду
                                    </div>
                                    <img src="<?=$src?>" alt="QR код для оплаты" class="img-fluid">
                                </div>
                                <div class="buy-page-block dfcc">
                                    <? // Оплата в приложении ?>
                                    <div class="bpb-block-label mb-15">
                                        Оплата в приложении
                                    </div>
                                    <a href="<?=$payLink1?>" class="btn btn-info rew-btn">Оплатить</a>
                                </div>
                                <div class="buy-page-block dfcc">
                                    <? // Оплата по реквизитам ?>

                                    <div class="bpb-block-label mb-15">
                                        Оплата по реквизитам
                                    </div>
                                    <div class="buy-form-row-1">
                                        <div class="buy-form-col">
                                            <div class="form-group">
                                                <label for="dest-address">Адрес кошелька</label>
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
                                                <label for="buy-amount">Сумма</label>
                                                <div id="buy-amount-container" class="field-container">
                                                    <input type="text" class="form-control" id="buy-amount" value="<?=$buyAmount?>" readonly>
                                                    <div id="buy-amount-copy" class="icon-copy" data-clipboard-action="copy" data-clipboard-target="#buy-amount"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="buy-form-col">
                                            <div class="form-group">
                                                <label for="buy-comment">Комментарий</label>
                                                <div id="buy-comment-container" class="field-container">
                                                    <input type="text" class="form-control" id="buy-comment" value="<?=$token?>" readonly>
                                                    <div id="buy-comment-copy" class="icon-copy" data-clipboard-action="copy" data-clipboard-target="#buy-comment"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="buy-page-block">
                                    <? // comment warning ?>
                                    <div id="error-message-container">
                                        <div class="error-icon"></div>
                                        <div class="error-text">
                                            <p>Комментарий к платежу обязателен. <br>Не забудьте указать комментарий.</p>
                                        </div>
                                    </div>
                                </div>
                                <div id="buy-check" class="buy-page-block dfcc">
                                    <? // Ожидание транзакции ?>
                                    <div id="acp-preloader2"></div>
                                    <div class="acp-description2">
                                        Ожидание транзакции
                                    </div>
                                </div>
                            </div>

                            <?
                            /*
                             * изменить отображение результата покупки
                             * */
                            ?>
                            <div id="buy-result">
                                <? /*
                                Получена оплата n TON
                                Куплено t билетов
                                дальше картинки с номераими купленных билетов
                                ссылка перехода в личный кабинет
                                ссылка на купить ещё
                                */ ?>

                                <div class="buy-page-block dfcc">
                                    <? // Получена оплата n TON ?>
                                    <p class="mbd-label">Получена оплата:</p>
                                    <p class="mbd-value text-gradient-gold-super-vyebon"><span><strong id="recivedTon">25</strong> TON</span></p>

                                    <div id="buy-process-description2" class="portfolio-description">
                                        <div class="error-icon" style="width: 30px; min-width: 30px;"></div>
                                        <div class="description-text">
                                            <p>Оплаченная сумма не совпадает с суммой заказа.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="buy-page-block dfcc">
                                    <? // Куплено t билетов ?>
                                    <p class="mbd-label">Вы приобрели:</p>
                                    <p class="mbd-value text-gradient-gold-super-vyebon"><span><strong id="soldTickets">5</strong> билетов</span></p>
                                </div>
                                <div id="buy-page-imgs" class="buy-page-block dfcc">
                                    <? // картинки с номераими купленных билетов ?>
                                    <p class="text-gradient-another-one">
                                        <span>Тираж №<?=$curCycleStr?></span>
                                    </p>

                                    <div id="personal-tickets-container">

                                    </div>

                                </div>

                                <div class="buy-page-block dfcc">
                                    <? // ссылки, куда двигать дальше ?>
                                    <p id="bres-link-purple"><span>Личный кабинет</span></p>
                                    <p id="bres-link-green"><span>Приобрести ещё билеты</span></p>
                                </div>

                            </div>

                            <div id="buy-breakdown">

                                <div class="buy-page-block dfrc">
                                    <p class="bb-error-header">Ошибка</p>
                                </div>
                                <div class="buy-page-block">
                                    <div id="error-message-container2">
                                        <div class="error-icon"></div>
                                        <div class="error-text">
                                            <p>Полученная сумма некорректна. <br>Средства возвращены, покупка отменена. <br>Попробуйте купить снова. Оплачивайте ровно ту сумму, которая указана на странице покупки.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="buy-page-block dfcc">
                                    <? // ссылки, куда двигать дальше ?>
                                    <p id="bres-link-green2"><span>Купить снова</span></p>
                                    <p id="bres-link-purple2"><span>Личный кабинет</span></p>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <input type="hidden" id="purchase-data" data-buyer-wallet="<?=$wallet?>" data-purchase-amount="<?=$buyAmount?>" data-purchase-token="<?=$token?>">

            </section>

            <footer id="footer">
                <div id="footer-container">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <div class="footer-logo">
                                    <picture>
                                        <source srcset="/images/fortuna-logo-1.webp" type="image/webp">
                                        <source srcset="/images/fortuna-logo-1.png" type="image/png">
                                        <img src="/images/fortuna-logo-1.png" alt="Fortunav logo">
                                    </picture>
                                </div>
                                <div class="footer-sign">
                                    <span>Created by tonlab.pro</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>

        </div>
    </div>


</div>



<div id="scroll-to-top"></div>

<div class="modal fade" id="buy" tabindex="-1" role="dialog" aria-labelledby="buyLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-gradient-gold-dark" id="authorizationLabel"><span>Получить шанс</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="icon-close"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-buy-description">
                    <p class="mbd-label">Цена билета:</p>
                    <p class="mbd-value text-gradient-gold-super-vyebon"><span><?=$ticketCost?> TON</span></p>
                </div>
                <div id="modal-buy-controls">
                    <div class="mbc-label">Количество билетов</div>
                    <div class="cli-qcounter">
                        <span class="qc-minus">&ndash;</span>
                        <span class="field-wrapper"><input type="text" id="ticket-quantity" class="qc-text" name="quantity" value="<?=$qty?>" data-cost="<?=$ticketCost?>"></span>
                        <span class="qc-plus">+</span>
                    </div>
                </div>
                <div class="modal-buy-description">
                    <p class="mbd-label">Сумма:</p>
                    <p class="mbd-value text-gradient-gold-super-vyebon"><span><strong id="mb-sum"><?=$buyAmount?></strong> TON</span></p>
                </div>
                <div id="modal-buy-place-order">
                    <button class="btn btn-info place-order">Продолжить</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/popper.min.js"></script>
<script src="/js/bootstrap.min.js"></script>

<script src="/js/modernizr-custom.js"></script>
<script src="/js/lightslider.min.js"></script>
<script src="/js/clipboard.min.js"></script>
<script src="/js/template.js?v=<?=$now?>"></script>
</body>
</html>
