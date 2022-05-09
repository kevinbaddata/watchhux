// Execute code snippet below on click event
$('.wrapper').on('click', function() {

    // Check if span doesn't contain grill class
    if (!$('span').hasClass("grill")) {
        $('span').addClass('grill');
        $('h1').show();
        $('.slider').addClass('showslide');
    }
    // Remove class grill if class doesn't contain
    else {
        $('span').removeClass('grill');
        $('h1').hide();
        $('.slider').removeClass('showslide');
    }
    //...
});
//...