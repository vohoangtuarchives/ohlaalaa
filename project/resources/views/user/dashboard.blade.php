@extends('layouts.front')
@section('content')


<section class="user-dashbord">
    <div class="container">
      <div class="row">
        @include('includes.user-dashboard-sidebar')
        <div class="col-lg-8">
          @include('includes.form-success')
          <div class="row mb-3">
            <div class="col-lg-6">
              <div class="user-profile-details">
                <div class="account-info">
                  <div class="header-area">
                    <h4 class="title">
                      {{ $langg->lang208 }}

                    </h4>
                  </div>
                  <div class="edit-info-area">
                  </div>
                  <div class="main-info">
                    <h5 class="title">{{ $user->name }} | {{ $user->rank_name() }}</h5>
                    <ul class="list">
                      <li>
                        <p><span class="user-title">{{ $langg->lang209 }}:</span> {{ $user->email }}</p>
                      </li>
                      @if($user->phone != null)
                      <li>
                        <p><span class="user-title">{{ $langg->lang210 }}:</span> {{ $user->phone }}</p>
                      </li>
                      @endif
                      <li>
                        <p><span class="user-title">{{ $langg->lang214 }}:</span> {{ $user->address }}{{ isset($user->province) ? ', '.$user->province->name : '' }}</p>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6" >
                <div class="user-profile-details h100">
                  <div class="account-info wallet h100">
                    <div class="header-area">
                      <h4 class="title">
                        {{ $langg->lang812 }}
                      </h4>
                    </div>
                    <div class="edit-info-area">
                    </div>
                    <div class="main-info">
                      <h3 class="title w-title">Reward Point: {{ number_format($user->reward_point) }}</h3>
                      <h3 class="title w-price">Shopping Point: {{ number_format($user->shopping_point) }}</h3>
                    </div>
                  </div>
                </div>
              </div>
        </div>

        <div class="row row-cards-one mb-3">
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
      </div>


        <div class="row">
        <div class="col-lg-12">
          <div class="user-profile-details">
            <div class="account-info wallet">
              <div class="header-area">
                <h4 class="title">
                  {{ isset($langg->lang808) ? $langg->lang808 : 'Recent Orders' }}
                </h4>
              </div>
              <div class="edit-info-area">
              </div>
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
                            <tbody>
                                @php

                                    // 'declined' 'completed' 'pending' 'on delivery'
                                    $st = ['completed'=>'Hoàn Thành', 'declined'=>'Từ Chối', 'pending'=>'chờ xử lý'];
                                @endphp
                                    @foreach(Auth::user()->orders()->latest()->take(5)->get() as $order)
                                <tr>
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
                                            {{ucwords(isset($st[$order->status]) ? $st[$order->status] : 'Đang Giao Hàng')}}
                                            {{-- {{ ucwords($order->status) }} --}}
                                        </div>
                                    </td>
                                    <td>
                                        <a class="mybtn2 sm" href="{{route('user-order',$order->id)}}">
                                                {{ $langg->lang283 }}
                                        </a>
                                    </td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>
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
