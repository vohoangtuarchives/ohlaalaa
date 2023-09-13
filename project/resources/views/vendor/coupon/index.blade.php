@extends('layouts.vendor')

@section('content')
					<input type="hidden" id="headerdata" value="{{ __('COUPON') }}">
					<div class="content-area">
						<div class="mr-breadcrumb">
							<div class="row">
								<div class="col-lg-12">
										<h4 class="heading">{{ __('Phiếu giảm giá') }}</h4>
										<ul class="links">
											<li>
												<a href="{{ route('vendor-dashboard') }}">{{ __('Bảng điều khiển') }} </a>
											</li>
											<li>
												<a href="{{ route('vendor-coupon-index') }}">{{ __('Phiếu giảm giá') }}</a>
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
												<table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
													<thead>
														<tr>
									                        <th>{{ __('Mã') }}</th>
									                        <th>{{ __('Loại') }}</th>
									                        <th>{{ __('Số lượng') }}</th>
									                        <th>{{ __('Đã sử dụng') }}</th>
									                        <th>{{ __('Trạng thái') }}</th>
									                        <th>{{ __('Tùy chọn') }}</th>
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
											<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
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
		<h4 class="modal-title d-inline-block">{{ __('Xác nhận xóa!') }}</h4>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
	</div>

      <!-- Modal body -->
      {{-- You are about to delete this Coupon. --}}
      <div class="modal-body">
            <p class="text-center">{{ __('Bạn muốn xóa Phiếu giảm giá này.') }}</p>
            <p class="text-center">{{ __('Bạn có muốn tiếp tục?') }}</p>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Hủy') }}</button>
            <a class="btn btn-danger btn-ok">{{ __('Xóa') }}</a>
      </div>

    </div>
  </div>
</div>

{{-- DELETE MODAL ENDS --}}

@endsection



@section('scripts')


{{-- DATA TABLE --}}

    <script type="text/javascript">

		var table = $('#geniustable').DataTable({
			   ordering: false,
               processing: true,
               serverSide: true,
               ajax: '{{ route('vendor-coupon-datatables') }}',
               columns: [
                        { data: 'code', name: 'code' },
                        { data: 'type', name: 'type' },
                        { data: 'price', name: 'price' },
                        { data: 'used', name: 'used' },
                        { data: 'status', searchable: false, orderable: false},
            			{ data: 'action', searchable: false, orderable: false }

                     ],
                language : {
                	processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
                },
				drawCallback : function( settings ) {
	    				$('.select').niceSelect();
				}
            });

      	$(function() {
        $(".btn-area").append('<div class="col-sm-4 table-contents">'+
        	'<a class="add-btn" href="{{route('vendor-coupon-create')}}">'+
          '<i class="fas fa-plus"></i> {{ __('Thêm phiếu giảm giá') }}'+
          '</a>'+
          '</div>');
      });



{{-- DATA TABLE ENDS--}}


</script>





@endsection
