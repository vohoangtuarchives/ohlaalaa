$(function ($) {
    "use strict";


    $(document).ready(function () {

        var status = $("#payment-status").text();
        if(status == "Unpaid"){
            console.log(status);
        }

        // $.ajax({
        //     type: "GET",
        //     url:mainurl+"/carts/coupon/clear",
        //     data:{code:val, total:total, tax:tax,province_id:province_id,district_id:district_id,is_online_payment:is_online_payment},
        //     success:function(data){
        //         $("#check-coupon-form").hide();
        //         $(".discount-bar").removeClass('d-none');
    
        //         if(pos == 0){
        //             $('#total-cost').html($('#curr-sign').val()+thousands_separators(data[0]));
        //             $('#discount').html($('#curr-sign').val()+thousands_separators(data[2]));
        //             $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
        //         }
        //         else{
        //             $('#total-cost').html(thousands_separators(data[0]) + $('#curr-sign').val());
        //             $('#discount').html(thousands_separators(data[2]) + $('#curr-sign').val());
        //             $(".customer_shippingcost1").text( thousands_separators(Number(data[7])) + $('#curr-sign').val());
        //         }
        //         $('#grandtotal').val(data[0]);
        //         $('#coupon_code').val(data[1]);
        //         $('#coupon_code_text').text(data[1]);
        //         $('#coupon_discount').val(data[2]);
        //         $('#shipping-cost').val(data[7]);
        //         if(data[4] != 0){
        //             $('.dpercent').html('('+data[4]+')');
        //         }
        //         else{
        //             $('.dpercent').html('');
        //         }
        //         calFinalPrice();
        //         $("#code").val("");
        //     }
        // });

        
        
    });

});