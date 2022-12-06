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
                                                <a href="{{route('admin-order-report-merchant-performance-summaries-top10byproductcategory')}}">{{ __('Merchant Perfromance Summaries - Top 10 By Product Category') }}</a>
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
                                                    <th style="width:300px">{{ __('Category Name') }}</th>
                                                    <th>{{ __('Total Number of Sales') }}</th>
                                                    <th>{{ __('Total Number of Sales Quantity') }}</th>
                                                    <th>{{ __('Total Number of Sales Amount') }}</th>
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
        pageLength: 10,
        ajax: '{{ route('admin-order-report-merchant-performance-summaries-top10byproductcategory-datatables',[\Carbon\Carbon::now()->format('Y-m-d'),\Carbon\Carbon::now()->format('Y-m-d')]) }}',
        columns: [
                { data: 'category_name', name: 'category_name'},
                { data: 'number_of_sales', name: 'number_of_sales'},
                { data: 'total_qty', name: 'total_qty'},
                { data: 'total_amount', name: 'total_amount'},
                ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }

    });

    $("#add-find" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var st = get_date_string($('#to').val());
        // var status = $('#status').val();
        var url = mainurl+'/admin/orders/reports/merchantperformance/summaries/top10byproductcategory/datatables/'+sf+'/'+st;
        console.log('url',url);
        table.ajax.url( url ).load();
    });

    $("#export-excel" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var st = get_date_string($('#to').val());
        // var status = $('#status').val();
        var url = mainurl+'/admin/orders/reports/merchantperformance/summaries/top10byproductcategory/export/'+sf+'/'+st;
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
