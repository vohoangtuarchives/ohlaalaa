<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <style type="text/css">
  </style>
</head>
<body>
    <div class="invoice-wrap">
            <div class="invoice__title">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="invoice__logo text-left">

                            <p><strong>Hello {{$order->customer_name}}, </strong></p><br>

                            <span>Your order has been placed successfully!</span><br>
                            <span>Please review order info with the following details.</span><br>


                           {{-- <img src="{{ asset('assets/images/'.$gs->invoice_logo) }}" alt="woo commerce logo"> --}}
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="invoice__metaInfo">
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
                        <span><strong>{{ __('Payment Status') }} :</strong> {{ $order->payment_status == 'Pending' ? "Unpaid":"Paid" }}</span>
                    </div>
                </div>
            </div>

            <div class="invoice__metaInfo">
                @if($order->dp == 0)
                <div class="col-lg-6">
                        <div class="invoice__orderDetails">
                            <p><strong>{{ __('Shipping Details') }}</strong></p>

                            @if ($order->is_shipdiff == 1)
                                    @php
                                        $shippingward = $order->shippingward()->first();
                                        $shippingwardtext = isset($shippingward) ? ''.$shippingward->name : '';
                                        $shippingdistrict = $order->shippingdistrict()->first();
                                        $shippingdistricttext = isset($shippingdistrict) ? ', '.$shippingdistrict->name : '';
                                        $shippingprovince = $order->shippingprovince()->first();
                                        $shippingprovincettext = isset($shippingprovince) ? ', '.$shippingprovince->name : '';
                                    @endphp

                                    <span><strong>{{ __('Customer Name') }}</strong>: {{ $order->shipping_name }}</span><br>
                                    <span><strong>{{ __('Phone') }}</strong>: {{ $order->shipping_phone }}</span><br>
                                    <span><strong>{{ __('Email') }}</strong>: {{ $order->shipping_email }}</span><br>
                                    <span><strong>{{ __('Address') }}</strong>: {{ $order->shipping_address }}, {{ $shippingwardtext }}{{ $shippingdistricttext }}{{ $shippingprovincettext }} </span><br>


                                @else
                                    @php
                                        $customerward = $order->customerward()->first();
                                        $customerwardtext = isset($customerward) ? ''.$customerward->name : '';
                                        $customerdistrict = $order->customerdistrict()->first();
                                        $customerdistricttext = isset($customerdistrict) ? ', '.$customerdistrict->name : '';
                                        $customerprovince = $order->customerprovince()->first();
                                        $customerprovincettext = isset($customerprovince) ? ', '.$customerprovince->name : '';
                                    @endphp
                                    <span><strong>{{ __('Customer Name') }}</strong>: {{ $order->customer_name }}</span><br>
                                    <span><strong>{{ __('Phone') }}</strong>: {{ $order->customer_phone }}</span><br>
                                    <span><strong>{{ __('Email') }}</strong>: {{ $order->customer_email }}</span><br>
                                    <span><strong>{{ __('Address') }}</strong>: {{ $order->customer_address }}, {{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }} </span><br>

                                @endif


                        </div>
                </div>
                @endif

            </div>

            <div class="invoice__metaInfo" >
                <div class="col-lg-6">
                        <div class="invoice__orderDetails">
                            <p><strong>{{ __('Billing Details') }}</strong></p>
                            @php
                                $customerward = $order->customerward()->first();
                                $customerwardtext = isset($customerward) ? ''.$customerward->name : '';
                                $customerdistrict = $order->customerdistrict()->first();
                                $customerdistricttext = isset($customerdistrict) ? ', '.$customerdistrict->name : '';
                                $customerprovince = $order->customerprovince()->first();
                                $customerprovincettext = isset($customerprovince) ? ', '.$customerprovince->name : '';
                            @endphp
                            <span><strong>{{ __('Customer Name') }}</strong>: {{ $order->customer_name }}</span><br>
                            <span><strong>{{ __('Phone') }}</strong>: {{ $order->customer_phone }}</span><br>
                            <span><strong>{{ __('Email') }}</strong>: {{ $order->customer_email }}</span><br>
                            <span><strong>{{ __('Address') }}</strong>: {{ $order->customer_address }}, {{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }} </span><br>
                        </div>
                </div>
            </div>


                <div class="col-lg-12">
                    <div class="invoice_table">
                        <div class="mr-table">
                            <div class="table-responsive">
                                <table id="example2" class="table table-hover dt-responsive" cellspacing="0"
                                    width="100%">
                                    <thead style="border-top:1px solid rgba(0, 0, 0, 0.1) !important;">
                                        <tr>
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Details') }}</th>
                                            <th>{{ __('Discount') }}</th>
                                            <th>{{ __('Shopping Point') }}</th>
                                            <th>{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $subtotal = 0;
                                        $tax = 0;
                                        @endphp
                                        @foreach($cart->items as $product)
                                        <tr>
                                            <td width="20%" >
                                                <div style="word-wrap: break-word;">{{ $product['item']['name']}}</div>
                                            </td>

                                            <td width="20%">
                                                @if($product['size'])
                                               <p>
                                                    Size : {{str_replace('-',' ',$product['size'])}}
                                               </p>
                                               @endif
                                               @if($product['color'])
                                                <p>
                                                        Color : <span style="margin-left: 40px; width: 20px; height: 0.2px; display: block; border: 10px solid {{$product['color'] == "" ? "white" : $product['color']}};"></span>
                                                </p>
                                                @endif
                                                <p>
                                                        Price : {{ $order->currency_sign }} {{ number_format(round($product['item_price'] * $order->currency_value , 2)) }}
                                                </p>
                                                <p>
                                                    SP : {{ number_format(round($product['item_price_shopping_point'] * $order->currency_value , 2)) }}
                                                </p>
                                               <p>
                                                    Qty : {{$product['qty']}} {{ $product['item']['measure'] }}
                                               </p>
                                            </td>

                                            <td width="20%">{{ $order->currency_sign }} {{ number_format(round($product['shop_coupon_amount'] * $order->currency_value , 2)) }}
                                                <p>
                                                    <strong>{{$product['shop_coupon_code']}}</strong>
                                               </p>
                                            </td>
                                            <td width="20%">{{ $order->currency_sign }} {{ number_format(round($product['shopping_point_amount'] * $order->currency_value , 2)) }}
                                            </td>
                                            <td width="20%">{{ $order->currency_sign }} {{ number_format(round(($product['price'] + $product['price_shopping_point_amount'] - $product['shop_coupon_amount'] - $product['shopping_point_amount']) * $order->currency_value , 2)) }}
                                            </td>
                                            @php
                                            $subtotal += round(($product['price'] + $product['price_shopping_point_amount']) * $order->currency_value, 2);
                                            @endphp

                                        </tr>

                                        @endforeach

                                        <tr class="semi-border">
                                            <td colspan="1"></td>
                                            <td colspan="2"><strong>{{ __('Subtotal') }}</strong></td>
                                            <td>{{ $order->currency_sign }} {{ number_format(round($subtotal, 2)) }}</td>

                                        </tr>
                                        @if($order->tax != 0)
                                        <tr class="no-border">
                                            <td colspan="1"></td>
                                            <td colspan="2"><strong>{{ __('TAX') }}</strong></td>

                                            @php
                                            $tax = ($subtotal / 100) * $order->tax;
                                            @endphp

                                            <td>{{ $order->currency_sign }} {{ number_format(round($tax, 2)) }}</td>
                                        </tr>
                                        @endif
                                        @if($order->shipping_cost != 0)
                                        <tr class="no-border">
                                            <td colspan="1"></td>
                                            <td colspan="2"><strong>{{ __('Shipping Cost') }}</strong></td>
                                            <td>{{ $order->currency_sign }} {{ number_format(round($order->shipping_cost , 2)) }}</td>
                                        </tr>
                                        @endif

                                        @if($order->packing_cost != 0)
                                        <tr class="no-border">
                                            <td colspan="1"></td>
                                            <td colspan="2"><strong>{{ __('Packaging Cost') }}({{ $order->currency_sign }})</strong></td>
                                            <td>{{ round($order->packing_cost , 2) }}</td>
                                        </tr>
                                        @endif

                                        @if($order->coupon_discount != null)
                                        <tr class="no-border">
                                            <td colspan="1"></td>
                                            <td colspan="2"><strong>{{ __('Company Discount') }}({{ $order->currency_sign }})</strong></td>
                                            <td>{{ $order->currency_sign }} {{ number_format(round($order->coupon_discount, 2)) }}</td>
                                        </tr>
                                        @endif
                                        <tr class="final-border">
                                            <td colspan="1"></td>
                                            <td colspan="2"><strong>{{ __('Shop Discount') }}</strong></td>
                                            <td>{{ $order->currency_sign }} {{ number_format(round($order->vendor_discount_amount * $order->currency_value , 2)) }}
                                            </td>
                                        </tr>
                                        <tr class="final-border">
                                            <td colspan="1"></td>
                                            <td colspan="2"><strong>{{ __('Final Price before SP') }}</strong></td>
                                            <td>{{ $order->currency_sign }} {{ number_format(round($order->pay_amount1 * $order->currency_value , 2)) }}
                                            </td>
                                        </tr>

                                        <tr class="final-border">
                                            <td colspan="1"></td>
                                            <td colspan="2"><strong>{{ __('Shopping Point') }}</strong></td>
                                            <td>{{ $order->currency_sign }} {{ number_format(round($order->shopping_point_amount * $order->currency_value , 2)) }}
                                            </td>
                                        </tr>

                                        <tr class="final-border">
                                            <td colspan="1"></td>
                                            <td colspan="2"><strong>{{ __('Final Price after SP') }}</strong></td>
                                            <td>{{ $order->currency_sign }} {{ number_format(round($order->pay_amount2 * $order->currency_value , 2)) }}
                                            </td>
                                        </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
<!-- ./wrapper -->



</body>
</html>
