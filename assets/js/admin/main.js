(function($){

    jQuery(document).ready(function(){
        $('#billing_country option[value="US"]').remove();
        $('#billing_country').prepend('<option value="US" selected="selected">United States (US)</option>');

        setTimeout(function(){
            $('#billing_state option[value="TN"]').remove();
            $('#billing_state').prepend('<option value="TN" selected="selected">Tennessee</option>');
        },1000)

    });


})(jQuery);
