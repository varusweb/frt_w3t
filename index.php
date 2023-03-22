<?
// определение мобильного устройства
require_once($_SERVER["DOCUMENT_ROOT"] . "/include/checkmobile.php");

// получаем время запуска следующего тиража
$launchTime = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/launch.txt');
$launchTime = $launchTime * 1;
$countDownFormat = date("F j Y H:i:s", $launchTime) . " GMT+0300";

// получаем стоимость одного билета
$ticketCost = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/ticketcost.txt');
$ticketCost = $ticketCost * 1;

// получаем номер предстоящего тиража (текущего)
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

// подключаемся к базе
require_once($_SERVER["DOCUMENT_ROOT"] . "/dbc.inc.php");

// получаем количество участников текущего тиража (равно количеству проданных билетов)
$bidders = 0;
$sth = $dbh->prepare("SELECT `id`, `cycleNum`, `activeTickets` FROM `x_cycle_stat` WHERE `cycleNum` = :cycleNum");
$sth->execute(array('cycleNum' => $cycleNum));
$array = $sth->fetch(PDO::FETCH_ASSOC);

if($array["activeTickets"])
{
    $bidders = $array["activeTickets"] * 1;
}

// адрес кошелька для приема средств
$destWallet = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/walletaddr.txt');

$now = time();

function shortWallet($wallet)
{
    // функция преобразования адреса кошелька в короткий (маска)
    $startStr = substr($wallet, 0, 3);
    $finishStr = substr($wallet, -3);
    $readyStr = $startStr . "...." . $finishStr;
    return $readyStr;
}

$tst1 = shortWallet($destWallet);

// флаг открытия отображения статистики по предыдущим тиражам
$lastStatOpen = 1;

// флаг открытия отображения статистики по текущему тиражу
$currentStatOpen = 1;

