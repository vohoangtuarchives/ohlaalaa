@extends('layouts.front')
@php
    function isMobileDevice() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo
    |fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i"
    , $_SERVER["HTTP_USER_AGENT"]);
    }
@endphp
@section('content')


  <!-- Breadcrumb Area Start -->
  <div class="breadcrumb-area">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <ul class="pages">

          {{-- Category Breadcumbs --}}

          @if(isset($bcat))

              <li>
                <a href="{{ route('front.index') }}">
                  {{ $langg->lang17 }}
                </a>
              </li>
              <li>
                <a href="{{ route('front.blog') }}">
                  {{ $langg->lang18 }}
                </a>
              </li>
              <li>
                <a href="{{ route('front.blogcategory',$bcat->slug) }}">
                  {{ $bcat->name }}
                </a>
              </li>

          @elseif(isset($slug))

              <li>
                <a href="{{ route('front.index') }}">
                  {{ $langg->lang17 }}
                </a>
              </li>
              <li>
                <a href="{{ route('front.blog') }}">
                  {{ $langg->lang18 }}
                </a>
              </li>
              <li>
                <a href="{{ route('front.blogtags',$slug) }}">
                  {{ $langg->lang35 }}: {{ $slug }}
                </a>
              </li>

          @elseif(isset($search))

              <li>
                <a href="{{ route('front.index') }}">
                  {{ $langg->lang17 }}
                </a>
              </li>
              <li>
                <a href="{{ route('front.blog') }}">
                  {{ $langg->lang18 }}
                </a>
              </li>
              <li>
                <a href="Javascript:;">
                  {{ $langg->lang36 }}
                </a>
              </li>
              <li>
                <a href="Javascript:;">
                  {{ $search }}
                </a>
              </li>

          @elseif(isset($date))

              <li>
                <a href="{{ route('front.index') }}">
                  {{ $langg->lang17 }}
                </a>
              </li>
              <li>
                <a href="{{ route('front.blog') }}">
                  {{ $langg->lang18 }}
                </a>
              </li>
              <li>
                <a href="Javascript:;">
                  {{ $langg->lang37 }}: {{ date('F Y',strtotime($date)) }}
                </a>
              </li>

          @else

              <li>
                <a href="{{ route('front.index') }}">
                  {{ $langg->lang17 }}
                </a>
              </li>
              <li>
                <a href="{{ route('front.coupon') }}">
                  Mã Giảm Giá
                </a>
              </li>
          @endif

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

      <div class="row">

        @foreach($products as $prod)
            @include("includes.product.home-product")
        @endforeach
      </div>
        <div class="page-center">
            {!! $products->links() !!}
        </div>
</div>

    </div>
  </section>
  <!-- Blog Page Area Start -->




@endsection


@section('scripts')

@endsection
