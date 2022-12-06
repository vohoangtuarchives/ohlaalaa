@extends('layouts.front')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">
<style>
    #ui-datepicker-div {
        width:350px;
     }
    .ui-datepicker-calendar {
       display: none;
    }
    .ui-datepicker-prev{
       display: none;
    }
    .ui-datepicker-next{
       display: none;
    }
    .ui-datepicker select.ui-datepicker-month, .ui-datepicker select.ui-datepicker-year {
        width: 50%;
    }
    .fontst {
        font-size:9px
    }
    .disable-click{
        pointer-events:none;
    }
    </style>
@endsection

@section('content')


<section class="user-dashbord">
    <div class="container">
      <div class="row">
        @include('includes.user-dashboard-sidebar')

        {{-- <div class="col-lg-8">
            @include('includes.form-success')
            <div class="col-lg-6">
                <div class="user-profile-details">
                <div class="account-info">
                    <div class="header-area">
                    <h4 class="title">
                        {{ $langg->lang208 }}
                    </h4>
                    </div>
                    <div class="edit-info-area"></div>
                    <div class="main-info">
                    <h5 class="title">{{ $user->name }} | {{ $user->rank_name() }}</h5>
                    <ul class="list">
                        <li>
                        <p>
                            <span class="user-title">{{ $langg->lang209 }}:</span> {{ $user->email }}
                        </p>
                        </li> @if($user->phone != null) <li>
                        <p>
                            <span class="user-title">{{ $langg->lang210 }}:</span> {{ $user->phone }}
                        </p>
                        </li> @endif <li>
                        <p>
                            <span class="user-title">{{ $langg->lang214 }}:</span> {{ $user->address }}{{ isset($user->province) ? ', '.$user->province->name : '' }}
                        </p>
                        </li>
                    </ul>
                    </div>
                </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="user-profile-details h100">
                <div class="account-info wallet h100">
                    <div class="header-area">
                    <h4 class="title">
                        {{ $langg->lang812 }}
                    </h4>
                    </div>
                    <div class="edit-info-area"></div>
                    <div class="main-info">
                    <h3 class="title w-title">Reward Point: {{ number_format($user->reward_point) }}</h3>
                    <h3 class="title w-price">Shopping Point: {{ number_format($user->shopping_point) }}</h3>
                    </div>
                </div>
                </div>
            </div>
        </div>
        --}}
        {{-- <div class="row row-cards-one mb-3">
          <div class="col-md-6 col-xl-6">
            <div class="card c-info-box-area">
              <div class="c-info-box box2">
                <p>{{ Auth::user()->orders()->where('status','completed')->count() }}</p>
              </div>
              <div class="c-info-box-content">
                <h6 class="title">{{ isset($langg->lang809) ? $langg->lang809 : 'Total Orders' }}</h6>
                <p class="text">{{ isset($langg->lang811) ? $langg->lang811 : 'All Time' }}</p>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xl-6">
            <div class="card c-info-box-area">
              <div class="c-info-box box1">
                <p>{{ Auth::user()->orders()->where('status','pending')->count() }}</p>
              </div>
              <div class="c-info-box-content">
                <h6 class="title">{{ isset($langg->lang810) ? $langg->lang810 : 'Pending Orders' }}</h6>
                <p class="text">{{ isset($langg->lang811) ? $langg->lang811 : 'All Time' }}</p>
              </div>
            </div>
          </div>
        </div> --}}

        {{-- <div class="row">
          <div class="col-lg-12">
            <div class="user-profile-details">
              <div class="account-info wallet">
                <div class="header-area">
                  <h4 class="title">
                    {{ isset($langg->lang808) ? $langg->lang808 : 'Recent Orders' }}
                  </h4>
                </div>
                <div class="edit-info-area"></div>
                <div class="main-info">
                  <div class="mr-table allproduct mt-4">
                    <div class="table-responsiv">
                      <table id="example" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                        <thead>
                          <tr>
                            <th>{{ $langg->lang278 }}</th>
                            <th>{{ $langg->lang279 }}</th>
                            <th>{{ $langg->lang280 }}</th>
                            <th>{{ $langg->lang281 }}</th>
                            <th>{{ $langg->lang282 }}</th>
                          </tr>
                        </thead>
                        <tbody> @foreach(Auth::user()->orders()->latest()->take(5)->get() as $order) <tr>
                            <td>
                              {{$order->order_number}}
                            </td>
                            <td>
                              {{date('d M Y',strtotime($order->created_at))}}
                            </td>
                            <td>
                              {{$order->currency_sign}}{{ number_format(round($order->pay_amount1 * $order->currency_value , 2)) }}
                            </td>
                            <td>
                              <div class="order-status {{ $order->status }}">
                                {{ucwords($order->status)}}
                              </div>
                            </td>
                            <td>
                              <a class="mybtn2 sm" href="{{route('user-order',$order->id)}}">
                                {{ $langg->lang283 }}
                              </a>
                            </td>
                          </tr> @endforeach </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> --}}

        {{-- <div id="validation-errors"></div> --}}
         <div class="col-lg-8">
            {{-- <div class="row mb-8"> --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="mr-table allproduct">
                           <div id="validation-errors"></div>
                            @include('includes.admin.form-success')
                            <div class="table-responsiv">
                            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>



                            <label for="datepicker3"><b>Chọn tháng và năm</b></label>
                            <input type="text" class="form-control-sm" id="from"  name="from_date" placeholder="(month and year only)" autocomplete="off" />

                            <a class="mybtn1" id="add-find" > <i class="fas fa-search"></i> {{ __('Tìm kiếm') }} </a>
                            &nbsp;&nbsp;
                            <a class="mybtn1" id="export-excel" > <i class="fas fa-file-excel"></i> {{ __('Xuất báo cáo') }} </a>
                            <a class="add-btn" hidden id="process-pay" data-toggle="modal" data-target="#confirm-verify" data-href="123href" > <i class="fab fa-cc-amazon-pay"></i> {{ __('Process Pay') }} </a>


                            <table id="geniustable1" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('KOL') }}</th>
                                        <th>{{ __('Tổng User mới') }}
                                            <br><code class="fontst" id="number_users_l1">(L1 >= {{(isset($config->number_users_l1) ?  $config->number_users_l1 :  "") }})</code>
                                            <br><code class="fontst" id="number_users_l2">(L2 >= {{ (isset($config->number_users_l2) ?  $config->number_users_l2 :  "") }})</code>
                                        </th>
                                        <th>{{ __('Số lượng đơn hàng') }}
                                            <br><code class="fontst" id="number_orders_l1">(L1 >= {{ (isset($config->number_orders_l1) ?  $config->number_orders_l1 :  "") }})</code>
                                            <br><code class="fontst" id="number_orders_l2">(L2 >= {{ (isset($config->number_orders_l2) ?  $config->number_orders_l2 :  "") }})</code>
                                        </th>
                                        <th>{{ __('Tổng đơn hàng') }}<br>{{ __('User mới') }}</th>
                                        <th>{{ __('Tống tiền') }}<br>{{ __('User mới') }}</th>
                                        <th>{{ __('Tổng đơn hàng') }}<br>{{ __('User cũ') }}</th>
                                        <th>{{ __('Tống tiền') }}<br>{{ __('User cũ') }}</th>
                                        <th>{{ __('Tống chi tiêu L1') }}</th>
                                        <th>{{ __('Tiền thưởng từ L1 (1)') }}</th>
                                        <th>{{ __('(%) được thưởng') }}
                                            <br><code class="fontst" id="con_bonus_l1">(L1 >= {{ (isset($config->con_bonus_l1) ?  $config->con_bonus_l1 :  "") }})</code>
                                            <br><code class="fontst" id="con_bonus_l2">(L2 >= {{ (isset($config->con_bonus_l2) ?  $config->con_bonus_l2 :  "")  }})</code>
                                        </th>
                                            <th>{{ __('Doanh thu shop') }}
                                            <br><code class="fontst" id="revenue_l1">(L1 >= {{ (isset($config->revenue_l1) ?  number_format($config->revenue_l1, 0, ',', ','). " đ" : "") }})</code>
                                            <br><code class="fontst" id="revenue_l2">(L2 >= {{ (isset($config->revenue_l2) ?  number_format($config->revenue_l2, 0, ',', ','). " đ" : "")  }})</code>
                                        </th>

                                        <th>{{ __('Số lượng shop uy tín') }}
                                            <br><code style="font-size:8px" id="number_shops_l1">(L1 >= {{(isset($config->number_shops_l1) ?  $config->number_shops_l1 :  "")   }})</code>
                                            <br><code style="font-size:8px" id="number_shops_l2">(L2 >= {{ (isset($config->number_shops_l2) ?  $config->number_shops_l2 :  "")  }})</code>
                                        </th>
                                        <th>{{ __('Thành viên Affiliate mới') }}
                                            <br><code class="fontst" id="number_affiliate_member_l1">(L1 >= {{ (isset($config->number_affiliate_member_l1) ?  $config->number_affiliate_member_l1 :  "") }})</code>
                                            <br><code class="fontst" id="number_affiliate_member_l2">(L2 >= {{ (isset($config->number_affiliate_member_l2) ?  $config->number_affiliate_member_l2 :  "") }})</code>
                                        </th>
                                        <th>{{ __('Tổng tiền thưởng từ Affiliate (2)') }}</th>
                                        <th>{{ __('VAT') }}</th>
                                        <th>{{ __('Tổng tiền thưởng (1) + (2)') }}</th>
                                    </tr>
                                </thead>
                            </table>
                            </div>
                        </div>
                    </div>
                {{-- </div> --}}
            </div>
        </div>
    </div>
  </div>
