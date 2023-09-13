@extends('layouts.front')

@section('content')
    @php
    function isMobileDevice() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo
    |fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i"
    , $_SERVER["HTTP_USER_AGENT"]);
    }
    @endphp
	@if($ps->slider == 1)

		@if(count($sliders))
			@include('includes.slider-style')
		@endif
	@endif

	@if($ps->slider == 1)
		<!-- Hero Area Start -->
		<section class="hero-area">
			@if($ps->slider == 1)
				@if(count($sliders))
					<div class="mobile-slider" style="display: none">
						<div class="slide-progress"></div>
						<div class="intro-carousel">
							@foreach($sliders as $data)
								<div class="position-relative">
									<div class="layer-3 {{$data->position}}">
										<a href="{{$data->link}}" target="_blank" class="mybtn1"><span>{{ $langg->lang25 }} <i class="fas fa-chevron-right"></i></span></a>
									</div>
									<img src="{{ $htd_photo->show_photo($data->photo, 'sliders') }}" alt="{{$data->id}}">
								</div>

							@endforeach
						</div>
					</div>
					<div class="hero-area-slider">
						<div class="slide-progress"></div>
						<div class="intro-carousel">
							@foreach($sliders as $data)

								{{-- <div class="intro-content {{$data->position}}" style="background-image: url({{asset('assets/images/sliders/'.$data->photo)}})"> --}}
                                <div class="intro-content {{$data->position}}" style="background-image: url({{ $htd_photo->show_photo($data->photo, 'sliders') }})">
									<div class="container">
										<div class="row">
											<div class="col-lg-12">
												<div class="slider-content">
													<!-- layer 1 -->
													<div class="layer-1">
														<h4 style="font-size: {{$data->subtitle_size}}px; color: {{$data->subtitle_color}}" class="subtitle subtitle{{$data->id}}" data-animation="animated {{$data->subtitle_anime}}">{{$data->subtitle_text}}</h4>
														<h2 style="font-size: {{$data->title_size}}px; color: {{$data->title_color}}" class="title title{{$data->id}}" data-animation="animated {{$data->title_anime}}">{{$data->title_text}}</h2>
													</div>
													<!-- layer 2 -->
													<div class="layer-2">
														<p style="font-size: {{$data->details_size}}px; color: {{$data->details_color}}"  class="text text{{$data->id}}" data-animation="animated {{$data->details_anime}}">{{$data->details_text}}</p>
													</div>
													<!-- layer 3 -->
													<div class="layer-3">
														<a href="{{$data->link}}" target="_blank" class="mybtn1"><span>{{ $langg->lang25 }} <i class="fas fa-chevron-right"></i></span></a>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							@endforeach
						</div>
					</div>
				@endif
			@endif
		</section>
		<!-- Hero Area End -->
	@endif


	@if($ps->featured_category == 1)

	{{-- Slider buttom Category Start --}}
	<section class="slider-buttom-category d-none d-md-block">
		<div class="container-fluid">
			<div class="row">
				@foreach($categories->where('is_featured','=',1) as $cat)
					<div class="col-xl-2 col-lg-3 col-md-4 sc-common-padding">
						<a href="{{ route('front.category',$cat->slug) }}" class="single-category">
							<div class="left">
								<h5 class="title">
									{{ $cat->name }}
								</h5>
								<p class="count">
									{{ count($cat->products) }} {{ $langg->lang4 }}
								</p>
							</div>
							<div class="right">
								{{-- <img src="{{asset('assets/images/categories/'.$cat->image) }}" alt=""> --}}
								<img src="{{ $cat->show_image() }}" alt="">
							</div>
						</a>
					</div>
				@endforeach
			</div>
		</div>
	</section>
	{{-- Slider buttom banner End --}}

	@endif
	<section  class="trending product_new">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 remove-padding">
					<div class="section-top">
						<h2 class="section-title">
							SP Mới
						</h2>
						{{-- <a href="#" class="link">View All</a> --}}
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12 remove-padding">
					<div class="trending-item-slider">
						@foreach($new_products as $prod)
							@include('includes.product.slider-product')
						@endforeach
					</div>
				</div>

			</div>
		</div>
	</section>
	@if($ps->featured == 1)
		<!-- Trending Item Area Start -->
		<section  class="trending">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 remove-padding">
						<div class="section-top">
							<h2 class="section-title">
								{{ $langg->lang26 }}
							</h2>
							{{-- <a href="#" class="link">View All</a> --}}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12 remove-padding">
						<div class="trending-item-slider">
							@foreach($feature_products as $prod)
								@include('includes.product.slider-product')
							@endforeach
						</div>
					</div>

				</div>
			</div>
		</section>
		<!-- Tranding Item Area End -->
	@endif

	@if($ps->small_banner == 1)

		<!-- Banner Area One Start -->
		<section class="banner-section">
			<div class="container">
				@foreach($top_small_banners->chunk(2) as $chunk)
					<div class="row">
						@foreach($chunk as $img)
							<div class="col-lg-6 remove-padding">
								<div class="left">
									<a class="banner-effect" href="{{ $img->link }}" target="_blank">
										<img src="{{asset('assets/images/banners/'.$img->photo)}}" alt="">
									</a>
								</div>
							</div>
						@endforeach
					</div>
				@endforeach
			</div>
		</section>
		<!-- Banner Area One Start -->
	@endif

	<section id="extraData">
		@if($ps->best == 1)
			<!-- Phone and Accessories Area Start -->
			<section class="phone-and-accessories categori-item">
				<div class="container">
					<div class="row">
						<div class="col-lg-12 remove-padding">
							<div class="section-top">
								<h2 class="section-title">
									{{ $langg->lang27 }}
								</h2>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="row">
								{{-- san pham ban chay nhat --}}
								@foreach($best_products as $prod)
									@include('includes.product.home-product')
								@endforeach
							</div>
						</div>
{{--						<div class="col-lg-3 remove-padding d-none d-lg-block">--}}
{{--							<div class="aside">--}}
{{--								<a class="banner-effect mb-10" href="{{ $ps->best_seller_banner_link }}">--}}
{{--									<img src="{{asset('assets/images/'.$ps->best_seller_banner)}}" alt="">--}}
{{--								</a>--}}
{{--								<a class="banner-effect" href="{{ $ps->best_seller_banner_link1 }}">--}}
{{--									<img src="{{asset('assets/images/'.$ps->best_seller_banner1)}}" alt="">--}}
{{--								</a>--}}
{{--							</div>--}}
{{--						</div>--}}
					</div>
				</div>
			</section>
			<!-- Phone and Accessories Area start-->
		@endif

		@if($ps->flash_deal == 1)
			<!-- Electronics Area Start -->
	
			@if($discount_products->count()	> 0)
				<section class="categori-item electronics-section">
					<div class="container">
						<div class="row">
							<div class="col-lg-12 remove-padding">
								<div class="section-top">
									<h2 class="section-title">
										{{ $langg->lang244 }}
									</h2>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12">
								<div class="flash-deals">
									<div class="flas-deal-slider">
										{{-- Deal Chớp Nhoáng --}}
										@foreach($discount_products as $prod)
											@include('includes.product.flash-product')
										@endforeach
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>
			@endif
			<!-- Electronics Area start-->
		@endif

		@if($ps->large_banner == 1)
			<!-- Banner Area One Start -->
			<?php 
		//	dd($large_banners->chunk(1));
			?>
			<section class="banner-section">
				<div class="container">
					@foreach($large_banners->chunk(1) as $chunk)
						<div class="row">
							@foreach($chunk as $img)
								<div class="col-lg-12 remove-padding">
									<div class="img">
										<a class="banner-effect" href="{{ $img->link }}">
											@if(file_exists(asset('assets/images/banners/'.$img->photo)))
												<img src="{{asset('assets/images/banners/'.$img->photo)}}" alt="">
											@endif	
										</a>
									</div>
								</div>
							@endforeach
						</div>
					@endforeach
				</div>
			</section>
			<!-- Banner Area One Start -->
		@endif

		@if($ps->top_rated == 1)
			<!-- Electronics Area Start -->
			<section class="categori-item electronics-section">
				<div class="container">
					<div class="row">
						<div class="col-lg-12 remove-padding">
							<div class="section-top">
								<h2 class="section-title">
									{{ $langg->lang28 }}
								</h2>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="row">
								{{-- Xếp hạng cao nhất --}}
								@foreach($top_products as $prod)
									@include('includes.product.top-product')
								@endforeach

							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- Electronics Area start-->
		@endif

		@if($ps->bottom_small == 1)
			<!-- Banner Area One Start -->
			<section class="banner-section">
				<div class="container">
					@foreach($bottom_small_banners->chunk(3) as $chunk)
						<div class="row">
							@foreach($chunk as $img)
								<div class="col-lg-4 remove-padding">
									<div class="left">
										<a class="banner-effect" href="{{ $img->link }}" target="_blank">
											<img src="{{asset('assets/images/banners/'.$img->photo)}}" alt="">
										</a>
									</div>
								</div>
							@endforeach
						</div>
					@endforeach
				</div>
			</section>
			<!-- Banner Area One Start -->
		@endif

		@if($ps->big == 1)
			<section class="categori-item clothing-and-Apparel-Area">
				<div class="container">
					<div class="row">
						<div class="col-lg-12 remove-padding">
							<div class="section-top">
								<h2 class="section-title">
									{{ $langg->lang29 }}
								</h2>

							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-9">
							<div class="row">
								@foreach($big_products as $prod)
									@include('includes.product.home-product')
								@endforeach
							</div>
						</div>
						<div class="col-lg-3 remove-padding d-none d-lg-block">
							<div class="aside">
								<a class="banner-effect mb-10" href="{{ $ps->big_save_banner_link }}">
									<img src="{{asset('assets/images/'.$ps->big_save_banner)}}" alt="">
								</a>
								<a class="banner-effect" href="{{ $ps->big_save_banner_link1 }}">
									<img src="{{asset('assets/images/'.$ps->big_save_banner1)}}" alt="">
								</a>
							</div>
						</div>
					</div>
				</div>
				</div>
			</section>
		@endif

		@if($ps->hot_sale == 1)
			<!-- hot-and-new-item Area Start -->
			<section class="hot-and-new-item d-none">
				<div class="container">
					<div class="row">
						<div class="col-lg-12">
							<div class="accessories-slider">
								<div class="slide-item">
									<div class="row">
										<div class="col-lg-3 col-sm-6">
											<div class="categori">
												<div class="section-top">
													<h2 class="section-title">
														{{ $langg->lang30 }}
													</h2>
												</div>
												<div class="hot-and-new-item-slider">
													@foreach($hot_products->chunk(3) as $chunk)
														<div class="item-slide">
															<ul class="item-list">
																@foreach($chunk as $prod)
																	@include('includes.product.list-product')
																@endforeach
															</ul>
														</div>
													@endforeach
												</div>

											</div>
										</div>

										<div class="col-lg-3 col-sm-6">
											<div class="categori">
												<div class="section-top">
													<h2 class="section-title">
														{{ $langg->lang31 }}
													</h2>
												</div>

												<div class="hot-and-new-item-slider">

													@foreach($latest_products as $chunk)
														<div class="item-slide">
															<ul class="item-list">
																{{-- @foreach($chunk as $prod)
																	@include('includes.product.list-product')
																@endforeach --}}
															</ul>
														</div>
													@endforeach

												</div>
											</div>
										</div>

										{{-- <div class="col-lg-3 col-sm-6">
											<div class="categori">
												<div class="section-top">
													<h2 class="section-title">
														{{ $langg->lang32 }}
													</h2>
												</div>


												<div class="hot-and-new-item-slider">

													@foreach($trending_products->chunk(1) as $chunk)
														<div class="item-slide">
															<ul class="item-list">
																@foreach($chunk as $prod)
																	@include('includes.product.list-product')
																@endforeach
															</ul>
														</div>
													@endforeach

												</div>

											</div>
										</div> --}}

										{{-- <div class="col-lg-3 col-sm-6">
											<div class="categori">
												<div class="section-top">
													<h2 class="section-title">
														{{ $langg->lang33 }}
													</h2>
												</div>

												<div class="hot-and-new-item-slider">

													@foreach($sale_products->chunk(1) as $chunk)
														<div class="item-slide">
															<ul class="item-list">
																@foreach($chunk as $prod)
																	@include('includes.product.list-product')
																@endforeach
															</ul>
														</div>
													@endforeach

												</div>
											</div>
										</div> --}}

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- Clothing and Apparel Area start-->
		@endif

		@if($ps->review_blog == 1)
			<!-- Blog Area Start -->
			<section class="blog-area">
				<div class="container">
					<div class="row">
						<div class="col-lg-6">
							<div class="aside">
								<div class="slider-wrapper">
									<div class="aside-review-slider">
										@foreach($reviews as $review)
											<div class="slide-item">
												<div class="top-area">
													<div class="left">
														<img src="{{ $review->photo ? asset('assets/images/reviews/'.$review->photo) : asset('assets/images/noimage.png') }}" alt="">
													</div>
													<div class="right">
														<div class="content">
															<h4 class="name">{{ $review->title }}</h4>
															<p class="dagenation">{{ $review->subtitle }}</p>
														</div>
													</div>
												</div>
												<blockquote class="review-text">
													<p>
														{!! $review->details !!}
													</p>
												</blockquote>
											</div>
										@endforeach
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							@foreach(DB::table('blogs')->orderby('views','desc')->take(2)->get() as $blogg)
								<div class="blog-box">
									<div class="blog-images">
										<div class="img">
											<img src="{{ $blogg->photo ? asset('assets/images/blogs/'.$blogg->photo):asset('assets/images/noimage.png') }}" class="img-fluid" alt="">
											<div class="date d-flex justify-content-center">
												<div class="box align-self-center">
													<p>{{date('d', strtotime($blogg->created_at))}}</p>
													<p>{{date('M', strtotime($blogg->created_at))}}</p>
												</div>
											</div>
										</div>

									</div>
									<div class="details">
										<a href='{{route('front.blogshow',$blogg->id)}}'>
											<h4 class="blog-title">
												{{mb_strlen($blogg->title,'utf-8') > 40 ? mb_substr($blogg->title,0,40,'utf-8')."...":$blogg->title}}
											</h4>
										</a>
										<p class="blog-text">
											{{substr(strip_tags($blogg->details),0,170)}}
										</p>
										<a class="read-more-btn" href="{{route('front.blogshow',$blogg->id)}}">{{ $langg->lang34 }}</a>
									</div>
								</div>

							@endforeach

						</div>
					</div>
				</div>
			</section>
			<!-- Blog Area start-->
		@endif

		@if($ps->partners == 1)
			<!-- Partners Area Start -->
			<section class="partners">
				<div class="container">
					<div class="row">
						<div class="col-lg-12">
							<div class="section-top">
								<h2 class="section-title">
									{{ $langg->lang236 }}
								</h2>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="partner-slider">
								@foreach($partners as $data)
									<div class="item-slide">
										<a href="{{ $data->link }}" target="_blank">
											<img src="{{asset('assets/images/partner/'.$data->photo)}}" alt="">
										</a>
									</div>
								@endforeach
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- Partners Area Start -->
		@endif

		@if($ps->service == 1)
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
		@endif
		<!-- <div class="text-center">
			<img src="{{asset('assets/images/'.$gs->loader)}}">
		</div> -->
	</section>


@endsection

@section('scripts')
	<!-- <script>
        $(window).on('load',function() {

            setTimeout(function(){

                $('#extraData').load('{{route('front.extraIndex')}}');

            }, 15000);
        });

	</script> -->
@endsection
