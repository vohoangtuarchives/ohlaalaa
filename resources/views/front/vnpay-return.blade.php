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
            <a href="{{ route('payment.return') }}">
              {{ $langg->lang169 }}
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- Breadcrumb Area End -->







<section class="tempcart">

@if(!empty($tempcart))

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Starting of Dashboard data-table area -->
                    <div class="content-box section-padding add-product-1">
                        <div class="top-area">
                                <div class="content">
                                    <h4 class="heading">
                                        {{ $langg->order_title }}
                                        @if(!isset($type))
                                            {{ $langg->order_title }}
                                        @endif
                                        @if(isset($type))
                                            THANH TOÁN KHÔNG THÀNH CÔNG
                                        @endif
                                    </h4>
                                    <p class="text">
                                        {{ $langg->order_text }}
                                    </p>
                                    <a href="{{ route('front.index') }}" class="link">{{ $langg->lang170 }}</a>
                                  </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">

                                    <div class="product__header">
                                        <div class="row reorder-xs">
                                            <div class="col-lg-12">
                                                <div class="product-header-title">
                                                    <h2>{{ $langg->lang285 }} {{$order->order_number}}</h2>
                                                    <input type="hidden" id="order-number" value="{{$order->order_number}}">

                                        </div>
                                    </div>
                                        @include('includes.form-success')
                                            <div class="col-md-12" id="tempview">
                                                <div class="dashboard-content">
                                                    <div class="view-order-page" id="print">
                                                        <p class="order-date">{{ $langg->lang301 }} {{date('d-M-Y',strtotime($order->created_at))}}</p>


