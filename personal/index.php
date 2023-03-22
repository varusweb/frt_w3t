<?
// определение мобильного устройства
require_once($_SERVER["DOCUMENT_ROOT"] . "/include/checkmobile.php");

// загружаем языковые сообщения
require_once($_SERVER["DOCUMENT_ROOT"] . "/include/lang.ru.php");

// получаем время запуска следующего тиража
$launchTime = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/launch.txt');
$launchTime = $launchTime * 1;
$countDownFormat = date("F j Y H:i:s", $launchTime) . " GMT+0300";

// получаем стоимость одного билета
$ticketCost = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/ticketcost.txt');
$ticketCost = $ticketCost * 1;

// адрес кошелька для приема средств
$destWallet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/walletaddr.txt');

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
    exit;
}*/

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

// подключаемся к базе
require_once($_SERVER["DOCUMENT_ROOT"] . "/dbc.inc.php");

// определяем количество купленных билетов на текущий тираж
// $cycleNum - тираж; $wallet - кошелек клиента
//$sth = $dbh->prepare("SELECT * FROM `x_transactions` WHERE `received_message` = :received_message AND `processed` = :processed");
$sth = $dbh->prepare("SELECT * FROM `x_sales` WHERE `cycleNum` = :cycleNum AND `buyerWallet` = :buyerWallet");
$sth->execute(array('cycleNum' => $cycleNum, 'buyerWallet' => $wallet));
$array = $sth->fetchAll(PDO::FETCH_ASSOC);

/*echo "<pre>";
print_r($array);
echo "</pre>";
exit;*/

