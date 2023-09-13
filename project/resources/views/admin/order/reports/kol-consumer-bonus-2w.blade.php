@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">
<style>
    #ui-datepicker-div {
        width:350px;
     }
    .ui-datepicker-calendar {
       display: none;
    }
    .ui-datepicker-prev{
       display: none;
    }
    .ui-datepicker-next{
       display: none;
    }
    .ui-datepicker select.ui-datepicker-month, .ui-datepicker select.ui-datepicker-year {
        width: 50%;
    }
    .fontst {
        font-size:9px
    }
    .disable-click{
        pointer-events:none;
    }
    </style>
@endsection

@section('content')


{{-- @if(Session::has('jsAlert'))
<script type="text/javascript" >
    alert({{ session()->get('jsAlert') }});
</script>
@endif --}}


<input type="hidden" id="headerdata" value="{{ __('ORDER') }}">

<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
        <div class="col-lg-12">
        <h4 class="heading">{{ __('KOL Bonus L2') }}</h4>
        <ul class="links">
        <li>
            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
        </li>
        <li>
            <a href="javascript:;">{{ __('KOL Bonus L2') }}</a>
        </li>
        <li>
            <a href="{{route('admin-order-report-kol-consumerbonus-2w')}}">{{ __('KOL Consumer Bonus L2') }}</a>
        </li>
        </ul>
        </div>
    </div>
</div>


<div id="validation-errors"></div>
<div class="product-area">
    <div class="row">
        <div class="col-lg-12">
            <div class="mr-table allproduct">
                @include('includes.admin.form-success')
                <div class="table-responsiv">
                <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>



                <label for="datepicker3"><b>Select Month and Year</b></label>
                <input type="text" class="form-control-sm" id="from"  name="from_date"
                placeholder="(month and year only)" />


                <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> {{ __('Find') }} </a>
                &nbsp;&nbsp;
                <a class="add-btn" id="export-excel" > <i class="fas fa-file-excel"></i> {{ __('Export') }} </a>
                <a class="add-btn" hidden id="process-pay" data-toggle="modal" data-target="#confirm-verify" data-href="123href" > <i class="fab fa-cc-amazon-pay"></i> {{ __('Process Pay') }} </a>


                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            {{-- <th>{{ __('Oder Id') }}</th>
                            <th>{{ __('Order Number') }}</th>
                            <th>{{ __('L1 Info') }}</th> --}}
                            {{-- <th>{{ __('Beneficiary Bank') }}</th> --}}
                            <th>{{ __('KOL') }}</th>
                            <th>{{ __('Total New Users') }}
                                <br><code class="fontst" id="number_users_l1">(L1 >= {{(isset($config->number_users_l1) ?  $config->number_users_l1 :  "") }})</code>
                                <br><code class="fontst" id="number_users_l2">(L2 >= {{ (isset($config->number_users_l2) ?  $config->number_users_l2 :  "") }})</code>
                            </th>
                            <th>{{ __('Number Orders') }}
                                <br><code class="fontst" id="number_orders_l1">(L1 >= {{ (isset($config->number_orders_l1) ?  $config->number_orders_l1 :  "") }})</code>
                                <br><code class="fontst" id="number_orders_l2">(L2 >= {{ (isset($config->number_orders_l2) ?  $config->number_orders_l2 :  "") }})</code>
                            </th>
                            <th>{{ __('Total Orders') }}<br>{{ __('New Users') }}</th>
                            <th>{{ __('Total Amount') }}<br>{{ __('New Users') }}</th>
                            <th>{{ __('Number Orders') }}<br>{{ __('Existing Users') }}</th>
                            <th>{{ __('Total Amount') }}<br>{{ __('Existing Users') }}</th>
                            <th>{{ __('Total L1 Consumer Amount') }}</th>
                            <th>{{ __('Bonus From L1 Consumer Amount') }}</th>
                            <th>{{ __('KOL Bonus Rate (%)') }}
                                <br><code class="fontst" id="con_bonus_l1">(L1 >= {{ (isset($config->con_bonus_l1) ?  $config->con_bonus_l1 :  "") }})</code>
                                <br><code class="fontst" id="con_bonus_l2">(L2 >= {{ (isset($config->con_bonus_l2) ?  $config->con_bonus_l2 :  "")  }})</code>
                            </th>
                              <th>{{ __('Shop Revenue') }}
                                <br><code class="fontst" id="revenue_l1">(L1 >= {{ (isset($config->revenue_l1) ?  number_format($config->revenue_l1, 0, ',', ','). " đ" : "") }})</code>
                                <br><code class="fontst" id="revenue_l2">(L2 >= {{ (isset($config->revenue_l2) ?  number_format($config->revenue_l2, 0, ',', ','). " đ" : "")  }})</code>
                            </th>

                            <th>{{ __('Number Preferred Shops') }}
                                <br><code style="font-size:8px" id="number_shops_l1">(L1 >= {{(isset($config->number_shops_l1) ?  $config->number_shops_l1 :  "")   }})</code>
                                <br><code style="font-size:8px" id="number_shops_l2">(L2 >= {{ (isset($config->number_shops_l2) ?  $config->number_shops_l2 :  "")  }})</code>
                            </th>
                            <th>{{ __('New Affiliate Member') }}
                                <br><code class="fontst" id="number_affiliate_member_l1">(L1 >= {{ (isset($config->number_affiliate_member_l1) ?  $config->number_affiliate_member_l1 :  "") }})</code>
                                <br><code class="fontst" id="number_affiliate_member_l2">(L2 >= {{ (isset($config->number_affiliate_member_l2) ?  $config->number_affiliate_member_l2 :  "") }})</code>
                            </th>
                            <th>{{ __('Total Affiliate Bonus') }}</th>
                            <th>{{ __('VAT') }}</th>
                            <th>{{ __('Total Bonus') }}</th>
                    </thead>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- confirm MODAL --}}

