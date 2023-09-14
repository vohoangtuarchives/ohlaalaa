$(function ($) {
    "use strict";


    $(document).ready(function () {

        //#region init properties
        var ck = 0;
        $('a.payment:first').addClass('active');
        $('.checkoutform').prop('action',$('a.payment:first').data('form'));
        $($('a.payment:first').attr('href')).load($('a.payment:first').data('href'));
        var show = $('a.payment:first').data('show');
        if(show != 'no') {
            $('.pay-area').removeClass('d-none');
        }
        else {
            $('.pay-area').addClass('d-none');
        }
        $($('a.payment:first').attr('href')).addClass('active').addClass('show');

        if($('a.payment:first').data('val') == 'cod'){
            $('.is-online-payment').val(0);
        }
        var coup = 0;
        var pos = $('#currency_format').val();
        if($('#checked').val() != null)
        {
            $('#comment-log-reg1').modal('show');
        }
        
        var mship = $('.shipping').length > 0 ? $('.shipping').first().val() : 0;
        var mpack = $('.packing').length > 0 ? $('.packing').first().val() : 0;
        mship = parseFloat(mship);
        mpack = parseFloat(mpack);

        $('#shipping-cost').val(mship);
        $('#packing-cost').val(mpack);
        var ftotal = parseFloat($('#grandtotal').val()) + mship + mpack;

        ftotal = parseFloat(ftotal);
        if(ftotal % 1 != 0)
        {
            ftotal = ftotal.toFixed(0); 
        }
        if(pos == 0){
            //$('#final-cost').html($('#curr-sign').val()+ftotal.toLocaleString())
            $('#final-cost').html($('#curr-sign').val()+thousands_separators(ftotal));
        }
        else{
            //$('#final-cost').html(ftotal.toLocaleString()+$('#curr-sign').val());
            $('#final-cost').html(thousands_separators(ftotal)+$('#curr-sign').val());
        }

        $('#grandtotal').val(ftotal);

        if($("#auth-check").val())
        

        // #endregion init properties

        $('.shipping').on('click',function(){
            // mship = $(this).val();
        
            // shipping_change(mship);
        
            var province_id = $('.customer_province').val();
            var district_id = $('.customer_district').val();
        
            if($('.is_shipdiff').val() == 'true'){
                province_id = $('.shipping_province').val();
                district_id = $('.shipping_district').val();
            }
            fillShippingCost(province_id, district_id);
        });

        function shipping_change(shippingvalue){
            mship = shippingvalue;
            $('#shipping-cost').val(mship);
            var ttotal = parseFloat($('#grandtotal').val()) + parseFloat(mship) + parseFloat(mpack);
            $('#grandtotal').val(ttotal);
            calFinalPrice();
        }
        
        $('.packing').on('click',function(){
            mpack = $(this).val();
            $('#packing-cost').val(mpack);
            var ttotal = parseFloat($('#tgrandtotal').val()) + parseFloat(mship) + parseFloat(mpack);
            ttotal = parseFloat(ttotal);
            if(ttotal % 1 != 0)
            {
            ttotal = ttotal.toFixed(0);
            }
        
            if(pos == 0){
                $('#final-cost').html($('#curr-sign').val()+thousands_separators(ttotal));
            }
            else{
                $('#final-cost').html(thousands_separators(ttotal)+$('#curr-sign').val());
            }
        
            $('#grandtotal').val(ttotal);
        
        });

        $("#clear-coupon").on('click', function () {
            var val = $("#code").val();
            if(val.length == 0){
                val = $("#coupon_code").val();
            }
            if(val.length == 0)
                return;
            var province_id = $('.customer_province').val();
            var district_id = $('.customer_district').val();
            if($('.is_shipdiff').val() == 'true'){
                province_id = $('.shipping_province').val();
                district_id = $('.shipping_district').val();
            }
            var is_online_payment = $('.is-online-payment').val();
            var total = $("#ttotal").val();
            var ship = 0;
            var tax = $("#gs_tax").val();
            $.ajax({
                type: "GET",
                url:mainurl+"/carts/coupon/clear",
                data:{code:val, total:total, tax:tax,province_id:province_id,district_id:district_id,is_online_payment:is_online_payment},
                success:function(data){
                    $("#check-coupon-form").hide();
                    $(".discount-bar").removeClass('d-none');
        
                    if(pos == 0){
                        $('#total-cost').html($('#curr-sign').val()+thousands_separators(data[0]));
                        $('#discount').html($('#curr-sign').val()+thousands_separators(data[2]));
                        $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
                    }
                    else{
                        $('#total-cost').html(thousands_separators(data[0]) + $('#curr-sign').val());
                        $('#discount').html(thousands_separators(data[2]) + $('#curr-sign').val());
                        $(".customer_shippingcost1").text( thousands_separators(Number(data[7])) + $('#curr-sign').val());
                    }
                    $('#grandtotal').val(data[0]);
                    $('#coupon_code').val(data[1]);
                    $('#coupon_code_text').text(data[1]);
                    $('#coupon_discount').val(data[2]);
                    $('#shipping-cost').val(data[7]);
                    if(data[4] != 0){
                        $('.dpercent').html('('+data[4]+')');
                    }
                    else{
                        $('.dpercent').html('');
                    }
                    calFinalPrice();
                    $("#code").val("");
                }
            });
            return false;
        });

        $("#check-coupon-form").on('submit', function () {
            var val = $("#code").val();
            if(val.length == 0){
                val = $("#coupon_code").val();
            }
            if(val.length == 0)
                return;
            var province_id = $('.customer_province').val();
            var district_id = $('.customer_district').val();
            if($('.is_shipdiff').val() == 'true'){
                province_id = $('.shipping_province').val();
                district_id = $('.shipping_district').val();
            }
            var is_online_payment = $('.is-online-payment').val();
            var total = $("#total-product-final-amount").val();
            var ship = 0;
            var tax = $("#gs_tax").val();
            $.ajax({
                type: "GET",
                url:mainurl+"/carts/coupon/check",
                data:{code:val, total:total, shipping_cost:ship, tax:tax,province_id:province_id,district_id:district_id,is_online_payment:is_online_payment},
                success:function(data){
                    if(data == 0)
                    {
                        toastr.error(langg.no_coupon);
                        $("#code").val("");
                    }
                    else if(data == 2)
                    {
                        toastr.error(langg.already_coupon);
                        $("#code").val("");
                    }
                    else
                    {
                        $("#check-coupon-form").hide();
                        $(".discount-bar").removeClass('d-none');
                        $('#grandtotal').val(data[0]);
                        $('#coupon_code').val(data[1]);
                        $('#coupon_code_text').text(data[1]);
                        $('#coupon_discount').val(data[2]);
                        $('#shipping-cost').val(data[7]);
                        if(pos == 0){
                            $('#total-cost').html($('#curr-sign').val()+thousands_separators(data[0]));
                            $('#discount').html($('#curr-sign').val()+thousands_separators(data[2]));
                            $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
                        }
                        else{
                            $('#total-cost').html(thousands_separators(data[0]) + $('#curr-sign').val());
                            $('#discount').html(thousands_separators(data[2]) + $('#curr-sign').val());
                            $(".customer_shippingcost1").text( thousands_separators(Number(data[7])) + $('#curr-sign').val());
                        }
        
                        if(data[4] != 0){
                            $('.dpercent').html('('+data[4]+')');
                        }
                        else{
                            $('.dpercent').html('');
                        }
                        calFinalPrice();
                        $("#code").val("");
                    }
                }
            });
            return false;
        });

        // Password Checking

        $("#open-pass").on( "change", function() {
            if(this.checked){
                $('.set-account-pass').removeClass('d-none');
                $('.set-account-pass input').prop('required',true);
                $('#personal-email').prop('required',true);
                $('#personal-name').prop('required',true);
            }
            else{
                $('.set-account-pass').addClass('d-none');
                $('.set-account-pass input').prop('required',false);
                $('#personal-email').prop('required',false);
                $('#personal-name').prop('required',false);

            }
        });

        // Password Checking Ends

        // Shipping Address Checking

        $("#ship-diff-address").on( "change", function() {
            $('.is_shipdiff').val(this.checked);
            if(this.checked){
                $('.ship-diff-addres-area').removeClass('d-none');
                $('.ship-diff-addres-area input, .ship-diff-addres-area select').prop('required',true);
            }
            else{
                $('.ship-diff-addres-area').addClass('d-none');
                $('.ship-diff-addres-area input, .ship-diff-addres-area select').prop('required',false);
            }
        });

        // Shipping Address Checking Ends

        // shopping point input checking

        $(".use-shopping-point-item").on( "change", function() {
            var productid = $(this).val();
            var product_size_color_id = $(this).data('val');
            var province_id = $('.customer_province').val();
            var district_id = $('.customer_district').val();
            if($('.is_shipdiff').val() == 'true'){
                province_id = $('.shipping_province').val();
                district_id = $('.shipping_district').val();
            }
            var is_online_payment = $('.is-online-payment').val();

            if(this.checked){
                $('#use-shopping-point-item-input-'+productid).removeClass('d-none');
                var product_price_sp = parseInt($('#product-price-shopping-point-'+ productid).val());
                var shop_coupon_amount = $('#use-shop-coupon-amount-'+ productid).data('val');
                var maxpoint = product_price_sp;
                maxpoint = maxpoint < 0 ? 0 : maxpoint;
                var url = mainurl+'/cart/sp/'+productid+'/'+maxpoint;

                $.get(url , {province_id:province_id,district_id:district_id,is_online_payment:is_online_payment, product_size_color_id:product_size_color_id}, function( data ) {
                    if(data[0] == 1) {
                        $("#use-shopping-point-item-amount-" + productid).text(thousands_separators(data[1]["shopping_point_amount"]));
                        $("#use-shopping-point-item-payment-remain-" + productid).text(thousands_separators(data[1]["shopping_point_payment_remain"]));
                        $("#product-sub-amount-" + productid).text(thousands_separators(data[1]["product_sub_amount"]));
                        $("#product-sub-amount-" + productid).data('val', data[1]["product_sub_amount"]);
                        $('#use-shop-coupon-amount-'+ productid).data('val', data[1]["shop_coupon_amount"]);
                        $('#use-shop-coupon-amount-'+ productid).text(thousands_separators(data[1]["shop_coupon_amount"]));
                        $('#product-final-amount-'+ productid).data('val', data[1]["product_final_amount"]);
                        $('#product-final-amount-'+ productid).text(thousands_separators(data[1]["product_final_amount"]));
                        $("#totalSPUsed").text(thousands_separators(data[2]));
                        $("#totalSPAmount").text(thousands_separators(data[3]));
                        $("#totalSPDiff").text(thousands_separators(data[4]));
                        $('#total-SP-Amount').val(data[3]);
                        $('#total-SP-used').val(data[2]);
                        $('#total-SP-Diff').val(data[4]);
                        $('#total-ShopCoupon-Amount').val(data[5]);
                        $("#use-shopping-point-item-number-" + productid).val(maxpoint);
                        $('#grandtotal').val(data[6]['total_cost_amount']);
                        $('#coupon_discount').val(data[6]['coupon_amount']);
                        $('#shipping-cost').val(data[7]);

                        if(pos == 0){
                            $("#totalShopCouponAmount").text($('#curr-sign').val()+thousands_separators(data[5]));
                            $('#shopping-point-amount').text($('#curr-sign').val()+thousands_separators(Math.round(data[3])));
                            $('#discount').html($('#curr-sign').val()+thousands_separators(data[6]['coupon_amount']));
                            $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
                        }
                        else{
                            $("#totalShopCouponAmount").text(thousands_separators(data[5]) + $('#curr-sign').val());
                            $('#shopping-point-amount').text(thousands_separators(Math.round(data[3])) + $('#curr-sign').val());
                            $('#discount').html(thousands_separators(data[6]['coupon_amount']) + $('#curr-sign').val());
                            $(".customer_shippingcost1").text(thousands_separators(Number(data[7])) + $('#curr-sign').val());
                        }

                        calFinalPrice();

                        if(data[4] < 0)
                        {
                            toastr.error('Số dư ví shopping point không đủ!');
                        }
                        else{
                            toastr.success('OK!');
                        }
                    }
                    else if(data[0] == 0) {
                        toastr.error('error');
                    }
                });
            }
            else{
                $('#use-shopping-point-item-input-'+productid).addClass('d-none');
                var url = mainurl+'/cart/rmsp/'+productid;
                
                $.get(url , {province_id:province_id,district_id:district_id,is_online_payment:is_online_payment, product_size_color_id:product_size_color_id}, function( data ) {
                    if(data[0] == 1) {
                        $("#use-shopping-point-item-number-" + productid).val(0);
                        $("#use-shopping-point-item-amount-" + productid).text(thousands_separators(data[1]["shopping_point_amount"]));
                        $("#use-shopping-point-item-payment-remain-" + productid).text(thousands_separators(data[1]["shopping_point_payment_remain"]));
                        $("#product-sub-amount-" + productid).text(thousands_separators(data[1]["product_sub_amount"]));
                        $("#product-sub-amount-" + productid).data('val', data[1]["product_sub_amount"]);
                        $('#use-shop-coupon-amount-'+ productid).data('val', data[1]["shop_coupon_amount"]);
                        $('#use-shop-coupon-amount-'+ productid).text(thousands_separators(data[1]["shop_coupon_amount"]));
                        $('#product-final-amount-'+ productid).data('val', data[1]["product_final_amount"]);
                        $('#product-final-amount-'+ productid).text(thousands_separators(data[1]["product_final_amount"]));
                        $("#totalSPUsed").text(thousands_separators(data[2]));
                        $("#totalSPAmount").text(thousands_separators(data[3]));
                        $("#totalSPDiff").text(thousands_separators(data[4]));
                        $('#total-SP-Amount').val(data[3]);
                        $('#total-SP-used').val(data[2]);
                        $('#total-SP-Diff').val(data[4]);
                        $('#total-ShopCoupon-Amount').val(data[5]);
                        $('#grandtotal').val(data[6]['total_cost_amount']);
                        $('#coupon_discount').val(data[6]['coupon_amount']);
                        $('#shipping-cost').val(data[7]);
                        if(pos == 0){
                            $("#totalShopCouponAmount").text($('#curr-sign').val()+thousands_separators(data[5]));
                            $('#shopping-point-amount').text(thousands_separators(Math.round(data[3])));
                            $('#discount').html($('#curr-sign').val()+thousands_separators(data[6]['coupon_amount']));
                            $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
                        }
                        else{
                            $("#totalShopCouponAmount").text(thousands_separators(data[5]) + $('#curr-sign').val());
                            $('#shopping-point-amount').text(thousands_separators(Math.round(data[3])) + $('#curr-sign').val());
                            $('#discount').html(thousands_separators(data[6]['coupon_amount']) + $('#curr-sign').val());
                            $(".customer_shippingcost1").text(thousands_separators(Number(data[7])) + $('#curr-sign').val());
                        }
                        calFinalPrice();
                        if(data[4] < 0)
                        {
                            toastr.error('Số dư ví shopping point không đủ!');
                        }
                        else{
                            toastr.success('OK!');
                        }
                    }
                    else if(data[0] == 0) {
                        toastr.error('error');
                    }
                });
            }
        });

        $(".use-shopping-point-item-input").on( "change", function() {
            var point = $(this).val() == '' ? 0 : $(this).val();
            var productid = $(this).data('val');
            var product_size_color_id = $(this).data('sizecolor');
            if(point < 0){
                point = 0;
            }
            else
            {
                var product_price_sp = parseInt($('#product-price-shopping-point-'+ productid).val());
                var shop_coupon_amount = $('#use-shop-coupon-amount-'+ productid).data('val');
                var maxpoint = product_price_sp;
                maxpoint = maxpoint < 0 ? 0 : maxpoint;
                point = point > maxpoint ? maxpoint : point;
            }
            $(this).val(point);
            var url = mainurl+'/cart/sp/'+productid+'/'+point;
            var province_id = $('.customer_province').val();
            var district_id = $('.customer_district').val();
            if($('.is_shipdiff').val() == 'true'){
                province_id = $('.shipping_province').val();
                district_id = $('.shipping_district').val();
            }
            var is_online_payment = $('.is-online-payment').val();
        
            $.get(url , {province_id:province_id,district_id:district_id,is_online_payment:is_online_payment, product_size_color_id:product_size_color_id}, function( data ) {
                if(data[0] == 1) {
                    // console.log(data);
                    $("#use-shopping-point-item-amount-" + productid).text(thousands_separators(data[1]["shopping_point_amount"]));
                    $("#use-shopping-point-item-payment-remain-" + productid).text(thousands_separators(data[1]["shopping_point_payment_remain"]));
                    $("#product-sub-amount-" + productid).text(thousands_separators(data[1]["product_sub_amount"]));
                    $("#product-sub-amount-" + productid).data('val', data[1]["product_sub_amount"]);
                    $('#use-shop-coupon-amount-'+ productid).data('val', data[1]["shop_coupon_amount"]);
                    $('#use-shop-coupon-amount-'+ productid).text(thousands_separators(data[1]["shop_coupon_amount"]));
                    $('#product-final-amount-'+ productid).data('val', data[1]["product_final_amount"]);
                    $('#product-final-amount-'+ productid).text(thousands_separators(data[1]["product_final_amount"]));
                    $("#totalSPUsed").text(thousands_separators(data[2]));
                    $("#totalSPAmount").text(thousands_separators(data[3]));
                    $("#totalSPDiff").text(thousands_separators(data[4]));
                    $('#total-SP-Amount').val(data[3]);
                    $('#total-SP-used').val(data[2]);
                    $('#total-SP-Diff').val(data[4]);
                    $('#total-ShopCoupon-Amount').val(data[5]);
                    $('#grandtotal').val(data[6]['total_cost_amount']);
                    $('#coupon_discount').val(data[6]['coupon_amount']);
                    $('#shipping-cost').val(data[7]);
                    if(pos == 0){
                        $("#totalShopCouponAmount").text($('#curr-sign').val() + thousands_separators(data[5]));
                        $('#shopping-point-amount').text($('#curr-sign').val()+thousands_separators(Math.round(data[3])));
                        $('#discount').html($('#curr-sign').val()+thousands_separators(data[6]['coupon_amount']));
                        $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
                    }
                    else{
                        $("#totalShopCouponAmount").text(thousands_separators(data[5]) + $('#curr-sign').val());
                        $('#shopping-point-amount').text(thousands_separators(Math.round(data[3])) + $('#curr-sign').val());
                        $('#discount').html(thousands_separators(data[6]['coupon_amount']) + $('#curr-sign').val());
                        $(".customer_shippingcost1").text(thousands_separators(Number(data[7])) + $('#curr-sign').val());
                    }
                    calFinalPrice();
                    if(data[4] < 0)
                    {
                        toastr.error('Số dư ví shopping point không đủ!');
                    }
                    else{
                        var maxpoint = parseInt($('#product-price-shopping-point-'+ productid).val());
                        if(point > maxpoint){
                            toastr.error('Shopping point không hợp lệ! Tối đa ['+maxpoint+']');
                        }
                        else{
                            toastr.success('OK!');
                        }
                    }
                }
                else if(data[0] == 0) {
                    toastr.error('error');
                }
            });
        });
        //// shopping point input checking Ends

        function calFinalPrice(){
            var product_amount = parseFloat($('#ttotal').val());
            var product_sp_amount = parseFloat($('#total-sp-price-amount').val());
            var total_product_amount = product_amount + product_sp_amount;
            var gs_tax = $('#gs_tax').val();
            var shipping_cost = parseFloat($('#shipping-cost').val());
            var coupon_discount = $('#coupon_discount').val();
            var sp_amount = $('#total-SP-Amount').val();
            var shop_coupon_amount = $('#total-ShopCoupon-Amount').val();
            var tax_amount = total_product_amount * gs_tax / 100.0;
            var final1 = total_product_amount - coupon_discount - sp_amount - shop_coupon_amount;
            var final_price = final1 > 0 ? final1 + tax_amount + shipping_cost : tax_amount + shipping_cost;
            var total_cost = final1 > 0 ? final1 + tax_amount : tax_amount;
            if(pos == 0){
                $('#total-cost').text($('#curr-sign').val()+thousands_separators(Math.round(total_cost)));
                $('#final-cost2').text($('#curr-sign').val()+thousands_separators(Math.round(final_price)));
            }
            else{
                $('#total-cost').text(thousands_separators(Math.round(total_cost)) + $('#curr-sign').val());
                $('#final-cost2').text(thousands_separators(Math.round(final_price)) + $('#curr-sign').val());
            }
            $('#alepayAmout').val(final_price);
            return final_price;
        }

        //shop coupon check 
        $(".use-shop-coupon-input").on( "change", function() {
            var coupon_code = $(this).val();
            var productid = $(this).data('val');
            var product_size_color_id = $(this).data('sizecolor');
            var province_id = $('.customer_province').val();
            var district_id = $('.customer_district').val();
            if($('.is_shipdiff').val() == 'true'){
                province_id = $('.shipping_province').val();
                district_id = $('.shipping_district').val();
            }
            var is_online_payment = $('.is-online-payment').val();
            if(coupon_code == ''){
                var url = mainurl+'/cart/coupon/clear/'+productid;
                $.get(url, {province_id:province_id,district_id:district_id,is_online_payment:is_online_payment, product_size_color_id:product_size_color_id} , function( data ) {
                    $("#use-shop-coupon-amount-" + productid).text(0);
                    if(data[0] == 1) {

                        $("#total-ShopCoupon-Amount").val(data[2]);
                        $('#use-shop-coupon-amount-'+ productid).data('val', 0);
                        $('#product-final-amount-'+ productid).data('val', data[1]["product_final_amount"]);
                        $('#product-final-amount-'+ productid).text(thousands_separators(data[1]["product_final_amount"]));
                        $('#grandtotal').val(data[6]['total_cost_amount']);
                        $('#coupon_discount').val(data[6]['coupon_amount']);
                        $('#shipping-cost').val(data[7]);
                        if(pos == 0){
                            $("#totalShopCouponAmount").text($('#curr-sign').val() + thousands_separators(Math.round(data[2])));
                            $('#discount').html($('#curr-sign').val()+thousands_separators(data[6]['coupon_amount']));
                            $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
                        }
                        else{
                            $("#totalShopCouponAmount").text(thousands_separators(Math.round(data[2])) + $('#curr-sign').val());
                            $('#discount').html(thousands_separators(data[6]['coupon_amount']) + $('#curr-sign').val());
                            $(".customer_shippingcost1").text(thousands_separators(Number(data[7])) + $('#curr-sign').val());
                        }
                        calFinalPrice();
                        // toastr.success('Coupon Cleared!');
                    }
                    else if(data[0] == 0) {
                        toastr.error('error');
                    }
                });
            }
            else{
                var url = mainurl+'/cart/coupon/apply/'+productid+'/'+coupon_code;
                $.get(url, {province_id:province_id,district_id:district_id,is_online_payment:is_online_payment, product_size_color_id:product_size_color_id} , function( data ) {
                    var totalShopCouponAmount = 0;
                    if(data[0] == 1) {
                        $("#use-shop-coupon-amount-" + productid).text(thousands_separators(Math.round(data[1]["shop_coupon_amount"])));

                        $("#total-ShopCoupon-Amount").val(data[2]);
                        $('#use-shop-coupon-amount-'+ productid).data('val',data[1]["shop_coupon_amount"]);
                        $('#product-final-amount-'+ productid).data('val', data[1]["product_final_amount"]);
                        $('#product-final-amount-'+ productid).text(thousands_separators(data[1]["product_final_amount"]));
                        totalShopCouponAmount = Math.round(data[2]);
                    }
                    else if(data[0] == 0) {
                        $("#use-shop-coupon-amount-" + productid).text(0);
                        $("#total-ShopCoupon-Amount").val(data[2]);
                        $('#use-shop-coupon-amount-'+ productid).data('val', 0);
                        $('#product-final-amount-'+ productid).data('val', data[1]["product_final_amount"]);
                        $('#product-final-amount-'+ productid).text(thousands_separators(data[1]["product_final_amount"]));
                        totalShopCouponAmount = Math.round(data[2]);
                    }
                    $('#discount').html($('#curr-sign').val()+thousands_separators(data[6]['coupon_amount']));
                    $('#grandtotal').val(data[6]['total_cost_amount']);
                    $('#coupon_discount').val(data[6]['coupon_amount']);
                    $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));

                    if(pos == 0){
                        $("#totalShopCouponAmount").text($('#curr-sign').val() + thousands_separators(totalShopCouponAmount));
                        $('#discount').html($('#curr-sign').val()+thousands_separators(data[6]['coupon_amount']));
                        $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
                    }
                    else{
                        $("#totalShopCouponAmount").text(thousands_separators(totalShopCouponAmount) + $('#curr-sign').val());
                        $('#discount').html(thousands_separators(data[6]['coupon_amount']) + $('#curr-sign').val());
                        $(".customer_shippingcost1").text(thousands_separators(Number(data[7])) + $('#curr-sign').val());
                    }

                    $('#shipping-cost').val(data[7]);
                    calFinalPrice();
                });
            }
        });

        $(".check-shop-coupon").on( "click", function() {
            var productid = $(this).data('val');
            var product_size_color_id = $(this).data('sizecolor');
            var coupon_code = $('#use-shop-coupon-code-' + productid).val();
            var province_id = $('.customer_province').val();
            var district_id = $('.customer_district').val();
            if($('.is_shipdiff').val() == 'true'){
                province_id = $('.shipping_province').val();
                district_id = $('.shipping_district').val();
            }
            var is_online_payment = $('.is-online-payment').val();
            if(coupon_code == ''){
                var url = mainurl+'/cart/coupon/clear/'+productid;
                $.get(url, {province_id:province_id,district_id:district_id,is_online_payment:is_online_payment, product_size_color_id:product_size_color_id} , function( data ) {
                    $("#use-shop-coupon-amount-" + productid).text(0);
                    if(data[0] == 1) {

                        $("#total-ShopCoupon-Amount").val(data[2]);
                        $('#use-shop-coupon-amount-'+ productid).data('val', 0);
                        $('#product-final-amount-'+ productid).data('val', data[1]["product_final_amount"]);
                        $('#product-final-amount-'+ productid).text(thousands_separators(data[1]["product_final_amount"]));
                        $('#grandtotal').val(data[6]['total_cost_amount']);
                        $('#coupon_discount').val(data[6]['coupon_amount']);
                        $('#shipping-cost').val(data[7]);
                        if(pos == 0){
                            $("#totalShopCouponAmount").text($('#curr-sign').val() + thousands_separators(Math.round(data[2])));
                            $('#discount').html($('#curr-sign').val()+thousands_separators(data[6]['coupon_amount']));
                            $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
                        }
                        else{
                            $("#totalShopCouponAmount").text(thousands_separators(Math.round(data[2])) + $('#curr-sign').val());
                            $('#discount').html(thousands_separators(data[6]['coupon_amount']) + $('#curr-sign').val());
                            $(".customer_shippingcost1").text(thousands_separators(Number(data[7])) + $('#curr-sign').val());
                        }
                        calFinalPrice();
                        // toastr.success('Coupon Cleared!');
                    }
                    else if(data[0] == 0) {
                        toastr.error('error');
                    }
                });
            }
            else{
                var url = mainurl+'/cart/coupon/apply/'+productid+'/'+coupon_code;
                $.get(url, {province_id:province_id,district_id:district_id,is_online_payment:is_online_payment, product_size_color_id:product_size_color_id} , function( data ) {
                    var totalShopCouponAmount = 0;
                    if(data[0] == 1) {
                        $("#use-shop-coupon-amount-" + productid).text(thousands_separators(Math.round(data[1]["shop_coupon_amount"])));

                        $("#total-ShopCoupon-Amount").val(data[2]);
                        $('#use-shop-coupon-amount-'+ productid).data('val',data[1]["shop_coupon_amount"]);
                        $('#product-final-amount-'+ productid).data('val', data[1]["product_final_amount"]);
                        $('#product-final-amount-'+ productid).text(thousands_separators(data[1]["product_final_amount"]));
                        totalShopCouponAmount = Math.round(data[2]);
                        toastr.success('Mã giảm giá được áp dụng!');
                    }
                    else if(data[0] == 0) {
                        $("#use-shop-coupon-amount-" + productid).text(0);
                        $("#total-ShopCoupon-Amount").val(data[2]);
                        $('#use-shop-coupon-amount-'+ productid).data('val', 0);
                        $('#product-final-amount-'+ productid).data('val', data[1]["product_final_amount"]);
                        $('#product-final-amount-'+ productid).text(thousands_separators(data[1]["product_final_amount"]));
                        totalShopCouponAmount = Math.round(data[2]);
                        toastr.error('Mã giảm giá đã hết hạn!');
                    }
                    $('#discount').html($('#curr-sign').val()+thousands_separators(data[6]['coupon_amount']));
                    $('#grandtotal').val(data[6]['total_cost_amount']);
                    $('#coupon_discount').val(data[6]['coupon_amount']);
                    $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));

                    if(pos == 0){
                        $("#totalShopCouponAmount").text($('#curr-sign').val() + thousands_separators(totalShopCouponAmount));
                        $('#discount').html($('#curr-sign').val()+thousands_separators(data[6]['coupon_amount']));
                        $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data[7])));
                    }
                    else{
                        $("#totalShopCouponAmount").text(thousands_separators(totalShopCouponAmount) + $('#curr-sign').val());
                        $('#discount').html(thousands_separators(data[6]['coupon_amount']) + $('#curr-sign').val());
                        $(".customer_shippingcost1").text(thousands_separators(Number(data[7])) + $('#curr-sign').val());
                    }

                    $('#shipping-cost').val(data[7]);
                    calFinalPrice();
                });
            }
        });
        //shop coupon check ends
        

        //load district
        $('.customer_province').on('change',function(e){
            $(".customer_district").empty();
            $(".customer_ward").empty();

            if($('.is_shipdiff').val() == 'false'){
                if(pos == 0){
                    $(".customer_shippingcost1").text($('#curr-sign').val()+0);
                }
                else{
                    $(".customer_shippingcost1").text(0 + $('#curr-sign').val());
                }

                $(".shipping_viettelpost").val(0);
                //$('.mybtn1').addClass('btn btn-secondary');
                console.log("change 1");
                // $('.mybtn1').prop('disabled',true);
                mship = $('.shipping').val();
                shipping_change(mship);
            }


            $('.city-default').val($('.customer_province option:selected').text().trim());
            var sldistrict = $(".customer_district")[0];
            var option = document.createElement("option");
            option.text = "Chọn Quận/Huyện";
            option.value = "";
            sldistrict.add(option);

            var slward = $(".customer_ward")[0];
            var option_ward = document.createElement("option");
            option_ward.text = "Chọn Phường/Xã";
            option_ward.value = "";
            slward.add(option_ward);
            fillDistrict(($(this).val()), sldistrict);
        });

        function fillDistrict(province_id, district_select){
            var url = mainurl+'/districts/'+province_id;
            $.ajax({
                type:"GET",
                   url:url,
                   data:{},
                   success:function(data)
                   {
                      if ((data.errors)) {
                        console.log(data.errors);
                      }
                      else
                      {
                        data.sort(function(a, b) {
                            var x = a.name.toLowerCase(), y = b.name.toLowerCase();
                            return x < y ? -1 : x > y ? 1 : 0;
                        });
                        $.each(data, function( i, val ) {
                            var opt = document.createElement("option");
                            opt.text = val.name;
                            opt.value = val.id;
                            district_select.add(opt);
                        });
                      }
                   }
            });
        }

        //shipping cost calculation
        $('.customer_district').on('change',function(e){
            $(".customer_ward").empty();
            var slward = $(".customer_ward")[0];
            var option_ward = document.createElement("option");
            option_ward.text = "Chọn Phường/Xã";
            option_ward.value = "";
            slward.add(option_ward);
            var province_id = $('.customer_province').val();
            var district_id = $(this).val();
            if(district_id > 0)
                fillWard(district_id, slward);
            if($('.is_shipdiff').val() == 'false'){
                fillShippingCost(province_id, district_id);
            }
            
        });

        function fillWard(district_id, ward_select){
            var url1 = mainurl+'/wards/'+district_id;
            $.ajax({
                type:"GET",
                   url:url1,
                   data:{},
                   success:function(data)
                   {
                      if ((data.errors)) {
                        console.log(data.errors);
                      }
                      else
                      {
                        data.sort(function(a, b) {
                            var x = a.name.toLowerCase(), y = b.name.toLowerCase();
                            return x < y ? -1 : x > y ? 1 : 0;
                        });
        
                        $.each(data, function( i, val ) {
                            var opt = document.createElement("option");
                            opt.text = val.name;
                            opt.value = val.id;
                            opt.selected = $("#auth-ward-id").val() == val.id ? 'selected' : '';
                            ward_select.add(opt);
                        });
                      }
                   },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log(XMLHttpRequest);
                        console.log(textStatus);
                        console.log(errorThrown);
                    }
            });
        }

        $('.shipping_province').on('change',function(e){
            $(".shipping_district").empty();
            $(".shipping_ward").empty();
        
            if($('.is_shipdiff').val() == 'true'){
                if(pos == 0){
                    $(".customer_shippingcost1").text($('#curr-sign').val()+0);
                }
                else{
                    $(".customer_shippingcost1").text(0 + $('#curr-sign').val());
                }
                $(".shipping_viettelpost").val(0);
                //$('.mybtn1').addClass('btn btn-secondary');
                console.log("change 2");
                // $('.mybtn1').prop('disabled',true);
                mship = $('.shipping').val();
                shipping_change(mship);
            }
        
            $('#shipping_city').val($('.shipping_province option:selected').text().trim());
            var sldistrict = $(".shipping_district")[0];
            var option = document.createElement("option");
            option.text = "Chọn Quận/Huyện";
            option.value = "";
            sldistrict.add(option);
        
            var slward = $(".shipping_ward")[0];
            var option_ward = document.createElement("option");
            option_ward.text = "Chọn Phường/Xã";
            option_ward.value = "";
            slward.add(option_ward);
            fillDistrict(($(this).val()), sldistrict);
        });

        $('.shipping_district').on('change',function(e){

            $(".shipping_ward").empty();
            var slward = $(".shipping_ward")[0];
            var option_ward = document.createElement("option");
            option_ward.text = "Chọn Phường/Xã";
            option_ward.value = "";
            slward.add(option_ward);
        
            var province_id = $('.shipping_province').val();
            var district_id = $(this).val();
        
            if($('.is_shipdiff').val() == 'true'){
                fillShippingCost(province_id, district_id);
            }
            if(district_id > 0)
                fillWard(district_id, slward);
        
        });

        $('.checkoutform').on('submit',function(e){
            if(ck == 0) {
                e.preventDefault();
                $('#pills-step2-tab').removeClass('disabled');
                $('#pills-step2-tab').click();
        
                }else {
                    $('#preloader').show();
                }
                $('#pills-step1-tab').addClass('active');
            });
        
        $('#step1-btn').on('click',function(){
            $('#pills-step1-tab').removeClass('active');
            $('#pills-step2-tab').removeClass('active');
            $('#pills-step3-tab').removeClass('active');
            $('#pills-step2-tab').addClass('disabled');
            $('#pills-step3-tab').addClass('disabled');
            $('#pills-step1-tab').click();
        
        });

        // Step 2 btn DONE

        $('#step2-btn').on('click',function(){
            $('#pills-step3-tab').removeClass('active');
            $('#pills-step1-tab').removeClass('active');
            $('#pills-step2-tab').removeClass('active');
            $('#pills-step3-tab').addClass('disabled');
            $('#pills-step2-tab').click();
            $('#pills-step1-tab').addClass('active');

        });

        $('#step3-btn').on('click',function(){
            if($('#total-SP-Diff').val() < 0){
                toastr.error('Số dư ví shopping point không đủ!');
                return;
            }
            if($('a.payment:first').data('val') == 'paystack'){
                $('.checkoutform').prop('id','step1-form');
            }
            else {
                $('.checkoutform').prop('id','');
            }
            $('#pills-step3-tab').removeClass('disabled');
            $('#pills-step3-tab').click();

            var shipping_user  = !$('input[name="shipping_name"]').val() ? $('input[name="name"]').val() : $('input[name="shipping_name"]').val();
            var shipping_location  = !$('input[name="shipping_address"]').val() ? $('input[name="address"]').val() : $('input[name="shipping_address"]').val();
            var shipping_phone = !$('input[name="shipping_phone"]').val() ? $('input[name="phone"]').val() : $('input[name="shipping_phone"]').val();
            var shipping_email= !$('input[name="shipping_email"]').val() ? $('input[name="email"]').val() : $('input[name="shipping_email"]').val();

            $('#shipping_user').html('<i class="fas fa-user"></i>'+shipping_user);
            $('#shipping_location').html('<i class="fas fas fa-map-marker-alt"></i>'+shipping_location);
            $('#shipping_phone').html('<i class="fas fa-phone"></i>'+shipping_phone);
            $('#shipping_email').html('<i class="fas fa-envelope"></i>'+shipping_email);

            $('#pills-step1-tab').addClass('active');
            $('#pills-step2-tab').addClass('active');
        });

        $('#final-btn').on('click',function(){
            ck = 1;
        })

        $('.payment').on('click',function(){
            if($(this).data('val') == 'paystack'){
                $('.checkoutform').prop('id','step1-form');
            }
            else {
                $('.checkoutform').prop('id','');
            }
            $('.checkoutform').prop('action',$(this).data('form'));
            $('.pay-area #v-pills-tabContent .tab-pane.fade').not($(this).attr('href')).html('');
            var show = $(this).data('show');
            if(show != 'no') {
                $('.pay-area').removeClass('d-none');
            }
            else {
                $('.pay-area').addClass('d-none');
            }
            $($(this).attr('href')).load($(this).data('href'));
    
            if($(this).data('val') == 'cod'){
                $('.is-online-payment').val(0);
            }
            else{
                $('.is-online-payment').val(1);
            }
    
            var province_id = $('.customer_province').val();
            var district_id = $('.customer_district').val();
    
            if($('.is_shipdiff').val() == 'true'){
                province_id = $('.shipping_province').val();
                district_id = $('.shipping_district').val();
            }
    
            fillShippingCost(province_id, district_id);
        });

        $(document).on('submit','#step1-form',function(){
            $('#preloader').hide();
            var val = $('#sub').val();
            var total = $('#grandtotal').val();
            total = Math.round(total);
            if(val == 0)
            {
                return false;
            }
            else {
                $('#preloader').show();
                return true;
            }
        });

        

        // #region define function

        function fillShippingCost(province_id, district_id){

            if ($("#shipping-viettelpost").is(":checked")) {
        
                $('#shipping-type').val('viettelpost');
                $('#v-pills-tab3-tab').removeClass('d-none');
                if(province_id > 0 && district_id > 0)
                    getViettelPostShippingCost(province_id, district_id);
            }
            else if ($("#shipping-negotiate").is(":checked")) {
                if(pos == 0){
                    $(".customer_shippingcost1").text($('#curr-sign').val()+0);
                }
                else{
                    $(".customer_shippingcost1").text(0 + $('#curr-sign').val());
                }
                $('#shipping-type').val('negotiate');
        
                if($('.is-online-payment').val() == 0){
                    $('#final-btn').prop('disabled',true);
                    $('.is-online-payment').val(1);
                    $('.payment').removeClass('active');
                }
                else{
                    $('#final-btn').prop('disabled',false);
                }
                $('#v-pills-tab3-tab').addClass('d-none');
                shipping_change(0);
            }
            else{
                $('.is-online-payment').val(1);
                $('#shipping-type').val('negotiate');
                shipping_change(0);
            }
        }//end shipping cost calculation

        function getViettelPostShippingCost(province_id, district_id){
            if($('#digital').val() != 0){
                $(".shipping_viettelpost").val(0);
                shipping_change(0);
                return;
            }
            var tax = $('#gs_tax').val();
            var coupon_discount = $('#coupon_discount').val();
            tax = tax ? tax : 0;
            coupon_discount = coupon_discount ? coupon_discount : 0;
            var is_online_payment = $('.is-online-payment').val();
            var url = mainurl+'/viettelpost/fee/'+province_id+'/'+district_id+'/'+tax+'/'+coupon_discount+'/'+is_online_payment;
            console.log("change 3");
            $('.mybtn1').prop('disabled',true);
            console.log( url);
            $.ajax({
                type:"GET",
                   url:url,
                   data:{},
                   success:function(data)
                   {
                      if ((data.errors)) {
                        console.log(data.errors);
                        toastr.error('Lỗi: '+data.errors+'! Hệ thống gặp sự cố!');
                      }
                      else
                      {
                        if(pos == 0){
                            $(".customer_shippingcost1").text($('#curr-sign').val() + thousands_separators(Number(data)));
                        }
                        else{
                            $(".customer_shippingcost1").text(thousands_separators(Number(data)) + $('#curr-sign').val());
                        }
        
                        $(".shipping_viettelpost").val(data);
                        mship = $('.shipping').val();
                        shipping_change(mship);
                        if(data > 0){
                            console.log("change 4");
                            $('.mybtn1').prop('disabled',false);
                        }
                        else{
                            toastr.error('Lỗi: không thể xác định phí vận chuyển! Vui lòng kiểm tra lại địa chỉ!');
                            //toastr.error('Error: Undefined Shipping Cost! Please select another destination!');
                        }
                      }
                   },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log(XMLHttpRequest);
                        console.log(textStatus);
                        console.log(errorThrown);
                        toastr.error('Lỗi: '+errorThrown+'! Hệ thống gặp sự cố!');
                    }
            });
        }

        // #endregion define function

        //#region init calling
        $('.customer_district').trigger("change");
        //#endregion

    });
});