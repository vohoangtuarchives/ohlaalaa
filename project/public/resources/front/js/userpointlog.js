$(function ($) {
    "use strict";


    $(document).ready(function () {

        var dateToday = new Date();
        var dates =  $( "#from,#to" ).datepicker({
            defaultDate: "+0w",
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            //minDate: dateToday,
            onSelect: function(selectedDate) {
            var option = this.id == "from" ? "minDate" : "maxDate",
            instance = $(this).data("datepicker"),
            date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
            dates.not(this).datepicker("option", option, date);
            }
        });

        var table = $('#geniustable').DataTable({
            ordering: false,
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: mainurl+"/user/pointlog/all/{{ \Carbon\Carbon::now()->format('Y-m-d') }}/{{ \Carbon\Carbon::now()->format('Y-m-d') }}",
            columns: [
                    { data: 'created_at1', name: 'created_at1' },
                    { data: 'reward_point', name: 'reward_point' },
                    { data: 'shopping_point', name: 'shopping_point' },
                    { data: 'amount_bonus', name: 'amount_bonus' },
                    { data: 'exchange_rate', name: 'exchange_rate' },
                    { data: 'descriptions', name: 'descriptions' },
                    { data: 'action', searchable: false, orderable: false }
                    ],
            language : {
                processing: '<img src="'+mainurl+'/assets/images/'+$('#admin_loader').val()+'">'
            },
            drawCallback : function( settings ) {
                    $('.select').niceSelect();
            }
        });

        
        $("#add-find" ).on('click' , function(e){
            var sf = get_date_string($('#from').val());
            var st = get_date_string($('#to').val());
            var status = $('#status').val();
            var url = mainurl+'/user/pointlog/'+status+'/'+sf+'/'+st;
            table.ajax.url( url ).load();
        });

        
    });

});