$(function ($) {
    "use strict";


    $(document).ready(function () {

        //#region init properties
        var url = mainurl+'/track/membership/notification';
        $.ajax({
            type:"GET",
            url:url,
            data:{},
            success:function(data)
            {
                console.log(data);
            }
        });

    });
});