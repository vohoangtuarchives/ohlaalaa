@extends('layouts.front')
@section('content')

<section class="user-dashbord">
    <div class="container">
      <div class="row">
        @include('includes.user-dashboard-sidebar')
<div class="col-lg-8">
                    <div class="user-profile-details">
                        <div class="account-info">
                            <div class="header-area">
                                <h4 class="title">
                                    {{ $langg->lang828 }}
                                </h4>
                            </div>
                            <div class="edit-info-area">

                                <div class="body">
                                        <div class="edit-info-area-form">
                                                <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                                                <form  id="userform1" action="{{route('user-sending-sp-global')}}" method="POST">

                                                    {{ csrf_field() }}
                                                    @include('includes.form-success')
                                                    <div class="row">
                                                        <div class="col-lg-4 text-right pt-2 f-14">
                                                            <label>{{ $langg->lang266 }}:</label>
                                                            <br>
                                                            <small>{{ $langg->lang829 }}</small>
                                                        </div>
                                                        <div class="col-lg-8 pt-2">
                                                             <input id="phone-number" readonly class="input-field" name="phone_number" value="{{ $user->phone }}">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-4 text-right pt-2 f-14">
                                                            <label>Email:</label>
                                                            <br>
                                                            <small></small>
                                                        </div>
                                                        <div class="col-lg-8 pt-2">
                                                             <input id="email" class="input-field" name="email" value="{{ $user->email }}" readonly>
                                                             <button type="button" class="mybtn1" id="btnCheckEmail">{{ $langg->lang36 }}</button>
                                                        </div>
                                                    </div>
                                                    <div id="shopping-point-area" class="d-none">
                                                        <div class="row">
                                                            <div class="col-lg-4 text-right pt-2 f-14">
                                                                <label>{{ $langg->lang832 }}:</label>
                                                                <br>
                                                                <small>{{ $langg->lang833 }}</small>
                                                            </div>
                                                            <div class="col-lg-8 pt-2">
                                                                <input type="text" id="shopping-point" class="input-field" name="shopping_point" data-val="{{ $user->shopping_point }}" readonly="" value="{{ number_format($user->shopping_point) }}">

                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 text-right pt-2 f-14">
                                                                <label>{{ $langg->lang834 }}:</label>
                                                                <br>
                                                                <small>{{ $langg->lang835 }}</small>
                                                            </div>
                                                            <div class="col-lg-8 pt-2">
                                                                <input type="number" id="sending-shopping-point" class="input-field" name="sending_shopping_point" value="0">
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 text-right pt-2 f-14">
                                                                <label>{{ $langg->lang836 }}:</label>
                                                                <br>
                                                                <small>{{ $langg->lang837 }}</small>
                                                            </div>
                                                            <div class="col-lg-8 pt-2">
                                                                <input type="text" id="shopping-point-diff" class="input-field" name="shopping_point_diff" readonly value="{{ number_format($user->shopping_point) }}">

                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-lg-4 text-right pt-2 f-14">
                                                            </div>
                                                            <div class="col-lg-8  pt-2">
                                                                <button type="button" class="mybtn1 lg" id="btnSendSP" data-toggle="modal" data-target="#confirm-delete">{{ $langg->lang838 }}</button>
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

    {{-- ORDER MODAL --}}

    <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">

          <div class="modal-header d-block text-center">
              <h4 class="modal-title d-inline-block">Thông báo</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
          </div>

                      <div class="modal-body">
                  <p class="text-center">Xác nhận chuyển Shopping Point</p>
                  <p class="text-center">Bạn có xác nhận thực hiện thao tác không?</p>
                      </div>
                      <div class="modal-footer justify-content-center">
                          <button type="button" class="btn btn-default" data-dismiss="modal">{{ $langg->lang260 }}</button>
                          <a class="btn btn-primary btn-ok1" href="javascript:;" data-dismiss="modal">&nbsp;&nbsp;OK&nbsp;&nbsp;</a>
                      </div>
                  </div>
              </div>
          </div>

  {{-- ORDER MODAL ENDS --}}
  </section>

@endsection

@section('scripts')

<script src="{{asset('assets/front/js/htdnew3.js')}}"></script>

@endsection
