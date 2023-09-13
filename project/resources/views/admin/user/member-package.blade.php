@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">

@endsection

@section('content')
					<input type="hidden" id="headerdata" value="MEMBER PACKAGE CONFIG">
					<div class="content-area">
						<div class="mr-breadcrumb">
							<div class="row">
								<div class="col-lg-12">
										<h4 class="heading">{{ __('Member Package Request') }}</h4>
										<ul class="links">
											<li>
												<a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
											</li>
											<li>
												<a href="javascript:;">{{ __('General Settings') }} </a>
											</li>
											<li>
												<a href="{{ route('admin-user-member-package') }}">{{ __('Member Package Request') }}</a>
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
                                            <input type="text" class="form-control-sm" name="from_date" id="from" placeholder="{{ __('Select a date') }}" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" style="width: 100px;">
                                            <label for="to">{{ __('To') }}: </label>
                                            <input type="text" class="form-control-sm" name="to_date" id="to" placeholder="{{ __('Select a date') }}"  value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" style="width: 100px;">
                                            <label for="status">{{ __('Status') }}: </label>
                                            <select id="status" style="display: inline; width: 135px;" >
                                                <option value="-1" selected>{{ __('All') }}</option>
                                                <option value="1">{{ __('Pending') }}</option>
                                                <option value="2">{{ __('Approved') }}</option>
                                                <option value="3">{{ __('Rejected') }}</option>
                                            </select>
                                            <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> {{ __('Find') }} </a>
                                            <a class="add-btn" id="export-excel" > <i class="fas fa-file-excel"></i> {{ __('Export') }} </a>
												<table id="example" class="table table-hover dt-responsive" cellspacing="0" width="100%">
													<thead>
														<tr>
									                        <th>{{ __('Name') }}</th>
									                        <th>{{ __('Email') }}</th>
									                        <th>{{ __('Phone') }}</th>
									                        <th>{{ __('Aff. Code') }}</th>
                                                            <th>{{ __('Package') }}</th>
                                                            <th>{{ __('Payment Number') }}</th>
                                                            <th>{{ __('Payment Status') }}</th>
                                                            <th>{{ __('Submit Date') }}</th>
                                                            <th>{{ __('Current End Date') }}</th>
                                                            <th>{{ __('Renewal End Date') }}</th>
                                                            <th>{{ __('T&C Checked') }}</th>
                                                            <th>{{ __('Approved At') }}</th>
                                                            <th>{{ __('Rejected At') }}</th>
                                                            <th>{{ __('Status') }}</th>
                                                            <th>{{ __('Is Renew') }}</th>
                                                            <th>{{ __('Action By') }}</th>
                                                            <th>{{ __('Action') }}</th>

														</tr>
													</thead>
												</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

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

    <script type="text/javascript">

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

		var table = $('#example').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin-user-member-package-datatables', [\Carbon\Carbon::now()->format('Y-m-d'), \Carbon\Carbon::now()->format('Y-m-d'), -1]) }}',
            columns: [
                    { data: 'user_name', name: 'user_name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'affilate_code', name: 'affilate_code' },
                    { data: 'package_name', name: 'package_name' },
                    { data: 'payment_number', name: 'payment_number' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'package_old_end_at', name: 'package_old_end_at' },
                    { data: 'package_new_end_at', name: 'package_new_end_at' },
                    { data: 'checked_tnc', name: 'checked_tnc' },
                    { data: 'approval_at', name: 'approval_at' },
                    { data: 'rejected_at', name: 'rejected_at' },
                    { data: 'status_caption', name: 'status_caption' },
                    { data: 'is_renew', name: 'is_renew' },
                    { data: 'action_by', name: 'action_by' },
                    { data: 'action', searchable: false, orderable: false },
                    ],
            language: {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

        $("#add-find" ).on('click' , function(e){
            var sf = get_date_string($('#from').val());
            var st = get_date_string($('#to').val());
            var status = $('#status').val();
            var url = mainurl+'/admin/user/memberpackage/datatables/'+sf+'/'+st+'/'+status;
            console.log('url',url);
            table.ajax.url( url ).load();
        });

        $("#export-excel" ).on('click' , function(e){
            var sf = get_date_string($('#from').val());
            var st = get_date_string($('#to').val());
            var status = $('#status').val();
            var url = mainurl+'/admin/user/memberpackage/export/'+sf+'/'+st+'/'+status;
        window.open(url, '_blank');
    });


    </script>
@endsection