$statClosedShort = "soon";
$statClosedFull = "data coming soon";
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

    <link rel="stylesheet" href="/css/bootstrap.min.css?v=<?=$now?>" type="text/css">
    <link rel="stylesheet" href="/css/lightslider.min.css" type="text/css">
    <link rel="stylesheet" href="/css/fonts.css" type="text/css">
    <link rel="stylesheet" href="/css/style.css?v=<?=$now?>" type="text/css">

    <title>Fortuna TON</title>
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
                                            <a href="#about" class="nav-link">Что такое fortuna</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#how-it-works" class="nav-link">Как работает</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#hall-of-fame" class="nav-link">Победители</a>
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

    <section id="hero" class="section">
        <div class="container">
            <div class="row">
                <div id="hero-wrapper" class="col-12">
                    <div id="hero-flag-left"></div>
                    <div id="hero-flag-right"></div>

                    <div id="hero-logo">
                        <picture>
                            <source srcset="/images/fortuna-logo-full.webp" type="image/webp">
                            <source srcset="/images/fortuna-logo-full.png" type="image/png">
                            <img src="/images/fortuna-logo-full.png" alt="Fortuna" id="hero-logo-img">
                        </picture>
                    </div>

                    <div id="hero-countdown">
                        <div class="hc-wrapper">
                            <p class="hc-header text-gradient-gold-dark"><span>Следующий розыгрыш через:</span></p>
                        </div>
                        <div id="countdown-container">

                            <div id="cdown" class="cdown">
                                <div id="countdown-day" class="countdown-number">
                                    <div class="countdown-time-wrapper">
                                        <span class="days countdown-time"></span>
                                    </div>
                                    <strong>дней</strong>
                                </div>
                                <div class="countdown-separator">:</div>
                                <div id="countdown-hour" class="countdown-number">
                                    <div class="countdown-time-wrapper">
                                        <span class="hours countdown-time"></span>
                                    </div>
                                    <strong>часов</strong>
                                </div>
                                <div class="countdown-separator">:</div>
                                <div id="countdown-minutes" class="countdown-number">
                                    <div class="countdown-time-wrapper">
                                        <span class="minutes countdown-time"></span>
                                    </div>
                                    <strong>минут</strong>
                                </div>
                                <div class="countdown-separator">:</div>
                                <div class="countdown-number">
                                    <div class="countdown-time-wrapper">
                                        <span class="seconds countdown-time"></span>
                                    </div>
                                    <strong>секунд</strong>
                                </div>
                            </div>

                            <script>
                                function getTimeRemaining(endtime) {
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

                                function initializeClock(id, endtime) {
                                    var clock = document.getElementById(id);
                                    var daysSpan = clock.querySelector('.days');
                                    var hoursSpan = clock.querySelector('.hours');
                                    var minutesSpan = clock.querySelector('.minutes');
                                    var secondsSpan = clock.querySelector('.seconds');

                                    function updateClock() {
                                        var t = getTimeRemaining(endtime);

                                        //daysSpan.innerHTML = t.days;
                                        daysSpan.innerHTML = ('0' + t.days).slice(-2);
                                        hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
                                        minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
                                        secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

                                        if (t.total <= 0) {
                                            clearInterval(timeinterval);
                                        }
                                    }

                                    updateClock();
                                    let timeinterval = setInterval(updateClock, 1000);
                                }

                                initializeClock('cdown', '<?=$countDownFormat?>');
                            </script>

                        </div>
                    </div>

                    <div id="hero-buy-btn-wrapper">
                        <div id="hero-buy-btn-container">
                            <span>Купить билет</span>
                        </div>
                    </div>

                    <div id="hero-prize-pool">
                        <div id="hero-prize-pool-container">
                            <div class="hpp-header">
                                <p class="text-gradient-gold-vyebon"><span>Призовой фонд:</span></p>
                            </div>
                            <div class="hpp-amount-ton">
                                <p class="text-gradient-gold-super-vyebon">
                                    <?
                                    if($currentStatOpen)
                                    {
                                        ?>
                                        <span>50.000 TON</span>
                                        <?
                                    }
                                    else
                                    {
                                        ?>
                                        <span><?=$statClosedShort?></span>
                                        <?
                                    }
                                    ?>
                                </p>
                                <p class="text-shadow-hack">
                                    <?
                                    if($currentStatOpen)
                                    {
                                        ?>
                                        <span>50.000 TON</span>
                                        <?
                                    }
                                    else
                                    {
                                        ?>
                                        <span><?=$statClosedShort?></span>
                                        <?
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="hpp-nft-count">
                                <p class="text-gradient-gold-super-vyebon">
                                    <?
                                    if($currentStatOpen)
                                    {
                                        ?>
                                        <span>16 NFT</span>
                                        <?
                                    }
                                    else
                                    {
                                        ?>
                                        <span><?=$statClosedShort?></span>
                                        <?
                                    }
                                    ?>
                                </p>
                                <p class="text-shadow-hack">
                                    <?
                                    if($currentStatOpen)
                                    {
                                        ?>
                                        <span>16 NFT</span>
                                        <?
                                    }
                                    else
                                    {
                                        ?>
                                        <span><?=$statClosedShort?></span>
                                        <?
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section id="about" class="section">

        <div id="about-sticky-wrapper">
            <div id="about-sticky"></div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-12">

                    <div id="about-main-text">
                        <div id="about-main-text-inner">
                            <div id="amt-icon">
                                <i class="icon-scroll-1"></i>
                            </div>
                            <div id="amt-text">
                                <p><span class="amt-high-light">Fortuna.ton</span> – честная автоматическая лотерея на блокчейне TON.<br>
                                    Все розыгрыши проводятся с помощью специального <span class="amt-high-light">смарт-контракта</span> (компьютерного алгоритма) <span class="amt-high-light">без участия людей.</span></p>
                                <p>Все собранные денежные средства с продажи лотерейных билетов <span class="amt-high-light">распределяются между участниками лотереи</span> и ХОЛДерами проекта <span class="amt-high-light">NFT Web3TON.</span></p>
                            </div>
                        </div>
                    </div>

                    <div id="about-ticket">
                        <picture>
                            <source srcset="/images/ticket.webp" type="image/webp">
                            <source srcset="/images/ticket.png" type="image/png">
                            <img src="/images/ticket.png" alt="Fortunav ticket">
                        </picture>
                    </div>

                    <div id="about-run">
                        <div class="about-run-header">
                            <p class="text-gradient-another-one">
                                <span>Информация о текущем тираже:</span>
                            </p>
                        </div>
                        <div class="about-run-info">
                            <div class="about-run-info-inner">
                                <div class="ari-col-1">
                                    <p class="ac-hdr text-gradient-another-one">
                                        <span>№ тиража:</span>
                                    </p>
                                    <p class="ac-val text-gradient-another-one">
                                        <span><?=$curCycleStr?></span>
                                    </p>
                                </div>
                                <div class="ari-col-2">
                                    <p class="ac-hdr text-gradient-another-one">
                                        <span>Участников:</span>
                                    </p>
                                    <p class="ac-val text-gradient-another-one">
                                        <span><?=$bidders?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="about-winners">
                        <div id="about-winners-inner">
                            <div id="about-winners-header">
                                <p class="text-gradient-another-one">
                                    <span>Количество призовых мест:</span>
                                </p>
                            </div>
                            <div id="about-winners-body">

                                <div id="winners1" class="winners-block">
                                    <div class="winners-block-inner">
                                        <p class="wb-header">1 категория:</p>
                                        <p class="wb-label label-orange"><span>1</span></p>
                                        <p class="wb-label-descr text-gradient-another-one"><span>место</span></p>
                                        <p class="wb-text">Победитель получает <strong>50% призового фонда</strong></p>
                                    </div>
                                </div>
                                <div id="winners2" class="winners-block">
                                    <div class="winners-block-inner">
                                        <p class="wb-header">2 категория:</p>
                                        <p class="wb-label label-purple"><span>4</span></p>
                                        <p class="wb-label-descr text-gradient-another-one"><span>места</span></p>
                                        <p class="wb-text">Победители <strong>делят поровну</strong> оставшиеся <strong>50% призового фонда</strong></p>
                                    </div>
                                </div>
                                <div id="winners3" class="winners-block">
                                    <div class="winners-block-inner">
                                        <p class="wb-header">3 категория:</p>
                                        <p class="wb-label label-green"><span>16</span></p>
                                        <p class="wb-label-descr text-gradient-another-one"><span>мест</span></p>
                                        <p class="wb-text">Победители получают <strong>по 1 NFT из коллекции Web3TON NFT</strong></p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12 distribution-img-wrapper">

                    <?
                    if($isMobile)
                    {
                    ?>
                        <picture>
                            <source srcset="/images/distribution-vertical.webp" type="image/webp">
                            <source srcset="/images/distribution-vertical.png" type="image/png">
                            <img src="/images/distribution-vertical.png" alt="Распределение" id="distribution-img" class="img-fluid distribution-mobile">
                        </picture>
                    <?
                    }
                    else
                    {
                    ?>
                        <picture>
                            <source srcset="/images/distribution-horizontal.webp" type="image/webp">
                            <source srcset="/images/distribution-horizontal.png" type="image/png">
                            <img src="/images/distribution-horizontal.png" alt="Распределение" id="distribution-img" class="img-fluid distribution-horizontal">
                        </picture>
                    <?
                    }
                    ?>


                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="section">
        <div id="hiw-arrow-decor"></div>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div id="how-it-works-header">
                        <p class="text-gradient-gold-vyebon"><span>Как работает лотерея?</span></p>
                    </div>

                    <div id="hiw-block-1" class="hiw-block">
                        <div class="hiw-block-inner">
                            <div class="hiw-block-1-icon">
                                <i class="icon-book"></i>
                            </div>
                            <div class="hiw-block-1-text">
                                <p>Мы запускаем тираж, вы покупаете билеты до истечения срока таймера, а затем автоматически проводится розыгрыш.</p>
                                <p>Чтобы обеспечить честность лотереи, <strong>мы открыли исходный код смарт-контракта и разместили его на GitHub,</strong> где любой желающий может его проверить. </p>
                                <p>После проведения розыгрыша, денежные средства и NFT <strong>автоматически отправляются победителям на TON-кошельки,</strong> с которых покупались билеты.</p>
                                <p>В личном кабинете хранятся ваши билеты и история розыгрышей.</p>
                            </div>
                        </div>
                    </div>

                    <div id="hiw-block-2" class="hiw-block">
                        <div class="hiw-block-inner">
                            <div class="hiw-block-2-text">
                                <p class="hiw-block-2-text-header">Существует 3 категории победителей:</p>
                                <p><strong class="orange">1 категория:</strong>  1 - призовое место. Обладатель выигрышного билета <strong class="orange">получает 50%</strong> от суммы призового фонда.</p>
                                <p><strong class="purple">2 категория:</strong> N - призовых мест. Несколько участников (количество зависит от объёма призового фонда) <strong class="purple">разделяют поровну между собой</strong> оставшиеся <strong class="purple">50%</strong> призового фонда.</p>
                                <p><strong class="green">3 категория:</strong> N - призовых мест. Несколько участников (зависит от суммы выкупленных NFT) <strong class="green">получают приз в виде случайной NFT</strong> из коллекции Web3TON.</p>
                            </div>
                            <div class="hiw-block-2-icon">
                                <i class="icon-crown-sm"></i>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>


    <div id="footer-block-wrapper">
        <div id="footer-block-container">

            <section id="hall-of-fame" class="section">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="hof-header-wrapper">
                                <div id="hof-header">
                                    <p class="text-gradient-gold-vyebon">
                                        <span>Аллея славы победителей</span>
                                    </p>
                                </div>
                            </div>


                            <div id="hof-container">
                                <div class="hof-container-inner">
                                    <div id="hof-block-1" class="hof-block">
                                        <div class="hof-block-bg">
                                            <div class="hof-block-inner">
                                                <div class="hof-icon">
                                                    <i class="icon-crown-xl"></i>
                                                </div>
                                                <p class="hof-block-header">Категория 1</p>
                                                <?
                                                if($lastStatOpen)
                                                {
                                                ?>
                                                    <div class="hof-text-body">
                                                        <p>1. QHR....74h - <strong>25 000 TON</strong></p>
                                                        <p>2. QHR....74h - <strong>25 000 TON</strong></p>
                                                        <p>3. QHR....74h - <strong>25 000 TON</strong></p>
                                                        <p>4. QHR....74h - <strong>25 000 TON</strong></p>
                                                        <p>5. QHR....74h - <strong>25 000 TON</strong></p>
                                                        <p>6. QHR....74h - <strong>25 000 TON</strong></p>
                                                        <p>7. QHR....74h - <strong>25 000 TON</strong></p>
                                                        <p>8. QHR....74h - <strong>25 000 TON</strong></p>
                                                        <p>9. QHR....74h - <strong>25 000 TON</strong></p>
                                                        <p>10. QHR....74h - <strong>25 000 TON</strong></p>
                                                    </div>
                                                <?
                                                }
                                                else
                                                {
                                                ?>
                                                    <div class="hof-text-body-plug">
                                                        <span>data coming soon</span>
                                                    </div>
                                                <?
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="hof-block-2" class="hof-block">
                                        <div class="hof-block-bg">
                                            <div class="hof-block-inner">
                                                <div class="hof-icon">
                                                    <i class="icon-coin"></i>
                                                </div>
                                                <p class="hof-block-header">Категория 2</p>
                                                <?
                                                if($lastStatOpen)
                                                {
                                                ?>
                                                    <div class="hof-text-body">
                                                        <p>1. QHR....74h - <strong>5 000 TON</strong></p>
                                                        <p>2. QHR....74h - <strong>5 000 TON</strong></p>
                                                        <p>3. QHR....74h - <strong>5 000 TON</strong></p>
                                                        <p>4. QHR....74h - <strong>5 000 TON</strong></p>
                                                        <p>5. QHR....74h - <strong>5 000 TON</strong></p>
                                                        <p>6. QHR....74h - <strong>5 000 TON</strong></p>
                                                        <p>7. QHR....74h - <strong>5 000 TON</strong></p>
                                                        <p>8. QHR....74h - <strong>5 000 TON</strong></p>
                                                        <p>9. QHR....74h - <strong>5 000 TON</strong></p>
                                                        <p>10. QHR....74h - <strong>5 000 TON</strong></p>
                                                    </div>
                                                <?
                                                }
                                                else
                                                {
                                                ?>
                                                    <div class="hof-text-body-plug">
                                                        <span>data coming soon</span>
                                                    </div>
                                                <?
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="hof-block-3" class="hof-block">
                                        <div class="hof-block-bg">
                                            <div class="hof-block-inner">
                                                <div class="hof-icon">
                                                    <i class="icon-scroll2"></i>
                                                </div>
                                                <p class="hof-block-header">Категория 3</p>
                                                <?
                                                if($lastStatOpen)
                                                {
                                                ?>
                                                <div class="hof-text-body">
                                                    <p>1. QHR....74h - <strong>1 NFT</strong></p>
                                                    <p>2. QHR....74h - <strong>1 NFT</strong></p>
                                                    <p>3. QHR....74h - <strong>1 NFT</strong></p>
                                                    <p>4. QHR....74h - <strong>1 NFT</strong></p>
                                                    <p>5. QHR....74h - <strong>1 NFT</strong></p>
                                                    <p>6. QHR....74h - <strong>1 NFT</strong></p>
                                                    <p>7. QHR....74h - <strong>1 NFT</strong></p>
                                                    <p>8. QHR....74h - <strong>1 NFT</strong></p>
                                                    <p>9. QHR....74h - <strong>1 NFT</strong></p>
                                                    <p>10. QHR....74h - <strong>1 NFT</strong></p>
                                                </div>
                                                <?
                                                }
                                                else
                                                {
                                                ?>
                                                    <div class="hof-text-body-plug">
                                                        <span>data coming soon</span>
                                                    </div>
                                                <?
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <p class="tp-subheader<?if(!$lastStatOpen){echo " xhdn";}?>">Указаны кошельки победителей последних 10 тиражей*</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 total-played-wrapper">

                            <div id="total-played-container">
                                <div id="total-played">

                                    <div id="tp-flag-left" class="tp-flag"></div>
                                    <div id="tp-body">

                                        <div id="tp-counter">
                                            <div id="tp-counter-container">

                                                <div class="tpc-header">
                                                    <p class="text-gradient-gold-vyebon">
                                                        <span>Всего разыграно:</span>
                                                    </p>
                                                </div>

                                                <div class="tpc-amount-ton">
                                                    <p class="text-gradient-gold-super-vyebon">
                                                        <?
                                                        if($lastStatOpen)
                                                        {
                                                        ?>
                                                        <span>500.000 TON</span>
                                                        <?
                                                        }
                                                        else
                                                        {
                                                        ?>
                                                        <span><?=$statClosedShort?></span>
                                                        <?
                                                        }
                                                        ?>

                                                    </p>
                                                    <p class="text-shadow-hack">
                                                        <?
                                                        if($lastStatOpen)
                                                        {
                                                            ?>
                                                            <span>500.000 TON</span>
                                                            <?
                                                        }
                                                        else
                                                        {
                                                            ?>
                                                            <span><?=$statClosedShort?></span>
                                                            <?
                                                        }
                                                        ?>
                                                    </p>
                                                </div>

                                                <div class="tpc-nft-count">
                                                    <p class="text-gradient-gold-super-vyebon">
                                                        <?
                                                        if($lastStatOpen)
                                                        {
                                                            ?>
                                                            <span>136 NFT</span>
                                                            <?
                                                        }
                                                        else
                                                        {
                                                            ?>
                                                            <span><?=$statClosedShort?></span>
                                                            <?
                                                        }
                                                        ?>
                                                    </p>
                                                    <p class="text-shadow-hack">
                                                        <?
                                                        if($lastStatOpen)
                                                        {
                                                            ?>
                                                            <span>136 NFT</span>
                                                            <?
                                                        }
                                                        else
                                                        {
                                                            ?>
                                                            <span><?=$statClosedShort?></span>
                                                            <?
                                                        }
                                                        ?>
                                                    </p>
                                                </div>

                                            </div>
                                        </div>

                                        <div id="tp-countdown">

                                            <div class="tpc-wrapper">
                                                <p class="tpc-header2 text-gradient-gold-dark"><span>Следующий розыгрыш через:</span></p>
                                            </div>
                                            <div id="tp-countdown-container">

                                                <div id="tp-cdown" class="cdown">
                                                    <div id="tp-countdown-day" class="countdown-number">
                                                        <div class="countdown-time-wrapper">
                                                            <span class="days countdown-time"></span>
                                                        </div>
                                                        <strong>дней</strong>
                                                    </div>
                                                    <div class="countdown-separator">:</div>
                                                    <div id="tp-countdown-hour" class="countdown-number">
                                                        <div class="countdown-time-wrapper">
                                                            <span class="hours countdown-time"></span>
                                                        </div>
                                                        <strong>часов</strong>
                                                    </div>
                                                    <div class="countdown-separator">:</div>
                                                    <div id="tp-countdown-minutes" class="countdown-number">
                                                        <div class="countdown-time-wrapper">
                                                            <span class="minutes countdown-time"></span>
                                                        </div>
                                                        <strong>минут</strong>
                                                    </div>
                                                    <div class="countdown-separator">:</div>
                                                    <div class="countdown-number">
                                                        <div class="countdown-time-wrapper">
                                                            <span class="seconds countdown-time"></span>
                                                        </div>
                                                        <strong>секунд</strong>
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
                                                <span>Купить билет</span>
                                            </div>
                                        </div>

                                    </div>
                                    <div id="tp-flag-right" class="tp-flag"></div>

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


<?
if(!isset($_COOKIE["wallet"]) || $_COOKIE["wallet"] == "")
{
    // куки авторизации нет, добавляем pop-up авторизации
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


    $amountNano = 1000000;
    $amount = convNanoToDecimalTon($amountNano);

    // генерируем токен для комментария к транзакции
    $token = getToken(20);
    // формируем ссылку для оплаты
    $payLink = "ton://transfer/" . $destWallet . "?amount=" . $amountNano . "&text=" . $token;

    // генерируем изображение с QR кодом для оплаты
    require_once $_SERVER["DOCUMENT_ROOT"] . '/include/phpqrcode/qrlib.php';

    QRcode::png($payLink, $_SERVER["DOCUMENT_ROOT"] . "/images/qr/" . $token . ".png", 'H', 6, 2);

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
    <?
    /*
    ?>
    <div class="modal fade" id="authorization" tabindex="-1" role="dialog" aria-labelledby="authorizationLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-gradient-gold-dark" id="authorizationLabel"><span>Authorization</span></h5>
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
                    <p class="auth-modal-description">Authorization takes place by transaction.<br>
                        Send 0,001 TON to specified wallet.<br><span class="warn-text"><i class="error-icon-sm"></i>Transaction comment required.</span></p>
                    <div id="buy-qr">
                        <div class="buy-step-description">
                            Pay with QR
                            <p style="font-size: 12px;">(Scan with app Tonkeeper, Tonhub)</p>
                        </div>
                        <img src="<?=$src?>" alt="QR код для оплаты" class="img-fluid">
                    </div>
                    <div id="buy-button-link">
                        <div class="buy-step-description">
                            Pay with wallet app
                        </div>
                        <a href="<?=$payLink?>" class="btn btn-info rew-btn">Оплатить</a>
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
                                        <input type="text" class="form-control" id="buy-amount" value="<?=$amount?>" readonly>
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
                    <div id="buy-check">

                        <div id="acp-preloader2"></div>
                        <div class="acp-description2">
                            Waiting for transaction
                        </div>
                    </div>
                    <div id="auth-check-success">
                        <div class="acp-inner">
                            <div id="acp-check"></div>
                            <div class="acp-description">
                                Success!
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
    */
    ?>


    <div class="modal fade" id="authorization" tabindex="-1" role="dialog" aria-labelledby="authorizationLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-gradient-gold-dark" id="authorizationLabel"><span>Авторизация</span></h5>
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
                        <span>Транзакция</span>
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
                            <a class="btn btn-info" href="https://app.tonkeeper.com/ton-login/fortuna.web3ton.pro/authRequest">Подключить кошелек</a>
                        </div>
                        <div class="auth-goback">
                            <span>← Назад</span>
                        </div>
                    </div>
                    <?
                }
                ?>
                <div id="auth-transaction" class="modal-body">
                    <p class="auth-modal-description">Авторизация происходит по транзакции.<br>
                        Отправьте 0,001 TON на указанный кошелек.<br><span class="warn-text"><i class="error-icon-sm"></i>Комментарий к транзакции обязателен.</span></p>
                    <div id="buy-qr">
                        <div class="buy-step-description">
                            Оплата по QR-коду
                            <p style="font-size: 12px;">(Сканировать в приложениях Tonkeeper, Tonhub)</p>
                        </div>
                        <img src="<?=$src?>" alt="QR код для оплаты" class="img-fluid">
                    </div>
                    <div id="buy-button-link">
                        <div class="buy-step-description">
                            Оплата в приложении
                        </div>
                        <a href="<?=$payLink?>" class="btn btn-info rew-btn">Оплатить</a>
                    </div>
                    <div id="buy-form">
                        <div class="buy-step-description">
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
                                        <input type="text" class="form-control" id="buy-amount" value="<?=$amount?>" readonly>
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
                    <div id="buy-check">
                        <?/*<button id="auth-check-btn" class="btn btn-info" data-token="<?=$token?>">Проверить транзакцию</button>*/?>
                        <div id="acp-preloader2"></div>
                        <div class="acp-description2">
                            Ожидание транзакции
                        </div>
                    </div>
                    <div id="auth-check-success">
                        <div class="acp-inner">
                            <div id="acp-check"></div>
                            <div class="acp-description">
                                Авторизация прошла успешно!
                            </div>
                        </div>
                    </div>
                    <div class="auth-goback">
                        <span>← Назад</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?
}
?>

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
                        <span class="field-wrapper"><input type="text" id="ticket-quantity" class="qc-text" name="quantity" value="1" data-cost="<?=$ticketCost?>"></span>
                        <span class="qc-plus">+</span>
                    </div>
                </div>
                <div class="modal-buy-description">
                    <p class="mbd-label">Сумма:</p>
                    <p class="mbd-value text-gradient-gold-super-vyebon"><span><strong id="mb-sum"><?=$ticketCost?></strong> TON</span></p>
                </div>
                <div id="modal-buy-place-order">
                    <button class="btn btn-info place-order">Купить</button>
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