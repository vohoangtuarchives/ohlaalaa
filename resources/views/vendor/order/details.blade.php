@extends('layouts.vendor')

@section('styles')


@endsection


@section('content')
    <div class="content-area">
                        <div class="mr-breadcrumb">
                            <div class="row">
                                <div class="col-lg-12">
                                        <h4 class="heading">{{ $langg->lang549 }} <a class="add-btn" onclick="history.back()"><i class="fas fa-arrow-left"></i> {{ $langg->lang550 }}</a></h4>
                                        <ul class="links">
                                            <li>
                                                <a href="{{ route('vendor-dashboard') }}">{{ $langg->lang441 }} </a>
                                            </li>
                                            <li>
                                                <a href="javascript:;">{{ $langg->lang443 }}</a>
                                            </li>
                                            <li>
                                                <a href="javascript:;">{{ $langg->lang549 }}</a>
                                            </li>
                                        </ul>
                                </div>
                            </div>
                        </div>

                        <div class="order-table-wrap">
                            @include('includes.admin.form-both')
                            <div class="row">

                                <div class="col-lg-6">
                                    <div class="special-box">
                                        <div class="heading-area">
                                            <h4 class="title">
                                            {{ $langg->lang549 }}
                                            </h4>
                                        </div>
                                        <div class="table-responsive-sm">
                                            <table class="table">
                                                <tbody>
                                                <tr>
                                                    <th class="45%" width="45%">{{ $langg->lang551 }}</th>
                                                    <td width="10%">:</td>
                                                    <td class="45%" width="45%">{{$order->order_number}}</td>
                                                </tr>
                                                <tr>
                                                    <th width="45%">{{ $langg->lang552 }}</th>
                                                    <td width="10%">:</td>
                                                    <td width="45%">{{$order->vendororders()->where('user_id','=',$user->id)->sum('qty')}}</td>
                                                </tr>

                                                @php
                                                    $price = round($order->vendororders()->where('user_id','=',$user->id)->sum('price'),2);
                                                    $price_sp = round($order->vendororders()->where('user_id','=',$user->id)->sum('price_shopping_point_amount'),2);
                                                    $price += $price_sp;
                                                @endphp

                                                <tr>
                                                    <th width="45%">{{ $langg->lang552 }} Price</th>
                                                    <td width="10%">:</td>
                                                    <td width="45%">{{$order->currency_sign}}{{number_format($price)}}</td>
                                                </tr>


                                                @if ($order->tax > 0)
                                                    <tr>
                                                        <th width="45%">VAT</th>
                                                        <td width="10%">:</td>
                                                        <td width="45%">{{$order->currency_sign}}{{number_format($order->tax / 100.0 * $price)}}</td>
                                                    </tr>
                                                @endif

                                                {{-- <tr>
                                                    <th width="45%"><strong>{{ $langg->lang867 }}:</strong></th>
                                                    <th width="10%">:</th>
                                                    <td width="45%">{{$order->currency_sign}}{{
                                                        $order->orderconsumershippingcosts->count() > 0 ?
                                                                ($order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first() == null ?
                                                                    'Lỗi: '.$order->id.'---'.Auth::user()->id :
                                                                    number_format($order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first()->shipping_cost) ) :
                                                            0
                                                        }}
                                                    </td>
                                                </tr> --}}
                                                @php
                                                    $shop_discount = round($order->vendororders()->where('user_id','=',$user->id)->sum('shop_coupon_amount'),0);
                                                @endphp
                                                <tr>
                                                    <th width="45%">Shop Discount</th>
                                                    <td width="10%">:</td>
                                                    <td width="45%">{{$order->currency_sign}}{{number_format($shop_discount)}}</td>
                                                </tr>

                                                <tr>
                                                    {{-- <th width="45%">{{ $langg->lang553 }}</th> --}}
                                                    <th width="45%">Price After Discount</th>
                                                    <td width="10%">:</td>

                                                        @php


                                                        $vendor_amount = $price;

                                                        if($order->tax > 0){
                                                            $tax = ($price / 100) * $order->tax;
                                                            $vendor_amount += $tax;
                                                        }

                                                        // if ($order->orderconsumershippingcosts->count() > 0) {
                                                        //     $shipping = $order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first();
                                                        //     if ($shipping != null) {
                                                        //         $vendor_amount += $shipping->shipping_cost;
                                                        //     }
                                                        // }

                                                        @endphp

                                                    <td width="45%">{{$order->currency_sign}}{{ number_format(round(($vendor_amount - $shop_discount) * $order->currency_value , 2)) }}</td>
                                                </tr>

                                                <tr>
                                                    <th width="45%">Shopping Point</th>
                                                    <td width="10%">:</td>
                                                    <td width="45%">{{$order->currency_sign}}{{ number_format($order->vendororders()->where('user_id','=',$user->id)->sum('shopping_point_amount')) }}</td>
                                                </tr>

                                                <tr>
                                                    <th width="45%">Payment Amount after SP & Discount</th>
                                                    <td width="10%">:</td>
                                                    <td width="45%">{{$order->currency_sign}}{{ number_format($vendor_amount - $shop_discount - $order->vendororders()->where('user_id','=',$user->id)->where('is_shopping_point_used','=',1)->sum('shopping_point_amount')) }}</td>
                                                </tr>

                                                <tr>
                                                    <th width="45%">{{ $langg->lang554 }}</th>
                                                    <td width="10%">:</td>
                                                    <td width="45%">{{date('d-M-Y H:i:s a',strtotime($order->created_at))}}</td>
                                                </tr>


                                                <tr>
                                                    <th width="45%">{{ $langg->lang795 }}</th>
                                                    <td width="10%">:</td>
                                                    <td width="45%">{{$order->method}}</td>
                                                </tr>

                                                @if($order->method != "Cash On Delivery")
                                                @if($order->method=="Stripe")
                                                <tr>
                                                    <th width="45%">{{$order->method}} {{ $langg->lang796 }}</th>
                                                    <td width="10%">:</td>
                                                    <td width="45%">{{$order->charge_id}}</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <th width="45%">{{$order->method}} {{ $langg->lang797 }}</th>
                                                    <td width="10%">:</td>
                                                    <td width="45%">{{$order->txnid}}</td>
                                                </tr>
                                                @endif

                                                <tr>
                                                    <th width="45%">{{ $langg->lang798 }}</th>
                                                    <th width="10%">:</th>
                                                    <td width="45%">{!! $order->payment_status == 'Pending' ? "<span class='badge badge-danger'>". $langg->lang799 ."</span>":"<span class='badge badge-success'>". $langg->lang800 ."</span>" !!}</td>
                                                </tr>
                                                @if(!empty($order->order_note))
                                                <tr>
                                                    <th width="45%">{{ $langg->lang801 }}</th>
                                                    <th width="10%">:</th>
                                                    <td width="45%">{{$order->order_note}}</td>
                                                </tr>
                                                @endif

                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="footer-area">
                                            <a href="{{ route('vendor-order-invoice',$order->order_number) }}" class="mybtn1"><i class="fas fa-eye"></i> {{ $langg->lang555 }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="special-box">
                                        <div class="heading-area">
                                            <h4 class="title">
                                            {{ $langg->lang556 }}
                                            </h4>
                                        </div>
                                        <div class="table-responsive-sm">
                                            <table class="table">
                                                <tbody>
                                                        <tr>
                                                            <th width="45%">{{ $langg->lang557 }}</th>
                                                            <th width="10%">:</th>
                                                            <td width="45%">{{$order->customer_name}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th width="45%">{{ $langg->lang558 }}</th>
                                                            <th width="10%">:</th>
                                                            <td width="45%">{{$order->customer_email}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th width="45%">{{ $langg->lang559 }}</th>
                                                            <th width="10%">:</th>
                                                            <td width="45%">{{$order->customer_phone}}</td>
                                                        </tr>
                                                        <tr>
                                                            @php
                                                                $customerward = $order->customerward()->first();
                                                                $customerwardtext = isset($customerward) ? ', '.$customerward->name : '';
                                                                $customerdistrict = $order->customerdistrict()->first();
                                                                $customerdistricttext = isset($customerdistrict) ? ', '.$customerdistrict->name : '';
                                                                $customerprovince = $order->customerprovince()->first();
                                                                $customerprovincettext = isset($customerprovince) ? ', '.$customerprovince->name : '';
                                                            @endphp
                                                            <th width="45%">{{ $langg->lang560 }}</th>
                                                            <th width="10%">:</th>
                                                            <td width="45%">{{$order->customer_address}}{{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }}</td>
                                                        </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                @if($order->dp == 0)
                                <div class="col-lg-6">
                                    <div class="special-box">
                                        <div class="heading-area">
                                            <h4 class="title">
                                            {{ $langg->lang564 }}
                                            </h4>
                                        </div>
                                        <div class="table-responsive-sm">
                                            <table class="table">
                                                <tbody>
                            @if($order->shipping == "pickup")
                        <tr>
                                    <th width="45%"><strong>{{ $langg->lang565 }}:</strong></th>
                                    <th width="10%">:</th>
                                    <td width="45%">{{$order->pickup_location}}</td>
                                </tr>
                            @else

                            @if ($order->is_shipdiff == 1)
                                @php
                                    $shippingward = $order->shippingward()->first();
                                    $shippingwardtext = isset($shippingward) ? ', '.$shippingward->name : '';

                                    $shippingdistrict = $order->shippingdistrict()->first();
                                    $shippingdistricttext = isset($shippingdistrict) ? ', '.$shippingdistrict->name : '';

                                    $shippingprovince = $order->shippingprovince()->first();
                                    $shippingprovincettext = isset($shippingprovince) ? ', '.$shippingprovince->name : '';
                                @endphp
                                <tr>
                                    <th width="45%"><strong>{{ $langg->lang557 }}:</strong></th>
                                    <th width="10%">:</th>
                                    <td>{{$order->shipping_name}}</td>
                                </tr>
                                <tr>
                                    <th width="45%"><strong>{{ $langg->lang558 }}:</strong></th>
                                    <th width="10%">:</th>
                                    <td width="45%">{{$order->shipping_email}}</td>
                                </tr>
                                <tr>
                                    <th width="45%"><strong>{{ $langg->lang559 }}:</strong></th>
                                    <th width="10%">:</th>
                                    <td width="45%">{{$order->shipping_phone}}</td>
                                </tr>
                                <tr>
                                    <th width="45%"><strong>{{ $langg->lang560 }}:</strong></th>
                                    <th width="10%">:</th>
                                    <td width="45%">{{$order->shipping_address}}{{ $shippingwardtext }}{{ $shippingdistricttext }}{{ $shippingprovincettext }}</td>
                                </tr>

                            @else
                                <tr>
                                    <th width="45%"><strong>{{ $langg->lang557 }}:</strong></th>
                                    <th width="10%">:</th>
                                    <td>{{$order->customer_name}}</td>
                                </tr>
                                <tr>
                                    <th width="45%"><strong>{{ $langg->lang558 }}:</strong></th>
                                    <th width="10%">:</th>
                                    <td width="45%">{{$order->customer_email}}</td>
                                </tr>
                                <tr>
                                    <th width="45%"><strong>{{ $langg->lang559 }}:</strong></th>
                                    <th width="10%">:</th>
                                    <td width="45%">{{$order->customer_phone}}</td>
                                </tr>
                                <tr>
                                    <th width="45%"><strong>{{ $langg->lang560 }}:</strong></th>
                                    <th width="10%">:</th>
                                    <td width="45%">{{$order->customer_address}}{{ $customerwardtext }}{{ $customerdistricttext }}{{ $customerprovincettext }}</td>
                                </tr>

                            @endif

                            <tr>
                                <th width="45%"><strong>{{ $langg->lang865 }}:</strong></th>
                                <th width="10%">:</th>
                                <td width="45%">{{
                                    $order->orderconsumershippingcosts->count() > 0 ?
                                        ($order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first() == null ?
                                            '' :
                                            $order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first()->shipping_partner ) :
                                    ''
                                    }}
                                </td>
                            </tr>
                            <tr>
                                <th width="45%"><strong>{{ $langg->lang866 }}:</strong></th>
                                <th width="10%">:</th>
                                <td width="45%">{{
                                    $order->orderconsumershippingcosts->count() > 0 ?
                                            ($order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first() == null ?
                                                'Lỗi: '.$order->id.'---'.Auth::user()->id :
                                                $order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first()->shipping_partner_code ) :
                                        ''
                                    }}
                                </td>
                            </tr>
                            <tr>
                                <th width="45%"><strong>{{ $langg->lang867 }}:</strong></th>
                                <th width="10%">:</th>
                                <td width="45%">{{$order->currency_sign}}{{
                                    $order->orderconsumershippingcosts->count() > 0 ?
                                            ($order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first() == null ?
                                                'Lỗi: '.$order->id.'---'.Auth::user()->id :
                                                number_format($order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first()->shipping_cost) ) :
                                        0
                                    }}
                                </td>
                            </tr>
                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>



                            <div class="row">
                                    <div class="col-lg-12 order-details-table">
                                        <div class="mr-table">
                                            <h4 class="title">{{ $langg->lang566 }}</h4>
                                            <div class="table-responsiv">
                                                    <table id="example2" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                                        <thead>
                                                            <tr>
                                <tr>
                                    <th>{{ $langg->lang567 }}</th>
                                    <th>{{ $langg->lang568 }}</th>
                                    <th>{{ $langg->lang569 }}</th>
                                    <th>{{ $langg->lang570 }}</th>
                                    <th>{{ $langg->lang539 }}</th>
                                    <th>Shop Discount</th>
                                    <th>Shopping Point</th>
                                    <th>{{ $langg->lang574 }}</th>
                                </tr>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                @foreach($cart->items as $key => $product)

                                @if($product['item']['user_id'] != 0)
                                    @if($product['item']['user_id'] == $user->id)
                                    <tr>

                                            <td><input type="hidden" value="{{$key}}">{{ $product['item']['id'] }}</td>

                                            <td>
                                                @if($product['item']['user_id'] != 0)
                                                @php
                                                $user = App\Models\User::find($product['item']['user_id']);
                                                @endphp
                                                @if(isset($user))
                                                <a target="_blank" href="{{route('admin-vendor-show',$user->id)}}">{{$user->shop_name}}</a>
                                                @else
                                                {{ $langg->lang575 }}
                                                @endif
                                                @endif

                                            </td>
                                            <td>
                                                @if($product['item']['user_id'] != 0)
                                                @php
                                                $user = App\Models\VendorOrder::where('order_id','=',$order->id)->where('user_id','=',$product['item']['user_id'])->first();
                                                @endphp

                                                    @if($order->dp == 1 && $order->payment_status == 'Completed')

                                                   <span class="badge badge-success">{{ $langg->lang542 }}</span>

                                                    @else

                                                        @if($user->status == 'pending')
                                                        <span class="badge badge-warning">{{ucwords($user->status)}}</span>
                                                        @elseif($user->status == 'processing')
                                                        <span class="badge badge-info">{{ucwords($user->status)}}</span>
                                                       @elseif($user->status == 'on delivery')
                                                        <span class="badge badge-primary">{{ucwords($user->status)}}</span>
                                                       @elseif($user->status == 'completed')
                                                        <span class="badge badge-success">{{ucwords($user->status)}}</span>
                                                       @elseif($user->status == 'declined')
                                                        <span class="badge badge-danger">{{ucwords($user->status)}}</span>
                                                       @endif

                                                    @endif

                                            @endif
                                            </td>



                                            <td>
                                                <input type="hidden" value="{{ $product['license'] }}">

                                                @if($product['item']['user_id'] != 0)
                                                @php
                                                $user = App\Models\User::find($product['item']['user_id']);
                                                @endphp
                                                @if(isset($user))
                                              <a target="_blank" href="{{ route('front.product', $product['item']['slug']) }}">{{mb_strlen($product['item']['name'],'utf-8') > 30 ? mb_substr($product['item']['name'],0,30,'utf-8').'...' : $product['item']['name']}}</a>
                                                @else
                                                <a href="javascript:;">{{mb_strlen($product['item']['name'],'utf-8') > 30 ? mb_substr($product['item']['name'],0,30,'utf-8').'...' : $product['item']['name']}}</a>
                                                @endif
                                                @endif


                                                @if($product['license'] != '')
                              <a href="javascript:;" data-toggle="modal" data-target="#confirm-delete" class="btn btn-info product-btn" id="license" style="padding: 5px 12px;"><i class="fa fa-eye"></i> View License</a>
                                                @endif
                                                <br>
                                                <img class="img-fluid" src="{{ $product['item']->show_photo() }}" alt="" style="height: 130px">

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
                                                    <strong>{{ $langg->lang311 }} :</strong> {{$product['qty']}} {{ $product['item']['measure'] }}
                                               </p>
                                               <p>
                                                <strong>{{ __('Handling Fee') }} :</strong> {{ $product['item']->getHandlingFee() }}%
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

                                    </tr>

                    @endif

                @endif
                                @endforeach
                                                        </tbody>
                                                    </table>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div class="col-lg-12 text-center mt-2">
                                        <a class="btn sendEmail send" href="javascript:;" class="send" data-email="{{ $order->customer_email }}" data-toggle="modal" data-target="#vendorform">
                                                <i class="fa fa-send"></i> {{ $langg->lang576 }}
                                        </a>
                                    </div> --}}
                                </div>
                        </div>
                    </div>
                    <!-- Main Content Area End -->
                </div>
            </div>


    </div>

{{-- LICENSE MODAL --}}

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

    <div class="modal-header d-block text-center">
        <h4 class="modal-title d-inline-block">{{ $langg->lang577 }}</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
    </div>

                <div class="modal-body">
                    <p class="text-center">{{ $langg->lang578 }} :  <span id="key"></span> <a href="javascript:;" id="license-edit">{{ $langg->lang577 }}</a><a href="javascript:;" id="license-cancel" class="showbox">{{ $langg->lang584 }}</a></p>
                    <form method="POST" action="{{route('vendor-order-license',$order->order_number)}}" id="edit-license" style="display: none;">
                        {{csrf_field()}}
                        <input type="hidden" name="license_key" id="license-key" value="">
                        <div class="form-group text-center">
                    <input type="text" name="{{ $langg->lang585 }}" placeholder="{{ $langg->lang579 }}" style="width: 40%; border: none;" required=""><input type="submit" name="submit" value="Save License" class="btn btn-primary" style="border-radius: 0; padding: 2px; margin-bottom: 2px;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{ $langg->lang580 }}</button>
                </div>
            </div>
        </div>
    </div>


{{-- LICENSE MODAL ENDS --}}

{{-- MESSAGE MODAL --}}
<div class="sub-categori">
    <div class="modal" id="vendorform" tabindex="-1" role="dialog" aria-labelledby="vendorformLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vendorformLabel">{{ $langg->lang576 }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                </div>
            <div class="modal-body">
                <div class="container-fluid p-0">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="contact-form">
                                <form id="emailreply">
                                    {{csrf_field()}}
                                    <ul>
                                        <li>
                                            <input type="email" class="input-field eml-val" id="eml" name="to" placeholder="{{ $langg->lang583 }} *" value="" required="">
                                        </li>
                                        <li>
                                            <input type="text" class="input-field" id="subj" name="subject" placeholder="{{ $langg->lang581 }} *" required="">
                                        </li>
                                        <li>
                                            <textarea class="input-field textarea" name="message" id="msg" placeholder="{{ $langg->lang582 }} *" required=""></textarea>
                                        </li>
                                    </ul>
                                    <button class="submit-btn" id="emlsub" type="submit">{{ $langg->lang576 }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

{{-- MESSAGE MODAL ENDS --}}




@endsection


@section('scripts')

<script type="text/javascript">
$('#example2').dataTable( {
  "ordering": false,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : false,
      'info'        : false,
      'autoWidth'   : false,
      'responsive'  : true
} );
</script>

    <script type="text/javascript">
        $(document).on('click','#license' , function(e){
            var id = $(this).parent().find('input[type=hidden]').val();
            var key = $(this).parent().parent().find('input[type=hidden]').val();
            $('#key').html(id);
            $('#license-key').val(key);
    });
        $(document).on('click','#license-edit' , function(e){
            $(this).hide();
            $('#edit-license').show();
            $('#license-cancel').show();
        });
        $(document).on('click','#license-cancel' , function(e){
            $(this).hide();
            $('#edit-license').hide();
            $('#license-edit').show();
        });

        $(document).on('submit','#edit-license' , function(e){
            e.preventDefault();
          $('button#license-btn').prop('disabled',true);
              $.ajax({
               method:"POST",
               url:$(this).prop('action'),
               data:new FormData(this),
               dataType:'JSON',
               contentType: false,
               cache: false,
               processData: false,
               success:function(data)
               {
                  if ((data.errors)) {
                    for(var error in data.errors)
                    {
                        $.notify('<li>'+ data.errors[error] +'</li>','error');
                    }
                  }
                  else
                  {
                    $.notify(data,'success');
                    $('button#license-btn').prop('disabled',false);
                    $('#confirm-delete').modal('toggle');

                   }
               }
                });
        });
    </script>

@endsection
