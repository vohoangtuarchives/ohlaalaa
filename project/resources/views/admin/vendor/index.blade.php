@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">

@endsection

@section('content')
					<input type="hidden" id="headerdata" value="{{ __("VENDOR") }}">
					<div class="content-area">
						<div class="mr-breadcrumb">
							<div class="row">
								<div class="col-lg-12">
										<h4 class="heading">{{ __("Vendors") }}</h4>
										<ul class="links">
											<li>
												<a href="{{ route('admin.dashboard') }}">{{ __("Dashboard") }} </a>
											</li>
											<li>
												<a href="javascript:;">{{ __("Vendors") }}</a>
											</li>
											<li>
												<a href="{{ route('admin-vendor-index') }}">{{ __("Vendors List") }}</a>
											</li>
										</ul>
								</div>
							</div>
						</div>
						<div class="product-area">
							<div class="row">
								<div class="col-lg-12">

								<div class="heading-area">
									<h4 class="title">
										{{ __("Vendor Registration") }} :
									</h4>
	                                <div class="action-list">
	                                    <select class="process select1 vdroplinks {{ $gs->reg_vendor == 1 ? 'drop-success' : 'drop-danger' }}">
	                                      <option data-val="1" value="{{route('admin-gs-regvendor',1)}}" {{ $gs->reg_vendor == 1 ? 'selected' : '' }}>{{ __("Activated") }}</option>
	                                      <option data-val="0" value="{{route('admin-gs-regvendor',0)}}" {{ $gs->reg_vendor == 0 ? 'selected' : '' }}>{{ __("Deactivated") }}</option>
	                                    </select>
	                                  </div>
								</div>


									<div class="mr-table allproduct">
										@include('includes.admin.form-success')
										<div class="table-responsiv">
                                            <label for="from">From: </label>
                                            <input type="text" class="form-control-sm" name="from_date" id="from" placeholder="{{ __('Select a date') }}" value="{{ $now }}" autocomplete="off" style="width: 100px;">
                                            <label for="to">To: </label>
                                            <input type="text" class="form-control-sm" name="to_date" id="to" placeholder="{{ __('Select a date') }}"  value="{{ $now }}" autocomplete="off" style="width: 100px;">
                                            <label for="email">Email: </label>
                                            <input type="text" name="email" id="email">
                                            <label for="status">Status: </label>
                                            <select id="status" style="display: inline; width: 150px;" >
                                                <option value="-1" selected>All</option>
                                                <option value="2">Actived</option>
                                                <option value="1">Deactived</option>
                                            </select>
                                            <label for="plan">Plan: </label>
                                            <select id="plan" style="display: inline; width: 150px;" >
                                                <option value="-1" selected>All</option>
                                                <option value="0">Shop Experience</option>
                                                <option value="1">Yearly Plan</option>
                                            </select>
                                            <br>
                                            <label for="preferred">Preferred: </label>
                                            <select id="preferred" style="display: inline; width: 150px;" >
                                                <option value="-1" selected>All</option>
                                                <option value="0">Non-Preferred</option>
                                                <option value="1">Preferred</option>
                                            </select>
                                            <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> Find </a>
                                            <a class="add-btn" id="export-excel" > <i class="fas fa-file-excel"></i> Export </a>
                                            <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __("Store Name") }}</th>
                                                        <th>{{ __("Vendor Email") }}</th>
                                                        <th>{{ __("Rank") }}</th>
                                                        <th>{{ __("Preferred") }}</th>
                                                        <th>{{ __("Active") }}</th>
                                                        <th>{{ __("Verification") }}</th>
                                                        <th>{{ __("Subscription") }}</th>
                                                        <th>{{ __("Created") }}</th>
                                                        <th>{{ __("Subscription End") }}</th>
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


{{-- VERIFICATION MODAL --}}

<div class="modal fade" id="verify-modal" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">

		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="submit-loader">
					<img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
				</div>
				<div class="modal-header">
					<h5 class="modal-title">ASK FOR VERIFICATION</h5>
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

{{-- VERIFICATION MODAL ENDS --}}


{{-- DELETE MODAL --}}

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

	<div class="modal-header d-block text-center">
		<h4 class="modal-title d-inline-block">{{ __("Confirm Delete") }}</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
	</div>

      <!-- Modal body -->
      <div class="modal-body">
            <p class="text-center">{{__("You are about to delete this Vendor. Every informtation under this vendor will be deleted.")}}</p>
            <p class="text-center">{{ __("Do you want to proceed?") }}</p>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ __("Cancel") }}</button>
            <a class="btn btn-danger btn-ok">{{ __("Delete") }}</a>
      </div>

    </div>
  </div>
</div>

{{-- DELETE MODAL ENDS --}}


