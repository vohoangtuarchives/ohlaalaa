@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">

@endsection

@section('content')
					<input type="hidden" id="headerdata" value="{{ __("CUSTOMER") }}">
					<div class="content-area">
						<div class="mr-breadcrumb">
							<div class="row">
								<div class="col-lg-12">
										<h4 class="heading">{{ __("Customers") }}</h4>
										<ul class="links">
											<li>
												<a href="{{ route('admin.dashboard') }}">{{ __("Dashboard") }} </a>
											</li>
											<li>
												<a href="{{ route('admin-user-index') }}">{{ __("Customers") }}</a>
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
                                            <input type="text" class="form-control-sm" name="from_date" id="from" placeholder="{{ __('Select a date') }}" value="{{ '2020-01-01' }}" autocomplete="off" style="width: 100px;">
                                            <label for="to">To: </label>
                                            <input type="text" class="form-control-sm" name="to_date" id="to" placeholder="{{ __('Select a date') }}"  value="{{ $now }}" autocomplete="off" style="width: 100px;">
                                            <label for="status">Status: </label>
                                            <select id="status" style="display: inline; width: 150px;" >
                                                <option value="-1" selected>All</option>
                                                <option value="2">Blocked</option>
                                                <option value="1">Unblocked</option>
                                            </select>
                                            <label for="rank">Rank: </label>
                                            <select id="rank" style="display: inline; width: 150px;" >
                                                <option value="-1" selected>All</option>
                                                <option value="1">Regular</option>
                                                <option value="2">Premium</option>
                                                <option value="3">Gold</option>
                                                <option value="4">Platinum</option>
                                            </select>
                                            <label for="keyword">Name or Email: </label>
                                            <input type="text" name="keyword" id="keyword">

                                            <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> Find </a>
                                            <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __("Name") }}</th>
                                                        <th>{{ __("Email") }}</th>
                                                        <th>{{ __("Rank") }}</th>
                                                        <th>{{ __("Rank Option") }}</th>
                                                        <th>{{ __("End") }}</th>
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
            <p class="text-center">{{ __("You are about to delete this Customer.") }}</p>
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

{{-- DELETE MODAL --}}

<div class="modal fade" id="confirm-verify" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

      <div class="modal-header d-block text-center">
          <h4 class="modal-title d-inline-block">{{ __("Confirm User") }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
      </div>

        <!-- Modal body -->
        <div class="modal-body">
              <p class="text-center">{{ __("You are about to confirm this Customer.") }}</p>
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

  {{-- DELETE MODAL ENDS --}}

{{-- UPDATE MODAL --}}

<div class="modal fade" id="confirm-update" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

      <div class="modal-header d-block text-center">
          <h4 class="modal-title d-inline-block">{{ __("Confirm Update") }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
      </div>

        <!-- Modal body -->
        <div class="modal-body">
              <p class="text-center">{{ __("You are about to update this Customer.") }}</p>
              <p class="text-center">{{ __("Do you want to proceed?") }}</p>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-default" data-dismiss="modal">{{ __("Cancel") }}</button>
              <a class="btn btn-primary btn-ok">{{ __("Confirm") }}</a>
        </div>

      </div>
    </div>
  </div>

  {{-- UPDATE MODAL ENDS --}}

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

{{-- SET KOL MODAL --}}
<div class="modal fade" id="confirm-kol" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

      <div class="modal-header d-block text-center">
          <h4 class="modal-title d-inline-block">{{ __("KOL For Customer") }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
      </div>

        <!-- Modal body -->
        <div class="modal-body">
              <p class="text-center">{{ __("You are about to confirm this customer to become KOL.") }}</p>
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
{{-- END KOL MODAL --}}


{{-- SET KOL MODAL --}}
<div class="modal fade" id="confirm-special-kol" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

      <div class="modal-header d-block text-center">
          <h4 class="modal-title d-inline-block">{{ __("SPECIAL KOL For Customer") }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
      </div>

        <!-- Modal body -->
        <div class="modal-body">
              <p class="text-center">{{ __("You are about to confirm this customer to become Special KOL.") }}</p>
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
{{-- END KOL MODAL --}}

                    {{-- SET Transfer Point MODAL --}}
                    <div class="modal fade" id="confirm-transfer-point" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <div class="modal-header d-block text-center">
                                    <h4 class="modal-title d-inline-block">{{ __("Cho Phep Thanh Vien Chuyen Diem") }}</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <!-- Modal body -->
                                <div class="modal-body">
                                    <p class="text-center">{{ __("Ban xac nhan cho phep thanh vien nay duoc phep chuyen diem.") }}</p>
                                    <p class="text-center">{{ __("Ban co muon tiep tuc?") }}</p>
                                </div>

                                <!-- Modal footer -->
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __("Hủy") }}</button>
                                    <a class="btn btn-primary btn-ok">{{ __("Xác Nhận") }}</a>
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- END KOL MODAL --}}
@endsection

@section('scripts')

{{-- DATA TABLE --}}

<script type="text/javascript">

    var dateToday = new Date();
    var dates =  $( "#from,#to" ).datepicker({
        defaultDate: "+0w",
        dateFormat: 'yy-mm-dd',
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

    var table = $('#geniustable').DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        ajax: '{{ route('admin-user-datatables', [-1, -1]) }}',
        columns: [
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'rank_name', name: 'rank_name' },
                { data: 'rank_ad_name', name: 'rank_ad_name' },
                { data: 'end_at', name: 'end_at' },
                { data: 'action', searchable: false, orderable: false }
                ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            },
            drawCallback : function( settings ) {
                    $('.select').niceSelect();
            }
        });

    $("#add-find" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var st = get_date_string($('#to').val());
        var status = $('#status').val();
        var rank = $('#rank').val();
        var keyword = $.trim($('#keyword').val());
        var url = mainurl+'/admin/users/datatables/'+status+'/'+rank+'/'+sf+'/'+st+'/'+keyword;
        console.log('url',url);
        table.ajax.url( url ).load();
    });



</script>

{{-- DATA TABLE --}}

@endsection
