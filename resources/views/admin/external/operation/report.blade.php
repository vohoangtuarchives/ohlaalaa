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
    .ui-datepicker-month{
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
        <h4 class="heading">{{ __('Operation Report') }}</h4>
        <ul class="links">
        <li>
            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
        </li>
        <li>
            <a href="javascript:;">{{ __('External') }}</a>
        </li>
        <li>
            <a href="{{route('admin-operation-report')}}">{{ __('Operation Report') }}</a>
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



                <label for="datepicker3"><b>Select Year</b></label>
                <input type="text" class="form-control-sm" id="from"  name="from_date" autocomplete="off"
                placeholder="(year only)" />


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
                            <th>{{ __('Total User') }}
                                <br><code name="year_s">From {{  date('Y') }}</code>
                            </th>
                            <th>{{ __('Total Shop') }}</th>
                            <th>{{ __('Shop ') }}
                                <br><code name="year_s">From {{  date('Y') }}</code>
                            </th>
                            <th>{{ __('Total Order') }}
                                <br><code name="year_s">From {{  date('Y') }}</code>
                            </th>
                            <th>{{ __('Order Complete') }}
                                <br><code name="year_s">From {{  date('Y') }}</code>
                            </th>
                            <th>{{ __('Order Declined') }}
                                <br><code name="year_s">From {{  date('Y') }}</code>
                            </th>
                            <th>{{ __('Order Delivery') }}
                                <br><code name="year_s">From {{  date('Y') }}</code>
                            </th>
                            <th>{{ __('Order Pending') }}
                                <br><code name="year_s">From {{  date('Y') }}</code>
                            </th>
                            <th>{{ __('Total Amount Order Completed') }}
                                <br><code name="year_s">From {{  date('Y') }}</code>
                            </th>

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
        ajax: "{{ route('get-admin-operation-report',date('Y')) }}",
        columns: [
                { data: 'user_total', name: 'user_total'}, //1
                { data: 'shop_total', name: 'shop_total'},//2
                { data: 'shop_date', name: 'shop_date'},//3
                { data: 'order_total', name: 'order_total'},//4
                { data: 'order_complete', name: 'order_complete'},//5
                { data: 'order_declined', name: 'order_declined'},//5
                { data: 'order_delivery', name: 'order_delivery'},//5
                { data: 'order_pending', name: 'order_pending'},//5
                { data: 'total_amount', name: 'total_amount'},//5
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
        var  y = $("code[name='year_s']");
        var currentTime = new Date()
        var from ='From ' + sf;
        if (sf <  currentTime.getFullYear()) {
            from += ' - ' + currentTime.getFullYear();
        }
        y.html(from);
        var url = mainurl+'/admin/external/operation/report/datatables/'+sf;
        table.ajax.url( url ).load();

    });

    $("#export-excel" ).on('click' , function(e){
        //var sf = get_date_string($('#from').val());
        var sf = $('#from').val();
        console.log(sf);
        var url = mainurl+'/admin/external/operation/report/export/'+sf;
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
        changeMonth: false,
        yearRange: "-10:+0",
        changeYear: true,
        showButtonPanel: true,
        dateFormat: 'yy',
        closeText:'Select',
        currentText: 'This year',
        autoclose:true, //to close picker once year is selected
        onClose: function(dateText, inst) {
            var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            $(this).val($.datepicker.formatDate("yy", new Date(year, 0, 1)));
        },

        beforeShow : function(input, inst) {
            if ($(this).val()!=''){
                var tmpyear = $(this).val();
                $(this).datepicker('option','defaultDate',new Date(tmpyear, 0, 1));
            }
        }
    })

    $("#from").datepicker("setDate", new Date());


    $(function () {
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
