@extends('layouts.front')

@section('styles')

<style type="text/css">

.root.root--in-iframe {
    background: #4682b447 !important;
}
</style>

@endsection

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
						<a href="{{ route('front.checkout') }}">
							{{ $langg->lang136 }}
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<!-- Breadcrumb Area End -->

	<!-- Check Out Area Start -->
	<section class="checkout">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="checkout-area mb-0 pb-0">
						<div class="checkout-process">
							<ul class="nav"  role="tablist">
								<li class="nav-item">
									<a class="nav-link active" id="pills-step1-tab" data-toggle="pill" href="#pills-step1" role="tab" aria-controls="pills-step1" aria-selected="true">
									<span>1</span> {{ $langg->lang743 }}
									<i class="far fa-address-card"></i>
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link disabled" id="pills-step2-tab" data-toggle="pill" href="#pills-step2" role="tab" aria-controls="pills-step2" aria-selected="false" >
										<span>2</span> {{ $langg->lang744 }}
										<i class="fas fa-dolly"></i>
									</a>
								</li>
								<li class="nav-item">
									<a class="nav-link disabled" id="pills-step3-tab" data-toggle="pill" href="#pills-step3" role="tab" aria-controls="pills-step3" aria-selected="false">
											<span>3</span> {{ $langg->lang745 }}
											<i class="far fa-credit-card"></i>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>


				<div class="col-lg-8">


		<form id="" action="" method="POST" class="checkoutform">

			@include('includes.form-success')
			@include('includes.form-error')

			{{ csrf_field() }}

					<div class="checkout-area">
						<div class="tab-content" id="pills-tabContent">
							<div class="tab-pane fade show active" id="pills-step1" role="tabpanel" aria-labelledby="pills-step1-tab">
								<div class="content-box">

									<div class="content">
										<div class="personal-info">
											<h5 class="title">
												{{ $langg->lang746 }} :
											</h5>
											<div class="row">
												<div class="col-lg-6">
													<input type="text" id="personal-name" class="form-control" name="personal_name" placeholder="{{ $langg->lang747 }}" value="{{ Auth::check() ? Auth::user()->name : '' }}" {!! Auth::check() ? 'readonly' : '' !!}>
												</div>
												<div class="col-lg-6">
													<input type="email" id="personal-email" class="form-control" name="personal_email" placeholder="{{ $langg->lang748 }}" value="{{ Auth::check() ? Auth::user()->email : '' }}"  {!! Auth::check() ? 'readonly' : '' !!}>
												</div>
											</div>
											@if(!Auth::check())
											<div class="row">
												<div class="col-lg-12 mt-3">
														<input class="styled-checkbox" id="open-pass" type="checkbox" value="1" name="pass_check">
														<label for="open-pass">{{ $langg->lang749 }}</label>
												</div>
											</div>
											<div class="row set-account-pass d-none">
												<div class="col-lg-6">
													<input type="password" name="personal_pass" id="personal-pass" class="form-control" placeholder="{{ $langg->lang750 }}">
												</div>
												<div class="col-lg-6">
													<input type="password" name="personal_confirm" id="personal-pass-confirm" class="form-control" placeholder="{{ $langg->lang751 }}">
												</div>
											</div>
											@endif
										</div>
										<div class="billing-address">
											<h5 class="title">
												{{ $langg->lang147 }}
											</h5>
											<div class="row">
												{{-- <div class="col-lg-6 {{ $digital == 1 ? 'd-none' : '' }} d-none"> --}}
                                                <div class="col-lg-6 d-none">
													<select class="form-control" id="shipop" name="shipping" >
														<option value="shipto" selected>{{ $langg->lang149 }}</option>
														<option value="viettelpost" >{{ isset($langg->lang891) ? $langg->lang891 : 'Viettel Post' }}</option>
														<option value="negotiate">{{ isset($langg->lang892) ? $langg->lang892 : 'Negotiate' }}</option>
													</select>
												</div>

												<div class="col-lg-6 d-none" id="shipshow">
													<select class="form-control nice" name="pickup_location">
														@foreach($pickups as $pickup)
														<option value="{{$pickup->location}}">{{$pickup->location}}</option>
														@endforeach
													</select>
												</div>

												<div class="col-lg-6">
													<input class="form-control" type="text" name="name"
														placeholder="{{ $langg->lang152 }}" required=""
														value="{{ Auth::guard('web')->check() ? Auth::guard('web')->user()->name : '' }}">
												</div>
												<div class="col-lg-6">
													<input class="form-control" type="text" name="phone"
														placeholder="{{ $langg->lang153 }}" required=""
														value="{{ Auth::guard('web')->check() ? Auth::guard('web')->user()->phone : '' }}">
												</div>
												<div class="col-lg-6">
													<input class="form-control" type="text" name="email"
														placeholder="{{ $langg->lang154 }}" required=""
														value="{{ Auth::guard('web')->check() ? Auth::guard('web')->user()->email : '' }}">
												</div>

												<div class="col-lg-6 d-none">
													<select class="form-control" name="customer_country" required="">
														@include('includes.countries')
													</select>
												</div>
												<div class="col-lg-6 d-none">
													<input class="form-control city-default" type="text" name="city"
														placeholder="{{ $langg->lang158 }}"
														value="{{ Auth::guard('web')->check() ? Auth::guard('web')->user()->city : '' }}">
												</div>
												<div class="col-lg-6 d-none">
													<input class="form-control" type="text" name="zip"
														placeholder="{{ $langg->lang159 }}"
														value="700000">
                                                </div>
                                                <div class="col-lg-6">
													<select class="form-control customer_province" name="customer_province" required="">
														@include('includes.provinces')
													</select>
												</div>
                                                <div class="col-lg-6">
													<select class="form-control customer_district" name="customer_district" required="">
                                                        <option value="">{{ $langg->lang894 }}</option>
                                                        @if(Auth::check())
                                                            @foreach (DB::table('districts')->where('province_id','=',Auth::user()->CityID)->get() as $data)
                                                            <option value="{{ $data->id }}" {{ Auth::user()->DistrictID == $data->id ? 'selected' : '' }}>
                                                                {{ $data->name }}
                                                            </option>
                                                            @endforeach
                                                        @endif

													</select>
                                                </div>
                                                <div class="col-lg-6">
													<select class="form-control customer_ward" name="customer_ward" required="">
                                                        <option value="">{{ $langg->lang895 }}</option>
													</select>
                                                </div>
                                                <div class="col-lg-6">
													<input class="form-control" type="text" name="address"
														placeholder="{{ $langg->lang155 }}" required=""
														value="{{ Auth::guard('web')->check() ? Auth::guard('web')->user()->address : '' }}">
                                                </div>
                                                <div class="col-lg-6 d-none">
                                                    <input type="text" class="form-control is_shipdiff" name="is_shipdiff" value="false">
												</div>
											</div>
										</div>
										<div class="row {{ $digital == 1 ? 'd-none' : '' }}">
											<div class="col-lg-12 mt-3">
                                                    <input class="styled-checkbox" id="ship-diff-address" type="checkbox" value="value1" >
													<label for="ship-diff-address">{{ $langg->lang160 }}</label>
											</div>
										</div>
										<div class="ship-diff-addres-area d-none">
												<h5 class="title">
														{{ $langg->lang752 }}
												</h5>
											<div class="row">
												<div class="col-lg-6">
													<input class="form-control ship_input" type="text" name="shipping_name"
														id="shippingFull_name" placeholder="{{ $langg->lang152 }}">
														<input type="hidden" name="shipping_email" value="">
												</div>
												<div class="col-lg-6">
													<input class="form-control ship_input" type="text" name="shipping_phone"
														id="shipingPhone_number" placeholder="{{ $langg->lang153 }}">
												</div>
                                            </div>
                                            <div class="row">
												<div class="col-lg-6">
                                                    <select class="form-control ship_input shipping_province" name="shipping_province">
                                                        <option value="">{{ $langg->lang893 }}</option>
                                                        @foreach (DB::table('provinces')->orderBy('name', 'asc')->get() as $data)
                                                            <option value="{{ $data->id }}">
                                                                {{ $data->name }}
                                                            </option>
                                                        @endforeach
													</select>
												</div>
												<div class="col-lg-6">
													<select class="form-control ship_input shipping_district" name="shipping_district">
                                                        <option value="">{{ $langg->lang894 }}</option>
													</select>
												</div>

											</div>
											<div class="row">
                                                <div class="col-lg-6">
													<select class="form-control ship_input shipping_ward" name="shipping_ward">
                                                        <option value="">{{ $langg->lang895 }}</option>
													</select>
                                                </div>
												<div class="col-lg-6">
													<input class="form-control ship_input" type="text" name="shipping_address"
														id="shipping_address" placeholder="{{ $langg->lang155 }}">
												</div>

												<div class="col-lg-6 d-none">
													<select class="form-control ship_input" name="shipping_country">
														@include('includes.countries')
													</select>
												</div>
                                            </div>

                                            <div class="row">
												<div class="col-lg-6">
													<input class="form-control ship_input" type="text" name="shipping_city"
														id="shipping_city" placeholder="{{ $langg->lang158 }}">
												</div>
												<div class="col-lg-6">
													<input class="form-control ship_input" type="text" name="shipping_zip"
														id="shippingPostal_code" placeholder="{{ $langg->lang159 }}" value="700000">
												</div>

											</div>
										</div>
										<div class="order-note mt-3">
											<div class="row">
												<div class="col-lg-12">
												<input type="text" id="Order_Note" class="form-control" name="order_notes" placeholder="{{ $langg->lang217 }} ({{ $langg->lang218 }})">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-lg-12  mt-3">
												<div class="bottom-area paystack-area-btn">
													<button type="submit"  class="mybtn1">{{ $langg->lang753 }}</button>
												</div>

											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="pills-step2" role="tabpanel" aria-labelledby="pills-step2-tab">
								<div class="content-box">
									<div class="content">

										<div class="order-area">

                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}
                                            {{-- ORDER DETAIL  --}}

											@foreach($products as $product)
											<div class="order-item">
												<div class="product-img">
													<div class="d-flex">
														{{-- <img src=" {{ asset('assets/images/products/'.$product['item']['photo']) }}" height="80" width="80" class="p-1"> --}}
														<img src=" {{ $htd_photo->show_photo($product['item']['photo'], 'products') }}" height="80" width="80" class="p-1">

													</div>
												</div>
												<div class="product-content">
													<p class="name"><a
															href="{{ route('front.product', $product['item']['slug']) }}"
															target="_blank">{{ $product['item']['name'] }}</a></p>
													<div class="unit-price">
														<h5 class="label">{{ $langg->lang754 }} : </h5>
														<p>{{ App\Models\Product::convertPrice($product['item_price']) }}</p>
													</div>
                                                    <div class="unit-price">
														<h5 class="label">{{ $langg->lang896 }} : </h5>
														<p>{{ number_format($product['item_price_shopping_point']) }} ({{ $product['percent_shopping_point'] }}%)</p>
													</div>
													@if(!empty($product['size']))
													<div class="unit-price">
														<h5 class="label">{{ $langg->lang312 }} : </h5>
														<p>{{ str_replace('-',' ',$product['size']) }}</p>
													</div>
													@endif
													@if(!empty($product['color']))
													<div class="unit-price">
														<h5 class="label">{{ $langg->lang313 }} : </h5>
														<span id="color-bar" style="border: 10px solid {{$product['color'] == "" ? "white" : '#'.$product['color']}};"></span>
													</div>
													@endif
													@if(!empty($product['keys']))

													@foreach( array_combine(explode(',', $product['keys']), explode(',', $product['values']))  as $key => $value)

														<div class="quantity">
															<h5 class="label">{{ ucwords(str_replace('_', ' ', $key))  }}: </h5>
															<span class="qttotal">{{ $value }} </span>
														</div>
													@endforeach

													@endif
													<div class="quantity">
														<h5 class="label">{{ $langg->lang755 }}: </h5>
														<span class="qttotal">{{ $product['qty'] }} </span>
													</div>
													<div class="total-price">
														<h5 class="label">{{ $langg->lang756 }}: </h5>
														<p>{{ App\Models\Product::convertPrice($product['price']) }}</p>

													</div>
                                                    <div class="total-price">
														<h5 class="label">{{ $langg->lang897 }}: </h5>
														<p>{{ number_format($product['price_shopping_point']) }}</p>
													</div>
                                                    <input type="hidden" id="product-amount-{{  $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" value="{{ $product['price'] }}">
                                                    <input type="hidden" id="product-price-shopping-point-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" value="{{ $product['price_shopping_point'] }}">
                                                    <input class="styled-checkbox use-shopping-point-item"
                                                        id="use-shopping-point-item-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" type="checkbox"
                                                            value="{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}"
                                                            {{ $product['is_shopping_point_used'] == 1 ? 'checked' : '' }}
                                                            data-val="{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}"
                                                            >
                                                    <label for="use-shopping-point-item-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}">{{ $langg->lang822 }}</label>
                                                    <div id="use-shopping-point-item-input-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" class="{{ $product['is_shopping_point_used'] == 1 ? '' : 'd-none' }}">
                                                        <input id="use-shopping-point-item-number-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" type="number" value="{{ $product['shopping_point_used'] }}" class="use-shopping-point-item-input"
                                                        data-val="{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}"
                                                        data-sizecolor="{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}"
                                                        >
                                                        {{ $langg->lang823 }}:
                                                        <span id="use-shopping-point-item-amount-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}">{{ App\Models\Product::convertPrice($product['shopping_point_amount']) }}</span>
                                                        <div class="total-price">
                                                            <h5 class="label">{{ $langg->lang898 }}: </h5>
                                                            <p id="use-shopping-point-item-payment-remain-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}">{{ App\Models\Product::convertPrice($product['shopping_point_payment_remain']) }}</p>
                                                            <h5 class="label"></h5>
                                                        </div>

                                                    </div>
                                                    <hr style="margin-top: 0px; margin-bottom: 1px;">
                                                    <div class="total-price">
														<h5 class="label">{{ $langg->lang899 }}: </h5>
														<p id="product-sub-amount-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" data-val="{{ $product['product_sub_amount'] }}">{{ App\Models\Product::convertPrice($product['product_sub_amount']) }}</p>
													</div>

                                                    <div class="shop-coupon" >
                                                        <label for="use-shop-coupon-code-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" style="color: #666">{{ $langg->lang900 }}</label><br>
														<input id="use-shop-coupon-code-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" type="text" value="{{ $product['shop_coupon_code'] }}" class="use-shop-coupon-input"
                                                            data-val="{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}"
                                                            data-sizecolor="{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}"
                                                            placeholder="shop's coupon">
                                                        {{ $langg->lang823 }}:
                                                        <span id="use-shop-coupon-amount-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" class="text-success" data-val="{{ $product['shop_coupon_amount'] }}">{{ App\Models\Product::convertPrice($product['shop_coupon_amount']) }}</span>
                                                        <button type="button" class='check-shop-coupon'
                                                            data-sizecolor="{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}"
                                                            data-val="{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}">{{ $langg->lang907 }}</button>
                                                    </div>
                                                    <hr style="margin-bottom: 1px;">
                                                    <div class="total-price">
														<h5 class="label">{{ $langg->lang901 }}: </h5>
														<p id="product-final-amount-{{ $product['item']['id'].$product['size'].$product['color'].str_replace(str_split(' ,'),'',$product['values']) }}" data-val="{{ $product['product_final_amount'] }}">{{ App\Models\Product::convertPrice($product['product_final_amount']) }}</p>
													</div>
												</div>
											</div>

											@endforeach

										</div>

										<div class="row">
											<div class="col-lg-12 mt-3">
												<div class="bottom-area">
													<a href="javascript:;" id="step1-btn"  class="mybtn1 mr-3">{{ $langg->lang757 }}</a>
													<a href="javascript:;" id="step3-btn"  class="mybtn1">{{ $langg->lang753 }}</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="pills-step3" role="tabpanel" aria-labelledby="pills-step3-tab">
								<div class="content-box">
									<div class="content">

											<div class="billing-info-area {{ $digital == 1 ? 'd-none' : '' }}">
															<h4 class="title">
																	{{ $langg->lang758 }}
															</h4>
													<ul class="info-list">
														<li>
															<p id="shipping_user"></p>
														</li>
														<li>
															<p id="shipping_location"></p>
														</li>
														<li>
															<p id="shipping_phone"></p>
														</li>
														<li>
															<p id="shipping_email"></p>
														</li>
													</ul>
											</div>
											<div class="payment-information">
													<h4 class="title">
														{{ $langg->lang759 }}
													</h4>
												<div class="row">
													<div class="col-lg-12">
														<div class="nav flex-column"  role="tablist" aria-orientation="vertical">
														@if($gs->paypal_check == 1)
															<a class="nav-link payment" data-val="" data-show="no" data-form="{{route('paypal.submit')}}" data-href="{{ route('front.load.payment',['slug1' => 'paypal','slug2' => 0]) }}" id="v-pills-tab1-tab" data-toggle="pill" href="#v-pills-tab1" role="tab" aria-controls="v-pills-tab1" aria-selected="true">
																	<div class="icon">
																			<span class="radio"></span>
																	</div>
																<p>
																		{{ $langg->lang760 }}

																	@if($gs->paypal_text != null)

																	<small>
																			{{ $gs->paypal_text }}
																	</small>

																	@endif

																</p>
															</a>
														@endif
														@if($gs->stripe_check == 1)
															<a class="nav-link payment" data-val="" data-show="yes" data-form="{{route('stripe.submit')}}" data-href="{{ route('front.load.payment',['slug1' => 'stripe','slug2' => 0]) }}" id="v-pills-tab2-tab" data-toggle="pill" href="#v-pills-tab2" role="tab" aria-controls="v-pills-tab2" aria-selected="false">
																	<div class="icon">
																			<span class="radio"></span>
																	</div>
																	<p>
																	{{ $langg->lang761 }}

																		@if($gs->stripe_text != null)

																		<small>
																			{{ $gs->stripe_text }}
																		</small>

																		@endif

																	</p>
															</a>
														@endif
														@if($gs->cod_check == 1)
														 @if($digital == 0)
															<a class="nav-link payment" data-val="cod" data-show="no" data-form="{{route('cash.submit')}}" data-href="{{ route('front.load.payment',['slug1' => 'cod','slug2' => 0]) }}" id="v-pills-tab3-tab" data-toggle="pill" href="#v-pills-tab3" role="tab" aria-controls="v-pills-tab3" aria-selected="false">
																	<div class="icon">
																			<span class="radio"></span>
																	</div>
																	<p>
																			{{ $langg->lang762 }}

																		@if($gs->cod_text != null)

																		<small>
																				{{ $gs->cod_text }}
																		</small>

																		@endif

																	</p>
															</a>
														 @endif
														@endif
														@if($gs->is_instamojo == 1)
															<a class="nav-link payment" data-val="" data-show="no" data-form="{{route('instamojo.submit')}}" data-href="{{ route('front.load.payment',['slug1' => 'instamojo','slug2' => 0]) }}"  id="v-pills-tab4-tab" data-toggle="pill" href="#v-pills-tab4" role="tab" aria-controls="v-pills-tab4" aria-selected="false">
																	<div class="icon">
																			<span class="radio"></span>
																	</div>
																	<p>
																			{{ $langg->lang763 }}

																		@if($gs->instamojo_text != null)

																		<small>
																				{{ $gs->instamojo_text }}
																		</small>

																		@endif

																	</p>
															</a>
															@endif
															@if($gs->is_paytm == 1)
																<a class="nav-link payment" data-val="" data-show="no" data-form="{{route('paytm.submit')}}" data-href="{{ route('front.load.payment',['slug1' => 'paytm','slug2' => 0]) }}"  id="v-pills-tab5-tab" data-toggle="pill" href="#v-pills-tab5" role="tab" aria-controls="v-pills-tab5" aria-selected="false">
																		<div class="icon">
																				<span class="radio"></span>
																		</div>
																		<p>
																				{{ $langg->paytm }}

																			@if($gs->paytm_text != null)

																			<small>
																					{{ $gs->paytm_text }}
																			</small>

																			@endif

																		</p>
																</a>
																@endif
																@if($gs->is_razorpay == 1)
																	<a class="nav-link payment" data-val="" data-show="no" data-form="{{route('razorpay.submit')}}" data-href="{{ route('front.load.payment',['slug1' => 'razorpay','slug2' => 0]) }}"  id="v-pills-tab6-tab" data-toggle="pill" href="#v-pills-tab6" role="tab" aria-controls="v-pills-tab6" aria-selected="false">
																			<div class="icon">
																					<span class="radio"></span>
																			</div>
																			<p>

																				{{ $langg->razorpay }}

																				@if($gs->razorpay_text != null)

																				<small>
																						{{ $gs->razorpay_text }}
																				</small>

																				@endif

																			</p>
																	</a>
																	@endif
															{{-- @if($gs->is_paystack == 1)

															<a class="nav-link payment" data-val="paystack" data-show="no" data-form="{{route('paystack.submit')}}" data-href="{{ route('front.load.payment',['slug1' => 'paystack','slug2' => 0]) }}" id="v-pills-tab7-tab" data-toggle="pill" href="#v-pills-tab7" role="tab" aria-controls="v-pills-tab7" aria-selected="false">
																	<div class="icon">
																			<span class="radio"></span>
																	</div>
																	<p>
																			{{ $langg->lang764 }}

																		@if($gs->paystack_text != null)

																		<small>
																				{{ $gs->paystack_text }}
																		</small>

																		@endif
																	</p>
															</a>

															@endif --}}


															@if($gs->is_molly == 1)
															<a class="nav-link payment" data-val="" data-show="no" data-form="{{route('molly.submit')}}" data-href="{{ route('front.load.payment',['slug1' => 'molly','slug2' => 0]) }}" id="v-pills-tab8-tab" data-toggle="pill" href="#v-pills-tab8" role="tab" aria-controls="v-pills-tab8" aria-selected="false">
																	<div class="icon">
																			<span class="radio"></span>
																	</div>
																	<p>
																			{{ $langg->lang802 }}

																		@if($gs->molly_text != null)

																		<small>
																				{{ $gs->molly_text }}
																		</small>

																		@endif
																	</p>
															</a>

															@endif


