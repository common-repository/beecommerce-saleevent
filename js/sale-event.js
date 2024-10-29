// making coupons that are no longer active grey
jQuery(document).ready(function(e) {

    if (jQuery('.active-coupons').children().length <= 1) {
        jQuery('.active-coupons').append('<p>Przykro nam, ale aktualnie nie ma żadnych promocji. <a href="#newsletter_signup">Zapisz się na newsletter</a>, a poinformujemy Cię, gdy pojawi się coś nowego!</p>');
    };

    if (jQuery('.future-coupons').children().length <= 1) {
        jQuery('.future-coupons').append('<p>Przykro nam, ale aktualnie nie ma informacji o nadchodzących promocjach. <a href="#newsletter_signup">Zapisz się na newsletter</a>, a poinformujemy Cię, gdy pojawi się coś nowego!</p>');
    };

    if (jQuery('.past-coupons').children().length <= 1) {
        jQuery('.past-coupons').hide();
    };

});