{{-- STATUS MODAL --}}

<div class="modal fade" id="confirm-delete1" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

    <div class="modal-header d-block text-center">
        <h4 class="modal-title d-inline-block">{{ __("Update Status") }}</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
    </div>

      <!-- Modal body -->
      <div class="modal-body">
            <p class="text-center">{{ __("You are about to change the status.") }}</p>
            <p class="text-center">{{ __("Do you want to proceed?") }}</p>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ __("Cancel") }}</button>
            <a class="btn btn-success btn-ok">{{ __("Update") }}</a>
      </div>

    </div>
  </div>
</div>

{{-- STATUS MODAL ENDS --}}

{{-- MESSAGE MODAL --}}

<div class="sub-categori">
	<div class="modal" id="vendorform" tabindex="-1" role="dialog" aria-labelledby="vendorformLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="vendorformLabel">{{ __("Send Message") }}</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
				</div>
			<div class="modal-body">
				<div class="container-fluid p-0">
					<div class="row">
						<div class="col-md-12">
							<div class="contact-form">
								<form id="emailreply1">
									{{csrf_field()}}
									<ul>
										<li>
											<input type="email" class="input-field eml-val" id="eml1" name="to" placeholder="{{ __("Email") }} *" value="" required="">
										</li>
										<li>
											<input type="text" class="input-field" id="subj1" name="subject" placeholder="{{ __("Subject") }} *" required="">
										</li>
										<li>
											<textarea class="input-field textarea" name="message" id="msg1" placeholder="{{ __("Your Message") }} *" required=""></textarea>
										</li>
									</ul>
									<button class="submit-btn" id="emlsub1" type="submit">{{ __("Send Message") }}</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>
</div>

{{-- MESSAGE MODAL ENDS --}}

@endsection

@section('scripts')

{{-- DATA TABLE --}}

<script type="text/javascript">

    var table = $('#geniustable').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin-vendor-datatables', [-1, -1, -1]) }}',
            columns: [
                    { data: 'shop_name', name: 'shop_name' },
                    { data: 'email', name: 'email' },
                    { data: 'rank_name', name: 'rank_name' },
                    { data: 'preferred', name: 'preferred' },
                    { data: 'status', searchable: false, orderable: false},
                    { data: 'ver_status', searchable: false},
                    { data: 'vendor_subscription', searchable: false},
                    { data: 'created_at', searchable: false},
                    { data: 'date', searchable: false},
                    { data: 'action', searchable: false, orderable: false }
                    ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            },
            drawCallback : function( settings ) {
                    $('.select').niceSelect();
            }
        });

    $('.select1').niceSelect();

    $("#add-find" ).on('click' , function(e){
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
        var plan = $('#plan').val();
        var preferred = $('#preferred').val();
        var email = $.trim($('#email').val());
        table.destroy();
        var url = mainurl+'/admin/vendors/datatables/'+status+'/'+plan+'/'+preferred+'/'+sf+'/'+st+'/'+email;
        table = $('#geniustable').DataTable({
            ordering: false,
            processing: true,
            destroy: true,
            serverSide: true,
            ajax: url,
            columns: [
                { data: 'shop_name', name: 'shop_name' },
                { data: 'email', name: 'email' },
                { data: 'rank_name', name: 'rank_name' },
                { data: 'preferred', name: 'preferred' },
                { data: 'status', searchable: false, orderable: false},
                { data: 'ver_status', searchable: false},
                { data: 'vendor_subscription', searchable: false},
                { data: 'created_at', searchable: false},
                { data: 'date', searchable: false},
                { data: 'action', searchable: false, orderable: false }
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            },
            drawCallback : function( settings ) {
                    $('.select').niceSelect();
            }
        });
    });

</script>


<script type="text/javascript">

$(document).on('click','.verify',function(){
if(admin_loader == 1)
  {
  $('.submit-loader').show();
}
  $('#verify-modal .modal-content .modal-body').html('').load($(this).attr('data-href'),function(response, status, xhr){
      if(status == "success")
      {
        if(admin_loader == 1)
          {
            $('.submit-loader').hide();
          }
      }
    });
});


</script>

{{-- DATA TABLE --}}

<script type="text/javascript">
    var dateToday = new Date();
    var dates =  $( "#from,#to" ).datepicker({
        defaultDate: "+1w",
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

    //Export Section Start
    $("#export-excel" ).on('click' , function(e){
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
            var plan = $('#plan').val();
            var preferred = $('#preferred').val();
            var email = $.trim($('#email').val());
            var url = mainurl+'/admin/vendors/export/'+status+'/'+plan+'/'+preferred+'/'+sf+'/'+st+'/'+email;
            window.open(url, '_blank');
        });
        //Export Section Ends

</script>

@endsection