@if($order->dp == 1)

                                                        <div class="billing-add-area">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h5>{{ $langg->lang287 }}</h5>
                                                                    <address>
                                                                        {{ $langg->lang288 }} {{$order->customer_name}}<br>
                                                                        {{ $langg->lang289 }} {{$order->customer_email}}<br>
                                                                        {{ $langg->lang290 }} {{$order->customer_phone}}<br>
                                                                        {{ $langg->lang291 }} {{$order->customer_address}}<br>
                                                                        {{$order->customer_city}}-{{$order->customer_zip}}
                                                                    </address>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h5>{{ $langg->lang292 }}</h5>
                                                                    <p>{{ $langg->lang870 }}: {{$order->currency_sign}}{{ number_format(round($order->products_amount * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang871 }} ({{ $order->tax }}%): {{$order->currency_sign}}{{ number_format(round(($order->products_amount * $order->tax / 100.0) * $order->currency_value , 2)) }}</p>
                                                                    <p>Company {{ $langg->lang872 }}: {{$order->currency_sign}}{{ number_format(round($order->coupon_discount * $order->currency_value , 2)) }}</p>
                                                                    <p>Shop Discount: {{$order->currency_sign}}{{ number_format(round($order->vendor_discount_amount * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang873 }}: {{$order->currency_sign}}{{ number_format(round($order->shipping_cost * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang874 }}: {{$order->currency_sign}}{{ number_format(round($order->pay_amount1 * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang875 }}: {{$order->currency_sign}}{{ number_format(round($order->shopping_point_amount * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang876 }}: {{$order->currency_sign}}{{ number_format(round($order->pay_amount2 * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang294 }} {{$order->method}}</p>
                                                                    <p>Payment Status: {!! $order->payment_status == 'Pending' ? "<span id='payment-status' class='badge badge-danger'>Unpaid</span>":"<span id='payment-status' class='badge badge-success'>Paid</span>" !!}</p>

                                                                    @if($order->method != "Cash On Delivery")
                                                                        @if($order->method=="Stripe")
                                                                            {{$order->method}} {{ $langg->lang295 }} <p>{{$order->charge_id}}</p>
                                                                        @endif
                                                                        {{$order->method}} {{ $langg->lang296 }} <p id="ttn">{{$order->txnid}}</p>

                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

@else
                                                        <div class="shipping-add-area">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    @if($order->shipping == "shipto")
                                                                        <h5>{{ $langg->lang302 }}</h5>
                                                                        <address>
                                                                            @if ($order->is_shipdiff == 1)
                                                                                @php
                                                                                    $shippingward = $order->shippingward()->first();
                                                                                    $shippingwardtext = isset($shippingward) ? ''.$shippingward->name : '';

                                                                                    $shippingdistrict = $order->shippingdistrict()->first();
                                                                                    $shippingdistricttext = isset($shippingdistrict) ? ', '.$shippingdistrict->name : '';

                                                                                    $shippingprovince = $order->shippingprovince()->first();
                                                                                    $shippingprovincettext = isset($shippingprovince) ? ', '.$shippingprovince->name : '';
                                                                                @endphp

                                                                                {{ $langg->lang288 }} {{ $order->shipping_name }}<br>
                                                                                {{ $langg->lang289 }} {{ $order->shipping_email }}<br>
                                                                                {{ $langg->lang290 }} {{ $order->shipping_phone }}<br>
                                                                                {{ $langg->lang291 }} {{ $order->shipping_address }}<br>
                                                                                {{ $shippingwardtext }}{{ $shippingdistricttext }}{{ $shippingprovincettext }}
                                                                            @else
                                                                                @php
                                                                                    $customerward = $order->customerward()->first();
                                                                                    $customerwardtext = isset($customerward) ? ''.$customerward->name : '';

                                                                                    $customerdistrict = $order->customerdistrict()->first();
                                                                                    $customerdistricttext = isset($customerdistrict) ? ', '.$customerdistrict->name : '';

                                                                                    $customerprovince = $order->customerprovince()->first();
                                                                                    $customerprovincettext = isset($customerprovince) ? ', '.$customerprovince->name : '';
                                                                                @endphp
                                                                                {{ $langg->lang288 }} {{ $order->customer_name }}<br>
                                                                                {{ $langg->lang289 }} {{ $order->customer_email }}<br>
                                                                                {{ $langg->lang290 }} {{ $order->customer_phone }}<br>
                                                                                {{ $langg->lang291 }} {{ $order->customer_address }}<br>
                                                                                {{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }}

                                                                            @endif

                                                                        </address>
                                                                    @else
                                                                        <h5>{{ $langg->lang303 }}</h5>
                                                                        <address>
                                                                            {{ $langg->lang304 }} {{$order->pickup_location}}<br>
                                                                        </address>
                                                                    @endif

                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h5>{{ $langg->lang305 }}</h5>
                                                                    <p>{{ $order->shipping_type }}</p>
                                                                    @if($order->shipping_cost != 0)
                                                                        @php
                                                                        $price = round(($order->shipping_cost / $order->currency_value),2);
                                                                        @endphp
                                                                        @if(DB::table('shippings')->where('price','=',$price)->count() > 0)
                                                                <p>
                                                                    {{ DB::table('shippings')->where('price','=',$price)->first()->title }}: {{$order->currency_sign}}{{ round($order->shipping_cost, 2) }}
                                                                </p>
                                                                        @endif
                                                                    @endif

                                                                    @if($order->packing_cost != 0)

                                                                        @php
                                                                        $pprice = round(($order->packing_cost / $order->currency_value),2);
                                                                        @endphp


                                                                        @if(DB::table('packages')->where('price','=',$pprice)->count() > 0)
                                                                <p>
                                                                    {{ DB::table('packages')->where('price','=',$pprice)->first()->title }}: {{$order->currency_sign}}{{ round($order->packing_cost, 2) }}
                                                                </p>
                                                                        @endif
                                                                    @endif

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="billing-add-area">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h5>{{ $langg->lang287 }}</h5>
                                                                    <address>
                                                                        @if ($order->is_shipdiff == 1)
                                                                            @php
                                                                                $customerward = $order->customerward()->first();
                                                                                $customerwardtext = isset($customerward) ? ''.$customerward->name : '';

                                                                                $customerdistrict = $order->customerdistrict()->first();
                                                                                $customerdistricttext = isset($customerdistrict) ? ', '.$customerdistrict->name : '';

                                                                                $customerprovince = $order->customerprovince()->first();
                                                                                $customerprovincettext = isset($customerprovince) ? ', '.$customerprovince->name : '';
                                                                            @endphp
                                                                        @endif
                                                                        {{ $langg->lang288 }} {{ $order->customer_name }}<br>
                                                                        {{ $langg->lang289 }} {{ $order->customer_email }}<br>
                                                                        {{ $langg->lang290 }} {{ $order->customer_phone }}<br>
                                                                        {{ $langg->lang291 }} {{ $order->customer_address }}<br>
                                                                        {{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }}
                                                                    </address>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h5>{{ $langg->lang292 }}</h5>
                                                                    <p>{{ $langg->lang870 }}: {{$order->currency_sign}}{{ number_format(round($order->products_amount * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang871 }} ({{ $order->tax }}%): {{$order->currency_sign}}{{ number_format(round(($order->products_amount * $order->tax / 100.0) * $order->currency_value , 2)) }}</p>
                                                                    <p>Company {{ $langg->lang872 }}: {{$order->currency_sign}}{{ number_format(round($order->coupon_discount * $order->currency_value , 2)) }}</p>
                                                                    <p>Shop Discount: {{$order->currency_sign}}{{ number_format(round($order->vendor_discount_amount * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang873 }}: {{$order->currency_sign}}{{ number_format(round($order->shipping_cost * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang874 }}: {{$order->currency_sign}}{{ number_format(round($order->pay_amount1 * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang875 }}: {{$order->currency_sign}}{{ number_format(round($order->shopping_point_amount * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang876 }}: {{$order->currency_sign}}{{ number_format(round($order->pay_amount2 * $order->currency_value , 2)) }}</p>
                                                                    <p>{{ $langg->lang294 }} {{$order->method}}</p>
                                                                    <p>Payment Status: {!! $order->payment_status == 'Pending' ? "<span id='payment-status' class='badge badge-danger'>Unpaid</span>":"<span id='payment-status' class='badge badge-success'>Paid</span>" !!}</p>

                                                                    @if($order->method != "Cash On Delivery")
                                                                        @if($order->method=="Stripe")
                                                                            {{$order->method}} {{ $langg->lang295 }} <p>{{$order->charge_id}}</p>
                                                                        @endif
                                                                        @if($order->method=="Paypal")
                                                                        {{$order->method}} {{ $langg->lang296 }} <p id="ttn">{{ isset($_GET['tx']) ? $_GET['tx'] : '' }}</p>
                                                                        @else
                                                                        {{$order->method}} {{ $langg->lang296 }} <p id="ttn">{{$order->txnid}}</p>
                                                                        @endif

                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
@endif
                                                        <br>
                                                        <div class="table-responsive">
                            <table  class="table">
                                <h4 class="text-center">{{ $langg->lang308 }}</h4>
                                <thead>
                                <tr>

                                    <th width="40%">{{ $langg->lang310 }}</th>
                                    <th width="20%">{{ $langg->lang539 }}</th>
                                    <th width="10%">{{ $langg->lang314 }}</th>
                                    <th width="10%">Discount</th>
                                    <th width="10%">SP</th>
                                    <th width="10%">{{ $langg->lang315 }}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($tempcart->items as $product)
                                    <tr>

                                            <td>{{ $product['item']['name'] }}</td>
                                            <td>
                                                <b>{{ $langg->lang311 }}</b>: {{$product['qty']}} <br>
                                                @if(!empty($product['size']))
                                                <b>{{ $langg->lang312 }}</b>: {{ $product['item']['measure'] }}{{str_replace('-',' ',$product['size'])}} <br>
                                                @endif
                                                @if(!empty($product['color']))
                                                <div class="d-flex mt-2">
                                                <b>{{ $langg->lang313 }}</b>:  <span id="color-bar" style="border: 10px solid #{{$product['color'] == "" ? "white" : $product['color']}};"></span>
                                                </div>
                                                @endif

                                                    @if(!empty($product['keys']))

                                                    @foreach( array_combine(explode(',', $product['keys']), explode(',', $product['values']))  as $key => $value)

                                                        <b>{{ ucwords(str_replace('_', ' ', $key))  }} : </b> {{ $value }} <br>
                                                    @endforeach

                                                    @endif

                                                  </td>
                                            <td>{{$order->currency_sign}}{{number_format(round($product['item_price'] * $order->currency_value,2))}}<br>
                                                SP<br> {{number_format($product['price_shopping_point'])}}
                                            </td>
                                            <td>
                                                {{$order->currency_sign}}{{number_format(round($product['shop_coupon_amount'] * $order->currency_value,2))}}<br>
                                                <b>{{$product['shop_coupon_code']}}</b>
                                            </td>
                                            <td>{{$order->currency_sign}}{{number_format(round($product['shopping_point_amount'] * $order->currency_value,2))}}</td>
                                            <td>{{$order->currency_sign}}{{number_format(round(($product['price'] + $product['price_shopping_point_amount'] - $product['shop_coupon_amount'] - $product['shopping_point_amount']) * $order->currency_value,2))}}</td>

                                    </tr>
                                @endforeach



                                </tbody>
                            </table>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                </div>

                        </div>
                    </div>
                </div>
                <!-- Ending of Dashboard data-table area -->
            </div>

@endif

  </section>

@endsection

@section('scripts')

@endsection
