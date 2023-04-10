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
            <a href="{{ route('front.faq') }}">
              {{ $langg->lang19 }}
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- Breadcrumb Area End -->



  <!-- faq Area Start -->
  <section class="faq-section blog-details">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-9">
          <div id="accordion">
              @foreach($faqs as $fq)
              <h3 class="heading">{{ $fq->title }}</h3>
              <div class="content">
                  <p>{!! $fq->details !!}</p>
              </div>
              @endforeach
          </div>
        </div>
        <div class="col-md-3">
          <div class="blog-aside" style="position: sticky;top: 0;">
            <div class="categori">
              <h4 class="title">{{ $langg->lang42 }}</h4>
              <span class="separator"></span>
              <ul class="categori-list">
                @foreach($bcats as $cat)
                  <li>
                    <a href="{{ route('front.faqcategory',$cat->slug) }}" >
                      <span>{{ $cat->name }}</span>
                      <span>({{ $cat->faqs()->count() }})</span>
                    </a>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- faq Area End-->

@endsection