if($array[0])
{
    $clientTicketQuantity = count($array);
}
else
{
    $clientTicketQuantity = 0;
}
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

    <title><?=$MESS["PT_PERSONAL_ACCOUNT"]?> Fortuna TON</title>
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

            <section id="personal-center" class="section">
                <div class="container">
                    <div class="row">
                        <div id="pc-page-block-wrapper" class="col-12">

                            <p class="re-sup-header"><span id="buy-main-header-1"><?=$MESS["PT_PERSONAL_ACCOUNT"]?></span></p>
                            <h2 class="text-gradient-gold-super-vyebon"><span id="buy-main-header-2">FORTUNA</span></h2>

                            <? /* Здесь личный кабинет */ ?>
                            <? /*
                            адрес кошелька
                            номер тиража?
                            купленные билеты
                              */ ?>

                            <div id="personal-info-wrapper">
                                <div id="personal-info-wrapper-inner">
                                    <div class="piw-block">
                                        <div class="piw-label">
                                            <?=$MESS["PT_YOUR_WALLET"]?>
                                        </div>
                                        <div id="wallet-value" class="piw-text">
                                            <?=$wallet?>
                                        </div>
                                    </div>
                                    <div class="piw-block">
                                        <div class="piw-label">
                                            <?=$MESS["PT_CURRENT_CYCLE"]?>
                                        </div>
                                        <div class="piw-text">
                                            <?=$curCycleStr?>
                                        </div>
                                    </div>
                                    <div class="piw-block">
                                        <div class="piw-label">
                                            <?=$MESS["PT_TICKETS_BOUGHT"]?>
                                        </div>
                                        <div class="piw-text">
                                            <?=$clientTicketQuantity?>
                                            <?
                                            if(!$clientTicketQuantity)
                                            {
                                            ?>
                                                <span id="pib-buy">Купить</span>
                                            <?
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="piw-block">
                                        <div class="piw-label">
                                            <?=$MESS["PT_YOUR_TOTAL_WIN_TON"]?>
                                        </div>
                                        <div class="piw-text">
                                            <?/* доделать функцилнал получения общей суммы выигрыша или 0 */?>
                                            5235
                                        </div>
                                    </div>
                                    <div class="piw-block">
                                        <div class="piw-label">
                                            <?=$MESS["PT_YOUR_TOTAL_WIN_NFT"]?>
                                        </div>
                                        <div class="piw-text">
                                            <?/* доделать функцилнал получения общей суммы выигрыша или 0 */?>
                                            7
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?
                            if($array[0])
                            {
                            ?>
                            <div id="personal-tickets">
                                <p class="text-gradient-another-one">
                                    <span><?=$MESS["PT_YOUR_TICKETS_CYCLE"]?><?=$curCycleStr?></span>
                                </p>

                                <div id="personal-tickets-container">
                                    <?
                                    foreach($array as $item)
                                    {
                                    ?>
                                        <?/*<img src="/include/get-ticket.php?number=<?=$item["ticketNum"]?>" alt="Билет <?=$item["ticketNum"]?>">*/?>

                                        <img src="/images/tickets/<?=$item["ticketNum"]?>.jpg" alt="Билет <?=$item["ticketNum"]?>">
                                    <?
                                    }
                                    ?>
                                </div>
                            </div>
                            <?
                            }
                            ?>

                            <div id="personal-bottom-wrapper">

                                <div id="tp-countdown">

                                    <div class="tpc-wrapper">
                                        <p class="tpc-header2 text-gradient-gold-dark"><span><?=$MESS["PT_LAUNCH_IN"]?></span></p>
                                    </div>
                                    <div id="tp-countdown-container">

                                        <div id="tp-cdown" class="cdown">
                                            <div id="tp-countdown-day" class="countdown-number">
                                                <div class="countdown-time-wrapper">
                                                    <span class="days countdown-time"></span>
                                                </div>
                                                <strong><?=$MESS["PT_CDWN_DAYS"]?></strong>
                                            </div>
                                            <div class="countdown-separator">:</div>
                                            <div id="tp-countdown-hour" class="countdown-number">
                                                <div class="countdown-time-wrapper">
                                                    <span class="hours countdown-time"></span>
                                                </div>
                                                <strong><?=$MESS["PT_CDWN_HOURS"]?></strong>
                                            </div>
                                            <div class="countdown-separator">:</div>
                                            <div id="tp-countdown-minutes" class="countdown-number">
                                                <div class="countdown-time-wrapper">
                                                    <span class="minutes countdown-time"></span>
                                                </div>
                                                <strong><?=$MESS["PT_CDWN_MINUTES"]?></strong>
                                            </div>
                                            <div class="countdown-separator">:</div>
                                            <div class="countdown-number">
                                                <div class="countdown-time-wrapper">
                                                    <span class="seconds countdown-time"></span>
                                                </div>
                                                <strong><?=$MESS["PT_CDWN_SECONDS"]?></strong>
                                            </div>
                                        </div>

                                        <script>
                                            function getTimeRemainingTp(endtime) {
                                                var t = Date.parse(endtime) - Date.parse(new Date());
                                                var seconds = Math.floor((t / 1000) % 60);
                                                var minutes = Math.floor((t / 1000 / 60) % 60);
                                                var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
                                                var days = Math.floor(t / (1000 * 60 * 60 * 24));
                                                return {
                                                    'total': t,
                                                    'days': days,
                                                    'hours': hours,
                                                    'minutes': minutes,
                                                    'seconds': seconds
                                                };
                                            }

                                            function initializeClockTp(id, endtime) {
                                                var clock = document.getElementById(id);
                                                var daysSpan = clock.querySelector('.days');
                                                var hoursSpan = clock.querySelector('.hours');
                                                var minutesSpan = clock.querySelector('.minutes');
                                                var secondsSpan = clock.querySelector('.seconds');

                                                function updateClockTp() {
                                                    var t = getTimeRemainingTp(endtime);

                                                    //daysSpan.innerHTML = t.days;
                                                    daysSpan.innerHTML = ('0' + t.days).slice(-2);
                                                    hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
                                                    minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
                                                    secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

                                                    if (t.total <= 0) {
                                                        clearInterval(timeintervalTp);
                                                    }
                                                }

                                                updateClockTp();
                                                let timeintervalTp = setInterval(updateClockTp, 1000);
                                            }

                                            initializeClockTp('tp-cdown', '<?=$countDownFormat?>');
                                        </script>

                                    </div>

                                </div>

                                <div id="tp-buy-btn-wrapper">
                                    <div id="tp-buy-btn-container">
                                        <span><?=$MESS["PT_BUY_TICKET"]?></span>
                                    </div>
                                </div>

                            </div>


                        </div>
                    </div>
                </div>
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
                <h5 class="modal-title text-gradient-gold-dark" id="authorizationLabel"><span><?=$MESS["MODAL_BUY_HEADER"]?></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="icon-close"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-buy-description">
                    <p class="mbd-label"><?=$MESS["MODAL_BUY_TICKET_PRICE"]?></p>
                    <p class="mbd-value text-gradient-gold-super-vyebon"><span><?=$ticketCost?> TON</span></p>
                </div>
                <div id="modal-buy-controls">
                    <div class="mbc-label"><?=$MESS["MODAL_BUY_TICKET_QUANTITY"]?></div>
                    <div class="cli-qcounter">
                        <span class="qc-minus">&ndash;</span>
                        <span class="field-wrapper"><input type="text" id="ticket-quantity" class="qc-text" name="quantity" value="1" data-cost="<?=$ticketCost?>"></span>
                        <span class="qc-plus">+</span>
                    </div>
                </div>
                <div class="modal-buy-description">
                    <p class="mbd-label"><?=$MESS["MODAL_BUY_AMOUNT"]?></p>
                    <p class="mbd-value text-gradient-gold-super-vyebon"><span><strong id="mb-sum"><?=$ticketCost?></strong> TON</span></p>
                </div>
                <div id="modal-buy-place-order">
                    <button class="btn btn-info place-order"><?=$MESS["MODAL_BUY_BUY_LABEL"]?></button>
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