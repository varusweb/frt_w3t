<?
$num = $_GET["num"];



$numStrLength = strlen($num);

$curNumStr = $num;

$nullNeeded = 5 - $numStrLength;
if ($nullNeeded > 0) {
    for ($n = $nullNeeded; $n > 0; $n--) {
        $curNumStr = "0" . $curNumStr;
    }
}

$now = time();

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

    <title>Личный кабинет Fortuna TON</title>
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
                                <picture>
                                    <source srcset="/images/fortuna-logo-1.webp" type="image/webp">
                                    <source srcset="/images/fortuna-logo-1.png" type="image/png">
                                    <img src="/images/fortuna-logo-1.png" alt="Fortunav logo">
                                </picture>
                            </div>

                            <div id="header-controls">
                                <div id="auth-wrapper">
                                    <i class="icon-auth"></i>
                                    <span>Войти</span>
                                </div>
                                <div class="navbar-btn-wrapper">
                                    <button id="header-buy-btn" class="btn btn-info buy-ticket"><i class="ico ico-cart"></i> Купить<span> билет</span></button>
                                </div>

                                <button id="mobile-menu-toggler" class="navbar-toggler first-button" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                                    <div class="animated-icon1"><span></span><span></span><span></span></div>
                                </button>
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

                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>

                            <img src="/include/get-ticket.php?number=<?=$curNumStr?>" alt="спать хочу, просто пиздец сука!" class="img-fluid">

                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
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


<script src="/js/jquery-3.2.1.min.js"></script>
<script src="/js/popper.min.js"></script>
<script src="/js/bootstrap.min.js"></script>

<script src="/js/modernizr-custom.js"></script>
<script src="/js/lightslider.min.js"></script>
<script src="/js/clipboard.min.js"></script>
<script src="/js/template.js?v=<?=$now?>"></script>
</body>
</html>
