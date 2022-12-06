@extends('layouts.front')
@section('content')
<!-- Breadcrumb Area Start -->
<div class="breadcrumb-area">
   <div class="container">
      <div class="row">
         <div class="col-lg-12">
            <ul class="pages">
               <li>
                  <a href="{{route('front.index')}}">{{ $langg->lang17 }}</a>
               </li>
               @if (!empty($cat))
               <li>
                  <a href="{{route('front.category', $cat->slug)}}">{{ $cat->name }}</a>
               </li>
               @endif
               @if (!empty($subcat))
               <li>
                  <a href="{{route('front.category', [$cat->slug, $subcat->slug])}}">{{ $subcat->name }}</a>
               </li>
               @endif
               @if (!empty($childcat))
               <li>
                  <a href="{{route('front.category', [$cat->slug, $subcat->slug, $childcat->slug])}}">{{ $childcat->name }}</a>
               </li>
               @endif
               @if (empty($childcat) && empty($subcat) && empty($cat))
               <li>
                  <a href="{{route('front.category')}}">{{ $langg->lang36 }}</a>
               </li>
               @endif

            </ul>
         </div>
      </div>
   </div>
</div>

<!-- Breadcrumb Area End -->
<!-- SubCategori Area Start -->
<input type="hidden"  readonly="" value="1" id="cpage"/>
<input type="hidden"  readonly=""  id="total_page" value="{{ $total_page }}" />
<section class="sub-categori">
   <div class="container">
      <div class="row">
         @include('includes.catalog')
         <div class="col-lg-9 order-first order-lg-last ajax-loader-parent">
            <div class="right-area" id="app">

               @include('includes.filter')
               <div class="categori-item-area">
                 <div class="row" id="ajaxContent">

                    @include('includes.product.filtered-products')
                    @if (count($prods) == 0)
                        <div class="col-lg-12">
                            <div class="page-center">
                                <h4 class="text-center">{{ $langg->lang60 }}</h4>
                            </div>
                        </div>
                    @endif
                 </div>
                 <div id="ajaxLoader" class="auto-load text-center">
                    <svg version="1.1" id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        x="0px" y="0px" viewBox="0 0 400 200" enable-background="new 0 0 0 0" xml:space="preserve">
                        <image width="100%" height="100%" href="{{ asset('assets/images/'.$gs->loader) }}" />

                    </svg>
                </div>
                 {{-- <div id="ajaxLoader" class="ajax-loader" style="background: url({{asset('assets/images/'.$gs->loader)}}) no-repeat scroll center center rgba(0,0,0,.6);"></div> --}}
               </div>

            </div>
         </div>
      </div>
   </div>
</section>
<!-- SubCategori Area End -->
@endsection


@if(isset($ajax_check))
<script type="text/javascript">

    $('[data-toggle="tooltip"]').tooltip({
        });
        $('[data-toggle="tooltip"]').on('click',function(){
            $(this).tooltip('hide');
        });

        $('[rel-toggle="tooltip"]').tooltip();

        $('[rel-toggle="tooltip"]').on('click',function(){
            $(this).tooltip('hide');
        });
</script>
@endif

@section('scripts')
<script>


if (history.scrollRestoration) {
    history.scrollRestoration = 'manual';
} else {
    window.onbeforeunload = function () {
        window.scrollTo(0, 0);
    }
}