{{-- @if($digital == 0) --}}

@foreach($gateways as $gt)
	@if($gt->title == 'VNPay')
		<a class="nav-link payment gateway">
			<div class="icon"><span class="radio"></span></div>
			<p style="color: gray;">
				{{ $gt->title }}  (Hệ thống đang được bảo trì)
				@if($gt->subtitle != null)
				<small>{{ $gt->subtitle }}</small>
				@endif
			</p>
		</a>
   @elseif($gt->title == 'Onepay')
   
	@else
		<a class="nav-link payment gateway" data-val="" data-show="{{ ($gt->title=='Alepay' || $gt->title=='Onepay') ? 'no' : 'yes' }}" data-form=" {{ route(($gt->title=='Onepay') ? 'onepay.submit' : (($gt->title=='Alepay')?'alepay.submit':'gateway.submit'))  }}" data-href="{{ route('front.load.payment',['slug1' => 'other','slug2' => $gt->id]) }}" id="v-pills-tab{{ $gt->id }}-tab" data-toggle="pill" href="#v-pills-tab{{ $gt->id }}" role="tab" aria-controls="v-pills-tab{{ $gt->id }}" aria-selected="false">
			<div class="icon">
					<span class="radio"></span>
			</div>
			<p>
					{{ $gt->title }}

				@if($gt->subtitle != null)

				<small>
						{{ $gt->subtitle }}
				</small>

				@endif

			</p>
		</a>
	@endif
