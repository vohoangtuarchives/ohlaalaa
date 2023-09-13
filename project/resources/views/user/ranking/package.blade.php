@extends('layouts.front')
@section('content')

<section class="user-dashbord">
    <div class="container">
      <div class="row">
        @include('includes.user-dashboard-sidebar')
            <div class="col-lg-8">
                <div class="user-profile-details">

                    <div class="row">

                    @if ($subs->count() > 0)
                        {{-- start foreach --}}
                        @foreach ($subs as $sub)
                            @if ($sub->allow_buy )

                            <div class="col-lg-6">
                                <div class="elegant-pricing-tables style-2 text-center">
                                    <div class="pricing-head">
                                        <h3>{{ $sub->name }}</h3>
                                        {{-- <span class="price">
                                            <sup>{{ $curr->sign }}</sup>
                                            <span class="price-digit">{{ number_format($sub->price) }}</span><br>
                                            <span class="price-month">{{ number_format($sub->price) }}</span>
                                        </span> --}}
                                    </div>
                                    <div class="pricing-detail">
                                        <p>{!! $sub->content !!}</p>
                                    </div>
                                    <a href="{{route('user-member-package-tnc', $sub->id)}}" class="btn btn-default">{{ $langg->lang408 }}</a>
                                    <br><small>&nbsp;</small>
                                </div>
                            </div>

                            @endif
                        @endforeach
                    {{-- end foreach --}}
                    @else
                    <div class="col-lg-12">
                        <div class="elegant-pricing-tables style-2 text-center">
                            <div class="pricing-head">
                                <h3>Congratulation!</h3>
                                <h3>You have reached the highest membership ranking.</h3>
                            </div>
                        </div>
                    </div>
                    @endif

                    </div>
                </div>
            </div>
      </div>
    </div>
  </section>

@endsection