<div class="modal fade" id="confirm-verify" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

      <div class="modal-header d-block text-center">
          <h4 class="modal-title d-inline-block">{{ __("Confirm") }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
      </div>

        <!-- Modal body -->
        <div class="modal-body">
              <p class="text-center">{{ __("You are about to confirm to Process Pay.") }}</p>
              <p class="text-center">{{ __("Do you want to proceed?") }}</p>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-default" data-dismiss="modal">{{ __("Cancel") }}</button>
              <a class="btn btn-primary btn-ok">{{ __("Proceed") }}</a>
        </div>

      </div>
    </div>
</div>

  {{-- confirm MODAL ENDS --}}

@endsection

@section('scripts')



{{-- DATA TABLE --}}

<script type="text/javascript">
    var table = $('#geniustable').DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: "{{ route('admin-order-report-kol-consumerbonus-datatables-2w',[date('m-Y'), date('m-Y')]) }}",
        columns: [
                { data: 'kol_info', name: 'kol_info'}, //1
                { data: 'total_user', name: 'total_user'},//2
                { data: 'total_order', name: 'total_order'},//3
                { data: 'total_order_user_new', name: 'total_order_user_new'},//4
                { data: 'total_amount_user_new', name: 'total_amount_user_new'},//5
                { data: 'total_order_user_exits', name: 'total_order_user_exits'},//6
                { data: 'total_amount_user_exits', name: 'total_amount_user_exits'},//7
                { data: 'total_amount', name: 'total_amount'},//8
                { data: 'bonus', name: 'bonus'},//9
                { data: 'kol_consumer_bonus_rate', name: 'kol_consumer_bonus_rate'},//10
                { data: 'revenue_total_sales', name: 'revenue_total_sales'},//10
                { data: 'total_new_shop', name: 'total_new_shop'},//11
                { data: 'total_affiliate_member', name: 'total_affiliate_member'},//12
                { data: 'total_affiliate_bonus', name: 'total_affiliate_bonus'},//13
                { data: 'vat', name: 'vat'},//13
                { data: 'total_bonus', name: 'total_bonus'}//14
                ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }

    });


    // $("#add-find" ).on('click' , function(e){
    //     var sf = get_date_string($('#from').val());
    //     var url = mainurl+'/admin/orders/reports/kolconsumerbonus/datatables/'+sf;
    //     console.log('url',url);
    //     table.ajax.url( url ).load();
    // });

    $("#add-find" ).on('click' , function(e) {
        var sf =  $("#from").val();
        var to = $("#from").val();
        var kol_date = $( "#kol_date" );
        var kol_id = $( "#kol_id" );

        var con_bonus_l1        = $( "#con_bonus_l1" );
        var number_orders_l1    = $( "#number_orders_l1" );
        var number_users_l1     = $( "#number_users_l1" );
        var number_shops_l1     = $( "#number_shops_l1" );
        var number_affiliate_member_l1 = $( "#number_affiliate_member_l1" );
        var revenue_l1          = $( "#revenue_l1" );
        var con_bonus_l2        = $( "#con_bonus_l2" );
        var number_orders_l2    = $( "#number_orders_l2" );
        var number_users_l2     = $( "#number_users_l2" );
        var number_shops_l2     = $( "#number_shops_l2" );
        var number_affiliate_member_l2 = $( "#number_affiliate_member_l2" );
        var revenue_l2          = $( "#revenue_l2" );
        $.ajax({
            type:"GET",
            url:"{{ route('admin-get-kol','') }}"+"/"+sf,
            success: function(data){
                $("#export-excel" ).removeClass("disable-click");
                if (Object.keys(data).length > 0) {
                    console.log(data);
                    con_bonus_l1.html('(L1 >= ' + data.con_bonus_l1 + ')');
                    number_orders_l1.html('(L1 >= ' + data.number_orders_l1 + ')');
                    number_users_l1.html('(L1 >= ' + data.number_users_l1 + ')');
                    number_shops_l1.html('(L1 >= ' + data.number_shops_l1 + ')');
                    number_affiliate_member_l1.html('(L1 >= ' + data.number_affiliate_member_l1 + ')');
                    revenue_l1.html('(L2 >= ' +  parseInt(data.revenue_l1).toLocaleString()  + ')');
                    con_bonus_l2.html('(L2 >= ' + data.con_bonus_l2 + ')');
                    number_orders_l2.html('(L2 >= ' + data.number_orders_l2 + ')');
                    number_users_l2.html('(L2 >= ' + data.number_users_l2 + ')');
                    number_shops_l2.html('(L2 >= ' + data.number_shops_l2 + ')');
                    number_affiliate_member_l2.html('(L2 >= ' + data.number_affiliate_member_l2 + ')');
                    revenue_l2.html('(L2 >= ' +  parseInt(data.revenue_l2).toLocaleString() + ')');
                    var url = mainurl+'/admin/orders/reports/kolconsumerbonus/datatables/'+sf+'/'+to;
                    // var url = mainurl+'/admin/orders/reports/kolconsumerbonus/datatables/'+sf;
                    // console.log('url',url);
                    table.ajax.url( url ).load();
                } else {
                    con_bonus_l1.val('');
                    number_orders_l1.val('');
                    number_users_l1.val('');
                    number_shops_l1.val('');
                    number_affiliate_member_l1.val('');
                    revenue_l1.val('');
                    con_bonus_l2.val('');
                    number_orders_l2.val('');
                    number_users_l2.val('');
                    number_shops_l2.val('');
                    number_affiliate_member_l2.val('');
                    revenue_l2.val('');
                    
                };
                $('#validation-errors').html('');
            },
            error: function (data) {
                $('#validation-errors').html('');
                $('#validation-errors').append('<div class="alert alert-danger">'+data.responseJSON.errors+'</div');
                $("#export-excel").addClass("disable-click");
            },
            complete: function(xmlHttp) {
            },

        });
        // var to = $("#from").datepicker("getDate");
        // sf = $.datepicker.formatDate("yy-mm-dd", sf);
        // to = $.datepicker.formatDate("yy-mm-dd", to);
        // sf = $.datepicker.formatDate("mm-yy", sf);
        // to = $.datepicker.formatDate("mm-yy", to);
    });

    $("#export-excel" ).on('click' , function(e){
        //var sf = get_date_string($('#from').val());
        var sf = $('#from').val();
        console.log(sf);
        var url = mainurl+'/admin/orders/reports/kolconsumerbonus2w/export/'+sf;
        window.open(url, '_blank');
        // $('#process-pay').removeAttr("hidden");
    });

    $(document).on('click', '#process-pay', function(e){
        var sf = get_date_string($('#from').val());
        var url = mainurl+'/admin/orders/reports/kolconsumerbonus/processpay/'+sf;
        $('.btn-ok').attr('href', url);
    });

    $('#from').datepicker(
    {
        dateFormat: "mm-yy",
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        onClose: function(dateText, inst) {
            console.log('close');
            function isDonePressed(){
                return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
            }

            if (isDonePressed()){

                var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(year, month, 1)).trigger('change');

                $('.date-picker').focusout()//Added to remove focus from datepicker input box on selecting date
                m =  parseInt(month)+1;
                var d = ('0' + m).slice(-2)+'-'+year;

                // var date = $(this).datepicker("getDate");
                // var year = inst.selectedYear, month = inst.selectedMonth+1;

                // var  daysOfMonth = new Date(year, month,0).getDate();
                // var d = date.getDate();

                // date.setDate(date.getDate() + (daysOfMonth-d));
                // $("#to").datepicker("setDate", date);
            }
        },

        beforeShow : function(input, inst) {
            inst.dpDiv.addClass('month_year_datepicker')

            if ((datestr = $(this).val()).length > 0) {
                year = datestr.substring(datestr.length-4, datestr.length);
                month = datestr.substring(0, 2);
                $(this).datepicker('option', 'defaultDate', new Date(year, month-1, 1));
                $(this).datepicker('setDate', new Date(year, month-1, 1));
                $(".ui-datepicker-calendar").hide();
            }
        }
    })

    $("#from").datepicker("setDate", new Date());


    $(function () {
        // $("#from").datepicker({
        //     dateFormat: 'dd/mm/yy',
        //     defaultDate: new Date(),
        //     changeMonth: true,
        //     changeYear: true,
        //     showButtonPanel: true,
        //     beforeShowDay: check_available_days,
        //     onSelect: function(selectedDate, inst) {
        //         var date = $(this).datepicker("getDate");
        //         var year = inst.selectedYear, month = inst.selectedMonth+1;
        //         // $(this).datepicker('setDate', new Date(year, month, 0));
        //         // $("#noofdays").val(new Date(year, month, 0).getDate())
        //         var  daysOfMonth = new Date(year, month,0).getDate();
        //         var d = date.getDate();

        //         // if (d == 1) {
        //         //     date.setDate(date.getDate() + 14);
        //         //     $("#to").datepicker("setDate", date);
        //         // } else {
        //             date.setDate(date.getDate() + (daysOfMonth-d));
        //             $("#to").datepicker("setDate", date);
        //         // }

        //         //  console.log(selectedDate);
        //         // console.log(date);
        //         // $("#to").datepicker({ dateFormat: "dd/mm/yy" }).datepicker("setDate", "+15d");
        //         // $("#to").datepicker("setDate", date);
        //         // $("#to").datepicker("option", "minDate", selectedDate);
        //         //$("#to").datepicker("option", "maxDate", date);
        //     }
        // });

        // $("#to").datepicker({
        //     dateFormat: 'dd/mm/yy',
        //     changeMonth: true,
        //     changeYear: true
        // })

        var available_formatted_days_list = [1];
        function check_available_days( date )
        {
            var formatted_date = '', ret = [true, "", ""];
            if (date instanceof Date)
            {
                formatted_date = $.datepicker.formatDate( 'mm-dd-yy', date );
            }
            else
            {
                formatted_date = '' + date;
            }
            let currentDay = date.getDate();
            if ( -1 === available_formatted_days_list.indexOf(currentDay) )
            {
                ret[0] = false;
                ret[1] = "date-disabled"; // put yopur custom csc class here for disabled dates
                ret[2] = "Date not available"; // put your custom message here
            }
            return ret;
        }
    });

</script>

@endsection
