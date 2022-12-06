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
                                                <a href="{{route('admin-user-report-sale-report-summaries-top10rpearningbymerchantbonus')}}">{{ __('Sale Report Summaries Top 10 RP Earning By Merchant Bonus') }}</a>
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
                                                    <th>{{ __('Customer Name') }}</th>
                                                    <th>{{ __('Rank') }}</th>
                                                    <th>{{ __('Joined Date') }}</th>
                                                    <th>{{ __('Total Direct Downline (L1)') }}</th>
                                                    <th>{{ __('RP Earning') }}</th>
                                                    <th>{{ __('Total RP') }}</th>
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
        ajax: '{{ route('admin-user-report-sale-report-summaries-top10rpearningbymerchantbonus-datatables',[\Carbon\Carbon::now()->format('Y-m-d'),\Carbon\Carbon::now()->format('Y-m-d')]) }}',
        columns: [
                { data: 'customer_name', name: 'customer_name'},
                { data: 'ranking', name: 'ranking'},
                { data: 'created_at', name: 'created_at'},
                { data: 'l1_count', name: 'l1_count'},
                { data: 'total_reward_point', name: 'total_reward_point'},
                { data: 'reward_point', name: 'reward_point'},
                ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }

    });

    $("#add-find" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var st = get_date_string($('#to').val());
        // var status = $('#status').val();
        var url = mainurl+'/admin/users/reports/salereport/summaries/top10rpearningbymerchantbonus/datatables/'+sf+'/'+st;
        console.log('url',url);
        table.ajax.url( url ).load();
    });

    $("#export-excel" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var st = get_date_string($('#to').val());
        // var status = $('#status').val();
        var url = mainurl+'/admin/users/reports/salereport/summaries/top10rpearningbymerchantbonus/export/'+sf+'/'+st;
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
