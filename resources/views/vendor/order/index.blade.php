@extends('layouts.vendor')
@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">


    <style>
    #ui-datepicker-div {
    width:350px;
    }
     /* .ui-datepicker-calendar {
    display: none;
    } */
    /* .ui-datepicker-month{
    display: none;
    } */
   /* .ui-datepicker-prev{
    display: none;
    }
    .ui-datepicker-next{
    display: none;
    } */
    .ui-datepicker select.ui-datepicker-month, .ui-datepicker select.ui-datepicker-year {
    width: 50%;
    }
    .fontst {
    font-size:9px
    }
    </style>
@endsection

@section('content')



<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <?php if ($status == 'pending') { ?>
                        <h4 class="heading">{{ $langg->lang465 }}</h4>
                <?php }elseif ($status == 'processing') {?>
                    <h4 class="heading">{{ $langg->lang466 }}</h4>
                <?php }elseif ($status == 'declined') {?>
                    <h4 class="heading">{{ $langg->lang917 }}</h4>
                <?php }elseif ($status == 'completed') {?>
                    <h4 class="heading">{{ $langg->lang467 }}</h4>
                <?php } else {?>
                    <h4 class="heading">{{ $langg->lang443 }}</h4>
                <?php } ?>

                    <ul class="links">
                        <li>
                            <a href="{{ route('vendor-dashboard') }}">{{ $langg->lang441 }} </a>
                        </li>
                        <li>
                            <a href="javascript:;">{{ $langg->lang442 }}</a>
                        </li>
                        <li>
                        <?php if ($status == 'pending') { ?>
                            <a href="{{ route('vendor-order-index', 'pending') }}">{{ $langg->lang465 }}</a>
                        <?php }elseif ($status == 'processing') {?>
                            <a href="{{ route('vendor-order-index', 'processing') }}">{{ $langg->lang466 }}</a>
                        <?php }elseif ($status == 'declined') {?>
                            <a href="{{ route('vendor-order-index', 'declined') }}">{{ $langg->lang917 }}</a>
                        <?php }elseif ($status == 'completed') {?>
                            <a href="{{ route('vendor-order-index', 'completed') }}">{{ $langg->lang467 }}</a>
                        <?php } else {?>
                            <a href="{{ route('vendor-order-index') }}">{{ $langg->lang443 }}</a>
                        <?php } ?>
                        </li>
                    </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">

                    @include('includes.admin.form-success')
                    <div class="table-responsiv">
                        {{-- @include('includes.admin.form-error') --}}

                    @if ($status == '' ||  $status == null)
                        <form id="search-form" action="{{route('vendor-order-index')}}" method="POST" enctype="multipart/form-data">
                    @else
                        <form id="search-form" action="{{route('vendor-order-index',["$status"])}}" method="POST" enctype="multipart/form-data">
                    @endif
                        {{-- <form id="search-form" action="{{route('vendor-order-index',["$status"])}}" method="POST" enctype="multipart/form-data"> --}}
                            {{csrf_field()}}
                            <input type="hidden" id="status" name="status"  value="{{ $status }}">
                            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                            <label for="datepicker3"><b>Từ Ngày</b></label>
                            <input type="text" class="form-control-sm" id="from"  name="from_date" autocomplete="off"  value="{{ $fr1 }}"
                            placeholder="(Từ Ngày)" />
                            <label for="datepicker3"><b>Đến Ngày</b></label>
                            <input type="text" class="form-control-sm" id="to"  name="to_date" autocomplete="off"  value="{{ $to1 }}"
                            placeholder="(Đến Ngày)" />
                            <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> {{ __('Tìm kiếm') }} </a>
                            &nbsp;&nbsp;
                            <a class="add-btn" id="export-excel" > <i class="fas fa-file-excel"></i> {{ __('Xuất Excel') }} </a>
                            <a class="add-btn" hidden id="process-pay" data-toggle="modal" data-target="#confirm-verify" data-href="123href" > <i class="fab fa-cc-amazon-pay"></i> {{ __('Process Pay') }} </a>
                        </form>
                    </div>

                    @include('includes.form-success1')
                    <div class="table-responsiv">
                    <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                        <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:20%">{{ $langg->lang534 }}</th>
                                    <th style="width:5%">{{ $langg->lang535 }}</th>
                                    <th>{{ $langg->lang536 }}</th>
                                    <th>{{ $langg->lang537 }}</th>
                                    <th>{{ $langg->lang858 }}</th>
                                    <th>{{ $langg->lang859 }}</th>
                                    <th>{{ $langg->lang860 }}</th>
                                    <th>{{ $langg->lang301 }}</th>
                                    <th style="width:25%">{{ $langg->lang538 }}</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($orders as $orderr)
                                    @php
                                    // $qty = $orderr->sum('qty');
                                    // $price = $orderr->sum('price');
                                    // $price_sp = $orderr->sum('price_shopping_point_amount');
                                    // $shop_discount = $orderr->sum('shop_coupon_amount');
                                    // $shopping_point = $orderr->sum('shopping_point_amount');
                                    // $qty = $orderr->sum('qty');

                                    $qty = $orderr->total_qty;
                                    $price = $orderr->total_price;
                                    $price_sp = $orderr->total_price_shopping_point_amount;
                                    $shop_discount = $orderr->total_shop_coupon_amount;
                                    $shopping_point = $orderr->total_shopping_point_amount;
                                    $order = $orderr;
                                    @endphp

                                    {{-- @foreach($orderr as $order) --}}
                                        @php
                                        // if($user->shipping_cost != 0){
                                        // $price +=  round($user->shipping_cost * $order->order->currency_value , 2);
                                        // }

                                        //$vendor_info = $order->order->ordervendorinfos()->where('shop_id','=',Auth::user()->id)->first();
                                        $vendor_info = $order->vendor_info;
                                        //$vendor_info=[];
                                        $shipping_cost = 0;
                                        // $shipping_cost = ($order->order->orderconsumershippingcosts->count() > 0 ?
                                        // ($order->order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first() == null  ?
                                        // 0 :
                                        // $order->order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first()->shipping_cost) :
                                        // 0);
                                        $tax_amount = ($price + $price_sp) * $order->order->tax / 100.0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <img class="mr-2" src="{{asset('assets/images/admins/copy-solid.svg')}}" style="cursor:pointer;width:20px;" onclick="copyOrderNumber('{{ $order->order->order_number}}')">
                                                <a class="{{ $order->status }} "
                                                    href="{{route('vendor-order-invoice',$order->order_number)}}">{{ $order->order->order_number}}</a>

                                                @if ($order->order->shipping_type == 'negotiate' && $order->order->status != 'declined')
                                                    @if ($order->order->customer_received)
                                                        <br>
                                                        <span class='badge badge-primary'>{{ $langg->lang909 }}</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>{{$qty}}</td>
                                            <td>{{$order->order->currency_sign}}
                                                {{ number_format(round(($price + $price_sp + $tax_amount + $shipping_cost  - $shop_discount - $shopping_point) * $order->order->currency_value, 2))}}
                                            </td>
                                            {{-- <td>{{$order->order->currency_sign}}{{round(($price + ($order->order->orderconsumershippingcosts->count() > 0 ? $order->order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first()->shipping_cost : 0)) * $order->order->currency_value, 2) }}</td> --}}
                                            <td>{{$order->order->method}}</td>
                                            <td>{{ $vendor_info != null && $vendor_info->is_paid == 1 ? \Carbon\Carbon::parse($vendor_info->payment_to_merchant_date)->format('d/m/Y') : '' }}</td>
                                            <td>{{ $vendor_info != null && $vendor_info->is_paid == 1 ? number_format($vendor_info->payment_to_merchant_amount) : '' }}</td>
                                            <td>
                                                {{
                                                    $order->order->orderconsumershippingcosts->count() > 0 ?
                                                        ($order->order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first() == null  ?
                                                            'Lỗi: '.$order->order->id.'---'.Auth::user()->id :
                                                            $order->order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first()->shipping_partner_code) :
                                                        ''
                                                }}
                                                {{-- {{$order->order->orderconsumershippingcosts->count() > 0 ? ($order->order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first() == null ? 'Lỗi: '.$order->order->id.'---'.Auth::user()->id  : $order->order->orderconsumershippingcosts->where('shop_id','=',Auth::user()->id)->first()->shipping_partner_code) : ''}} --}}
                                            </td>
                                                {{-- <td>{{$order->order->orderconsumershippingcosts->count() > 0 ? Auth::user()->id.'---'. $order->order->id : ''}}</td> --}}
                                            <td>{{ \Carbon\Carbon::parse($order->created_at_order)->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="action-list">
                                                    <a href="{{route('vendor-order-show',$order->order->order_number)}}" class="btn btn-primary product-btn"><i class="fa fa-eye"></i> {{ $langg->lang539 }}</a>
                                                    <select class="vendor-btn {{ $order->status }}"
                                                        id="order-number-{{ $order->order_number }}"
                                                        data-val="{{ $order->order_number }}"
                                                        {{-- onfocus="this.setAttribute('data-value', this.value);" --}}
                                                        >
                                                    {{--  <option value="{{ route('vendor-order-status',['slug' => $order->order->order_number, 'status' => 'pending']) }}" {{  $order->status == "pending" ? 'selected' : ''  }}>{{ $langg->lang540 }}</option>
                                                    <option value="{{ route('vendor-order-status',['slug' => $order->order->order_number, 'status' => 'processing']) }}" {{  $order->status == "processing" ? 'selected' : ''  }}>{{ $langg->lang541 }}</option>
                                                    <option value="{{ route('vendor-order-status',['slug' => $order->order->order_number, 'status' => 'completed']) }}" {{  $order->status == "completed" ? 'selected' : ''  }}>{{ $langg->lang542 }}</option>
                                                    <option value="{{ route('vendor-order-status',['slug' => $order->order->order_number, 'status' => 'declined']) }}" {{  $order->status == "declined" ? 'selected' : ''  }}>{{ $langg->lang543 }}</option>  --}}

                                                    <option value="{{ route('vendor-order-status',[$order->order->order_number, 'pending']) }}" {{  $order->status == "pending" ? 'selected' : ''  }}>{{ $langg->lang540 }}</option>
                                                    <option value="{{ route('vendor-order-status',[$order->order->order_number, 'processing']) }}" {{  $order->status == "processing" ? 'selected' : ''  }}>{{ $langg->lang541 }}</option>
                                                    <option value="{{ route('vendor-order-status',[$order->order->order_number, 'completed']) }}" {{  $order->status == "completed" ? 'selected' : ''  }}>{{ $langg->lang542 }}</option>
                                                    {{-- <option value="{{ route('vendor-order-status',[$order->order->order_number, 'completed']) }}" {{  $order->status == "completed" ? 'selected' : ''  }}>{{ $langg->lang542 }}</option> --}}
                                                    <option value="{{ route('vendor-order-status',[$order->order->order_number, 'declined']) }}" {{  $order->status == "declined" ? 'selected' : ''  }}>{{ $langg->lang543 }}</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                {{--
                                            @break
                                        @endforeach --}}
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ORDER MODAL --}}

