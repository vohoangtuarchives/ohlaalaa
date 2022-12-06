@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<style>
#ui-datepicker-div {
    width:350px;
 }
.ui-datepicker-calendar {
   display: none;
}
/* .ui-datepicker-month {
   display: none;
} */
.ui-datepicker-prev{
   display: none;
}
.ui-datepicker-next{
   display: none;
}
.ui-datepicker select.ui-datepicker-month, .ui-datepicker select.ui-datepicker-year {
    width: 50%;
}
</style>
@endsection

@section('content')
<?php
    $kol_date = '';
    $kol_id = '';

    $con_bonus_l1 = '';
    $number_orders_l1 = '';
    $number_users_l1 = '';
    $number_shops_l1 = '';
    $avg_amount_order_l1 = '';
    $number_affiliate_member_l1 = '';
    $revenue_l1 =  0;
    $con_bonus_l2 = '';
    $number_orders_l2 = '';
    $number_users_l2 = '';
    $number_shops_l2 = '';
    $avg_amount_order_l2 = '';
    $number_affiliate_member_l2 = '';
    $revenue_l2 = 0;


if (isset($data) && $data != null) {

    $con_bonus_l1 = (isset($data->con_bonus_l1) ? $data->con_bonus_l1 : '');
    $number_orders_l1 = (isset($data->number_orders_l1) ? $data->number_orders_l1 : '');
    $number_users_l1 = (isset($data->number_users_l1) ? $data->number_users_l1 : '');
    $number_shops_l1 =(isset($data->number_shops_l1) ? $data->number_shops_l1 : '');
    $avg_amount_order_l1 = (isset($data->avg_amount_order_l1) ? $data->avg_amount_order_l1 : '');
    $number_affiliate_member_l1 = (isset($data->number_affiliate_member_l1) ? $data->number_affiliate_member_l1 : '');
    $revenue_l1 = (isset($data->revenue_l1) ? $data->revenue_l1 : 0);

    $con_bonus_l2 = (isset($data->con_bonus_l2) ? $data->con_bonus_l2 : '');
    $number_orders_l2 = (isset($data->number_orders_l2) ? $data->number_orders_l2 : '');
    $number_users_l2 = (isset($data->number_users_l2) ? $data->number_users_l2 : '');
    $number_shops_l2 =(isset($data->number_shops_l2) ? $data->number_shops_l2 : '');
    $avg_amount_order_l2 = (isset($data->avg_amount_order_l2) ? $data->avg_amount_order_l2 : '');
    $number_affiliate_member_l2 = (isset($data->number_affiliate_member_l2) ? $data->number_affiliate_member_l2 : '');
    $revenue_l2 = (isset($data->revenue_l2) ? $data->revenue_l2 : 0);

    $kol_date = (isset($data->kol_date) ? $data->kol_date : '');
    $kol_id = (isset($data->id) ? $data->id : '');
}
?>

