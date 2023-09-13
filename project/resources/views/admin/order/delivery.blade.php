@extends('layouts.load')




@section('content')

            <div class="content-area">

              <div class="add-product-content1">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="product-description">
                      <div class="body-area">
                        @include('includes.admin.form-error')
                      <form id="geniusformdata" action="{{route('admin-order-update',$data->id)}}" method="POST" enctype="multipart/form-data">
                        {{csrf_field()}}

                        {{-- ///////////////////////////////////////////////////// --}}
                        <div class="row"><h4>VENDOR STATUS</h4></div>
                        @foreach ($order_result as $rs)
                        <div class="row">
                            <div class="col-lg-4">
                            <div class="left-area">
                                <h4 class="heading">{{ $rs->shop_name }}</h4>
                            </div>
                            </div>
                            <div class="col-lg-7">
                                @if($data->dp == 1 && $data->payment_status == 'Completed')
                                    <span class="badge badge-success">{{ __('Completed') }}</span>
                                @else
                                    @php $user = App\Models\VendorOrder::where('order_id','=',$rs->order_id)->where('user_id','=',$rs->shop_id)->first(); @endphp
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
                            </div>
                        </div>
                        @endforeach
                        {{-- /////////////////////////////////////////////////// --}}


                        <div class="row"><h4>ORDER STATUS</h4></div>

                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Payment Status') }} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-7">
                              <select name="payment_status" required="">
                                <option value="Pending" {{$data->payment_status == 'Pending' ? "selected":""}}>{{ __('Unpaid') }}</option>
                                <option value="Completed" {{$data->payment_status == 'Completed' ? "selected":""}}>{{ __('Paid') }}</option>
                              </select>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Delivery Status') }} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-7">
                              <select name="status" required="">
                                <option value="pending" {{ $data->status == "pending" ? "selected":"" }}>{{ __('Pending') }}</option>
                                <option value="processing" {{ $data->status == "processing" ? "selected":"" }}>{{ __('Processing') }}</option>
                                <option value="on delivery" {{ $data->status == "on delivery" ? "selected":"" }}>{{ __('On Delivery') }}</option>
                                <option value="completed" {{ $data->status == "completed" ? "selected":"" }}>{{ __('Completed') }}</option>
                                <option value="declined" {{ $data->status == "declined" ? "selected":"" }}>{{ __('Declined') }}</option>
                              </select>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Track Note') }} *</h4>
                                <p class="sub-heading">{{ __('(In Any Language)') }}</p>
                            </div>
                          </div>
                          <div class="col-lg-7">
                            <textarea required="" class="input-field" name="track_text" placeholder="{{ __('Enter Track Note Here') }}"></textarea>
                          </div>
                        </div>

                        <div class="row"><h4>MERCHANT HANDLING FEE</h4></div>
                        @foreach ($order_result as $rs)

                            <div class="row">
                                <div class="col-lg-4">
                                <div class="left-area">
                                    <h4 class="heading">{{ $rs->shop_name }}</h4>
                                </div>
                                </div>
                                <div class="col-lg-7">
                                <span>{{ number_format($rs->merchant_handling_fee) }} ({{ $rs->handling_fee_value }}%)</span>

                                <div class="uncollect-fee {{ $rs->is_handlingfee_collected == 1 ? 'd-none' : '' }}" id="uncollect-fee-{{ $rs->shop_id }}">
                                    <span class='badge badge-danger'>Uncollect</span>
                                    <a class="add-btn btn-collect-fee" data-val="{{ $rs->shop_id }}" data-href="{{ route('admin-order-collect-handling-fee-new',[$rs->order_id, $rs->shop_id]) }}" >
                                        <i class="fas fa-check"></i> Collect Fee </a>
                                </div>

                                <div class="collected-fee {{ $rs->is_handlingfee_collected == 1 ? '' : 'd-none' }}" id="collected-fee-{{ $rs->shop_id }}">
                                    <span class='badge badge-success'>Collected</span>
                                    @php
                                        $log = $data->handlingfeelogs()->where('shop_id','=', $rs->shop_id)->first();
                                        if ($log != null) {
                                            $issuer = $log->issuer()->first();
                                        }
                                    @endphp
                                    <span id="update-by-{{ $rs->shop_id }}"> {{ $rs->is_handlingfee_collected == 1 ? 'by '.(isset($issuer) ? $issuer->name : ' ').' at '.(isset($log) ? $log->created_at : ' ') : '' }}</span>
                                </div>
                                </div>
                            </div>

                        @endforeach

                        <div class="row"><h4>PAYMENT TO TECHHUB</h4></div>
                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Date') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control datepicker" name="payment_to_company_date" placeholder="{{ __('Select a date') }}" value="{{ $data->payment_to_company_date != null ? $data->payment_to_company_date : $todate }}" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Partner') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control" name="payment_to_company_partner" id="payment-to-company-partner" value="{{ $data->payment_to_company_partner != '' ? $data->payment_to_company_partner : $order_result->first()->_21_PAYMENT_TO_TECHHUB_PARTNER }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Amount') }}</h4>
                              </div>
                            </div>

                            <div class="col-lg-7">
                               <!-- <input type="number" step="any" class="form-control" name="payment_to_company_amount" id="payment-to-company-amount" value="{{ $data->payment_to_company_amount > 0 ? $data->payment_to_company_amount : $order_result->sum('_19_Amount_Partner_Must_Pay') }}"> -->
                               <!-- <input type="number" step="any" class="form-control" name="payment_to_company_amount" id="payment-to-company-amount" value="{{ ($data->payment_to_company_amount > 0) ? $data->payment_to_company_amount : (($data->method=='VNPay') ? $order_result->sum('_19_Amount_Partner_Must_Pay') : 0)}}"> -->
                               <input type="number" step="any" class="form-control" name="payment_to_company_amount" id="payment-to-company-amount" value="{{ ($data->payment_to_company_amount > 0) ? $data->payment_to_company_amount : (($data->method=='VNPay' || $data->method=='Onepay') ? $order_result->sum('_19_Amount_Partner_Must_Pay') : 0)}}">

			        <a class="add-btn btn-refresh-19">
                                    <i class="fas fa-sync-alt"></i>
                                     Refresh </a>
                                <input type="hidden" class="form-control" id="_19_Amount_Partner_Must_Pay" value="{{ $order_result->sum('_19_Amount_Partner_Must_Pay') }}">
                            </div>
                        </div>

                        <div class="row"><h4>PAYMENT TO  MERCHANT</h4></div>
                        @foreach ($order_result as $rs)
                        @php
                            $vinfo = $data->ordervendorinfos()->where('shop_id','=',$rs->shop_id)->first();
                        @endphp
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="left-area">
                                    <h4 class="heading text-primary">{{ $rs->shop_name }}

                                    </h4>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                @if ($rs->preferred)
                                <span class='badge badge-success'>Preferred</span>
                                @else
                                <span class='badge badge-danger'>NonPreferred</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="left-area">
                                    <h4 class="heading">{{ __('Status') }}</h4>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <select name="is_paid_{{ $rs->shop_id }}" required="">
                                    <option value="0" {{ $vinfo != null && $vinfo->is_paid == 0 ? "selected":""}}>{{ __('Unpaid') }}</option>
                                    <option value="1" {{ $vinfo != null && $vinfo->is_paid == 1 ? "selected":""}}>{{ __('Paid') }}</option>
                                  </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="left-area">
                                    <h4 class="heading">{{ __('Date') }}</h4>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control datepicker" name="payment_to_merchant_date_{{ $rs->shop_id }}" placeholder="{{ __('Select a date') }}" value="{{ $vinfo != null && $vinfo->payment_to_merchant_date != null ? $vinfo->payment_to_merchant_date : $todate }}" autocomplete="off">
                            </div>
                        </div>
@php
// dd($vinfo);
// if ('123.20.60.171' == getenv('REMOTE_ADDR')) {
    
//     $vinfotx = $data->ordervendorinfos()->first();
//     dd($rs->shop_id,$vinfotx);
// }
@endphp
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="left-area">
                                    <h4 class="heading">{{ __('Amount') }}</h4>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" step="any" class="form-control" name="payment_to_merchant_amount_{{ $rs->shop_id }}" value="{{ $vinfo != null ? ($vinfo->payment_to_merchant_amount > 0 ? $vinfo->payment_to_merchant_amount : $rs->_24_Amount_Must_Pay_to_Merchant) : $rs->_24_Amount_Must_Pay_to_Merchant }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Merchant In Debt') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="action-list">
                                    <select class="is-debt process select {{ $vinfo != null && $vinfo->is_debt == 1 ? 'drop-success' : 'drop-danger' }}" id="is-debt-{{ $rs->shop_id }}" name="is_debt_{{ $rs->shop_id }}" data-val="{{ $rs->shop_id }}">
                                      <option data-val="1" value="1" {{ $vinfo != null && $vinfo->is_debt == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                      <option data-val="0" value="0" {{ $vinfo == null || $vinfo->is_debt == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row {{ $vinfo != null && $vinfo->is_debt == 1 ? '' : 'd-none' }} " id="row-debt-amount-{{ $rs->shop_id }}">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Debt Amount') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" step="any" class="form-control" name="debt_amount_{{ $rs->shop_id }}" id="debt-amount-{{ $rs->shop_id }}" value="{{ $vinfo != null ? $vinfo->debt_amount : '' }}">
                            </div>
                        </div>

                        @endforeach

                        <div class="row"><h4>REFUND DETAIL</h4></div>
                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Date') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control datepicker" name="refund_date" id="refund-date" placeholder="{{ __('Select a date') }}" value="{{ $data->refund_date != null ? $data->refund_date : $todate }}" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Bank') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control" name="refund_bank" id="refund-bank" value="{{ $data->refund_bank }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Amount') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" step="any" class="form-control" name="refund_amount" id="refund-amount" value="{{ $data->refund_amount }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Refund Note') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <textarea class="input-field" name="refund_note" id="refund-note" placeholder="{{ __('Enter Refund Note Here') }}">{{ $data->refund_note }}</textarea>
                            </div>
                        </div>

                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">

                            </div>
                          </div>
                          <div class="col-lg-7">
                            <button class="addProductSubmit-btn" type="submit">{{ __('Save') }}</button>
                          </div>
                        </div>

                        @php
                            $logs = $data->admin_tracks()->orderByDesc('id')->get();
                        @endphp
                        @if ($logs->count() > 0)
                        <div class="row"><h4>LOG CHANGE</h4></div>
                        @endif
                        @foreach ($logs as $log)
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="left-area">
                                    <p>{{ $log->title }}</p>
                                    <p>{{ $log->created_at }}</p>
                                    <p>by {{ $log->issuer()->first()->name }}</p>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <p>{!! $log->content !!}</p>
                            </div>
                        </div>
                        @endforeach

                      </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

@endsection

@section('scripts')


<script type="text/javascript">

    $(".btn-collect-fee" ).on('click' , function(e){
        var url = $(this).data('href');
        var shop_id = $(this).data('val');
        $.ajax({
            url: url,
            type: "POST",
            data: {
                "_token": "{{ csrf_token() }}"
            },
            success: function (data) {
                $('#collected-fee-' + shop_id).removeClass('d-none');
                $('#uncollect-fee-' + shop_id).addClass('d-none');
                $('#update-by-' + shop_id).text('by ' + data['issuer']['name'] + ' at ' + data['created_at']);
            }
        });

    });

    $(".btn-refresh-19" ).on('click' , function(e){
        $('#payment-to-company-amount').val($('#_19_Amount_Partner_Must_Pay').val());
        check19Changed();
    });

    $(".is-debt" ).on('change' , function(e){
        var shop_id = $(this).data('val');
        $('#is-debt-' + shop_id).removeClass('drop-success');
        $('#is-debt-' + shop_id).removeClass('drop-danger');
        var v = $(this).val();
        if(v == 1){
            $('#is-debt-' + shop_id).addClass('drop-success');
            $('#row-debt-amount-' + shop_id).removeClass('d-none');
        }
        else{
            $('#is-debt-' + shop_id).addClass('drop-danger');
            $('#row-debt-amount-' + shop_id).addClass('d-none');
        }
    });

    $("#payment-to-company-amount" ).on('change' , function(e){
        check19Changed();
    });

    function check19Changed(){
        var value = $('#payment-to-company-amount').val();
        var _19cal = $('#_19_Amount_Partner_Must_Pay').val();
        if(value != _19cal){
            $('#refund-bank').prop('required',true);
            $('#refund-amount').prop('required',true);
            $('#refund-date').prop('required',true);
            $('#refund-note').prop('required',true);
        }
        else{
            $('#refund-bank').prop('required',false);
            $('#refund-amount').prop('required',false);
            $('#refund-date').prop('required',false);
            $('#refund-note').prop('required',false);
        }
    }

    check19Changed();

</script>


@endsection

