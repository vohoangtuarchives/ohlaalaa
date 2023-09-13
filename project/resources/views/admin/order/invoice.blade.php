@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Order Invoice') }} <a class="add-btn" href="javascript:history.back();"><i
                            class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h4>
                <ul class="links">
                    <li>
                        <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                    </li>
                    <li>
                        <a href="javascript:;">{{ __('Orders') }}</a>
                    </li>
                    <li>
                        <a href="javascript:;">{{ __('Invoice') }}</a>
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
                        <a class="btn  add-newProduct-btn print" href="{{route('admin-order-print',$order->id)}}"
                        target="_blank"><i class="fa fa-print"></i> {{ __('Print Invoice') }}</a>
                    </div>
                </div>
            </div>
            <br>
            <div class="row invoice__metaInfo mb-4">
                <div class="col-lg-6">
                    <div class="invoice__orderDetails">

                        <p><strong>{{ __('Order Details') }} </strong></p>
                        <span><strong>{{ __('Invoice Number') }} :</strong> {{ sprintf("%'.08d", $order->id) }}</span><br>
                        <span><strong>{{ __('Order Date') }} :</strong> {{ date('d-M-Y',strtotime($order->created_at)) }}</span><br>
                        <span><strong>{{  __('Order ID')}} :</strong> {{ $order->order_number }}</span><br>
                        @if($order->dp == 0)
                        <span> <strong>{{ __('Shipping Method') }} :</strong>
                            {{ $order->shipping_type }}
                        </span><br>
                        @endif
                        <span> <strong>{{ __('Payment Method') }} :</strong> {{$order->method}}</span><br>
                        <span> <strong>{{ __('Payment Status') }} :</strong> {!! $order->payment_status == 'Pending' ? "<span class='badge badge-danger'>". $langg->lang799 ."</span>":"<span class='badge badge-success'>". $langg->lang800 ."</span>" !!}</span>
                    </div>
                </div>
            </div>
            <div class="row invoice__metaInfo">
           @if($order->dp == 0)
                <div class="col-lg-6">
                        <div class="invoice__shipping">
                            <p><strong>{{ __('Shipping Address') }}</strong></p>

                            @if ($order->is_shipdiff == 1)
                                @php
                                    $shippingward = $order->shippingward()->first();
                                    $shippingwardtext = isset($shippingward) ? ', '.$shippingward->name : '';
                                    $shippingdistrict = $order->shippingdistrict()->first();
                                    $shippingdistricttext = isset($shippingdistrict) ? ', '.$shippingdistrict->name : '';
                                    $shippingprovince = $order->shippingprovince()->first();
                                    $shippingprovincettext = isset($shippingprovince) ? ', '.$shippingprovince->name : '';
                                @endphp
                                <span><strong>{{ __('Customer Name') }}</strong>: {{ $order->shipping_name}}</span><br>
                                <span><strong>{{ __('Address') }}</strong>: {{ $order->shipping_address }}{{ $shippingwardtext }}{{ $shippingdistricttext }}{{ $shippingprovincettext }}</span><br>
                            @else
                                @php
                                    $customerward = $order->customerward()->first();
                                    $customerwardtext = isset($customerward) ? ', '.$customerward->name : '';
                                    $customerdistrict = $order->customerdistrict()->first();
                                    $customerdistricttext = isset($customerdistrict) ? ', '.$customerdistrict->name : '';
                                    $customerprovince = $order->customerprovince()->first();
                                    $customerprovincettext = isset($customerprovince) ? ', '.$customerprovince->name : '';
                                @endphp

                                <span><strong>{{ __('Customer Name') }}</strong>: {{ $order->customer_name}}</span><br>
                                <span><strong>{{ __('Address') }}</strong>: {{ $order->customer_address }}{{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }}</span><br>

                            @endif

                        </div>
                </div>

            @endif

                <div class="col-lg-6">
                        <div class="buyer">
                            <p><strong>{{ __('Billing Details') }}</strong></p>
                            @php
                                $customerward = $order->customerward()->first();
                                $customerwardtext = isset($customerward) ? ', '.$customerward->name : '';
                                $customerdistrict = $order->customerdistrict()->first();
                                $customerdistricttext = isset($customerdistrict) ? ', '.$customerdistrict->name : '';
                                $customerprovince = $order->customerprovince()->first();
                                $customerprovincettext = isset($customerprovince) ? ', '.$customerprovince->name : '';
                            @endphp
                            <span><strong>{{ __('Customer Name') }}</strong>: {{ $order->customer_name}}</span><br>
                            <span><strong>{{ __('Address') }}</strong>: {{ $order->customer_address }}{{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }}</span><br>
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
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Delivery Code') }}</th>
                                            <th>{{ __('Details') }}</th>
                                            <th>{{ __('Shop Discount') }}</th>
                                            <th>{{ __('Shopping Point') }}</th>
                                            <th>{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $subtotal = 0;
                                        $tax = 0;
                                        $shipping_details = DB::table('order_consumer_shipping_costs')->where('order_id','=',$order->id)->get();
                                        @endphp

                                        @foreach($cart->items as $product)
                                        <tr>
                                            <td width="30%">
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
                                                @if($order->orderconsumershippingcosts->count() > 0)
                                                    @php
                                                        $shipping = $order->orderconsumershippingcosts->where('shop_id','=',$user->id)->first();
                                                    @endphp
                                                    {{ isset($shipping) ? $shipping->shipping_partner_code : '' }}
                                                @endif
                                            </td>

                                            <td>
                                                @if($product['size'])
                                               <p>
                                                    <strong>{{ __('Size') }} :</strong> {{str_replace('-',' ',$product['size'])}}
                                               </p>
                                               @endif
                                               @if($product['color'])
                                                <p>
                                                        <strong>{{ __('color') }} :</strong> <span
                                                        style="width: 40px; height: 20px; display: block; background: #{{$product['color']}};"></span>
                                                </p>
                                                @endif
                                                <p>
                                                    <strong>{{ __('Price') }} :</strong> {{$order->currency_sign}}{{ number_format(round($product['item_price'] * $order->currency_value , 2)) }}
                                                </p>
                                                <p>
                                                    <strong>{{ __('SP') }} :</strong> {{ number_format($product['item_price_shopping_point']) }}
                                                </p>
                                               <p>
                                                    <strong>{{ __('Qty') }} :</strong> {{$product['qty']}} {{ $product['item']['measure'] }}
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
                                                    <strong>{{$product['shop_coupon_code']}}</strong>
                                               </p>
                                            </td>
                                            <td>{{$order->currency_sign}}{{ number_format(round($product['shopping_point_amount'] * $order->currency_value , 2)) }}
                                            </td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($product['price'] + $product['price_shopping_point_amount'] - $product['shop_coupon_amount'] - $product['shopping_point_amount']) * $order->currency_value , 2)) }}
                                            </td>
                                            @php
                                            $subtotal += round(($product['price'] + $product['price_shopping_point_amount']) * $order->currency_value, 2);
                                            @endphp

                                        </tr>

                                        @endforeach
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="4">{{ __('Subtotal') }}</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round($subtotal, 2)) }}</td>
                                        </tr>
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

                                        @if($order->tax != 0)
                                        <tr>
                                            <td colspan="4">{{ __('TAX') }}({{$order->currency_sign}})</td>
                                            @php
                                            $tax = ($subtotal / 100) * $order->tax;
                                            @endphp
                                            <td>{{$order->currency_sign}}{{number_format(round($tax, 2))}}</td>
                                        </tr>
                                        @endif

                                        @if($order->orderconsumershippingcosts()->count() > 0)
                                        @php
                                            $sps = $order->orderconsumershippingcosts()->get();
                                        @endphp
                                            @foreach( $sps as $sp)
                                            <tr>
                                                <td colspan="4">{{ __('Shipping Cost') }} ({{ $sp->shop()->first()->shop_name.' - '.$sp->shipping_partner_code }})</td>
                                                <td>{{$order->currency_sign}}{{ number_format($sp->shipping_cost) }}</td>
                                            </tr>
                                            @endforeach
                                        @else

                                            @if($order->shipping_cost != 0)
                                                <tr>
                                                    <td colspan="4">{{ __('Shipping Cost') }}</td>
                                                    <td>{{$order->currency_sign}}{{ number_format($order->shipping_cost) }}</td>
                                                </tr>
                                            @endif

                                        @endif

                                        @if($order->coupon_discount != null)
                                        <tr>
                                            <td colspan="4">{{ __('Company Discount') }}</td>
                                            <td>{{$order->currency_sign}}{{number_format(round($order->coupon_discount, 2))}}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td colspan="3"></td>
                                            <td>{{ __('Shop Discount') }}</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round($order->vendor_discount_amount * $order->currency_value , 2)) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td>{{ __('Price After Discount') }}</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round($order->pay_amount1 * $order->currency_value , 2)) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td>{{ __('Shopping Point') }}</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round($order->shopping_point_amount * $order->currency_value , 2)) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td>{{ __('Payment Amount after SP & Discount') }}</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round($order->pay_amount2 * $order->currency_value , 2)) }}
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
</div>
</div>
</div>

@endsection
