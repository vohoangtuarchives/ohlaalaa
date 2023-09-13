
@extends('layouts.load')

@section('content')

            <div class="content-area">

              <div class="add-product-content1">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="product-description">
                      <div class="body-area">
                        @include('includes.admin.form-error')
                      <form id="geniusformdata" action="{{route('admin-order-update-invoice-info',$data->id)}}" method="POST" enctype="multipart/form-data">
                        {{csrf_field()}}

                        <div class="row"><h4>MERCHANT HANDLING FEE</h4></div>
                        @foreach ($order_result as $rs)

                            <div class="row">
                                <div class="col-lg-4">
                                <div class="left-area">
                                    <h4 class="heading">{{ $rs->shop_name }}</h4>
                                </div>
                                </div>
                                <div class="col-lg-7">
                                <span>{{ number_format($rs->merchant_handling_fee) }}</span>

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
                                    <span id="update-by-{{ $rs->shop_id }}"> {{ $rs->is_handlingfee_collected == 1 ? 'by '.$issuer->name.' at '.$log->created_at : '' }}</span>
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
                                <input type="text" class="form-control datepicker" name="payment_to_company_date" placeholder="{{ __('Select a date') }}" value="{{ $data->payment_to_company_date }}" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Partner') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control" name="payment_to_company_partner" id="payment-to-company-partner" value="{{ $data->payment_to_company_partner }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Amount') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" class="form-control" name="payment_to_company_amount" id="payment-to-company-amount" value="{{ $data->payment_to_company_amount }}">
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
                                  <h4 class="heading text-primary">{{ $rs->shop_name }}</h4>
                              </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Date') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="text" class="form-control datepicker" name="payment_to_merchant_date_{{ $rs->shop_id }}" placeholder="{{ __('Select a date') }}" value="{{ $vinfo != null ? $vinfo->payment_to_merchant_date : '' }}" autocomplete="off">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Amount') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-7">
                                <input type="number" class="form-control" name="payment_to_merchant_amount_{{ $rs->shop_id }}" value="{{ $vinfo != null ? $vinfo->payment_to_merchant_amount : '' }}">
                            </div>
                        </div>

                        @endforeach

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

                        <br>
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">

                            </div>
                          </div>
                          <div class="col-lg-7">
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

</script>

@endsection

