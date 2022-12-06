$(function ($) {
    "use strict";


    $(document).ready(function () {

        var table = $('#geniustable').DataTable({
            ordering: false,
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('user-point-logs-datatable',['all']) }}",
            columns: [
                    { data: 'created_at1', name: 'created_at1' },
                    // { data: 'log_type', name: 'log_type' },
                    { data: 'reward_point', name: 'reward_point' },
                    { data: 'shopping_point', name: 'shopping_point' },
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
            console.log('find handled');
            var df = new Date(1900,0,1);
            if($('#from').val() != ''){
                df = new Date($('#from').val());
            }
            const yef = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(df);
            const mof = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(df);
            const daf = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(df);
            var sf = `${yef}-${mof}-${daf}`;
    
            var dt = new Date();
            if($('#to').val() != ''){
                dt = new Date($('#to').val());
            }
            const yet = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(dt);
            const mot = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(dt);
            const dat = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(dt);
            var st = `${yet}-${mot}-${dat}`;
    
            var status = $('#status').val();
            table.destroy();
            var url = mainurl+'/user/pointlog/'+status+'/'+sf+'/'+st;
            console.log(url);
            table = $('#geniustable').DataTable({
                ordering: false,
                processing: true,
                destroy: true,
                serverSide: true,
                ajax: url,
                columns: [
                    { data: 'created_at', name: 'created_at' },
                    // { data: 'log_type', name: 'log_type' },
                    { data: 'reward_point', name: 'reward_point' },
                    { data: 'shopping_point', name: 'shopping_point' },
                    // { data: 'exchange_rate', name: 'exchange_rate' },
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
        });

        
    });

});