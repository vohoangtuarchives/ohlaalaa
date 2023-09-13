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
                                                <a href="{{route('admin-user-report-kol-affiliatebonus')}}">{{ __('KOL Affiliate Bonus') }}</a>
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
                                        <label for="include-renew">Include Renew: </label>
                                        <select id="include-renew" style="display: inline; width: 100px;" >
                                            <option value="0" selected>No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                        <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> {{ __('Find') }} </a>
                                        <a class="add-btn" id="export-excel" > <i class="fas fa-file-excel"></i> {{ __('Export') }} </a>
                                        <a class="add-btn" id="process-pay" data-toggle="modal" data-target="#confirm-verify" data-href="123href" > <i class="fab fa-cc-amazon-pay"></i> {{ __('Process Pay') }} </a>
                                        <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Id') }}</th>
                                                    <th>{{ __('Payment Number') }}</th>
                                                    <th>{{ __('L1 Info') }}</th>
                                                    <th>{{ __('Beneficiary') }}</th>
                                                    <th>{{ __('Beneficiary Bank') }}</th>
                                                    <th>{{ __('Package Price') }}</th>
                                                    <th>{{ __('Bonus') }}</th>
                                                </tr>
                                            </thead>
                                        </table>
                                        </div>
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
        ajax: '{{ route('admin-user-report-kol-affiliatebonus-datatables',['2021-12-01', 0]) }}',
        columns: [
                { data: 'mpr_id', name: 'mpr_id'},
                { data: 'payment_number', name: 'payment_number'},
                { data: 'consumer_info', name: 'consumer_info'},
                { data: 'l1_info', name: 'l1_info'},
                { data: 'l1_bank_info', name: 'l1_bank_info'},
                { data: 'package_price', name: 'package_price'},
                { data: 'bonus', name: 'bonus'}
                ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }

    });

    $("#add-find" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var include_new = $('#include-renew').val();
        var url = mainurl+'/admin/users/reports/kolaffiliatebonus/datatables/'+sf+'/'+include_new;
        console.log('url',url);
        table.ajax.url( url ).load();
    });

    $("#export-excel" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var include_new = $('#include-renew').val();
        var url = mainurl+'/admin/users/reports/kolaffiliatebonus/export/'+sf+'/'+include_new;
        window.open(url, '_blank');
    });

    $(document).on('click', '#process-pay', function(e){
        var sf = get_date_string($('#from').val());
        var include_new = $('#include-renew').val();
        var url = mainurl+'/admin/users/reports/kolaffiliatebonus/processpay/'+sf+'/'+include_new;
        $('.btn-ok').attr('href', url);
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
