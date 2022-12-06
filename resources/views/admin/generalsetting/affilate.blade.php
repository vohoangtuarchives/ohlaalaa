@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">

@endsection

@section('content')

<div class="content-area">
              <div class="mr-breadcrumb">
                <div class="row">
                  <div class="col-lg-12">
                      <h4 class="heading">{{ __('Affialte Informations') }}</h4>
                    <ul class="links">
                      <li>
                        <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                      </li>
                      <li>
                        <a href="javascript:;">{{ __('General Settings') }}</a>
                      </li>
                      <li>
                        <a href="{{ route('admin-gs-affilate') }}">{{ __('Affialte Informations') }}</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="add-product-content1">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="product-description">
                      <div class="body-area">
                        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                        <form action="{{ route('admin-gs-update') }}" id="geniusform" method="POST" enctype="multipart/form-data">
                          {{ csrf_field() }}

                        @include('includes.admin.form-both')

                        {{-- <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                <h4 class="heading">
                                    {{ __('Affilate Service') }}
                                </h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="action-list">
                                    <select class="process select droplinks {{ $gs->is_affilate == 1 ? 'drop-success' : 'drop-danger' }}">
                                      <option data-val="1" value="{{route('admin-gs-isaffilate',1)}}" {{ $gs->is_affilate == 1 ? 'selected' : '' }}>{{ __('Activated') }}</option>
                                      <option data-val="0" value="{{route('admin-gs-isaffilate',0)}}" {{ $gs->is_affilate == 0 ? 'selected' : '' }}>{{ __('Deactivated') }}</option>
                                    </select>
                                  </div>
                            </div>
                          </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Affilate Bonus(%)') }}</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{ __('Write Your Site Title Here') }}" name="affilate_charge" value="{{ $gs->affilate_charge }}" required="">
                          </div>
                        </div> --}}

                        {{-- <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Rebate Bonus (%)') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                              <input type="number" step="0.1" class="input-field" placeholder="rebate bonus" name="rebate_bonus" value="{{ $gs->rebate_bonus }}" required="">
                            </div>
                          </div> --}}

                          <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                <h4 class="heading">
                                    {{ __('Rebate Bonus In') }}
                                </h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="action-list">
                                    <select class="process select drop-warning" name="rebate_in">
                                        <option data-val="0" value="0" {{ $gs->rebate_in == 0 ? 'selected' : '' }}>Reward Point</option>
                                        <option data-val="1" value="1" {{ $gs->rebate_in == 1 ? 'selected' : '' }}>Shopping Point</option>
                                    </select>
                                  </div>
                            </div>
                          </div>

                          <div class="row justify-content-center" hidden>
                            <div class="col-lg-3">
                              <div class="left-area">
                                <h4 class="heading">
                                    Loại thanh toán
                                </h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="action-list">
                                    <select class="process select drop-warning" name="rebate_payment_in">
                                        <option data-val="0" value="0" {{ $gs->rebate_payment_in == 0 ? 'selected' : '' }}>Online</option>
                                        <option data-val="1" value="1" {{ $gs->rebate_payment_in == 1 ? 'selected' : '' }}>Khi ĐH hoàn thành</option>
                                    </select>
                                  </div>
                            </div>
                          </div>

                          <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Daily Convert Rate (%)') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                              <input type="number" step="0.01" class="input-field" placeholder="daily sp exchange rate" name="daily_sp_exchange_rate" value="{{ $gs->daily_sp_exchange_rate }}" required="">
                            </div>
                          </div>

                          <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                <h4 class="heading">
                                    {{ __('Affiliate Bonus In') }}
                                </h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="action-list">
                                    <select class="process select drop-warning" name="affiliate_exchange_in">
                                        <option data-val="0" value="0" {{ $gs->affiliate_exchange_in == 0 ? 'selected' : '' }}>Reward Point</option>
                                        <option data-val="1" value="1" {{ $gs->affiliate_exchange_in == 1 ? 'selected' : '' }}>Shopping Point</option>
                                    </select>
                                  </div>
                            </div>
                          </div>

                          <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Shopping Point Exchange Rate') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" step="0.1" class="input-field" placeholder="sp-vnd exchange rate" name="sp_vnd_exchange_rate" value="{{ $gs->sp_vnd_exchange_rate }}" required="">
                            </div>
                          </div>

                          <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('Merchant Sale Bonus Rate (%)') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" step="0.1" class="input-field" placeholder="% merchant sale bonus" name="merchant_sale_bonus" value="{{ $gs->merchant_sale_bonus }}" required="">
                            </div>
                          </div>

                          <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                <h4 class="heading">
                                    {{ __('Merchant Sale Bonus In (%)') }}
                                </h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="action-list">
                                    <select class="process select drop-warning" name="merchant_sale_bonus_in">
                                        <option data-val="0" value="0" {{ $gs->merchant_sale_bonus_in == 0 ? 'selected' : '' }}>Reward Point</option>
                                        <option data-val="1" value="1" {{ $gs->merchant_sale_bonus_in == 1 ? 'selected' : '' }}>Shopping Point</option>
                                    </select>
                                  </div>
                            </div>
                          </div>

                        {{-- <div class="row justify-content-center">
                            <div class="col-lg-3">
                                <div class="left-area">
                                    <h4 class="heading">{{ __('Current Featured Image') }} *</h4>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                        <div class="img-upload">
                                            <div id="image-preview" class="img-preview" style="background: url({{ $gs->affilate_banner ? asset('assets/images/'.$gs->affilate_banner):asset('assets/images/noimage.png') }});">
                                                <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __('Upload Image') }}</label>
                                                <input type="file" name="affilate_banner" class="img-upload">
                                              </div>
                                        </div>

                            </div>
                        </div> --}}

                        <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('KOL Consumer Bonus Rate (%)') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" step="Any" class="input-field" placeholder="% kol consumer bonus" name="kol_con_bonus" value="{{ $gs->kol_con_bonus }}" required="">
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('KOL Consumer From Date') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="input-field" name="kol_con_from" id="kol-consumer-from-date" placeholder="{{ __('Select a date') }}"  value="{{ $gs->kol_con_from }}" autocomplete="off">
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('KOL Affiliate Bonus Rate (%)') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" step="Any" class="input-field" placeholder="% kol affiliate bonus" name="kol_aff_bonus" value="{{ $gs->kol_aff_bonus }}" required="">
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('KOL Affiliate From Date') }}</h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="input-field" name="kol_aff_from" id="kol-affiliate-from-date" placeholder="{{ __('Select a date') }}"  value="{{ $gs->kol_aff_from }}" autocomplete="off">
                            </div>
                        </div>


                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">

                            </div>
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

@endsection

@section('scripts')

{{-- DATA TABLE --}}

    <script type="text/javascript">

        var dateToday = new Date();
        var dates =  $( "#kol-consumer-from-date,#kol-affiliate-from-date" ).datepicker({
            defaultDate: "+0w",
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd'
            //minDate: dateToday,
            // onSelect: function(selectedDate) {
            // var option =
            //     instance = $(this).data("datepicker"),
            //     date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
            //     dates.not(this).datepicker("option", option, date);
            // }
        });

    </script>

{{-- DATA TABLE --}}

@endsection

