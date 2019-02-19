/*Sticky Menu*/
jQuery.noConflict();
(function($, w){
    $(function(){
        var $nav = $('.bottom-header');
        var stickyNavTop = $nav.offset().top;

        var stickyNav = function(){
            var scrollTop = $(w).scrollTop();

            if (scrollTop > stickyNavTop) {
                $nav.addClass('sticky');
            } else {
                $nav.removeClass('sticky');
            }
        };

        $(w).scroll(function() {
            stickyNav();
        });

        stickyNav();
    });
})($j, window);
jQuery(document).ready(function($){
    /*Home slider product*/
    setTimeout(function () {
        var heightImg = $('.owl-theme .product-image img').height();
        var topNav = (heightImg/2) + 22;
        $('.owl-theme .owl-controls .owl-buttons > div').css('top',topNav);
    },1000);
    setHieght('.products-grid .item .product-info .product-name');
    setHieght('.products-grid .item .product-info .product-sku');
    setHieght('.products-grid .item .product-info .price-box');
    jQuery(window).resize(function(){
        setHieght('.products-grid .item .product-info .product-name');
        setHieght('.products-grid .item .product-info .product-sku');
        setHieght('.products-grid .item .product-info .price-box');
    });


    $('.customer-account-login ul.form-list .input-box, .customer-account-forgotpassword #form-validate ul.form-list .input-box').each(function () {
    var $this = $(this);
    var field = $this.find('[type=text], [type=file], [type=email], [type=password], textarea');
    var span = $(this).find('> span');
    var onBlur = function () {
        if ($.trim(field.val()) == '') {
            field.val('');
            span.fadeIn(100);
        } else {
            if (field.prop('type') == 'file') {
                span.text(field.val());
            } else {
                span.fadeTo(100, 0);
            }
        }
    };
    field.focus(function () {
        span.fadeOut(100);
        }).blur(onBlur);
        onBlur();
    });
    $('.customer-account-login .account-login').addClass('show')
    $('.title-tab-mobile .active-form-login').click(function(){
        $('.customer-account-login .account-login').addClass('show')
        $('.customer-account-login .account-create').removeClass('show')
    });
    $('.title-tab-mobile .active-form-register').click(function(){
        $('.customer-account-login .account-create').addClass('show')
        $('.customer-account-login .account-login').removeClass('show')
    });

    $('.product-viewed-slider').owlCarousel({
        loop            : true,
        nav             : true,
        dots            : false,
        margin          : 0,
        responsiveClass : true,
        navText         : ['<i class="fa"> &lt; </i>','<i class="fa"> &lt; </i>'],
        items           : 5,
        responsive:{
            0:{
                items:1
            },
            600:{
                items:3
            },
            1000:{
                items:5
            }
        }
    });

    $('#narrow-by-list dt:first-child,#narrow-by-list dt:first-child + dd').addClass('current');

});
function setHieght(e) {

        if(jQuery(e).length > 0){
            jQuery(e).css('height','');
            var maxHeightNameDesc = 0;
            jQuery(e).each(function(){
                if(jQuery(this).height() > maxHeightNameDesc){
                    maxHeightNameDesc = jQuery(this).height();
                }
            });
            if(maxHeightNameDesc > 0){
                jQuery(e).height(maxHeightNameDesc);
            }

        }

}