@extends('layouts.front')




@section('content')

<section class="tempcart">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Starting of Dashboard data-table area -->
                    <div class="content-box section-padding add-product-1">
                        <div class="top-area">
                                <div class="content">
                                    <h4 class="heading">
                                        XIN CẢM ƠN
                                    </h4>
                                    <p class="text">
                                        Việc thanh toán nâng hạng thành viên đã hoàn tất. Yêu cầu nâng hạng sẽ được kiểm tra và phê duyệt.
                                    </p>
                                    <a href="{{ route('front.index') }}" class="link">Trở về trang chủ</a>
                                  </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">

                                    <div class="product__header">
                                        <div class="row reorder-xs">
                                            <div class="col-lg-12">
                                                <div class="product-header-title">
                                                    <h2>Mã# {{$register->payment_number}}</h2>
                                                    <input type="hidden" id="order-number" value="{{$register->payment_number}}">

                                        </div>
                                    </div>
                                        @include('includes.form-success')
                                            <div class="col-md-12" id="tempview">
                                                <div class="dashboard-content">
                                                    <div class="view-order-page" id="print">
                                                        <p class="order-date">Ngày yêu cầu {{ date('d-M-Y',strtotime($register->created_at)) }}</p>

                                                        <div class="billing-add-area">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h5>Thông tin khách hàng</h5>
                                                                    <address>
                                                                        {{ $langg->lang288 }} {{ $register->user->name }}<br>
                                                                        {{ $langg->lang289 }} {{ $register->user->email }}<br>
                                                                        {{ $langg->lang290 }} {{ $register->user->phone }}<br>
                                                                        {{ $langg->lang291 }} {{ $register->user->address }}, {{ $register->user->province->name }}<br>
                                                                    </address>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h5>Thông tin thanh toán</h5>
                                                                    <p>Gói tài khoản: {{ $register->package_config->name }}</p>
                                                                    <p>Số tiền: {{ number_format($register->package_price) }}</p>
                                                                    <p>Ngân hàng: {{ $register->payment_bank }}</p>
                                                                    <p>Ngày thanh toán: {{ isset($register->payment_complete_at) ? date('d-M-Y',strtotime($register->payment_complete_at)) : '' }} </p>
                                                                    <p>Tình trạng: {!! $register->payment_status == 'Pending' ? "<span id='payment-status' class='badge badge-danger'>Unpaid</span>":"<span id='payment-status' class='badge badge-success'>Paid</span>" !!}</p>

                                                                </div>
                                                            </div>
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
            </div>
        </div>

  </section>

@endsection

@section('scripts')

@endsection
