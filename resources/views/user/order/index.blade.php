@extends('layouts.front')
@section('content')


<section class="user-dashbord">
    <div class="container">
      <div class="row">
        @include('includes.user-dashboard-sidebar')
        <div class="col-lg-8">
					<div class="user-profile-details">
						<div class="order-history">
							<div class="header-area">
								<h4 class="title">
									{{ $langg->lang277 }}
								</h4>
							</div>
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
													 @foreach($orders as $order)
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
																	{{ucwords($order->status)}}
															</div>
														</td>
														<td>
															<a class="mybtn2 sm" href="{{route('user-order',$order->id)}}">
																	{{ $langg->lang283 }}
															</a>
                                                            @if ($order->shipping_type == 'negotiate' && $order->status != 'declined')
                                                                @if ($order->customer_received)
                                                                <span class='badge badge-primary'>{{ $langg->lang909 }}</span>
                                                                @else
                                                                <a class="mybtn2 sm order-received-btn" href="javascript:;" data-href="{{route('user-order-received',$order->id)}}">
                                                                    {{ $langg->lang909 }}
                                                                </a>
                                                                @endif
                                                            @endif

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
	</section>
@endsection

@section('scripts')

<script src="{{asset('assets/front/js/htdnew3.js')}}"></script>


@endsection
