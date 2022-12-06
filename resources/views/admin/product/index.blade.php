@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">

@endsection

@section('content')
					<input type="hidden" id="headerdata" value="{{ __("PRODUCT") }}">
					<div class="content-area">
						<div class="mr-breadcrumb">
							<div class="row">
								<div class="col-lg-12">
										<h4 class="heading">{{ __("Products") }}</h4>
										<ul class="links">
											<li>
												<a href="{{ route('admin.dashboard') }}">{{ __("Dashboard") }} </a>
											</li>
											<li>
												<a href="javascript:;">{{ __("Products") }} </a>
											</li>
											<li>
												<a href="{{ route('admin-prod-index') }}">{{ __("All Products") }}</a>
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
                                            <label for="product_category">Category: </label>
                                            @php
                                                $categories = DB::table('categories')->orderBy('id','desc')->get();
                                            @endphp
                                            <select id="product_category" style="display: inline; width: 150px;" >
                                                <option value="-1" selected>All</option>
                                                @foreach ($categories as $cate)
                                                <option value="{{ $cate->id }}">{{ $cate->name }}</option>
                                                @endforeach
                                            </select>
                                            <label for="highlight">Highlight: </label>
                                            <select id="highlight" style="display: inline; width: 150px;" >
                                                <option value="all" selected>All</option>
                                                <option value="featured">Featured</option>
                                                <option value="best">Best Seller</option>
                                                <option value="top">Top Rated</option>
                                                <option value="big">Big Save</option>
                                                <option value="hot">Hot</option>
                                                <option value="latest">New</option>
                                                <option value="trending">Trending</option>
                                                <option value="sale">Sale</option>
                                                <option value="is_discount">Flash Deal</option>
                                            </select>
                                            <label for="keyword">Keyword: </label>
                                            <input type="text" name="keyword" id="keyword">
                                            <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> Find </a>
                                            <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __("Name") }}</th>
                                                        <th>{{ __("Type") }}</th>
                                                        <th>{{ __("Stock") }}</th>
                                                        <th>{{ __("Price") }}</th>
                                                        <th>{{ __("Shopping Point") }}</th>
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



{{-- HIGHLIGHT MODAL --}}

										<div class="modal fade" id="modal2" tabindex="-1" role="dialog" aria-labelledby="modal2" aria-hidden="true">


										<div class="modal-dialog highlight" role="document">
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

{{-- HIGHLIGHT ENDS --}}

{{-- CATALOG MODAL --}}

<div class="modal fade" id="catalog-modal" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
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
            <p class="text-center">{{ __("You are about to change the status of this Product.") }}</p>
            <p class="text-center">{{ __("Do you want to proceed?") }}</p>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ __("Cancel") }}</button>
            <a class="btn btn-success btn-ok">{{ __("Proceed") }}</a>
      </div>

    </div>
  </div>
</div>

{{-- CATALOG MODAL ENDS --}}


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
            <p class="text-center">{{ __("You are about to delete this Product.") }}</p>
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


{{-- GALLERY MODAL --}}

		<div class="modal fade" id="setgallery" tabindex="-1" role="dialog" aria-labelledby="setgallery" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalCenterTitle">{{ __("Image Gallery") }}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="top-area">
						<div class="row">
							<div class="col-sm-6 text-right">
								<div class="upload-img-btn">
									<form  method="POST" enctype="multipart/form-data" id="form-gallery">
										{{ csrf_field() }}
									<input type="hidden" id="pid" name="product_id" value="">
									<input type="file" name="gallery[]" class="hidden" id="uploadgallery" accept="image/*" multiple>
											<label for="image-upload" id="prod_gallery"><i class="icofont-upload-alt"></i>{{ __("Upload File") }}</label>
									</form>
								</div>
							</div>
							<div class="col-sm-6">
								<a href="javascript:;" class="upload-done" data-dismiss="modal"> <i class="fas fa-check"></i> {{ __("Done") }}</a>
							</div>
							<div class="col-sm-12 text-center">( <small>{{ __("You can upload multiple Images") }}.</small> )</div>
						</div>
					</div>
					<div class="gallery-images">
						<div class="selected-image">
							<div class="row">


							</div>
						</div>
					</div>
				</div>
				</div>
			</div>
		</div>


{{-- GALLERY MODAL ENDS --}}

@endsection



@section('scripts')