@endforeach


{{-- @endif --}}

														</div>
													</div>
													<div class="col-lg-12">
													  <div class="pay-area d-none">
														<div class="tab-content" id="v-pills-tabContent">
															@if($gs->paypal_check == 1)
															<div class="tab-pane fade" id="v-pills-tab1" role="tabpanel" aria-labelledby="v-pills-tab1-tab">

															</div>
															@endif
															@if($gs->stripe_check == 1)
															<div class="tab-pane fade" id="v-pills-tab2" role="tabpanel" aria-labelledby="v-pills-tab2-tab">
															</div>
															@endif
															@if($gs->cod_check == 1)
															@if($digital == 0)
															<div class="tab-pane fade" id="v-pills-tab3" role="tabpanel" aria-labelledby="v-pills-tab3-tab">
															</div>
															@endif
															@endif
															@if($gs->is_instamojo == 1)
																<div class="tab-pane fade" id="v-pills-tab4" role="tabpanel" aria-labelledby="v-pills-tab4-tab">
																</div>
															@endif
															@if($gs->is_paytm == 1)
																<div class="tab-pane fade" id="v-pills-tab5" role="tabpanel" aria-labelledby="v-pills-tab5-tab">
																</div>
															@endif
															@if($gs->is_razorpay == 1)
																<div class="tab-pane fade" id="v-pills-tab6" role="tabpanel" aria-labelledby="v-pills-tab6-tab">
																</div>
															@endif
															{{-- @if($gs->is_paystack == 1)
																<div class="tab-pane fade" id="v-pills-tab7" role="tabpanel" aria-labelledby="v-pills-tab7-tab">
																</div>
															@endif --}}
															@if($gs->is_molly == 1)
																<div class="tab-pane fade" id="v-pills-tab8" role="tabpanel" aria-labelledby="v-pills-tab8-tab">
																</div>
															@endif

													{{-- @if($digital == 0) --}}
														@foreach($gateways as $gt)

															<div class="tab-pane fade" id="v-pills-tab{{ $gt->id }}" role="tabpanel" aria-labelledby="v-pills-tab{{ $gt->id }}-tab">

															</div>

														@endforeach
													{{-- @endif --}}
													</div>
														</div>
													</div>
												</div>
											</div>

										<div class="row">
												<div class="col-lg-12 mt-3">
													<div class="bottom-area">

															<a href="javascript:;" id="step2-btn" class="mybtn1 mr-3">{{ $langg->lang757 }}</a>
															<button type="submit" id="final-btn" class="mybtn1">{{ $langg->lang753 }}</button>
													</div>

												</div>
											</div>
									</div>
								</div>
							</div>
						</div>
                    </div>

                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}
                                            {{-- HIDDEN DETAIL  --}}


                            <input type="hidden" class="BankPay" name="payment_bank" value="NCB" />
                            <input type="hidden" id="shipping-type" name="shipping_type" value="viettelpost" />
                            <input type="hidden" class="is-online-payment" name="is_online_payment" value="1" />
                            <input type="hidden" id="auth-check" value="{{ Auth::check() }}">
                            <input type="hidden" id="auth-ward-id" value="{{ Auth::check() ? Auth::user()->ward_id : 0 }}">
                            <input type="hidden" id="packing-cost" name="packing_cost" value="0">
                            <input type="hidden" name="dp" value="{{$digital}}" id="digital">
                            <input type="hidden" name="totalQty" value="{{$totalQty}}">
                            <input type="hidden" id="gs-sp-ex" value="{{$gs->sp_vnd_exchange_rate}}">
                            <input type="hidden" name="vendor_shipping_id" value="{{ $vendor_shipping_id }}">
                            <input type="hidden" name="vendor_packing_id" value="{{ $vendor_packing_id }}">
                            <input type="hidden" id="total-SP-used" value="{{ (double)Session::get('cart')->totalSPUsed }}">
                            <input type="hidden" id="total-SP-Diff" value="{{ Auth::check() ? Auth::user()->shopping_point - (double)Session::get('cart')->totalSPUsed : 0 }}">
                            <input type="hidden" id="ttotal" name="products_amount"  value="{{ Session::has('cart') ? Session::get('cart')->totalPrice : '0' }}">
                            <input type="hidden" id="gs_tax" name="tax" value="{{$gs->tax}}" >
                            <input type="hidden" id="shipping-cost" name="shipping_cost" value=0>
                            <input type="hidden" id="coupon_discount" name="coupon_discount"  value="{{ Session::has('coupon') ? Session::get('coupon') : '' }}">
                            <input type="hidden" id="total-SP-Amount" value="{{ (double)Session::get('cart')->totalSPAmount }}">
                            <input type="hidden" id="total-ShopCoupon-Amount" value="{{ (double)Session::get('cart')->totalShopCouponAmount }}">
                            <input type="hidden" id="total-sp-price" value="{{ (double)Session::get('cart')->totalSPPrice }}">
                            <input type="hidden" id="total-sp-price-amount" value="{{ (double)Session::get('cart')->totalSPPriceAmount }}">
                            <input type="hidden" id="total-sp-price-remain-amount" value="{{ (double)Session::get('cart')->totalSPPriceRemainAmount }}">
                            <input type="hidden" id="total-product-sub-amount" value="{{ (double)Session::get('cart')->totalProductSubAmount }}">
                            <input type="hidden" id="total-product-final-amount" value="{{ (double)Session::get('cart')->totalProductFinalAmount }}">
                            <input type="hidden" name="total" id="grandtotal" value="{{ $totalPrice }}">
                            <input type="hidden" name="coupon_code" id="coupon_code" value="{{ Session::has('coupon_code') ? Session::get('coupon_code') : '' }}">
                            <input type="hidden" name="coupon_id" id="coupon_id" value="{{ Session::has('coupon') ? Session::get('coupon_id') : '' }}">
                            <input type="hidden" name="user_id" id="user_id" value="{{ Auth::guard('web')->check() ? Auth::guard('web')->user()->id : '' }}">
                            <input type="hidden" id="checked" value="{{ isset($checked) ? $checked : null }}">
                            <input type="hidden" id="currency_format" value="{{ $gs->currency_format }}">
                            <input type="hidden" id="curr-sign" value="{{ $curr->sign }}">


							<!-- AlEPAY -->

                            <input type="hidden" name="cancelUrl" value="{{ route('alepay.cancel') }}" class="form-control">
                            <input type="hidden" class="form-control" name="currency" value="VND">
                            <input type="hidden" class="form-control" name="customMerchantId" value="lam123">
                            <input type="hidden" name="orderDescription" value="Mô tả hóa đơn" class="form-control">
                            <input type="hidden" name="returnUrl" value="{{ route('alepay.return') }}" class="form-control">
                            <input type="hidden" name="tokenKey" value="WfTWDF1rgtllijKQOssnr3y1yNCaoG" class="form-control">

