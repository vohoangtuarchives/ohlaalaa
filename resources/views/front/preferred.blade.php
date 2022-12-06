@extends('layouts.front')
@section('content')


  <!-- Breadcrumb Area Start -->
  <div class="breadcrumb-area">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <ul class="pages">

            <li>
                <a href="{{ route('front.index') }}">
                  {{ $langg->lang17 }}
                </a>
              </li>
              <li>
                <a href="{{ route('front.preferred') }}">
                  Cửa Hàng Ưu Tiên
                </a>
              </li>

          </ul>
        </div>
      </div>
    </div>
  </div>
  <!-- Breadcrumb Area End -->

  <!-- Blog Page Area Start -->
  <section class="blogpagearea">
    <div class="container">
      <div id="ajaxContent">
        @php
        $month = ['Jan' => 'Tháng 1', 'Feb' => 'Tháng 2', 'Mar' => 'Tháng 3', 'Apr' => 'Tháng 4', 'May' => 'Tháng 5', 'Jun' => 'Tháng 6', 'Jul' => 'Tháng 7'
                    , 'Aug' => 'Tháng 8', 'Sep' => 'Tháng 9', 'Oct' => 'Tháng 10', 'Nov' => 'Tháng 11', 'Dec' => 'Tháng 12'];
        @endphp
      <div class="row">
        @foreach($preferrers as $pre)
        <div class="col-md-6 col-lg-4">
              <div class="blog-box">
                <div class="blog-images">
                    <div class="img">
                    {{-- <img src="{{ $pre->photo ? asset('assets/images/users/'.$pre->photo):asset('assets/images/noimage.png') }}" class="img-fluid img1" alt=""> --}}
                    <img src="{{ $pre->show_photo() }}" class="img-fluid img1" alt="">
                    <div class="date d-flex justify-content-center">
                      <div class="box align-self-center">
                        <p>{{date('d', strtotime($pre->preferred_at))}}</p>
                        <p>{{ $month[date('M', strtotime($pre->preferred_at))]}}</p>
                      </div>
                    </div>
                    </div>
                </div>
                <div class="details">
                    <a href="{{ route('front.vendor',str_replace(' ', '-', $pre->shop_name)) }}">
                      <h4 class="blog-title">
                        {{mb_strlen($pre->shop_name,'utf-8') > 50 ? mb_substr($pre->shop_name,0,50,'utf-8')."...":$pre->shop_name}}
                      </h4>
                    </a>
                  <p class="blog-text">
                    {{substr(strip_tags($pre->shop_details),0,120)}}
                  </p>
                  <a class="read-more-btn" href="{{ route('front.vendor',str_replace(' ', '-', $pre->shop_name)) }}">Xem Shop</a>
                </div>
            </div>
        </div>


        @endforeach

      </div>

        <div class="page-center">
          {!! $preferrers->links() !!}
        </div>
</div>

    </div>
  </section>
  <!-- Blog Page Area Start -->




@endsection


@section('scripts')

<script type="text/javascript">


    // Pagination Starts

    $(document).on('click', '.pagination li', function (event) {
      event.preventDefault();
      if ($(this).find('a').attr('href') != '#' && $(this).find('a').attr('href')) {
        $('#preloader').show();
        $('#ajaxContent').load($(this).find('a').attr('href'), function (response, status, xhr) {
          if (status == "success") {
            $("html,body").animate({
              scrollTop: 0
            }, 1);
            $('#preloader').fadeOut();


          }

        });
      }
    });

    // Pagination Ends

</script>


@endsection
