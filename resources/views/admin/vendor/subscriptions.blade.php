@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">

@endsection

@section('content')
                    <input type="hidden" id="headerdata" value="{{ __("SUBSCRIPTIONS") }}">
                    <div class="content-area">
                        <div class="mr-breadcrumb">
                            <div class="row">
                                <div class="col-lg-12">
                                        <h4 class="heading">{{ __("Vendor Subscriptions") }}</h4>
                                        <ul class="links">
                                            <li>
                                                <a href="{{ route('admin.dashboard') }}">{{ __("Dashboard") }} </a>
                                            </li>
                                            <li>
                                                <a href="javascript:;">{{ __("Vendors") }}</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin-vendor-subs') }}">{{ __("Vendor Subscriptions") }}</a>
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
                                            <label for="from">From: </label>
                                            <input type="text" class="form-control-sm" name="from_date" id="from" placeholder="{{ __('Select a date') }}" value="{{ $now }}" autocomplete="off">
                                            <label for="to">To: </label>
                                            <input type="text" class="form-control-sm" name="to_date" id="to" placeholder="{{ __('Select a date') }}"  value="{{ $now }}" autocomplete="off">
                                            <label for="status">Status: </label>
                                            <select id="status" style="display: inline; width: 150px;" >
                                                <option value="-1" selected>All</option>
                                                <option value="1">Pending</option>
                                                <option value="2" >Approved</option>
                                                <option value="3" >Rejected</option>
                                            </select>
                                            <label for="plan">Plan: </label>
                                            <select id="plan" style="display: inline; width: 150px;" >
                                                <option value="-1" selected>All</option>
                                                <option value="1">Yearly Plan</option>
                                                <option value="0">Shop Experience</option>
                                            </select>
                                            <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> Find </a>
                                            <a class="add-btn" id="export-excel" > <i class="fas fa-file-excel"></i> Export </a>
                                            <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th style="width:200px">{{ __("Vendor Name") }}</th>
                                                        <th>{{ __("Email") }}</th>
                                                        <th>{{ __("Phone") }}</th>
                                                        <th>{{ __("Aff. Code") }}</th>
                                                        <th>{{ __("Plan") }}</th>
                                                        <th>{{ __("Submit Date") }}</th>
                                                        <th>{{ __("Current End Date") }}</th>
                                                        <th>{{ __("Renewal End Date") }}</th>
                                                        <th>{{ __("Approved At") }}</th>
                                                        <th>{{ __("Rejected At") }}</th>
                                                        <th>{{ __("Status") }}</th>
                                                        <th>{{ __("Options") }}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

{{-- ADD / EDIT MODAL --}}

            <div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">

                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="submit-loader">
                                <img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
                            </div>
                            <div class="modal-header">
                                <h5 class="modal-title"></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __("Close") }}</button>
                            </div>
                        </div>
                    </div>

                </div>

{{-- ADD / EDIT MODAL ENDS --}}


{{-- REJECT MODAL --}}

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
      <div class="submit-loader">
              <img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
      </div>
      <div class="modal-header d-block text-center">
          <h4 class="modal-title d-inline-block">{{ __('Confirm Reject') }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
      </div>

        <!-- Modal body -->
        <div class="modal-body">
              <p class="text-center">{{ __('You are about to reject this item.') }}</p>
              <p class="text-center">{{ __('Do you want to proceed?') }}</p>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
              <a class="btn btn-danger btn-ok" data-dismiss="modal">{{ __('Reject') }}</a>
        </div>

      </div>
    </div>
  </div>

  {{-- REJECT MODAL ENDS --}}

  {{-- APPROVE MODAL --}}

<div class="modal fade" id="confirm-approve" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="submit-loader">
            <img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
        </div>
      <div class="modal-header d-block text-center">
          <h4 class="modal-title d-inline-block">{{ __('Confirm Approve') }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
      </div>

        <!-- Modal body -->
        <div class="modal-body">
              <p class="text-center">{{ __('You are about to approve this item.') }}</p>
              <p class="text-center">{{ __('Do you want to proceed?') }}</p>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
              <a class="btn btn-success btn-ok">{{ __('Approve') }}</a>
        </div>

      </div>
    </div>
  </div>

  {{-- APPROVE MODAL ENDS --}}


@endsection

@section('scripts')

{{-- DATA TABLE --}}

    <script type="text/javascript">

        var table = $('#geniustable').DataTable({
               ordering: false,
               processing: true,
               serverSide: true,
               ajax: '{{ route('admin-vendor-subs-datatables',[-1, -1]) }}',
               columns: [
                        { data: 'name', name: 'name' },
                        { data: 'email', name: 'email' },
                        { data: 'phone', name: 'phone' },
                        { data: 'affilate_code', name: 'affilate_code' },
                        { data: 'title', name: 'title' },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'old_end_at', name: 'old_end_at' },
                        { data: 'new_end_at', name: 'new_end_at' },
                        { data: 'approved_at', name: 'approved_at' },
                        { data: 'rejected_at', name: 'rejected_at' },
                        { data: 'status_caption', name: 'status_caption' },
                        { data: 'action', searchable: false, orderable: false }
                     ],
               language : {
                    processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
                }
            });

            var dateToday = new Date();
        var dates =  $( "#from,#to" ).datepicker({
            defaultDate: "+0w",
            changeMonth: true,
            changeYear: true,
            //minDate: dateToday,
            onSelect: function(selectedDate) {
            var option =
            instance = $(this).data("datepicker"),
            date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
            dates.not(this).datepicker("option", option, date);
            }
        });

        $("#add-find" ).on('click' , function(e){
            var sf = get_date_string($('#from').val());
            var st = get_date_string($('#to').val());
            var status = $('#status').val();
            var plan = $('#plan').val();
            var url = mainurl+'/admin/vendors/subs/datatables/'+status+'/'+plan+'/'+sf+'/'+st;
            console.log(url);
            table.ajax.url( url ).load();
        });

        //Export Section Start
        $("#export-excel" ).on('click' , function(e){
            var sf = get_date_string($('#from').val());
            var st = get_date_string($('#to').val());
            var status = $('#status').val();
            var plan = $('#plan').val();
            var url = mainurl+'/admin/vendors/subs/export/'+status+'/'+plan+'/'+sf+'/'+st;
            window.open(url, '_blank');
        });
        //Export Section Ends

    </script>

{{-- DATA TABLE --}}

@endsection