<div class="modal fade" id="confirm-delete2" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="submit-loader">
        <img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
    </div>
    <div class="modal-header d-block text-center">
        <h4 class="modal-title d-inline-block">{{ $langg->lang544 }}</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

      <!-- Modal body -->
      <div class="modal-body">
        <p class="text-center">{{ $langg->lang545 }}</p>
        <p class="text-center">{{ $langg->lang546 }}</p>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ $langg->lang547 }}</button>
            <a class="btn btn-success btn-ok order-btn" data-val="123">{{ $langg->lang548 }}</a>
      </div>

    </div>
  </div>
</div>

{{-- ORDER MODAL ENDS --}}


@endsection

@section('scripts')

{{-- DATA TABLE --}}

    <script type="text/javascript">
        var mainurl = "{{url('/')}}";

        $("#export-excel" ).on('click' , function(e){
            //var sf = get_date_string($('#from').val());

            var sf = $('#from').val().replace(/\//g, "-");
            var to = $('#to').val().replace(/\//g, "-");
            var status = $('#status').val();
            if (status == null || status == '') {
                status = 'all';
            }

            if(sf == '' || sf == null){
                sf = null;
            }
            if(to == '' || to == null){
                to = null;
            }
            var url = mainurl+'/vendor/orders/export/'+ status + '/' + sf + '/'+to;
            window.open(url, '_blank');
            // $('#process-pay').removeAttr("hidden");
        });

        jQuery(function($) {
            $("#add-find" ).on('click' , function(e) {
                $("#search-form").submit();
            });

            $('input.datetimepicker').datepicker({
                duration: '',
                changeMonth: false,
                changeYear: false,
                yearRange: '2018:2028',
                showTime: false,
                time24h: true
            });

            $.datepicker.regional['vi'] = {
                closeText: 'Đóng',
                prevText: 'Tháng trước',
                nextText: 'Tháng sau',
                currentText: 'Hiện Tại',
                monthNames:["Tháng Một", "Tháng Hai", "Tháng Ba", "Tháng Tư", "Tháng Năm", "Tháng Sáu", "Tháng Bảy", "Tháng Tám", "Tháng Chín", "Tháng Mười", "Tháng Mười Một", "Tháng Mười Hai"],
                monthNamesShort: ["Th.1", "Th.2", "Th.3", "Th.4", "Th.5", "Th.6", "Th.7", "Th.8", "Th.9", "Th.10", "Th.11", "Th.12"],
                dayNames:  ["Chủ Nhật", "Thứ Hai", "Thứ Ba", "Thứ Tư", "Thứ Năm", "Thứ Sáu", "Thứ Bảy", "Chủ Nhật"],
                dayNamesShort: ["CN", "T2", "T3", "T4", "T5", "T6", "T7", "CN"],
                dayNamesMin: ["CN", "T2", "T3", "T4", "T5", "T6", "T7", "CN"],
                weekHeader: 'Týd',
                dateFormat: 'dd/mm/yy',
                firstDay: 1,
                isRTL: false,
                showMonthAfterYear: false,
                // yearSuffix: ''
            };
            $.datepicker.setDefaults($.datepicker.regional['vi']);
        });

        $('#from,#to').datepicker(
        {

            changeDay: true,
            changeMonth: true,
            // yearRange: "-10:+0",
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'dd/mm/yy',
            format: 'dd-mm-yy',
            // closeText:'Select',
            // currentText: 'This year',
            autoclose:true, //to close picker once year is selected

            onClose: function(dateText, inst) {
                // var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();

                // console.log( $(this).datepicker( 'getDate' ));
                // $(this).val($.datepicker.formatDate("dd/mm/yy",  $(this).datepicker( 'getDate' )));

                // function isDonePressed(){
                //     return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
                // }

                // if (isDonePressed()){
                //     var day = $("#ui-datepicker-div .ui-datepicker-day :selected").val();
                //     var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                //     var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                //     $(this).datepicker('setDate', new Date(year, month, day)).trigger('change');
                //     $('.date-picker').focusout()//Added to remove focus from datepicker input box on selecting date
                //     m =  parseInt(month)+1;
                //     var d = ('0' + m).slice(-2)+'-'+year;
                // }

            },
            // onSelect: function () {
            //     selectedDate = $.datepicker.formatDate("yy-mm-dd", $(this).datepicker('getDate'));
            // },
            beforeShow : function(input, inst) {
                if ($(this).val()!=''){
                    var tmpyear = $(this).val();
                    $(this).datepicker('option','defaultDate',new Date(tmpyear, 0, 1));
                }
            }
        })

        // $("#from").datepicker("setDate", new Date());
        $("body").delegate(".vendor-btn", "change", function(){
            console.log('.vendor-btn handled!');
            $('#confirm-delete2').modal('show');
            $('#confirm-delete2').find('.btn-ok').attr('href', $(this).val());
            $('#confirm-delete2').find('.btn-ok').attr('data-val', $(this).data('val'));
        });

        var table = $('#geniustable').DataTable({
            ordering: false,
            pageLength: 100
        });

        $.datepicker._gotoToday = function(id) {
            $(id).datepicker('setDate', new Date()).datepicker('hide').blur();
        };
    </script>
    <script>
        function copyOrderNumber(text){
            navigator.clipboard.writeText(text);
            toastr.success('Đã copy: ' + text);
        }
    </script>
{{-- DATA TABLE --}}
@endsection