</form>

				</div>



                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}
                {{-- SUMMARY DETAIL  --}}

				@if(Session::has('cart'))
				<div class="col-lg-4">
					<div class="right-area">

						<div class="order-box">
						@if (Auth::check())
						<h4 class="title">{{ $langg->lang896 }}</h4>
                        <ul class="order-list">
							<li>
							<p>
								{{ $langg->lang819 }}
							</p>
							<P>
								<b><span>{{ number_format(Auth::user()->shopping_point) }}</span></b>
							</P>
							</li>

                            <li>
                                <p>
                                    {{ $langg->lang820 }}
                                </p>
                                <P>
                                    <b><span id="totalSPUsed">{{ number_format(Session::get('cart')->totalSPUsed) }}</span></b>
                                </P>
                            </li>

                            {{-- <li>
                                <p>
                                    {{ $langg->lang820 }}
                                </p>
                                <P>{{ $curr->sign }}<b><span id="totalSPAmount">{{ number_format((double)Session::get('cart')->totalSPAmount) }}</span> </b>
                                </P>
                            </li> --}}

                            <li>
                                <p>
                                    {{ $langg->lang821 }}
                                </p>
                                <P>
                                    <b><span id="totalSPDiff">{{ number_format(Auth::user()->shopping_point - Session::get('cart')->totalSPUsed) }}</span> </b>
                                </P>
                            </li>
                        </ul>
						@endif


						<h4 class="title">{{ $langg->lang127 }}</h4>
						<ul class="order-list">
							<li>
                                <p>
                                    {{ $langg->lang902 }}
                                </p>
                                <P>
                                    <b
                                    class="cart-total">{{ Session::has('cart') ? App\Models\Product::convertPrice(Session::get('cart')->totalPrice) : '0.00' }}</b>
                                </P>
							</li>

                            <li>
                                <p>
                                    {{ $langg->lang903 }}
                                </p>
                                <P>
                                    <b>{{ Session::has('cart') ? App\Models\Product::convertPrice(Session::get('cart')->totalSPPriceAmount) : '0.00' }}</b>
                                </P>
							</li>

                            <li>
                                <p>
                                    {{ $langg->lang904 }}
                                </p>
                                <P>
                                    <b> <span id="shopping-point-amount">{{ Session::has('cart') ? App\Models\Product::convertPrice(Session::get('cart')->totalSPAmount) : '0.00' }}</span> </b>
                                </P>
                            </li>

                            <li>
                                <p>
                                    {{ $langg->lang905 }}
                                </p>
                                <P>
                                    <b> <span id="totalShopCouponAmount">{{ Session::has('cart') ? App\Models\Product::convertPrice(Session::get('cart')->totalShopCouponAmount) : '0.00' }}</span> </b>
                                </P>
                            </li>

                            <li class="discount-bar">
                                <p>
                                    {{ $langg->lang145 }} <span class="dpercent">{{ Session::get('coupon_percentage') == 0 ? '' : '('.Session::get('coupon_percentage').')' }}</span>
                                </p>
                                <P>
                                    @if($gs->currency_format == 0)
                                        <b id="discount">{{ $curr->sign }}{{ number_format(Session::get('coupon')) }}</b>
                                    @else
                                        <b id="discount">{{ number_format(Session::get('coupon')) }}{{ $curr->sign }}</b>
                                    @endif
                                </P>
                                </li>

                            {{-- @if(Session::has('coupon'))

                                <li class="discount-bar">
                                <p>
                                    {{ $langg->lang145 }} <span class="dpercent">{{ Session::get('coupon_percentage') == 0 ? '' : '('.Session::get('coupon_percentage').')' }}</span>
                                </p>
                                <P>
                                    @if($gs->currency_format == 0)
                                        <b id="discount">{{ $curr->sign }}{{ number_format(Session::get('coupon')) }}</b>
                                    @else
                                        <b id="discount">{{ number_format(Session::get('coupon')) }}{{ $curr->sign }}</b>
                                    @endif
                                </P>
                                </li>

                            @endif --}}

							@if($gs->tax != 0)

							<li>
							<p>
								{{ $langg->lang144 }} ({{$gs->tax}}%)
							</p>
							<P>
								<b> {{ Session::has('cart') ? App\Models\Product::convertPrice((Session::get('cart')->totalPrice + Session::get('cart')->totalSPPriceAmount) * ( $gs->tax / 100.0))   : '0.00' }} </b>

							</P>
							</li>

							@endif
						</ul>

		            <div class="total-price">
		              <p>
		                {{ $langg->lang131 }}
		              </p>
		              <p>
						<span id="total-cost">{{ App\Models\Product::convertPrice($totalPrice) }}</span>
		              </p>
		            </div>
						<div class="cupon-box">

							<div id="coupon-link">
							<img src="{{ asset('assets/front/images/tag.png') }}">
							{{ $langg->lang132 }}
							</div>

						    <form id="check-coupon-form" class="coupon">
                                <span id="coupon_code_text" style="color: green">{{ Session::has('coupon_code') ? Session::get('coupon_code') : '' }}</span> <br>
						        <input type="text" placeholder="{{ $langg->lang133 }}" id="code" required="" autocomplete="off" value=""><br>
						        <button type="submit">{{ $langg->lang134 }}</button>
                                <button type="button" id="clear-coupon">CLEAR</button>
						    </form>


						</div>

						@if($digital == 0)

						{{-- Shipping Method Area Start --}}
						<div class="packeging-area">
                                {{-- <h4 class="title">{{ $langg->lang765 }}</h4> --}}
                                <h4 class="title">{{ $langg->lang813 }}</h4>

							{{-- @foreach($shipping_data as $data)

								<div class="radio-design">
										<input type="radio" class="shipping" id="free-shepping{{ $data->id }}" name="shipping" value="{{ round($data->price * $curr->value,2) }}" {{ ($loop->first) ? 'checked' : '' }}>
										<span class="checkmark"></span>
										<label for="free-shepping{{ $data->id }}">
												{{ $data->title }}
												@if($data->price != 0)
												+ {{ $curr->sign }}{{ round($data->price * $curr->value,2) }}
												@endif
												<small>{{ $data->subtitle }}</small>
										</label>
								</div>

							@endforeach --}}
                            <div class="radio-design" style="display: none">
                                <input type="radio" class="shipping_viettelpost shipping" id="shipping-viettelpost" name="shipping" value="0" >
                                <span class="checkmark"></span>
                                <label for="shipping-viettelpost">
                                    {{ $langg->lang891 }}
                                </label>


                                {{-- <label for="free-shepping-viettelpost" class="customer_shippingcost1" style="padding-left: 50px">
                                    0
                                </label> --}}
                                {{-- <div class="col-lg-6">
                                    <input class="form-control customer_shippingcost2" type="text" name="customer_shippingcost2"
                                        placeholder="Shipping cost" required=""
                                        value="" readonly>
                                </div> --}}
                            </div>

                            <div class="radio-design">
                                <input type="radio" class="shipping-negotiate shipping" id="shipping-negotiate" name="shipping" value="0" checked>
                                <span class="checkmark"></span>
                                <label for="shipping-negotiate">
                                    {{ $langg->lang814 }}
                                </label>
                            </div>

                            <div class="total-price">
                                <p>
                                    {{ $langg->lang815 }}
                                </p>
                                <p>
                                    <span class="customer_shippingcost1">{{ App\Models\Product::convertPrice(0) }}</span>

                                </p>
                            </div>
                            {{-- <a href="" class="font-bold customer_shippingcost1">aaaaaaaaaaa</a> --}}
						</div>
						{{-- Shipping Method Area End --}}

						{{-- Packeging Area Start --}}
						{{-- <div class="packeging-area">
								<h4 class="title">{{ $langg->lang766 }}</h4>

							@foreach($package_data as $data)

								<div class="radio-design">
										<input type="radio" class="packing" id="free-package{{ $data->id }}" name="packeging" value="{{ round($data->price * $curr->value,2) }}" {{ ($loop->first) ? 'checked' : '' }}>
										<span class="checkmark"></span>
										<label for="free-package{{ $data->id }}">
												{{ $data->title }}
												@if($data->price != 0)
												+ {{ $curr->sign }}{{ round($data->price * $curr->value,2) }}
												@endif
												<small>{{ $data->subtitle }}</small>
										</label>
								</div>

							@endforeach

						</div> --}}
						{{-- Packeging Area End Start--}}



						@endif {{-- END CHECK DIGITAL --}}

                        {{-- Final Price Area Start--}}
						{{-- <div class="final-price">
							<span>{{ $langg->lang816 }}:</span>
						@if(Session::has('coupon_total'))
							@if($gs->currency_format == 0)
								<span id="final-cost">{{ $curr->sign }}{{ number_format($totalPrice) }}</span>
							@else
								<span id="final-cost">{{ number_format($totalPrice) }}{{ $curr->sign }}</span>
							@endif

						@elseif(Session::has('coupon_total1'))
                            <span id="final-cost">{{ App\Models\Product::convertPrice($totalPrice) }}</span>
							@else
							<span id="final-cost">{{ App\Models\Product::convertPrice($totalPrice) }}</span>
						@endif
						</div> --}}

                        @if (Auth::check())

                        {{-- <div class="final-price">
							<span>Shop Discount Total:</span>
                            <span id="totalShopCouponAmount">{{ $curr->sign }}{{ number_format((double)Session::get('cart')->totalShopCouponAmount) }}</span>
						</div> --}}

                        {{-- <div class="final-price">
							<span>{{ $langg->lang818 }}:</span>
                            <span id="shopping-point-amount">{{ $curr->sign }}{{ number_format((double)Session::get('cart')->totalSPAmount) }}</span>
						</div> --}}




                        @endif
                        <div class="final-price">
							<span>{{ $langg->lang906 }}:</span>
                            {{-- <span id="final-cost2">{{ number_format($totalPrice - (double)Session::get('cart')->totalSPAmount) }}</span> --}}
                            <span id="final-cost2"></span>
						</div>
						{{-- Final Price Area End --}}