$(document).ready(function() {
    $("#ajaxLoader").hide();

    var ENDPOINT = "{{ url('/') }}";
    var page = $('#cpage').val();
    var tmp = 0;
    var footer = $("#footer").height();

    $(window).scroll(function () {

        if($(window).width() >= 1024){
            if (tmp == 0) {
                var p = $( ".item" ).last();
                // if ($(window).scrollTop() + $(window).height() >= $(document).height() - footer) {
                if ($(window).scrollTop()  + $(window).height() > (p.offset().top + 450)) {
                    tmp = 1;
                    page = parseInt($('#cpage').val()) + 1;
                    $('#cpage').val(page);
                    var sort= $('#sort').val();
                    if ( parseInt($('#total_page').val()) >= page) {
                        infinteLoadMore(page, sort);
                    } else {
                        $('#ajaxLoader').fadeOut(1000);
                    }
                }
            }
        } else {
            if (tmp == 0) {
                // var itemfilter = $('.filter-result-area').height() + $('.service-center').height() ;
                // if ($(window).scrollTop() + $(window).height() >= $(document).height() - footer - itemfilter) {
                var p = $( ".item" ).last();
                if ($(window).scrollTop()  + $(window).height() > (p.offset().top + 450)) {
                    var position= $(window).scrollTop();
                    tmp = 1;
                    page = parseInt($('#cpage').val()) + 1;
                    $('#cpage').val(page);
                    var sort= $('#sort').val();
                    if (  parseInt($('#total_page').val()) >= page) {
                        position =  p.offset().top;
                        // setTimeout(() => {
                        //     console.log('loading 2')
                        // }, 5000);

                        infinteLoadMore(page, sort, 'get', position);
                    } else {
                        $('#ajaxLoader').fadeOut(1000);
                    }
                }
            }
        }
    });

    function infinteLoadMore(page, sort = '', method = 'get', position = 0 ) {
        var search = '';
        if ( $("#prod_name").val() != '' || typeof $('#prod_name').val() !== "undefined" ) {
            search = "search="+$("#prod_name").val();
        }

        var search_location = '';
        if ( $("#search-location").val() != '' || typeof $('#search-location').val() !== "undefined" ) {
            search_location = "&search_location="+$("#search-location").val();
        }


        if (sort != '' || sort != null) {
            sort =  '&sort='+sort
        }

        $.ajax({
            //  url: ENDPOINT + "/shop/" +  $("#vendor_name").val()  + cat + "?" + search + "&page=" + page + sort,
            url:"{{ route('front.category', [Request::route('category'), Request::route('subcategory'), Request::route('childcategory')])}}"+  "?" + search + search_location + "&page=" + page + sort,
            datatype: "html",
            type: method,
            beforeSend: function () {
                $("#ajaxLoader").show();
            }
        })
        .done(function (response) {
            if (response.length == 0) {
                $('#ajaxLoader').fadeOut(1000);
                return;
            }

            $('#ajaxLoader').fadeOut(1000);
            $("#ajaxContent").append(response);
            if (position != 0) {
                $(window).scrollTop(position);
            }

            tmp = 0;
        })
        .fail(function (jqXHR, ajaxOptions, thrownError) {
            console.log('Server error occured');
        });


    }



    // when dynamic attribute changes
    $(".attribute-input, #sortby").on('change', function() {
      //$("#ajaxLoader").show();
      $('#ajaxContent').empty();
      $('#cpage').val(1);
      infinteLoadMore(1, $(this).val());
      //filter();
    });

    // when price changed & clicked in search button
    // $(".filter-btn").on('click', function(e) {
    //   e.preventDefault();
    //   $("#ajaxLoader").show();
    //   filter();
    // });
  });

  function filter() {
    let filterlink = '';
    $('#cpage').val(1);

    if ($("#prod_name").val() != '') {
      if (filterlink == '') {
        filterlink += '{{route('front.category', [Request::route('category'), Request::route('subcategory'), Request::route('childcategory')])}}' + '?search='+$("#prod_name").val();
      } else {
        filterlink += '&search='+$("#prod_name").val();
      }
    }

    $(".attribute-input").each(function() {
      if ($(this).is(':checked')) {
        if (filterlink == '') {
          filterlink += '{{route('front.category', [Request::route('category'), Request::route('subcategory'), Request::route('childcategory')])}}' + '?'+$(this).attr('name')+'='+$(this).val();
        } else {
          filterlink += '&'+$(this).attr('name')+'='+$(this).val();
        }
      }
    });

    if ($("#sortby").val() != '') {
      if (filterlink == '') {
        filterlink += '{{route('front.category', [Request::route('category'), Request::route('subcategory'), Request::route('childcategory')])}}' + '?'+$("#sortby").attr('name')+'='+$("#sortby").val();
      } else {
        filterlink += '&'+$("#sortby").attr('name')+'='+$("#sortby").val();
      }
    }

    // if ($("#min_price").val() != '') {
    //   if (filterlink == '') {
    //     filterlink += '{{route('front.category', [Request::route('category'), Request::route('subcategory'), Request::route('childcategory')])}}' + '?'+$("#min_price").attr('name')+'='+$("#min_price").val();
    //   } else {
    //     filterlink += '&'+$("#min_price").attr('name')+'='+$("#min_price").val();
    //   }
    // }

    // if ($("#max_price").val() != '') {
    //   if (filterlink == '') {
    //     filterlink += '{{route('front.category', [Request::route('category'), Request::route('subcategory'), Request::route('childcategory')])}}' + '?'+$("#max_price").attr('name')+'='+$("#max_price").val();
    //   } else {
    //     filterlink += '&'+$("#max_price").attr('name')+'='+$("#max_price").val();
    //   }
    // }

    // console.log(filterlink);
    console.log(encodeURI(filterlink));
    $("#ajaxContent").load(encodeURI(filterlink), function(data) {
      // add query string to pagination
      addToPagination();
      $("#ajaxLoader").fadeOut(1000);
    });
  }

  // append parameters to pagination links
  function addToPagination() {
    // add to attributes in pagination links
    $('ul.pagination li a').each(function() {
      let url = $(this).attr('href');
      let queryString = '?' + url.split('?')[1]; // "?page=1234...."

      let urlParams = new URLSearchParams(queryString);
      let page = urlParams.get('page'); // value of 'page' parameter

      let fullUrl = '{{route('front.category', [Request::route('category'),Request::route('subcategory'),Request::route('childcategory')])}}?page='+page+'&search='+'{{request()->input('search')}}';

      $(".attribute-input").each(function() {
        if ($(this).is(':checked')) {
          fullUrl += '&'+encodeURI($(this).attr('name'))+'='+encodeURI($(this).val());
        }
      });

      if ($("#sortby").val() != '') {
        fullUrl += '&sort='+encodeURI($("#sortby").val());
      }

    //   if ($("#min_price").val() != '') {
    //     fullUrl += '&min='+encodeURI($("#min_price").val());
    //   }

    //   if ($("#max_price").val() != '') {
    //     fullUrl += '&max='+encodeURI($("#max_price").val());
    //   }

      $(this).attr('href', fullUrl);
    });
  }

  $(document).on('click', '.categori-item-area .pagination li a', function (event) {
    event.preventDefault();
    if ($(this).attr('href') != '#' && $(this).attr('href')) {
      $('#preloader').show();
      $('#ajaxContent').load($(this).attr('href'), function (response, status, xhr) {
        if (status == "success") {
          $('#preloader').fadeOut();
          $("html,body").animate({
            scrollTop: 0
          }, 1);

          addToPagination();
        }
      });
    }
  });

</script>

<script type="text/javascript">

  $(function () {

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

</script>



@endsection
