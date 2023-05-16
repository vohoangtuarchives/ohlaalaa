$(function ($) {
    "use strict";


    $(document).ready(function () {

        $("body").delegate(".vnpay_bank", "click", function(){
            $('.vnpay_bank').removeClass('active');
            var data = $(this).data('val');
            $('.BankPay').val(data);
            $(this).addClass('active');
		});

    });

});