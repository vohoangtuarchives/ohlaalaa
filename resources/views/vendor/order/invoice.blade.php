@extends('layouts.vendor')

@section('content')
<div class="content-area">
                    <div class="mr-breadcrumb">
                        <div class="row">
                            <div class="col-lg-12">
                                <h4 class="heading">{{ $langg->lang586 }} <a class="add-btn" href="{{ route('vendor-order-show',$order->order_number) }}"><i class="fas fa-arrow-left"></i> {{ $langg->lang550 }}</a></h4>
                                <ul class="links">
                                    <li>
                                        <a href="{{ route('vendor-dashboard') }}">{{ $langg->lang441 }} </a>
                                    </li>
                                    <li>
                                        <a href="javascript:;">{{ $langg->lang443 }}</a>
                                    </li>
                                    <li>
                                        <a href="javascript:;">{{ $langg->lang586 }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
    <div class="order-table-wrap">
        <div class="invoice-wrap">
            <div class="invoice__title">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="invoice__logo text-left">
                           <img src="{{ asset('assets/images/'.$gs->invoice_logo) }}" alt="woo commerce logo">
                        </div>
                    </div>
                    <div class="col-lg-6 text-right">
                        <a class="btn  add-newProduct-btn print" href="{{route('vendor-order-print',$order->order_number)}}"
                        target="_blank"><i class="fa fa-print"></i> {{ $langg->lang607 }}</a>
                    </div>
                </div>
            </div>
            <br>
            <div class="row invoice__metaInfo mb-4">
                <div class="col-lg-6">
                    <div class="invoice__orderDetails">

                        <p><strong>{{ $langg->lang601 }} </strong></p>
                        <span><strong>{{ $langg->lang588 }} :</strong> {{ sprintf("%'.08d", $order->id) }}</span><br>
                        <span><strong>{{ $langg->lang589 }} :</strong> {{ date('d-M-Y',strtotime($order->created_at)) }}</span><br>
                        <span><strong>{{  $langg->lang590 }} :</strong> {{ $order->order_number }}</span><br>
                        @if($order->dp == 0)
                        <span> <strong>{{ $langg->lang602 }} :</strong>
                            {{ $order->shipping_type }}
                        </span><br>
                        @endif
                        <span> <strong>{{ $langg->lang605 }} :</strong> {{$order->method}}</span><br>
                        <span> <strong>{{ $langg->lang798 }} :</strong> {!! $order->payment_status == 'Pending' ? "<span class='badge badge-danger'>". $langg->lang799 ."</span>":"<span class='badge badge-success'>". $langg->lang800 ."</span>" !!}</span>
                    </div>
                </div>
            </div>
            <div class="row invoice__metaInfo">
           @if($order->dp == 0)
                <div class="col-lg-6">
                        <div class="invoice__shipping">
                            <p><strong>{{ $langg->lang606 }}</strong></p>

                            @if ($order->is_shipdiff == 1)
                                @php
                                    $shippingward = $order->shippingward()->first();
                                    $shippingwardtext = isset($shippingward) ? ', '.$shippingward->name : '';
                                    $shippingdistrict = $order->shippingdistrict()->first();
                                    $shippingdistricttext = isset($shippingdistrict) ? ', '.$shippingdistrict->name : '';
                                    $shippingprovince = $order->shippingprovince()->first();
                                    $shippingprovincettext = isset($shippingprovince) ? ', '.$shippingprovince->name : '';
                                @endphp
                                <span><strong>{{ $langg->lang557 }}</strong>: {{ $order->shipping_name}}</span><br>
                                <span><strong>{{ $langg->lang558 }}</strong>: {{ $order->shipping_email}}</span><br>
                                <span><strong>{{ $langg->lang559 }}</strong>: {{ $order->shipping_phone}}</span><br>
                                <span><strong>{{ $langg->lang560 }}</strong>: {{ $order->shipping_address }}{{ $shippingwardtext }}{{ $shippingdistricttext }}{{ $shippingprovincettext }}</span><br>
                            @else
                                @php
                                    $customerward = $order->customerward()->first();
                                    $customerwardtext = isset($customerward) ? ', '.$customerward->name : '';
                                    $customerdistrict = $order->customerdistrict()->first();
                                    $customerdistricttext = isset($customerdistrict) ? ', '.$customerdistrict->name : '';
                                    $customerprovince = $order->customerprovince()->first();
                                    $customerprovincettext = isset($customerprovince) ? ', '.$customerprovince->name : '';
                                @endphp

                                <span><strong>{{ $langg->lang557 }}</strong>: {{ $order->customer_name}}</span><br>
                                <span><strong>{{ $langg->lang558 }}</strong>: {{ $order->customer_email}}</span><br>
                                <span><strong>{{ $langg->lang559 }}</strong>: {{ $order->customer_phone}}</span><br>
                                <span><strong>{{ $langg->lang560 }}</strong>: {{ $order->customer_address }}{{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }}</span><br>

                            @endif

                        </div>
                </div>

            @endif

                <div class="col-lg-6">
                        <div class="buyer">
                            <p><strong>{{ $langg->lang587 }}</strong></p>
                            @php
                            $customerward = $order->customerward()->first();
                            $customerwardtext = isset($customerward) ? ', '.$customerward->name : '';
                            $customerdistrict = $order->customerdistrict()->first();
                            $customerdistricttext = isset($customerdistrict) ? ', '.$customerdistrict->name : '';
                            $customerprovince = $order->customerprovince()->first();
                            $customerprovincettext = isset($customerprovince) ? ', '.$customerprovince->name : '';
                            @endphp

                            <span><strong>{{ $langg->lang557 }}</strong>: {{ $order->customer_name}}</span><br>
                            <span><strong>{{ $langg->lang558 }}</strong>: {{ $order->customer_email}}</span><br>
                            <span><strong>{{ $langg->lang559 }}</strong>: {{ $order->customer_phone}}</span><br>
                            <span><strong>{{ $langg->lang560 }}</strong>: {{ $order->customer_address }}{{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }}</span><br>
                        </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="invoice_table">
                        <div class="mr-table">
                            <div class="table-responsive">
                                <table id="example2" class="table table-hover dt-responsive" cellspacing="0"
                                    width="100%" >
                                    <thead>
                                        <tr>
                                            <th>{{ $langg->lang591 }}</th>
                                            <th>{{ $langg->lang539 }}</th>
                                            <th>Shop Discount</th>
                                            <th>Shopping Point</th>
                                            <th>{{ $langg->lang600 }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $subtotal = 0;
                                        $shopping_point_amount = 0;
                                        $discount_amount = 0;
                                        $data = 0;
                                        $tax = 0;

                                        @endphp
                                        @foreach($cart->items as $product)
                                @if($product['item']['user_id'] != 0)
                                    @if($product['item']['user_id'] == $user->id)

                                        <tr>
                                            <td width="50%">
                                                @if($product['item']['user_id'] != 0)
                                                @php
                                                $user = App\Models\User::find($product['item']['user_id']);
                                                @endphp
                                                @if(isset($user))
                                                <a target="_blank"
                                                    href="{{ route('front.product', $product['item']['slug']) }}">{{ $product['item']['name']}}</a>
                                                @else
                                                <a href="javascript:;">{{$product['item']['name']}}</a>
                                                @endif

                                                @else
                                                <a href="javascript:;">{{ $product['item']['name']}}</a>

                                                @endif
                                            </td>


                                            <td>
                                                @if($product['size'])
                                               <p>
                                                    <strong>{{ $langg->lang312 }} :</strong> {{str_replace('-',' ',$product['size'])}}
                                               </p>
                                               @endif
                                               @if($product['color'])
                                                <p>
                                                        <strong>{{ $langg->lang313 }} :</strong> <span
                                                        style="width: 40px; height: 20px; display: block; background: #{{$product['color']}};"></span>
                                                </p>
                                                @endif
                                                <p>
                                                    <strong>{{ $langg->lang754 }} :</strong> {{$order->currency_sign}}{{ number_format(round($product['item_price'] * $order->currency_value , 2)) }}
                                                </p>
                                                <p>
                                                    <strong>SP :</strong> {{ number_format($product['item_price_shopping_point']) }}
                                                </p>
                                               <p>
                                                    <strong>{{ $langg->lang595 }} :</strong> {{$product['qty']}} {{ $product['item']['measure'] }}
                                               </p>
                                                    @if(!empty($product['keys']))

                                                    @foreach( array_combine(explode(',', $product['keys']), explode(',', $product['values']))  as $key => $value)
                                                    <p>

                                                        <b>{{ ucwords(str_replace('_', ' ', $key))  }} : </b> {{ $value }}

                                                    </p>
                                                    @endforeach

                                                    @endif

                                            </td>


                                            <td>{{$order->currency_sign}}{{ number_format(round($product['shop_coupon_amount'] * $order->currency_value , 2)) }}
                                                <p>

                                                    <b>{{ $product['shop_coupon_code'] }}</b>

                                                </p>
                                            </td>
                                            <td>{{$order->currency_sign}}{{ number_format(round($product['shopping_point_amount'] * $order->currency_value , 2)) }}</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($product['price'] + $product['price_shopping_point_amount'] - $product['shop_coupon_amount'] - $product['shopping_point_amount']) * $order->currency_value , 2)) }}</td>
                                            @php
                                            $subtotal += round(($product['price'] + $product['price_shopping_point_amount']) * $order->currency_value, 2);
                                            if (isset($product['is_shopping_point_used']) && $product['is_shopping_point_used'] == 1) {
                                                $shopping_point_amount += $product['shopping_point_amount'];
                                            }

                                            if ($product['shop_coupon_amount'] > 0) {
                                                $discount_amount += $product['shop_coupon_amount'];
                                            }



                                            @endphp

                                        </tr>

                                    @endif
                                @endif
                                        @endforeach
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="4">{{ $langg->lang597 }}</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round($subtotal, 2)) }}</td>
                                        </tr>
                                        @if(Auth::user()->id == $order->vendor_shipping_id)
                                        @if($order->shipping_cost != 0)
                                            @php
                                            $price = round(($order->shipping_cost / $order->currency_value),2);
                                            @endphp
                                            @if(DB::table('shippings')->where('price','=',$price)->count() > 0)
                                            <tr>
                                                <td colspan="4">{{ DB::table('shippings')->where('price','=',$price)->first()->title }}({{$order->currency_sign}})</td>
                                                <td>{{ round($order->shipping_cost , 2) }}</td>
                                            </tr>
                                            @endif
                                        @endif
                                        @endif
                                        @if(Auth::user()->id == $order->vendor_packing_id)
                                        @if($order->packing_cost != 0)
                                            @php
                                            $pprice = round(($order->packing_cost / $order->currency_value),2);
                                            @endphp
                                            @if(DB::table('packages')->where('price','=',$pprice)->count() > 0)
                                            <tr>
                                                <td colspan="4">{{ DB::table('packages')->where('price','=',$pprice)->first()->title }}({{$order->currency_sign}})</td>
                                                <td>{{ round($order->packing_cost , 2) }}</td>
                                            </tr>
                                            @endif
                                        @endif
                                        @endif

                                        @php
                                            $vendor_amount = $subtotal;
                                        @endphp
                                        @if($order->tax != 0)
                                        <tr>
                                            <td colspan="4">{{ $langg->lang599 }}</td>
                                            @php
                                                $tax = ($subtotal / 100) * $order->tax;
                                                $vendor_amount =  $vendor_amount + $tax;
                                            @endphp
                                            <td>{{$order->currency_sign}}{{number_format(round($tax, 2))}}</td>
                                        </tr>
                                        @endif

                                        {{-- @if($order->orderconsumershippingcosts->count() > 0)
                                            @php
                                                $shipping = $order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first();
                                                if (isset($shipping)) {
                                                    $vendor_amount = $vendor_amount + $shipping->shipping_cost;
                                                }
                                            @endphp
                                            <tr>
                                                <td colspan="4">{{ $langg->lang878 }}</td>
                                                <td>{{$order->currency_sign}}{{ isset($shipping) ? number_format(round($shipping->shipping_cost , 2)) : 0 }}</td>
                                            </tr>
                                        @endif --}}

                                        <tr>
                                            <td colspan="3"></td>
                                            <td>Shop Discount</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($discount_amount), 2)) }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="3"></td>
                                            <td>Price After Discount</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($vendor_amount - $discount_amount), 2)) }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="3"></td>
                                            <td>{{ $langg->lang880 }}</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($shopping_point_amount), 2)) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td>Payment Amount After SP & Discount</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($vendor_amount - $shopping_point_amount - $discount_amount), 2)) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Main Content Area End -->

@endsection
