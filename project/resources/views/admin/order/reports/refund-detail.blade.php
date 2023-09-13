@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">

@endsection

@section('content')

<input type="hidden" id="headerdata" value="{{ __('ORDER') }}">

                    <div class="content-area">
                        <div class="mr-breadcrumb">
                            <div class="row">
                                <div class="col-lg-12">
                                        <h4 class="heading">{{ __('Reports') }}</h4>
                                        <ul class="links">
                                            <li>
                                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                                            </li>
                                            <li>
                                                <a href="javascript:;">{{ __('Reports') }}</a>
                                            </li>
                                            <li>
                                                <a href="{{route('admin-order-report-refund-detail')}}">{{ __('Order Refund Detail') }}</a>
                                            </li>
                                        </ul>
                                </div>
                            </div>
                        </div>
                        <div class="product-area">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mr-table allproduct">
                                        @include('includes.admin.form-success')
                                        <div class="table-responsiv">
                                        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                                        <label for="from">{{ __('From') }}: </label>
                                        <input type="text" class="form-control-sm" name="from_date" id="from" placeholder="{{ __('Select a date') }}" value="{{ $now }}" autocomplete="off" style="width: 100px;">
                                        <label for="to">{{ __('To') }}: </label>
                                        <input type="text" class="form-control-sm" name="to_date" id="to" placeholder="{{ __('Select a date') }}"  value="{{ $now }}" autocomplete="off" style="width: 100px;">
                                        <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> {{ __('Find') }} </a>
                                        <a class="add-btn" id="export-excel" > <i class="fas fa-file-excel"></i> {{ __('Export') }} </a>
                                        <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th style="width:100px">{{ __('Oder Date') }}</th>
                                                    <th>{{ __('Order Number') }}</th>
                                                    <th>{{ __('Order Amount') }}</th>
                                                    <th>{{ __('Shipping Cost') }}</th>
                                                    <th>{{ __('Shop Name') }}</th>
                                                    <th>{{ __('Shop Email') }}</th>
                                                    <th>{{ __('Refund To') }}</th>
                                                    <th>{{ __('Refund By') }}</th>
                                                    <th>{{ __('Refund Date') }}</th>
                                                    <th>{{ __('Refund Bank') }}</th>
                                                    <th>{{ __('Refund Amount') }}</th>
                                                    <th>{{ __('Refund Reason') }}</th>
                                                </tr>
                                            </thead>
                                        </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>




@endsection

@section('scripts')



{{-- DATA TABLE --}}

<script type="text/javascript">

    var table = $('#geniustable').DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: '{{ route('admin-order-report-refund-detail-datatables',[\Carbon\Carbon::now()->format('Y-m-d'),\Carbon\Carbon::now()->format('Y-m-d')]) }}',
        columns: [
                { data: 'created_at', name: 'created_at'},
                { data: 'order_number', name: 'order_number'},
                { data: 'pay_amount2', name: 'pay_amount2'},
                { data: 'shipping_cost', name: 'shipping_cost'},
                { data: 'shop_name', name: 'shop_name'},
                { data: 'email', name: 'email'},
                { data: 'refund_to', name: 'refund_to'},
                { data: 'refund_by_name', name: 'refund_by_name'},
                { data: 'refund_date', name: 'refund_date'},
                { data: 'refund_bank', name: 'refund_bank'},
                { data: 'refund_amount', name: 'refund_amount'},
                { data: 'refund_note', name: 'refund_note'},
                ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }

    });

    $("#add-find" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var st = get_date_string($('#to').val());
        // var status = $('#status').val();
        var url = mainurl+'/admin/orders/reports/refunddetail/datatables/'+sf+'/'+st;
        console.log('url',url);
        table.ajax.url( url ).load();
    });

    $("#export-excel" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var st = get_date_string($('#to').val());
        // var status = $('#status').val();
        var url = mainurl+'/admin/orders/reports/refunddetail/export/'+sf+'/'+st;
        window.open(url, '_blank');
    });
</script>
{{-- DATA TABLE --}}

<script type="text/javascript">
    var dateToday = new Date();
    var dates =  $( "#from,#to" ).datepicker({
        defaultDate: "+0w",
        changeMonth: true,
        changeYear: true,
        //minDate: dateToday,
        onSelect: function(selectedDate) {
        var option = this.id == "from" ? "minDate" : "maxDate",
          instance = $(this).data("datepicker"),
          date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
          dates.not(this).datepicker("option", option, date);
        }
    });


</script>



@endsection
