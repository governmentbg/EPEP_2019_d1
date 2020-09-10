$(function(){
    if ($(window).width() <= 1400) {
        var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
        $('.menu-item').on('mouseenter', function () {
            var position = $(this).position(),
                sub = $(this).find('.submenu-list');
            if (w < (position.left + sub.outerWidth())) {
                sub.css({
                    left: 'initial',
                    right: 0
                });
            }
        })
        $('.submenu-list').on('mouseenter', function () {
            var parent = $(this).closest('.menu-item').position().left,
                position = $(this).position().left,
                sub = $(this).find('.submenu-inner-list');
            if (position <= 0) {
                position = parent + $(this).closest('.menu-item').outerWidth();
            }
            if (w < (position + $(this).outerWidth() + sub.outerWidth())) {
                sub.css({
                    left: -sub.outerWidth() + 'px'
                });
            }
        })
    }
    if($(window).width() >= 992){
        var dynamicSize;
        $('ul.submenu-list').on('mouseover', function(){
            dynamicSize = $(this).outerHeight();
            $('.submenu-list').css({'top': $('.menu-item').outerHeight()});
            $('.submenu-inner-list').css('min-height', dynamicSize);
        })  
    }
    if($(window).width() <= 991){
        $('.menu-item a').on('click', function(e){
            if ($(e.target).hasClass('mobile-sub')) {
                e.preventDefault();
                $(this).siblings('.submenu-list').toggle(200);
            }
        })
        $('.submenu-list>li a').on('click', function(e){
            if ($(e.target).hasClass('mobile-sub-small')) {
                e.preventDefault();
                $(this).siblings('.submenu-inner-list').toggle(200);
            }
        })
    }
    $('.show-menu').on('click', function() {
        $('.show-menu').toggleClass("change");
        $('.menu-list').slideToggle(400);
    });
    $(window).bind('scroll', debounce(function() {
        if ($(window).scrollTop() > $('.header-container').outerHeight() - $('.menu-wrapper').outerHeight()) {
            $('.menu-wrapper').addClass('fixed-menu');
        } else {
            $('.menu-wrapper').removeClass('fixed-menu');
        }
    }, 15));
    function debounce (callback, wait) {
        var timeout, args, func;
        return function () {
            args = arguments;
            func = function () {
                callback.apply(this, args)
            }
            clearTimeout(timeout);
            timeout = setTimeout(func, wait)
        }
    }
    $('.accessibility-menu-btn').on('click', function(e) {
        e.preventDefault();
        $('.top-menu').toggleClass('accessible');
    });
    $('a.accessibility-btn').on('click', function(e) {
        var cls = $(this).attr('class').replace('accessibility-btn ', '');
        switch (cls) {
            case 'accessibility-blue':
            case 'accessibility-yellow':
                $('html').attr('class', cls);
                $('header').addClass('white');
                break;
            case 'accessibility-dark':
                $('html').attr('class', cls);
                $('header').removeClass('white');
                break;
            case 'accessibility-normal':
                $('html').attr('class', '');
                $('header').removeClass('white');
                break;
            case 'font-large':
                $('html').removeClass('small').addClass('large');
                break;
            case 'font-normal':
                $('html').removeClass('large').removeClass('small');
                break;
            case 'font-small':
                $('html').removeClass('large').addClass('small');
                break;
        }
        document.cookie = "accessibility=" + $('html').attr('class') + "; expires=" + $(this).closest('div').data('expire') + "; path=/; secure";
    });
    /*var accmenu = '';
    $('h2, footer').each(function(i, e) {
        if (!$(e).attr('id')) {
            $(e).attr('id', 'title-' + (i + 1));
        }
        if(!($(e).data('no-accessibility'))){
            switch ($(e).prop('tagName')) {
                case 'H2':
                    accmenu += '<a href="#' + $(e).attr('id') + '" class="goto">' + ($(e).data('accessibility') ? $(e).data('accessibility') : $(e).text() ) + '</a>';
                break;
                case 'FOOTER':
                    accmenu += '<a href="#' + $(e).attr('id') + '" class="goto">' + 'Контакти' + '</a>';
                break;
            }
        } 
    });
    $('.accessibility-menu a:not(.goto):eq(0)').before(accmenu);*/
    var menu = {
        run: function(item) {
            menu.clone(item);
            var scrollInterval = setInterval(menu.stickIt, 10);
        },
        clone: function(item) {
            $(item).addClass('original').clone().insertAfter(item).addClass('cloned').removeClass('original').hide();
        },
        stickIt: function() {
            if ($(window).scrollTop() >= ($('.original').offset().top)) {
                $('.cloned').show();
                $('.original').css('visibility', 'hidden');
            } else {
                $('.cloned').hide();
                $('.original').css('visibility', 'visible');
            }
        }
    }
    $('#cookies a.btn-accept').on('click', function(e) {
        e.preventDefault();
        document.cookie = "cookies=1; expires="+$('#cookies').data('expire')+"; path=/; secure";
        $('#cookies').hide();
    });
    $('#cookies a.btn-refuse').on('click', function(e) {
        e.preventDefault();
        document.cookie = "cookies=0; expires="+$('#cookies').data('expire')+"; path=/; secure";
        $('#cookies').hide();
    });
});