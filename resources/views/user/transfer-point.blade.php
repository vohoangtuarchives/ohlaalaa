@extends('layouts.front')
@section('content')


    <section class="user-dashbord">
        <div class="container">
            <div class="row">
                @include('includes.user-dashboard-sidebar')
                <div class="col-lg-9">
                    @include('includes.form-success')
                    <div class="row mb-3">

                        <div class="col-lg-12" >
                            <div class="user-profile-details h100">
                                <div class="account-info wallet h100">
                                    <div class="header-area">
                                        <h4 class="title">
                                           Thông Tin Tài Khoản
                                        </h4>
                                    </div>
                                    <div class="edit-info-area">
                                    </div>
                                    <div class="main-info">
                                        <h3 class="title w-price">Shopping Point: {{ number_format($user->shopping_point) }}</h3>
                                        <p>
                                            Bạn được phép chuyển {{ config("tuezy.monthly_transfer_percents", 0.2)*100 }}% tổng số SP mỗi tháng: {{ number_format(round($user->max_transfer_point)) }}
                                        </p>
                                        <p>
                                            Tháng này bạn đã chuyển đi: {{ number_format($user->transfered_shopping_point) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="user-profile-details">
                                <div class="account-info">
                                    <div class="header-area">
                                        <h4 class="title">
                                            Chuyển điểm SP
                                        </h4>
                                    </div>
                                    <div class="transfer-content">
                                        <form action="" method="POST">
                                            @csrf
                                            <div class="form-group row mt-4">
                                                <div class="w-100">
                                                    <input type="email" class="input-field" id="staticEmail" name="to_customer" placeholder="Email người nhận">
                                                </div>
                                            </div>
                                            <div class="form-group row mt-4">
                                                <div class="w-100">
                                                    <input type="number" class="input-field" id="staticEmail" name="amount" placeholder="Số lượng SP">
                                                </div>
                                            </div>
                                            <div class="form-group row justify-content-end">
                                               <button type="submit" class="btn btn-primary">Chuyển Điểm</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(!empty($transactions))
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="user-profile-details">
                                <div class="account-info">
                                    <div class="header-area">
                                        <h4 class="title">
                                            Lịch sử chuyển điểm
                                        </h4>
                                    </div>
                                    <div class="transfer-content">
                                        <ul class="list-group list-group-flush py-4">
                                            @foreach($transactions as $transaction)
                                                <li class="list-group-item">{{ $transaction->user_name }}: {{$transaction->content}}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>


    </section>

    {{-- ADD / EDIT MODAL --}}

    <div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                {{-- <div class="submit-loader">
                    <img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
                </div> --}}
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6 class="heading">Gói thành viên của bạn chỉ còn lại <span class="text-danger package-remain-day"></span> ngày</h6>
                    <hr>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="left-area">
                                <h6 class="heading">Gói</h6>
                                {{-- <p class="sub-heading">(In Any Language)</p> --}}
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <span class="text-danger"> {{ $user->rank_name() }} </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="left-area">
                                <h6 class="heading">Ngày bắt đầu</h6>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <span> {{ $user->getRankingStartDate() }} </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="left-area">
                                <h6 class="heading">Ngày hết hạn</h6>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <span class="text-danger"> {{ $user->getRankingEndDate() }} </span>
                        </div>
                    </div>
                    <hr>
                    <h6 class="heading">Hãy tiến hành gia hạn để không bỏ lỡ những tính năng mới nhất trên <span class="text-primary">Ohlaalaa.com</span> nhé!</h6>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('user-member-package') }}" id="send-btn" class="mybtn1 lg">
                        <i class="fas fa-paper-plane"></i> Gia hạn</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>

    </div>

    {{-- ADD / EDIT MODAL ENDS --}}

@endsection

@section('scripts')

    <script src="{{asset('resources/front/js/dashboard.js')}}"></script>

@endsection
