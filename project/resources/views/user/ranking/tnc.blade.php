@extends('layouts.front')
@section('content')

<section class="user-dashbord">
    <div class="container">
      <div class="row">
        @include('includes.user-dashboard-sidebar')
<div class="col-lg-8">
                    <div class="user-profile-details">
                        <div class="account-info">
                            {{-- <div class="header-area">
                                <h4 class="title">
                                    T & C
                                </h4>
                            </div> --}}
                            <div class="edit-info-area">

                                <div class="body">
                                        <div class="edit-info-area-form">
                                                <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                                                <form  id="userform1" action="{{route('user-membership-request', $package->id)}}" method="POST">

                                                    {{ csrf_field() }}
                                                    @include('includes.form-success')
                                                    <div class="row">

                                                        <div class="col-lg-12 pt-2">
                                                            <p>{!! $package->tnc !!}</p>
                                                            <hr>
                                                        </div>
                                                    </div>


                                                    <div id="shopping-point-area">
                                                        <div class="row">
                                                            <div class="col-lg-4 text-right pt-2 f-14">

                                                            </div>
                                                            <div class="col-lg-8 pt-2">
                                                                <input class="styled-checkbox" id="tnc" type="checkbox" value="0" name="checked_tnc" >
													            <label for="tnc">Tôi đã đọc và đồng ý với các điều khoản</label>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 text-right pt-2 f-14">
                                                            </div>
                                                            <div class="col-lg-8  pt-2 ">
                                                                <button type="submit" id="send-btn" class="select-bank mybtn1 lg disabled"> <i class="fas fa-paper-plane"></i> Gửi yêu cầu</a></button>
                                                                <!-- <a href="javascript:;" data-href="{{ route('user-member-package-banks') }}" id="send-btn" class="select-bank mybtn1 lg disabled" data-toggle="modal" data-target="#modal1">
                                                                    <i class="fas fa-paper-plane"></i> Gửi yêu cầu</a> -->
                                                                <input type="hidden" class="BankPay" name="payment_bank" value="NCB" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
      </div>
    </div>


  </section>

  {{-- ADD / EDIT MODAL --}}

  <div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
                <div class="submit-loader">
                        <img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
                </div>
            <div class="modal-header">
            <h5 class="modal-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" id="membership-payment-btn" class="mybtn1 lg"><i class="fas fa-money-check-alt"></i> Thanh toán</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>

</div>

{{-- ADD / EDIT MODAL ENDS --}}

@endsection

@section('scripts')


<script src="{{asset('assets/front/js/htdnew2.js')}}"></script>
<script src="{{asset('assets/front/js/htdnew3.js')}}"></script>


@endsection
