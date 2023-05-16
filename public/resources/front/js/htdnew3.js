$(function ($) {
    "use strict";


    $(document).ready(function () {

        /*
            favorite
            message.index
            ticket.index
        */

        $('#confirm-delete').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        });


        /*
            order-track
        */
        $('#t-form').on('submit',function(e){
            e.preventDefault();
            var code = $('#code').val();
            $('#order-track').load(mainurl + '/user/order/trackings/'+code);
            $('#order-tracking-modal').modal('show');
        });

        /*
            send-shopping-point
        */
        $('#btnCheckEmail').on('click',function(){
            var phone = $('#phone-number').val();
            $('#shopping-point-area').addClass('d-none');
            var url = mainurl+'/user/sp/sending'+'/'+phone;
            $.get(url , function( data ) {
                if(data['status'] == 1){
                    if(data['value'] == true){
                        toastr.success('Số điện thoại hợp lệ!');
                        $('#shopping-point-area').removeClass('d-none');
                    }
                    else{
                        toastr.error('Số điện thoại không đúng!');
                    }
                }
                else{
                    toastr.error('Số điện thoại không đúng!');
                }
            });
        });

        $('#sending-shopping-point').on('change',function(){
            var sp = parseInt($('#shopping-point').data('val'));
            var sp_transfer = $(this).val();
            var sp_temp = sp_transfer == '' ? 0 : sp_transfer;
            if(sp_temp < 0){
                sp_temp = 0;
            }
            else
            {
                sp_temp = sp_temp > sp ? sp : sp_temp;
            }
            if(sp_temp!= sp_transfer){
                sp_transfer = sp_temp;
                $(this).val(sp_temp);
            }
            var sp_diff = sp - sp_transfer;
            $('#shopping-point-diff').val(thousands_separators(Math.round(sp_diff)));
        });
        
        $('.btn-ok1').on('click',function(){
            $("#userform1").submit();
        });

        // send-shopping-point ENDS


        /*
            wishlist
        */
        $("#sortby").on('change',function () {
            var sort = $("#sortby").val();
            window.location = mainurl+"/user/wishlists?sort="+sort;
        });

        /*
            order.details
        */
        $(document).on("click", "#tid", function (e) {
            $(this).hide();
            $("#tc").show();
            $("#tin").show();
            $("#tbtn").show();
        });
        $(document).on("click", "#tc", function (e) {
            $(this).hide();
            $("#tid").show();
            $("#tin").hide();
            $("#tbtn").hide();
        });
        $(document).on("submit", "#tform", function (e) {
            var oid = $("#oid").val();
            var tin = $("#tin").val();
            $.ajax({
                type: "GET",
                url: "{{URL::to('user/json/trans')}}",
                data: {
                    id: oid,
                    tin: tin
                },
                success: function (data) {
                    $("#ttn").html(data);
                    $("#tin").val("");
                    $("#tid").show();
                    $("#tin").hide();
                    $("#tbtn").hide();
                    $("#tc").hide();
                }
            });
            return false;
        });
        
        $(document).on('click', '#license', function (e) {
            var id = $(this).parent().find('input[type=hidden]').val();
            $('#key').html(id);
        });

        // order.details ENDS

        /*
            package.details
        */
        $(document).on('submit','#subscribe-form',function(){
            $('#preloader').show();
        });

        /*
            ranking.tnc
        */
        $('#tnc').on('change',function(){
            if(this.checked) {
                $('#send-btn').removeClass('disabled');
                $(this).val(1);
            }
            else {
                $('#send-btn').addClass('disabled');
                $(this).val(0);
            }
        });


        /*
            withdraw.affilate-code
        */
        $('#affilate_code_click').on('click',function(){
            var copyText =  document.getElementById("affilate_code");
            /* Select the text field */
            copyText.select();
            copyText.setSelectionRange(0, 99999); /*For mobile devices*/
            /* Copy the text inside the text field */
            document.execCommand("copy");
        
        });

        $('#affilate_click').on('click',function(){
            var copyText =  document.getElementById("affilate_address");
            /* Select the text field */
            copyText.select();
            copyText.setSelectionRange(0, 99999); /*For mobile devices*/
            /* Copy the text inside the text field */
            document.execCommand("copy");
        
        });

        $('#affilate_html_click').on('click',function(){
            var copyText =  document.getElementById("affilate_html");
            /* Select the text field */
            copyText.select();
            copyText.setSelectionRange(0, 99999); /*For mobile devices*/
            /* Copy the text inside the text field */
            document.execCommand("copy");
    
        });

        $('#membership-payment-btn').on('click',function(){
            $('.submit-loader').show();
            $('#userform1').submit();
        });


        $(document).on('click', '.order-received-btn', function (e) {
            var el = $(this);
            var url = $(this).data('href');
            $.ajax({
                type: "GET",
                url: url,
                data: {},
                success: function (data) {
                    if(data){
                        toastr.success('Đã xác nhận!');
                        el.hide();
                    }
                }
            });
        });

        
        
    });

});