$(function ($) {
    "use strict";


    $(document).ready(function () {

        var url = mainurl+'/user/track/membership/notification';
        $.ajax({
            type:"GET",
            url:url,
            data:{},
            success:function(data)
            {
                if(data[0]){
                    $('#modal1').modal('show');
                    $('#modal1').find('.modal-title').html('GIA HẠN THÀNH VIÊN');
                    $('#modal1').find('.package-remain-day').html(data[1]);
                    
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(XMLHttpRequest);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    });

    
});
