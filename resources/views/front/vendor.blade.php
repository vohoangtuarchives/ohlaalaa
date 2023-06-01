@extends('layouts.front')
@section('styles')


<style>
body {
      overflow: hidden;
 }
 .highlight {
    background-color: yellow;
  }
</style>
@endsection

@section('content')
@isset($vendor->shop_image)
<!-- Vendor Area Start -->
    <div class="new_vendor-banner">
        <img src="{{  $vendor->show_banner() }}" alt="{{  Request::route('name') }}">
    </div>
{{--  <div class="vendor-banner" style="background: url({{  $vendor->show_banner() }}); background-repeat: no-repeat; background-size: cover;--}}
{{--  background-position: center;{!! $vendor->shop_image != null ? '' : 'background-color:'.$gs->vendor_color !!} ">--}}

    {{-- <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="content">
            <p class="sub-title">
                {{ $langg->lang226 }}
            </p>
            <h2 class="title">
              {{ $vendor->shop_name }}
            </h2>
          </div>
        </div>
      </div>
    </div> --}}
  </div>
@endisset
  <input type="hidden"  id="vendor_name" name="vendor_name" value="{{  Request::route('name') }}">
  <input type="hidden"  readonly="" value="1" id="cpage"/>
  <input type="hidden"  readonly=""  id="total_page" value="{{ $total_page }}" />
  <input type="hidden"  readonly=""  id="sc" value="0" />
{{-- Info Area Start --}}
<section class="info-area">
  <div class="container">


        @foreach($services->chunk(4) as $chunk)

        <div class="row">

        <div class="col-lg-12 p-0">
          <div class="info-big-box">
              <div class="row">
                @foreach($chunk as $service)
              <div class="col-6 col-xl-3 p-0">
                <div class="info-box">
                  <div class="icon">
                    <img src="{{ asset('assets/images/services/'.$service->photo) }}">
                  </div>
                  <div class="info">
                      <div class="details">
                        <h4 class="title">{{ $service->title }}</h4>
                      <p class="text">
                        {!! $service->details !!}
                      </p>
                      </div>
                  </div>
                </div>
              </div>
              @endforeach
              </div>
          </div>
        </div>

        </div>

          @endforeach


        </div>
</section>
{{-- Info Area End  --}}
<!-- SubCategori Area Start -->
  <section class="sub-categori">
    <div class="container">
      <div class="row">

        @include('includes.vendor-catalog')

        <div class="col-lg-9 order-first order-lg-last">
          <div class="right-area">
            {{-- @if(count($vprods) > 0) --}}

              @include('includes.vendor-filter')

            <div class="categori-item-area">
              {{-- <div id="ajaxContent"> --}}
                <div class="container mt-5" >
                    <div class="row" id="data-wrapper">
                        {{-- @foreach($vprods as $prod)
                            @include('includes.product.product')
                        @endforeach --}}
                        @include('includes.product.filtered-products')

                    </div>
                    <div id="ajaxLoader" class="auto-load text-center">
                        <svg version="1.1" id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                            x="0px" y="0px"  viewBox="0 0 400 200" enable-background="new 0 0 0 0" xml:space="preserve">
                            <image  width="100%" height="100%"  href="{{ asset('assets/images/'.$gs->loader) }}" style="background: url({{asset('assets/images/'.$gs->loader)}}) no-repeat scroll center center rgba(0,0,0,.6);"/>

                        </svg>
                    </div>
                       {{-- <div id="ajaxLoader" class="ajax-loader" style="background: url({{asset('assets/images/'.$gs->loader)}}) no-repeat scroll center center rgba(0,0,0,.6);"></div> --}}
                    @if (count($prods) == 0)
                    <div class="col-lg-12">
                        <div class="page-center">
                            <h4 class="text-center">{{ $langg->lang60 }}</h4>
                        </div>
                    </div>
                    @endif
                    {{-- <div id="ajaxLoader" class="ajax-loader" style="background: url({{asset('assets/images/'.$gs->loader)}}) no-repeat scroll center center rgba(0,0,0,.6);"></div> --}}
                </div>

                {{-- <div class="page-center category">
                {!! $vprods->appends(['sort' => request()->input('sort'), 'min' => request()->input('min'), 'max' => request()->input('max')])->links() !!}
                </div> --}}
                {{-- </div> --}}
            </div>
            {{-- @else
              <div class="page-center">
                <h4 class="text-center">{{ $langg->lang60 }}</h4>
              </div>
            @endif --}}


          </div>
        </div>
      </div>
    </div>
  </section>
<!-- SubCategori Area End -->


@if(Auth::guard('web')->check())

{{-- MESSAGE VENDOR MODAL --}}

<div class="message-modal">
  <div class="modal" id="vendorform1" tabindex="-1" role="dialog" aria-labelledby="vendorformLabel1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="vendorformLabel1">{{ $langg->lang118 }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
      <div class="modal-body">
        <div class="container-fluid p-0">
          <div class="row">
            <div class="col-md-12">
              <div class="contact-form">

                <form id="emailreply">
                  {{csrf_field()}}
                  <ul>

                    <li>
                      <input type="text" class="input-field" readonly="" placeholder="Send To {{ $vendor->shop_name }}" readonly="">
                    </li>

                    <li>
                      <input type="text" class="input-field" id="subj" name="subject" placeholder="{{ $langg->lang119}}" required="">
                    </li>

                    <li>
                      <textarea class="input-field textarea" name="message" id="msg" placeholder="{{ $langg->lang120 }}" required=""></textarea>
                    </li>

                    <input type="hidden" name="email" value="{{ Auth::guard('web')->user()->email }}">
                    <input type="hidden" name="name" value="{{ Auth::guard('web')->user()->name }}">
                    <input type="hidden" name="user_id" value="{{ Auth::guard('web')->user()->id }}">
                    <input type="hidden"  id="vendor_id" name="vendor_id" value="{{ $vendor->id }}">

                  </ul>
                  <button class="submit-btn" id="emlsub1" type="submit">{{ $langg->lang118 }}</button>
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

{{-- MESSAGE VENDOR MODAL ENDS --}}


@endif

@endsection

@section('scripts')
<script>
    if (history.scrollRestoration) {
        history.scrollRestoration = 'manual';
    } else {
        window.onbeforeunload = function () {
            window.scrollTo(0, 0);
        }
    }

    var ENDPOINT = "{{ url('/') }}";
    var page = $('#cpage').val();
    // infinteLoadMore(page);

    var tmp = 0;
    var footer = $("#footer").height();
    $(document).scroll(function(e){
        e.preventDefault()

        if($(window).width() >= 1024){
            if (tmp == 0) {
                var p = $( ".item" ).last();
                // p.addClass( "highlight" );
                // if ($(window).scrollTop() + $(window).height() >= $(document).height() - footer) {
                if ($(window).scrollTop()  + $(window).height() > p.offset().top + 450) {
                    tmp = 1;
                    page = parseInt($('#cpage').val()) + 1;
                    $('#cpage').val(page);
                    var sort= $('#sort').val();
                    if (parseInt($('#total_page').val()) >= page) {
                        infinteLoadMore(page, sort)
                    } else {
                        $('#ajaxLoader').fadeOut(1000);
                    }
                }
            }
        } else {
            if (tmp == 0) {
                // var itemfilter = $('.filter-result-area').height() + $('.service-center').height() ;
                var p = $( ".item" ).last();
                    // p.addClass( "highlight" );
                // if ($(window).scrollTop() + $(window).height() >= $(document).height() - footer - itemfilter) {
                if ($(window).scrollTop()  + $(window).height() > (p.offset().top + 450)) {
                    var position =  p.offset().top;
                    tmp = 1;
                    page = parseInt($('#cpage').val()) + 1;
                    $('#cpage').val(page);
                    var sort= $('#sort').val();
                    if (  parseInt($('#total_page').val()) >= page) {
                        // var position= $(window).scrollTop();
                        infinteLoadMore(page, sort, position)
                    } else {
                        $('#ajaxLoader').fadeOut(1000);
                    }
                }
            }
        }
    });

     function infinteLoadMore(page, sort = '', position = 0) {
        var id = "{{ $vendor->id }}";

        var search = '';
        if ( $("#prod_name").val() != '' || typeof $('#prod_name').val() !== "undefined" ) {
            search = "search="+$("#prod_name").val();
        }

        var search_location = '';
        if ( $("#search-location").val() != '' || typeof $('#search-location').val() !== "undefined" ) {
            search_location = "&search_location="+$("#search-location").val();
        }

        if (id != '') {
            if (sort != '' || sort != null) {
                sort =  '&sort='+sort
            }

            const res =  getData(search,search_location, page, sort, 'get', position);

        }
    }

    function getData(search,search_location, page, sort, method = 'get', position = 0) {
        return $.ajax({
                //  url: ENDPOINT + "/shop/" +  $("#vendor_name").val()  + cat + "?" + search + "&page=" + page + sort,
                url:"{{ route('front.shop',[   Request::route('name'), Request::route('slug1'), Request::route('slug2'), Request::route('slug3') ])}}"+  "?" + search + search_location + "&page=" + page + sort,
                datatype: "html",
                type: method,
                beforeSend: function () {
                    $("#ajaxLoader").fadeIn(1000);
                }
            })
            .success(function (response) {
                if (response.length == 0) {
                    $('#ajaxLoader').fadeOut(1000);
                    return
                }

                $('#ajaxLoader').fadeOut(1000);
                $("#data-wrapper").append(response);

                if (position != 0) {
                    $(window).scrollTop(position);
                }
                tmp = 0;
            })
            .fail(function (jqXHR, ajaxOptions, thrownError) {
                console.log('Server error occured');
            });
    };

$(document).ready(function(){
    $("#ajaxLoader").hide();
    $('#search-btn').click(function(e){
        e.preventDefault();
        if ("{{ Route::currentRouteAction() }}" == 'App\Http\Controllers\Front\VendorController@index' ) {
            var formData = $('').serialize();
            $('#data-wrapper').empty();
            $('#cpage').val(1);
            infinteLoadMore(1, $(this).val());
        }
    });
    $('#sort').change(function(){
        $('#data-wrapper').empty();
        $('#cpage').val(1);
        infinteLoadMore(1, $(this).val());
    });


    $("#slider-range").slider({
        range: true,
        orientation: "horizontal",
        min: 0,
        max: 10000000,
        values: [{{ isset($_GET['min']) ? $_GET['min'] : '0' }}, {{ isset($_GET['max']) ? $_GET['max'] : '10000000' }}],
        step: 5,

        slide: function (event, ui) {
        if (ui.values[0] == ui.values[1]) {
            return false;
        }

        $("#min_price").val(ui.values[0]);
        $("#max_price").val(ui.values[1]);
        }
        });

        $("#min_price").val($("#slider-range").slider("values", 0));
        $("#max_price").val($("#slider-range").slider("values", 1));

    });

    $(document).on("submit", "#emailreply" , function(){
          var token = $(this).find('input[name=_token]').val();
          var subject = $(this).find('input[name=subject]').val();
          var message =  $(this).find('textarea[name=message]').val();
          var email = $(this).find('input[name=email]').val();
          var name = $(this).find('input[name=name]').val();
          var user_id = $(this).find('input[name=user_id]').val();
          var vendor_id = $(this).find('input[name=vendor_id]').val();
          $('#subj').prop('disabled', true);
          $('#msg').prop('disabled', true);
          $('#emlsub').prop('disabled', true);
     $.ajax({
            type: 'post',
            url: "{{URL::to('/vendor/contact')}}",
            data: {
                '_token': token,
                'subject'   : subject,
                'message'  : message,
                'email'   : email,
                'name'  : name,
                'user_id'   : user_id,
                'vendor_id'  : vendor_id
                  },
            success: function() {
          $('#subj').prop('disabled', false);
          $('#msg').prop('disabled', false);
          $('#subj').val('');
          $('#msg').val('');
        $('#emlsub').prop('disabled', false);
        toastr.success("{{ $langg->message_sent }}");
        $('.ti-close').click();
            }
        });
          return false;
        });
</script>


@endsection
