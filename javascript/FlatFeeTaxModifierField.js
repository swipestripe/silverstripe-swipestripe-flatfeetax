(function($) { 
    $(document).ready(function() { 
    	$('#CheckoutForm_OrderForm_Shipping-CountryCode').live('change', updateOrderFormCartAJAX);
    	$('.modifier-set-field select').live('change', updateOrderFormCartAJAX);
    })
})(jQuery);