{{-- DATA TABLE --}}

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

		var table = $('#geniustable').DataTable({
			   ordering: false,
               processing: true,
               serverSide: true,
               ajax: '{{ route('admin-prod-datatables', [-1, 'all']) }}',
               columns: [
                        { data: 'name', name: 'name' },
                        { data: 'type', name: 'type' },
                        { data: 'stock', name: 'stock' },
                        { data: 'price', name: 'price' },
                        { data: 'price_shopping_point', name: 'price_shopping_point' },
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
        	'<a class="add-btn" href="{{route('admin-prod-types')}}">'+
          '<i class="fas fa-plus"></i> <span class="remove-mobile">{{ __("Add New Product") }}<span>'+
          '</a>'+
          '</div>');
      });



{{-- DATA TABLE ENDS--}}

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

            var product_category = $('#product_category').val();
            var highlight = $('#highlight').val();
            var keyword = $.trim($('#keyword').val());

            table.destroy();
            var url = mainurl+'/admin/products/datatables/'+product_category+'/'+highlight+'/'+sf+'/'+st+'/'+keyword;
            table = $('#geniustable').DataTable({
                ordering: false,
                processing: true,
                destroy: true,
                serverSide: true,
                ajax: url,
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'type', name: 'type' },
                    { data: 'stock', name: 'stock' },
                    { data: 'price', name: 'price' },
                    { data: 'price_shopping_point', name: 'price_shopping_point' },
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
        });


</script>


<script type="text/javascript">


// Gallery Section Update

    $(document).on("click", ".set-gallery" , function(){
        var pid = $(this).find('input[type=hidden]').val();
        $('#pid').val(pid);
        $('.selected-image .row').html('');
            $.ajax({
                    type: "GET",
                    url:"{{ route('admin-gallery-show') }}",
                    data:{id:pid},
                    success:function(data){
                      if(data[0] == 0)
                      {
	                    $('.selected-image .row').addClass('justify-content-center');
	      				$('.selected-image .row').html('<h3>{{ __("No Images Found.") }}</h3>');
     				  }
                      else {
	                    $('.selected-image .row').removeClass('justify-content-center');
	      				$('.selected-image .row h3').remove();
                          var arr = $.map(data[1], function(el) {
                          return el });

                          for(var k in arr)
                          {
        				$('.selected-image .row').append('<div class="col-sm-6">'+
                                        '<div class="img gallery-img">'+
                                            '<span class="remove-img"><i class="fas fa-times"></i>'+
                                            '<input type="hidden" value="'+arr[k]['id']+'">'+
                                            '</span>'+
                                            '<a href="'+'{{asset('assets/images/galleries').'/'}}'+arr[k]['photo']+'" target="_blank">'+
                                            '<img src="'+'{{asset('assets/images/galleries').'/'}}'+arr[k]['photo']+'" alt="gallery image">'+
                                            '</a>'+
                                        '</div>'+
                                  	'</div>');
                          }
                       }

                    }
                  });
      });


  $(document).on('click', '.remove-img' ,function() {
    var id = $(this).find('input[type=hidden]').val();
    $(this).parent().parent().remove();
	    $.ajax({
	        type: "GET",
	        url:"{{ route('admin-gallery-delete') }}",
	        data:{id:id}
	    });
  });

  $(document).on('click', '#prod_gallery' ,function() {
    $('#uploadgallery').click();
  });


  $("#uploadgallery").change(function(){
    $("#form-gallery").submit();
  });

  $(document).on('submit', '#form-gallery' ,function() {
		  $.ajax({
		   url:"{{ route('admin-gallery-store') }}",
		   method:"POST",
		   data:new FormData(this),
		   dataType:'JSON',
		   contentType: false,
		   cache: false,
		   processData: false,
		   success:function(data)
		   {
		    if(data != 0)
		    {
	                    $('.selected-image .row').removeClass('justify-content-center');
	      				$('.selected-image .row h3').remove();
		        var arr = $.map(data, function(el) {
		        return el });
		        for(var k in arr)
		           {
        				$('.selected-image .row').append('<div class="col-sm-6">'+
                                        '<div class="img gallery-img">'+
                                            '<span class="remove-img"><i class="fas fa-times"></i>'+
                                            '<input type="hidden" value="'+arr[k]['id']+'">'+
                                            '</span>'+
                                            '<a href="'+'{{asset('assets/images/galleries').'/'}}'+arr[k]['photo']+'" target="_blank">'+
                                            '<img src="'+'{{asset('assets/images/galleries').'/'}}'+arr[k]['photo']+'" alt="gallery image">'+
                                            '</a>'+
                                        '</div>'+
                                  	'</div>');
		            }
		    }

		                       }

		  });
		  return false;
 });


// Gallery Section Update Ends


</script>




@endsection