{{-- 						<a href="{{ route('front.checkout') }}" class="order-btn mt-4">
							{{ $langg->lang135 }}
						</a> --}}
						</div>
					</div>
				</div>
				@endif
			</div>
		</div>
	</section>
		<!-- Check Out Area End-->

@if(isset($checked))

<!-- LOGIN MODAL -->
<div class="modal fade" id="comment-log-reg1" data-keyboard="false" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="comment-log-reg-Title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" aria-label="Close">
          <a href="{{ url()->previous() }}"><span aria-hidden="true">&times;</span></a>
        </button>
      </div>
      <div class="modal-body">
				<nav class="comment-log-reg-tabmenu">
					<div class="nav nav-tabs" id="nav-tab" role="tablist">
						<a class="nav-item nav-link login active" id="nav-log-tab" data-toggle="tab" href="#nav-log" role="tab" aria-controls="nav-log" aria-selected="true">
							{{ $langg->lang197 }}
						</a>
						<a class="nav-item nav-link" id="nav-reg-tab" data-toggle="tab" href="#nav-reg" role="tab" aria-controls="nav-reg" aria-selected="false">
							{{ $langg->lang198 }}
						</a>
					</div>
				</nav>
				<div class="tab-content" id="nav-tabContent">
					<div class="tab-pane fade show active" id="nav-log" role="tabpanel" aria-labelledby="nav-log-tab">
				        <div class="login-area">
				          <div class="header-area">
				            <h4 class="title">{{ $langg->lang172 }}</h4>
				          </div>
				          <div class="login-form signin-form">
				                @include('includes.admin.form-login')
				            <form id="loginform" action="{{ route('user.login.submit') }}" method="POST">
				              {{ csrf_field() }}
				              <div class="form-input">
				                <input type="email" name="email" placeholder="{{ $langg->lang173 }}" required="">
				                <i class="icofont-user-alt-5"></i>
				              </div>
				              <div class="form-input">
				                <input type="password" class="Password" name="password" placeholder="{{ $langg->lang174 }}" required="">
				                <i class="icofont-ui-password"></i>
				              </div>
				              <div class="form-forgot-pass">
				                <div class="left">
				              <input type="hidden" name="modal" value="1">
				                  <input type="checkbox" name="remember"  id="mrp" {{ old('remember') ? 'checked' : '' }}>
				                  <label for="mrp">{{ $langg->lang175 }}</label>
				                </div>
				                <div class="right">
				                  <a href="{{ route('user-forgot') }}">
				                    {{ $langg->lang176 }}
				                  </a>
				                </div>
				              </div>
				              <input id="authdata" type="hidden"  value="{{ $langg->lang177 }}">
				              <button type="submit" class="submit-btn">{{ $langg->lang178 }}</button>
					              @if(App\Models\Socialsetting::find(1)->f_check == 1 || App\Models\Socialsetting::find(1)->g_check == 1)
					              <div class="social-area">
					                  <h3 class="title">{{ $langg->lang179 }}</h3>
					                  <p class="text">{{ $langg->lang180 }}</p>
					                  <ul class="social-links">
					                    @if(App\Models\Socialsetting::find(1)->f_check == 1)
					                    <li>
					                      <a href="{{ route('social-provider','facebook') }}">
					                        <i class="fab fa-facebook-f"></i>
					                      </a>
					                    </li>
					                    @endif
					                    @if(App\Models\Socialsetting::find(1)->g_check == 1)
					                    <li>
					                      <a href="{{ route('social-provider','google') }}">
					                        <i class="fab fa-google-plus-g"></i>
					                      </a>
					                    </li>
					                    @endif
					                  </ul>
					              </div>
					              @endif
				            </form>
				          </div>
				        </div>
					</div>
					<div class="tab-pane fade" id="nav-reg" role="tabpanel" aria-labelledby="nav-reg-tab">
                <div class="login-area signup-area">
                    <div class="header-area">
                        <h4 class="title">{{ $langg->lang181 }}</h4>
                    </div>
                    <div class="login-form signup-form">
                       @include('includes.admin.form-login')
                        <form id="registerform" action="{{route('user-register-submit')}}" method="POST">
                          {{ csrf_field() }}

                            <div class="form-input">
                                <input type="text" class="User Name" name="name" placeholder="{{ $langg->lang182 }}" required="">
                                <i class="icofont-user-alt-5"></i>
                            </div>

                            <div class="form-input">
                                <input type="email" class="User Name" name="email" placeholder="{{ $langg->lang183 }}" required="">
                                <i class="icofont-email"></i>
                            </div>

                            <div class="form-input">
                                <input type="tel" class="User Name" name="phone" pattern="[+.0-9.]+" placeholder="{{ $langg->lang184 }}" required="">
                                <i class="icofont-phone"></i>
                            </div>

                            <div class="form-input">
                                <input type="text" class="User Name" name="address" placeholder="{{ $langg->lang185 }}" required="">
                                <i class="icofont-location-pin"></i>
                            </div>

                            <div class="form-input">
                                <input type="password" class="Password" name="password" placeholder="{{ $langg->lang186 }}" required="">
                                <i class="icofont-ui-password"></i>
                            </div>

                            <div class="form-input">
                                <input type="password" class="Password" name="password_confirmation" placeholder="{{ $langg->lang187 }}" required="">
                                <i class="icofont-ui-password"></i>
                            </div>

@if($gs->is_capcha == 1)

                                    <ul class="captcha-area">
                                        <li>
                                            <p><img class="codeimg1" src="{{asset("assets/images/capcha_code.png")}}" alt=""> <i class="fas fa-sync-alt pointer refresh_code "></i></p>
                                        </li>
                                    </ul>

                            <div class="form-input">
                                <input type="text" class="Password" name="codes" placeholder="{{ $langg->lang51 }}" required="">
                                <i class="icofont-refresh"></i>
                            </div>

@endif

                            <input id="processdata" type="hidden"  value="{{ $langg->lang188 }}">
                            <button type="submit" class="submit-btn">{{ $langg->lang189 }}</button>

                        </form>
                    </div>
                </div>
					</div>
				</div>
      </div>
    </div>
  </div>
</div>
<!-- LOGIN MODAL ENDS -->

@endif

@endsection

@section('scripts')

{{-- <script src="https://js.paystack.co/v1/inline.js"></script> --}}
<script src="{{asset('assets/front/js/htdnew1.js?v=3')}}"></script>
<script src="{{asset('assets/front/js/htdnew2.js')}}"></script>

@endsection
