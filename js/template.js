$(function()
{
    // Меню Личный кабинет
    let userIcon = $('#auth-wrapper');
    userIcon.data('currentState', 0);

    $('#auth-wrapper').on('click', function()
    {
        if($(this).hasClass('user-unlocked'))
        {
            if(userIcon.data('currentState') == 0)
            {
                if($('#navbarCollapse').is(":visible"))
                {
                    $('#navbarCollapse').collapse('hide');
                    $('#mobile-menu-toggler').addClass('collapsed').attr('aria-expanded', 'false').find('.animated-icon1').removeClass('open');
                }
                $('#user-controls-wrapper').slideDown();
                userIcon.data('currentState', 1);
            }
            else if(userIcon.data('currentState') == 1)
            {
                $('#user-controls-wrapper').slideUp();
                userIcon.data('currentState', 0);
            }
        }
        else if(!$(this).hasClass('user-unlocked'))
        {
            $('#authorization').modal('show');
        }
    });

    $('.uc-disconnect').on('click', function()
    {
        let goToUrl = $('#header-logo-link').attr("href");
        eraseCookie('wallet');
        // window.location.reload(); // нахуй!
        window.location.href = goToUrl;
    });

    // кнопка меню на мобильных
    $('#mobile-menu-toggler.first-button').on('click', function ()
    {
        if(userIcon.data('currentState') == 1)
        {
            $('#user-controls-wrapper').slideUp();
            userIcon.data('currentState', 0);
        }
        $('#mobile-menu-toggler .animated-icon1').toggleClass('open');
    });

    $(window).scroll(function()
    {
        if($(this).scrollTop() >= 300)
        {
            $("#scroll-to-top").fadeIn();
        }else{
            $("#scroll-to-top").fadeOut();
        }
    });
    $("#scroll-to-top").click(function()
    {
        $("html, body").animate(
            {
                scrollTop: 0
            }, 800);
    });

    // закрыть меню на мобильных после клика
    $('.navbar-nav>li>a').on('click', function(e)
    {
        if(!$(this).hasClass("lang-toggle") && !$(this).hasClass("external"))
        {
            e.preventDefault();
            if (window.innerWidth < 1200)
            {
                $('.navbar-collapse').collapse('hide');
                $('#mobile-menu-toggler').addClass('collapsed').attr('aria-expanded', false).find('.animated-icon1').removeClass('open');
            }
            let target = $(this).attr('href');
            let offsetParam = 94;

            $('html, body').animate({
                scrollTop: $(target).offset().top - offsetParam
            }, 600);
        }

    });


    // открытие pop-up авторизации
    $('#auth-wrapper').on('click', function(){
        $('#authorization').modal("show");
    });
    // при редиректе на авторизацию, по таймауту выкидывает pop-up авторизации
    setTimeout(function()
    {
        let urlHash = window.location.hash;
        if(urlHash == "#auth")
        {
            $('#authorization').modal("show");
        }
    }, 1500);

    // копирование в буфер обмена
    let clipboard = new ClipboardJS('.icon-copy');

    clipboard.on('success', function (e) {
        //console.info('Action:', e.action);
        //console.info('Text:', e.text);
        console.info('Trigger:', e.trigger.id);
        $("#"+e.trigger.id).addClass("done");
        setTimeout(function()
        {
            $("#"+e.trigger.id).removeClass("done");
        }, 1200);
    });

    clipboard.on('error', function (e) {
        console.log(e);
    });

    // Работа с cookie
    function setCookie(key, value, expiry) {
        let expires = new Date();
        expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
        document.cookie = key + '=' + value + '; path=/; expires=' + expires.toUTCString();
    }

    function getCookie(key) {
        let keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
        return keyValue ? keyValue[2] : null;
    }

    function eraseCookie(key) {
        let keyValue = getCookie(key);
        setCookie(key, keyValue, '-1');
    }

    // начало процесса покупки (модальные окна, изменение количества)
    $('#hero-buy-btn-container, #header-buy-btn, #tp-buy-btn-container, .change-order, #pib-buy').on('click', function(){
        $('#buy').modal("show");
    });

    $('.qc-plus').on('click', function(){
        let qty = $('#ticket-quantity').val() * 1;
        let cst = $('#ticket-quantity').attr("data-cost") * 1;
        qty += 1;
        let smm = cst * qty;
        $('#ticket-quantity').val(qty);
        $('#mb-sum').text(smm);
    });
    $('.qc-minus').on('click', function(){
        let qty = $('#ticket-quantity').val() * 1;
        let cst = $('#ticket-quantity').attr("data-cost") * 1;
        if(qty > 1)
        {
            qty -= 1;
            let smm = cst * qty;
            $('#ticket-quantity').val(qty);
            $('#mb-sum').text(smm);
        }
        else
        {
            return false;
        }
    });
    $("#ticket-quantity").on("input", function()
    {
        let qty = $('#ticket-quantity').val() * 1;
        let cst = $('#ticket-quantity').attr("data-cost") * 1;
        if(qty > 1)
        {
            let smm = cst * qty;
            $('#mb-sum').text(smm);
        }
        else
        {
            return false;
        }
    });

    $('.place-order').on('click', function(){
        let ticketQuantity = $('#ticket-quantity').val();
        let curHost = window.location.origin;
        let completeLink = curHost + "/buy/?quantity=" + ticketQuantity;
        window.location.href = completeLink;
    });
    $('#bres-link-purple, #bres-link-purple2').on('click', function(){
        let curHost = window.location.origin;
        let completeLink = curHost + "/personal/";
        window.location.href = completeLink;
    });
    $('#bres-link-green, #bres-link-green2').on('click', function(){
        $('#buy').modal("show");
    });



    // Авторизационная транзакция
    // запуск проверки авторизационной транзакции
    function checkAuthTransaction(param)
    {
        if(param)
        {
            clearInterval(param);
            return false;
        }
        let token = $('#buy-comment').val();
        console.log("auth token: " + token);
        let intervalID = setInterval(function()
        {
            $.ajax({
                url: "/include/checkauthtransaction.php",
                dataType: "json",
                type: "GET",
                data: {token:token},
                success: function(msg)
                {
                    console.log("auth in progress");
                    if(msg.approval == 1)
                    {
                        console.log(msg);
                        clearInterval(intervalID);
                        // транзакция найдена, ставим куку
                        let userWallet = msg.wallet;
                        setCookie("wallet", userWallet, 30);
                        $('#user-icon').addClass("user-unlocked");
                        //$('#auth-check-progress').slideUp();

                        $('.auth-modal-description').slideUp();
                        $('#buy-qr').slideUp();
                        $('#buy-form').slideUp();
                        $('#buy-button-link').slideUp();
                        $('#buy-check').slideUp();
                        $('#auth-transaction .auth-goback').slideUp();

                        $('#auth-check-success').slideDown();
                        setTimeout(function()
                        {
                            window.location.reload();
                        }, 1000);
                    }
                }
            });
            console.log("auth iteration");
        }, 15000);
        return intervalID;
    }

    if($('#auth-select').length)
    {
        $('#auth-select-tonkeeper').on('click', function(){
            $('#auth-select').slideUp(100);
            $('#auth-tonkeeper').slideDown(100);
        });

        let intVID = '';
        $('#auth-select-transaction').on('click', function(){
            $('#auth-select').slideUp(100);
            $('#auth-transaction').slideDown(100);
            intVID = checkAuthTransaction();
            console.log('auth start');
            console.log(intVID);
        });

        $('.auth-goback span').on('click', function(){
            if(intVID)
            {
                checkAuthTransaction(intVID);
            }
            $(this).parent().parent().slideUp(100);
            $('#auth-select').slideDown(100);
        });
    }

    // цепочка продажи
    if($('#purchase-data').length)
    {
        console.log("waching started!");
        let buyerWallet = $('#purchase-data').attr("data-buyer-wallet");
        let purchaseAmount = $('#purchase-data').attr("data-purchase-amount");
        let purchaseToken = $('#purchase-data').attr("data-purchase-token");

        console.log("buyerWallet: " + buyerWallet + " ;;; purchaseAmount: " + purchaseAmount + " ;;; purchaseToken: " + purchaseToken);

        if(buyerWallet && purchaseAmount && purchaseToken)
        {
            console.log("buy check iterations started");
            let intervalPurchaseID = setInterval(function()
            {
                console.log("buy check iteration");
                $.ajax({
                    url: "/checkpurchasetransaction.php",
                    dataType: "json",
                    type: "GET",
                    data: {buyerWallet:buyerWallet, purchaseAmount:purchaseAmount, purchaseToken:purchaseToken},
                    success: function(msg)
                    {
                        console.log(msg);
                        if(msg.approval == 1)
                        {
                            // продажа прошла успешно
                            clearInterval(intervalPurchaseID);

                            $('#recivedTon').text(msg.paidTotal);
                            $('#soldTickets').text(msg.ticketsBought);

                            if(msg.precision == 0)
                            {
                                $('#buy-process-description2').css("display", "block");
                            }

                            let ticketsBoughtNum = msg.ticketsBoughtNum;
                            console.log("ticketsBoughtNum: " + ticketsBoughtNum);

                            if(typeof(ticketsBoughtNum) !== 'string')
                            {
                                ticketsBoughtNum = String(ticketsBoughtNum);
                            }

                            if(ticketsBoughtNum.indexOf("|") !== -1)
                            {
                                // несколько проданных билетов, надо разбирать
                                let ticketsBoughtNumArr = ticketsBoughtNum.split("|");
                                $.each(ticketsBoughtNumArr, function(key, value)
                                {
                                    console.log('img added');
                                    $('<img src="/images/tickets/' + value + '.jpg" alt="Билет ' + value + '">').appendTo("#personal-tickets-container");
                                });
                            }
                            else
                            {
                                // один проданный билет
                                $('<img src="/images/tickets/' + ticketsBoughtNum + '.jpg" alt="Билет ' + ticketsBoughtNum + '">').appendTo("#personal-tickets-container");

                            }

                            $('#buy-process-description').slideUp();
                            $('#buy-data-container').slideUp();
                            $('#buy-result').slideDown();
                        }
                        else if(msg.approval == 9)
                        {
                            // обнаружена некорректная транзакция, покупка-продажа не состоится, средства будут возвращены смарт-контрактом обратно
                            // тормозим таймер
                            clearInterval(intervalPurchaseID);

                            $('#buy-process-description').slideUp();
                            $('#buy-data-container').slideUp();
                            $('#buy-breakdown').slideDown();
                        }
                    }
                });
            }, 15000);
        }
    }



});