</section>

<input type="hidden" id="headerdata" value="{{ __('ORDER') }}">


{{-- confirm MODAL --}}

<div class="modal fade" id="confirm-verify" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

      <div class="modal-header d-block text-center">
          <h4 class="modal-title d-inline-block">{{ __("Confirm") }}</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
      </div>

        <!-- Modal body -->
        <div class="modal-body">
              <p class="text-center">{{ __("You are about to confirm to Process Pay.") }}</p>
              <p class="text-center">{{ __("Do you want to proceed?") }}</p>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-default" data-dismiss="modal">{{ __("Cancel") }}</button>
              <a class="btn btn-primary btn-ok">{{ __("Proceed") }}</a>
        </div>

      </div>
    </div>
</div>

  {{-- confirm MODAL ENDS --}}

@endsection

@section('scripts')


{{-- DATA TABLE --}}

<script type="text/javascript">

$(document).ready(function () {
    var table = $('#geniustable1').DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: "{{ route('user-order-kol-bonus-datatables',[date('m-Y')]) }}",
        columns: [
                { data: 'kol_info', name: 'kol_info'}, //1
                { data: 'total_user', name: 'total_user'},//2
                { data: 'total_order', name: 'total_order'},//3
                { data: 'total_order_user_new', name: 'total_order_user_new'},//4
                { data: 'total_amount_user_new', name: 'total_amount_user_new'},//5
                { data: 'total_order_user_exits', name: 'total_order_user_exits'},//6
                { data: 'total_amount_user_exits', name: 'total_amount_user_exits'},//7
                { data: 'total_amount', name: 'total_amount'},//8
                { data: 'bonus', name: 'bonus'},//9
                { data: 'kol_consumer_bonus_rate', name: 'kol_consumer_bonus_rate'},//10
                { data: 'revenue_total_sales', name: 'revenue_total_sales'},//10
                { data: 'total_new_shop', name: 'total_new_shop'},//11
                { data: 'total_affiliate_member', name: 'total_affiliate_member'},//12
                { data: 'total_affiliate_bonus', name: 'total_affiliate_bonus'},//13
                { data: 'vat', name: 'vat'},//14
                { data: 'total_bonus', name: 'total_bonus'}//14
                ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }


    });

    // $("#add-find" ).on('click' , function(e){
    //     var sf = get_date_string($('#from').val());
    //     var url = mainurl+'/admin/orders/reports/kolconsumerbonus/datatables/'+sf;
    //     console.log('url',url);
    //     table.ajax.url( url ).load();
    // });

    $("#add-find" ).on('click' , function(e) {
        var sf =  $("#from").val();
        var to = $("#from").val();
        var kol_date = $( "#kol_date" );
        var kol_id = $( "#kol_id" );
        var con_bonus_l1        = $( "#con_bonus_l1" );
        var number_orders_l1    = $( "#number_orders_l1" );
        var number_users_l1     = $( "#number_users_l1" );
        var number_shops_l1     = $( "#number_shops_l1" );
        var number_affiliate_member_l1 = $( "#number_affiliate_member_l1" );
        var revenue_l1          = $( "#revenue_l1" );
        var con_bonus_l2        = $( "#con_bonus_l2" );
        var number_orders_l2    = $( "#number_orders_l2" );
        var number_users_l2     = $( "#number_users_l2" );
        var number_shops_l2     = $( "#number_shops_l2" );
        var number_affiliate_member_l2 = $( "#number_affiliate_member_l2" );
        var revenue_l2          = $( "#revenue_l2" );
        $.ajax({
            type:"GET",
            url:"{{ route('user-get-kol','') }}"+"/"+sf,
            success: function(data){
              $("#export-excel" ).removeClass("disable-click");
              if (Object.keys(data).length > 0) {
                  con_bonus_l1.html('(L1 >= ' + data.con_bonus_l1 + ')');
                  number_orders_l1.html('(L1 >= ' + data.number_orders_l1 + ')');
                  number_users_l1.html('(L1 >= ' + data.number_users_l1 + ')');
                  number_shops_l1.html('(L1 >= ' + data.number_shops_l1 + ')');
                  number_affiliate_member_l1.html('(L1 >= ' + data.number_affiliate_member_l1 + ')');
                  revenue_l1.html('(L2 >= ' +  parseInt(data.revenue_l1).toLocaleString()  + ')');
                  con_bonus_l2.html('(L2 >= ' + data.con_bonus_l2 + ')');
                  number_orders_l2.html('(L2 >= ' + data.number_orders_l2 + ')');
                  number_users_l2.html('(L2 >= ' + data.number_users_l2 + ')');
                  number_shops_l2.html('(L2 >= ' + data.number_shops_l2 + ')');
                  number_affiliate_member_l2.html('(L2 >= ' + data.number_affiliate_member_l2 + ')');
                  revenue_l2.html('(L2 >= ' +  parseInt(data.revenue_l2).toLocaleString() + ')');
                  var url = mainurl+'/user/orders/reports/kolconsumerbonus/datatables/'+sf;
                  // var url = mainurl+'/admin/orders/reports/kolconsumerbonus/datatables/'+sf;
                  // console.log('url',url);
                  table.ajax.url( url ).load();
              } else {
                  con_bonus_l1.val('');
                  number_orders_l1.val('');
                  number_users_l1.val('');
                  number_shops_l1.val('');
                  number_affiliate_member_l1.val('');
                  revenue_l1.val('');
                  con_bonus_l2.val('');
                  number_orders_l2.val('');
                  number_users_l2.val('');
                  number_shops_l2.val('');
                  number_affiliate_member_l2.val('');
                  revenue_l2.val('');
              };
              $('#validation-errors').html('');
            },
            error: function (data) {
                $('#validation-errors').html('');
                $('#validation-errors').append('<div class="alert alert-danger">'+data.responseJSON.errors+'</div');
                $("#export-excel").addClass("disable-click");
            },
            complete: function(xmlHttp) {
            },

        });
        // var to = $("#from").datepicker("getDate");
        // sf = $.datepicker.formatDate("yy-mm-dd", sf);
        // to = $.datepicker.formatDate("yy-mm-dd", to);
        // sf = $.datepicker.formatDate("mm-yy", sf);
        // to = $.datepicker.formatDate("mm-yy", to);
    });

    $("#export-excel" ).on('click' , function(e){
        //var sf = get_date_string($('#from').val());
        var sf = $('#from').val();
        var url = mainurl+'/user/orders/reports/kolconsumerbonus/export/'+sf;
        window.open(url, '_blank');
        // $('#process-pay').removeAttr("hidden");
    });

    $(document).on('click', '#process-pay', function(e){
        var sf = get_date_string($('#from').val());
        var url = mainurl+'/admin/orders/reports/kolconsumerbonus/processpay/'+sf;
        $('.btn-ok').attr('href', url);
    });

    $('#from').datepicker(
    {
        dateFormat: "mm-yy",
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        onClose: function(dateText, inst) {
            console.log('close');
            function isDonePressed(){
                return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
            }

            if (isDonePressed()){

                var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(year, month, 1)).trigger('change');

                $('.date-picker').focusout()//Added to remove focus from datepicker input box on selecting date
                m =  parseInt(month)+1;
                var d = ('0' + m).slice(-2)+'-'+year;

                // var date = $(this).datepicker("getDate");
                // var year = inst.selectedYear, month = inst.selectedMonth+1;

                // var  daysOfMonth = new Date(year, month,0).getDate();
                // var d = date.getDate();

                // date.setDate(date.getDate() + (daysOfMonth-d));
                // $("#to").datepicker("setDate", date);
            }
        },

        beforeShow : function(input, inst) {
            inst.dpDiv.addClass('month_year_datepicker')

            if ((datestr = $(this).val()).length > 0) {
                year = datestr.substring(datestr.length-4, datestr.length);
                month = datestr.substring(0, 2);
                $(this).datepicker('option', 'defaultDate', new Date(year, month-1, 1));
                $(this).datepicker('setDate', new Date(year, month-1, 1));
                $(".ui-datepicker-calendar").hide();
            }
        }
    })

    $("#from").datepicker("setDate", new Date());

    // var available_formatted_days_list = [1];
    // function check_available_days( date )
    // {
    //     var formatted_date = '', ret = [true, "", ""];
    //     if (date instanceof Date)
    //     {
    //         formatted_date = $.datepicker.formatDate( 'mm-dd-yy', date );
    //     }
    //     else
    //     {
    //         formatted_date = '' + date;
    //     }
    //     let currentDay = date.getDate();
    //     if ( -1 === available_formatted_days_list.indexOf(currentDay) )
    //     {
    //         ret[0] = false;
    //         ret[1] = "date-disabled"; // put yopur custom csc class here for disabled dates
    //         ret[2] = "Date not available"; // put your custom message here
    //     }
    //     return ret;
    // }
});

</script>

@endsection