<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('KOL Bonus Informations') }}</h4>
                <ul class="links">
                    <li>
                    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                    </li>
                    <li>
                    <a href="javascript:;">{{ __('General Settings') }}</a>
                    </li>
                    <li>
                    <a href="{{ route('admin-gs-kol') }}">{{ __('Kol bonus') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div id="validation-errors"></div>
    <div class="add-product-content1">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">

                        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                            <form  id="geniusform" method="POST" enctype="multipart/form-data">
                                {{ csrf_field() }}


                                <div class="form-label-group">
                                <label for="datepicker3">Select Month Year <code>Set configs to KOL</code> </label>
                                    <input type="text" class="form-control" id="datepicker3"
                                            placeholder="(year only, no controls)" />
                                </div>




                                <div  style="background-color:  #e9e7e7; max-width: 700px; padding: 15px; margin: 0 auto;">
                                    {{-- KOL Consumer Bonus Rate --}}
                                    <h5 class="heading">{{ __('KOL Consumer Bonus Rate Level 1') }}</h5>
                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                        <div class="left-area">
                                            <h4 class="heading">{{ __('Rate Level 1 (%)') }}</h4>
                                        </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="% kol consumer bonus"
                                            name="con_bonus_l1" id="con_bonus_l1"
                                            value="{{ $con_bonus_l1 }}" required="">
                                        </div>
                                    </div>

                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Number Orders') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="Number Orders"
                                            name="number_orders_l1"   id="number_orders_l1"
                                            value="{{ $number_orders_l1 }}" required="">
                                        </div>
                                    </div>

                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Number Users') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="Number Users"
                                            id="number_users_l1" name="number_users_l1"
                                            value="{{ $number_users_l1 }}" required="">
                                        </div>
                                    </div>
                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Number Shops') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="Number Shops"
                                            id="number_shops_l1" name="number_shops_l1"
                                            value="{{ $number_shops_l1 }}" required="">
                                        </div>
                                    </div>
                                        <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('New Affiliate Member') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="New Affiliate Member"
                                            id="number_affiliate_member_l1"
                                            name="number_affiliate_member_l1" value="{{ $number_affiliate_member_l1 }}" required="">
                                        </div>
                                    </div>


                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Shop Revenue') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="Shop Revenue"
                                            id="revenue_l1"
                                            name="revenue_l1" value="{{ $revenue_l1 }}" required="">
                                        </div>
                                    </div>
                                        {{-- <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Minimum Amount For an Order') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="AVG Amount For Order"
                                            name="avg_amount_order_l1"  id="avg_amount_order_l1"
                                            value="{{ $avg_amount_order_l1 }}" required="">
                                        </div>
                                    </div> --}}
                                </div>


                                <div  style="background-color:  #e9e7e7; max-width: 700px; padding: 15px; margin: 0 auto;">
                                    {{-- KOL Consumer Bonus Rate --}}
                                    <h5 class="heading">{{ __('KOL Consumer Bonus Rate Level 2') }}</h5>

                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Rate Level 2 (%)') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="% kol consumer bonus"
                                            name="con_bonus_l2"  id="con_bonus_l2"
                                            value="{{ $con_bonus_l2 }}" required="">
                                        </div>
                                    </div>

                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Number Orders') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="Number Orders"
                                            name="number_orders_l2"   id="number_orders_l2"
                                            value="{{ $number_orders_l2 }}" required="">
                                        </div>
                                    </div>

                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Number Users') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="Number Users"
                                            id="number_users_l2" name="number_users_l2"
                                            value="{{ $number_users_l2 }}" required="">
                                        </div>
                                    </div>
                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Number Shops') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="Number Shops"
                                            id="number_shops_l2" name="number_shops_l2"
                                            value="{{ $number_shops_l2 }}" required="">
                                        </div>
                                    </div>
                                        <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('New Affiliate Member') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="New Affiliate Member"
                                            id="number_affiliate_member_l2"
                                            name="number_affiliate_member_l2" value="{{ $number_affiliate_member_l2 }}" required="">
                                        </div>
                                    </div>
                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Shop Revenue') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="Shop Revenue"
                                            id="revenue_l2"
                                            name="revenue_l2" value="{{ $revenue_l2 }}" required="">
                                        </div>
                                    </div>
                                    {{-- <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area   ">
                                                <h4 class="heading">{{ __('AVG Amount For Order') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="AVG Amount For Order"
                                            name="avg_amount_order_l2"  id="avg_amount_order_l2"
                                            value="{{ $avg_amount_order_l2 }}" required="">
                                        </div>
                                    </div> --}}
                                </div>


                                <div  style="background-color:  #e9e7e7; max-width: 700px; padding: 15px; margin: 0 auto;">
                                    {{-- KOL Consumer Bonus Rate --}}
                                    <h5 class="heading">{{ __('Minimum Amount For an Order') }}</h5>
                                    <div class="row justify-content-center">
                                        <div class="col-lg-3">
                                            <div class="left-area">
                                                <h4 class="heading">{{ __('Minimum Amount') }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="number" step="Any" class="input-field" placeholder="AVG Amount For Order"
                                            name="avg_amount_order_l1"  id="avg_amount_order_l1"
                                            value="{{ $avg_amount_order_l1 }}" required="">
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" id='kol_date' name='kol_date'  value="{{ $kol_date }}"/>
                                <input type="hidden" id='kol_id' name='kol_id' value="{{ $kol_id }}"/>

                                <div class="row justify-content-center">
                                    <div class="col-lg-3">
                                        <div class="left-area"></div>
                                    </div>
                                    <div class="col-lg-6">
                                        <button class="addProductSubmit-btn" type="submit">{{ __('Save') }}</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
@php

// dd ($gs);
@endphp
@endsection

@section('scripts')



    <script type="text/javascript">
        $(document).ready(function() {

            var dateToday = new Date();
            var kol_date = $( "#kol_date" );
            var kol_id = $( "#kol_id" );

            var con_bonus_l1        = $( "#con_bonus_l1" );
            var number_orders_l1    = $( "#number_orders_l1" );
            var number_users_l1     = $( "#number_users_l1" );
            var number_shops_l1     = $( "#number_shops_l1" );
            var total_amount_l1     = $( "#total_amount_l1" );
            var avg_amount_order_l1 = $( "#avg_amount_order_l1" );
            var number_affiliate_member_l1 = $( "#number_affiliate_member_l1" );
            var revenue_l1 = $( "#revenue_l1" );

            var con_bonus_l2        = $( "#con_bonus_l2" );
            var number_orders_l2    = $( "#number_orders_l2" );
            var number_users_l2     = $( "#number_users_l2" );
            var number_shops_l2     = $( "#number_shops_l2" );
            var total_amount_l2     = $( "#total_amount_l2" );
            var avg_amount_order_l2 = $( "#avg_amount_order_l2" );
            var number_affiliate_member_l2 = $( "#number_affiliate_member_l2" );
            var revenue_l2 = $( "#revenue_l2" );

            $("form#geniusform").submit(function(event) {
                event.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin-kol-update') }}",
                    data: formData,
                    success: function(data){
                        setTimeout(function() {
                            $('#datepicker3').datepicker('hide');
                        }, 1500);

                        if (Object.keys(data).length > 0) {
                            con_bonus_l1.val(data.con_bonus_l1);
                            number_orders_l1.val(data.number_orders_l1);
                            number_users_l1.val(data.number_users_l1);
                            number_shops_l1.val(data.number_shops_l1);
                            avg_amount_order_l1.val(data.avg_amount_order_l1);
                            number_affiliate_member_l1.val(data.number_affiliate_member_l1);
                            revenue_l1.val(data.revenue_l1);
                            con_bonus_l2.val(data.con_bonus_l2);
                            number_orders_l2.val(data.number_orders_l2);
                            number_users_l2.val(data.number_users_l2);
                            number_shops_l2.val(data.number_shops_l2);
                            avg_amount_order_l2.val(data.avg_amount_order_l2);
                            number_affiliate_member_l2.val(data.number_affiliate_member_l2);
                            revenue_l2.val(data.revenue_l2);
                            kol_date.val(data.kol_date);
                            kol_id.val(data.id);
                            $msg = 'Data Updated Successfully.';

                            $('#validation-errors').html('');
                            $('#validation-errors').append('<div class="alert alert-success">Data Updated Successfully.</div');
                        } else {
                            con_bonus_l1.val('');
                            number_orders_l1.val('');
                            number_users_l1.val('');
                            number_shops_l1.val('');
                            avg_amount_order_l1.val('');
                            number_affiliate_member_l1.val('');
                            revenue_l1.val('');
                            con_bonus_l2.val('');
                            number_orders_l2.val('');
                            number_users_l2.val('');
                            number_shops_l2.val('');
                            avg_amount_order_l2.val('');
                            number_affiliate_member_l2.val('');
                            revenue_l2.val('');
                            kol_date.val(data.kol_date);
                            kol_id.val('');
                            $('#validation-errors').html('');
                        }

                        // $('#datepicker3 .ui-datepicker-calendar').css("display","none");
                        // $(".ui-datepicker-calendar").hide();
                    }

                });
                setTimeout(function() {
                    $('#datepicker3').datepicker('hide');
                }, 1500);

            });

            // var dates =  $( "#from,#to" ).datepicker({
            //     defaultDate: "+0w",
            //     changeMonth: true,
            //     changeYear: true,
            //     //minDate: dateToday,
            //     onSelect: function(selectedDate) {
            //     var option =
            //         instance = $(this).data("datepicker"),
            //         date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
            //         dates.not(this).datepicker("option", option, date);
            //     }
            // });

            $('#datepicker3').datepicker(
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

                        $.ajax({
                            type:"GET",
                            url:"{{ route('admin-get-kol','') }}"+"/"+d,
                            success:function(data) {
                                $('#validation-errors').html('');
                                if (Object.keys(data).length > 0) {
                                    con_bonus_l1.val(data.con_bonus_l1);
                                    number_orders_l1.val(data.number_orders_l1);
                                    number_users_l1.val(data.number_users_l1);
                                    number_shops_l1.val(data.number_shops_l1);
                                    avg_amount_order_l1.val(data.avg_amount_order_l1);
                                    number_affiliate_member_l1.val(data.number_affiliate_member_l1);

                                    con_bonus_l2.val(data.con_bonus_l2);
                                    number_orders_l2.val(data.number_orders_l2);
                                    number_users_l2.val(data.number_users_l2);
                                    number_shops_l2.val(data.number_shops_l2);
                                    avg_amount_order_l2.val(data.avg_amount_order_l2);
                                    number_affiliate_member_l2.val(data.number_affiliate_member_l2);
                                    revenue_l1.val(data.revenue_l1);
                                    revenue_l2.val(data.revenue_l2);
                                    kol_date.val(d);
                                    kol_id.val(data.id);
                                } else {
                                    con_bonus_l1.val('');
                                    number_orders_l1.val('');
                                    number_users_l1.val('');
                                    number_shops_l1.val('');
                                    avg_amount_order_l1.val('');
                                    number_affiliate_member_l1.val('');
                                    con_bonus_l2.val('');
                                    number_orders_l2.val('');
                                    number_users_l2.val('');
                                    number_shops_l2.val('');
                                    avg_amount_order_l2.val('');
                                    number_affiliate_member_l2.val('');
                                    revenue_l1.val('');
                                    revenue_l2.val('');
                                    kol_date.val(d);
                                    kol_id.val('');
                                }
                            },
                            error: function (data) {
                                $('#validation-errors').html('');
                                $('#validation-errors').append('<div class="alert alert-danger">'+data.responseJSON.errors+'</div');
                                con_bonus_l1.val('');
                                number_orders_l1.val('');
                                number_users_l1.val('');
                                number_shops_l1.val('');
                                avg_amount_order_l1.val('');
                                number_affiliate_member_l1.val('');
                                con_bonus_l2.val('');
                                number_orders_l2.val('');
                                number_users_l2.val('');
                                number_shops_l2.val('');
                                avg_amount_order_l2.val('');
                                number_affiliate_member_l2.val('');
                                revenue_l1.val('');
                                revenue_l2.val('');
                                kol_date.val(d);
                                kol_id.val('');
                            },
                        });
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

           $("#datepicker3").datepicker("setDate", new Date());



            // $('#datepicker3').datepicker( {
            //     dateFormat: "yy",
            //     yearRange: "c-100:c",
            //     changeMonth: false,
            //     changeYear: true,
            //     showButtonPanel: false,
            //     closeText:'Select',
            //     currentText: 'This year',
            //     onClose: function(dateText, inst) {
            //     var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            //     $(this).val($.datepicker.formatDate('yy', new Date(year, 1, 1)));
            //     },
            //     onChangeMonthYear : function () {
            //         alert('aaaa')
            //     $(this).datepicker( "hide" );
            //     }
            // }).focus(function () {
            //         $(".ui-datepicker-month").hide();
            //         $(".ui-datepicker-calendar").hide();
            //         $(".ui-datepicker-current").hide();
            //         $(".ui-datepicker-prev").hide();
            //         $(".ui-datepicker-next").hide();
            //         $("#ui-datepicker-div").position({
            //     my: "left top",
            //     at: "left bottom",
            //     of: $(this)
            //     });
            // }).attr("readonly", false);



// --------------------

            // $('.date-picker-year').datepicker({


            //     changeYear: true,
            //     showButtonPanel: true,
            //     dateFormat: 'yy',
            //     onClose: function(dateText, inst) {
            //         var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
            //         $(this).datepicker('setDate', new Date(year, 1));
            //     }
            // });
            // $(".date-picker-year").focus(function () {
            //         $(".ui-datepicker-month").hide();
            //     });

        // var dp= $("#aaa").datepicker({
        //     changeYear: true,
        //     showButtonPanel: true,
        //     dateFormat: 'yyyy',
        //     // viewMode: "years",
        //     // minViewMode: "years",
        //     // autoclose:true
        // });

        // dp.on('changeYear', function (e) {
        //     //do something here
        //     alert("Year changed ");
        // });



});

        // var dateToday = new Date();
        // var dates =  $( "#kol-consumer-from-date,#kol-affiliate-from-date" ).datepicker({
        //     defaultDate: "+0w",
        //     changeMonth: true,
        //     changeYear: true,
        //     dateFormat: 'yy-mm-dd'

            //minDate: dateToday,
            // onSelect: function(selectedDate) {
            // var option =
            //     instance = $(this).data("datepicker"),
            //     date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
            //     dates.not(this).datepicker("option", option, date);
            // }
        // });

    </script>

{{-- DATA TABLE --}}

@endsection

