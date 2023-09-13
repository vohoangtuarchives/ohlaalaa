<!DOCTYPE html>
<html>
<head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="{{$seo->meta_keys}}">
        <meta name="author" content="GeniusOcean">

        <title>{{$gs->title}}</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="{{asset('assets/print/bootstrap/dist/css/bootstrap.min.css')}}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('assets/print/font-awesome/css/font-awesome.min.css')}}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="{{asset('assets/print/Ionicons/css/ionicons.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('assets/print/css/style.css')}}">
  <link href="{{asset('assets/print/css/print.css')}}" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        <link rel="icon" type="image/png" href="{{asset('assets/images/'.$gs->favicon)}}">
  <style type="text/css">
@page { size: auto;  margin: 0mm; }
@page {
  size: A4;
  margin: 0;
}
@media print {
    html, body {
        width: 210mm;
        height: 287mm;
    }

    html {

    }
    ::-webkit-scrollbar {
        width: 0px;  /* remove scrollbar space */
        background: transparent;
    }
    thead {display: table-row-group;}
}
  </style>
</head>
<body onload="window.print();">
    <div class="invoice-wrap">
            <div class="invoice__title">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="invoice__logo text-left">
                           <img src="{{ asset('assets/images/'.$gs->invoice_logo) }}" alt="woo commerce logo">
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="invoice__metaInfo">
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
                        <span> <strong>{{ $langg->lang605 }} :</strong> {{$order->method}}</span>
                    </div>
                </div>
            </div>

            <div class="invoice__metaInfo" style="margin-top:0px;">
                @if($order->dp == 0)
                <div class="col-lg-6">
                        <div class="invoice__orderDetails" style="margin-top:5px;">
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
                <div class="col-lg-6" style="width:50%;">
                        <div class="invoice__orderDetails" style="margin-top:5px;">
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

                <div class="col-lg-12">
                    <div class="invoice_table">
                        <div class="mr-table">
                            <div class="table-responsive">
                                <table id="example2" class="table table-hover dt-responsive" cellspacing="0"
                                    width="100%">
                                    <thead style="border-top:1px solid rgba(0, 0, 0, 0.1) !important;">
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
                                            <td width="25%">
                                                @if($product['item']['user_id'] != 0)
                                                @php
                                                $user = App\Models\User::find($product['item']['user_id']);
                                                @endphp
                                                @if(isset($user))
                                                {{ $product['item']['name']}}
                                                @else
                                                {{$product['item']['name']}}
                                                @endif

                                                @else
                                                {{ $product['item']['name']}}
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
                                                        <strong>{{ __('color') }} :</strong> <span style="width: 20px; height: 5px; display: block; border: 10px solid {{$product['color'] == "" ? "white" : '#'.$product['color']}};"></span>
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

                                            <td width="15%">{{$order->currency_sign}}{{ number_format(round($product['shop_coupon_amount'] * $order->currency_value , 2)) }}
                                                <p>

                                                    <b>{{ $product['shop_coupon_code'] }}</b>

                                                </p>
                                            </td>
                                            <td  width="15%">{{$order->currency_sign}}{{ number_format(round($product['shopping_point_amount'] * $order->currency_value , 2)) }}</td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($product['price'] + $product['price_shopping_point_amount'] - $product['shop_coupon_amount'] - $product['shopping_point_amount']) * $order->currency_value , 2)) }}</td>
                                            {{-- <td>{{$order->currency_sign}}{{ round($product['price'] * $order->currency_value , 2) }}
                                            </td> --}}
                                            @php
                                            // $subtotal += round($product['price'] * $order->currency_value, 2);

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

                                        <tr class="semi-border">
                                            <td colspan="2"></td>
                                            <td colspan="2"><strong>{{ $langg->lang597 }}</strong></td>
                                            <td>{{$order->currency_sign}}{{ number_format(round($subtotal, 2)) }}</td>

                                        </tr>
                                        @if(Auth::user()->id == $order->vendor_shipping_id)
                                        @if($order->shipping_cost != 0)
                                            @php
                                            $price = round(($order->shipping_cost / $order->currency_value),2);
                                            @endphp
                                            @if(DB::table('shippings')->where('price','=',$price)->count() > 0)
                                            <tr class="no-border">
                                                <td colspan="1"></td>
                                                <td><strong>{{ DB::table('shippings')->where('price','=',$price)->first()->title }}({{$order->currency_sign}})</strong></td>
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
                                            <tr class="no-border">
                                                <td colspan="1"></td>
                                                <td><strong>{{ DB::table('packages')->where('price','=',$pprice)->first()->title }}({{$order->currency_sign}})</strong></td>
                                                <td>{{ round($order->packing_cost , 2) }}</td>
                                            </tr>
                                            @endif
                                        @endif
                                        @endif

                                        @php
                                            $vendor_amount = $subtotal;
                                        @endphp

                                        @if($order->tax != 0)
                                        <tr class="no-border">
                                            <td colspan="2"></td>
                                            <td colspan="2"><strong>{{ $langg->lang599 }}({{$order->currency_sign}})</strong></td>

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
                                            <tr class="no-border">
                                                <td colspan="2"></td>
                                                <td colspan="2"><strong>{{ $langg->lang878 }}</strong></td>
                                                <td>{{$order->currency_sign}}{{ isset($shipping) ? number_format(round($shipping->shipping_cost , 2)) : 0 }}</td>
                                            </tr>
                                        @endif --}}

                                        <tr class="no-border">
                                            <td colspan="2"></td>
                                            <td colspan="2"><strong>Shop Discount</strong></td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($discount_amount), 2)) }}
                                            </td>
                                        </tr>

                                        <tr class="no-border">
                                            <td colspan="2"></td>
                                            <td colspan="2"> <strong>Price After Discount</strong></td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($vendor_amount - $discount_amount), 2)) }}
                                            </td>
                                        </tr>

                                        <tr class="no-border">
                                            <td colspan="2"></td>
                                            <td colspan="2"><strong>{{ $langg->lang880 }}</strong></td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($shopping_point_amount), 2)) }}
                                            </td>
                                        </tr>

                                        <tr class="final-border">
                                            <td colspan="2"></td>
                                            <td colspan="2"><strong>Payment Amount After SP & Discount</strong></td>
                                            <td>{{$order->currency_sign}}{{ number_format(round(($vendor_amount - $shopping_point_amount - $discount_amount), 2)) }}
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

<script type="text/javascript">
setTimeout(function () {
        window.close();
      }, 500);
</script>

</body>
</html>
