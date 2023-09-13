@extends('layouts.load')
@section('content')

						<div class="content-area">
							<div class="add-product-content1">
								<div class="row">
									<div class="col-lg-12">
										<div class="product-description">
											<div class="body-area">
                        					@include('includes.admin.form-error')
											<form id="geniusformdata" action="{{ route('admin-user-edit',$data->id) }}" method="POST" enctype="multipart/form-data">
												{{csrf_field()}}

						                        <div class="row">
						                          <div class="col-lg-4">
						                            <div class="left-area">
						                                <h4 class="heading">{{ __("Customer Profile Image") }} *</h4>
						                            </div>
						                          </div>
						                          <div class="col-lg-7">
						                            <div class="img-upload">
						                            	@if($data->is_provider == 1)
						                                <div id="image-preview" class="img-preview" style="background: url({{ $data->photo ? asset($data->photo):asset('assets/images/noimage.png') }});">
						                            	@else
						                                {{-- <div id="image-preview" class="img-preview" style="background: url({{ $data->photo ? asset('assets/images/users/'.$data->photo):asset('assets/images/noimage.png') }});"> --}}
                                                        <div id="image-preview" class="img-preview" style="background: url({{ $data->show_photo() }});">
						                                @endif
						                                @if($data->is_provider != 1)
						                                    <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __("Upload Image") }}</label>
						                                    <input type="file" name="photo" class="img-upload" id="image-upload">
						                                @endif
						                                  </div>
						                                  <p class="text">{{ __("Prefered Size: (600x600) or Square Sized Image") }}</p>
						                            </div>
						                          </div>
						                        </div>


												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __("Name") }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="name" placeholder="{{ __("User Name") }}" required="" value="{{ $data->name }}">
													</div>
												</div>


												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __("Email") }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="email" class="input-field" name="email" placeholder="{{ __("Email Address") }}" value="{{ $data->email }}">
													</div>
												</div>

												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __("Phone") }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="phone" placeholder="{{ __("Phone Number") }}" required="" value="{{ $data->phone }}">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ $langg->lang893 }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<select class="input-field province" name="province" required>
                                                            <option value="">{{ $langg->lang893 }}</option>
                                                            @foreach (DB::table('provinces')->get() as $d)
                                                                <option value="{{ $d->id }}" {{ $data->CityID == $d->id ? 'selected' : '' }}>
                                                                    {{ $d->name }}
                                                                </option>
                                                             @endforeach
                                                        </select>
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ $langg->lang894 }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<select class="input-field district" name="district" required>
                                                            <option value="">{{ $langg->lang894 }}</option>
                                                            @foreach (DB::table('districts')->where('province_id','=',$data->CityID)->get() as $d)
                                                                <option value="{{ $d->id }}" {{ $data->DistrictID == $d->id ? 'selected' : '' }}>
                                                                    {{ $d->name }}
                                                                </option>
                                                             @endforeach
                                                        </select>
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ $langg->lang895 }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<select class="input-field ward" name="ward" required>
                                                            <option value="">{{ $langg->lang895 }}</option>
                                                            @foreach (DB::table('wards')->where('district_id','=',$data->DistrictID)->get() as $d)
                                                                <option value="{{ $d->id }}" {{ $data->ward_id == $d->id ? 'selected' : '' }}>
                                                                    {{ $d->name }}
                                                                </option>
                                                             @endforeach
                                                        </select>
													</div>
												</div>

												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __("Address") }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="address" placeholder="{{ __("Address") }}" required="" value="{{ $data->address }}">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Affiliate Code </h4>
														</div>
													</div>
													<div class="col-lg-7">
														<span>{{ $data->affilate_code }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Referral </h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="referral_code" placeholder="Referral Code" value="{{ $data->referral_code }}">
                                                        @php
                                                            $referral = DB::table('users')
                                                            ->whereNotNull('affilate_code')
                                                            ->where('affilate_code','=',$data->referral_code)->first();
                                                        @endphp
                                                        @if (isset($referral))
                                                        <span>{{ $referral->name }}</span> -
                                                        <span>{{ $referral->email }}</span>
                                                        @endif
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Reward Point </h4>
														</div>
													</div>
													<div class="col-lg-7">
														<span>{{ number_format($data->reward_point) }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Shopping Point </h4>
														</div>
													</div>
													<div class="col-lg-7">
                                                        <span>{{ number_format($data->shopping_point) }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Bank Account </h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="BankAccountNumber" placeholder="bank account number" value="{{ $data->BankAccountNumber }}">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Bank Account Name </h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="BankAccountName" placeholder="bank account name" value="{{ $data->BankAccountName }}">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Bank Name </h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="BankName" placeholder="bank name" value="{{ $data->BankName }}">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">Bank Address </h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="BankAddress" placeholder="bank address" value="{{ $data->BankAddress }}">
													</div>
												</div>

                                                <div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __("New Password") }}</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="password" class="input-field" name="new_password" placeholder="new password" value="">
													</div>
												</div>

						                        <div class="row">
						                          <div class="col-lg-4">
						                            <div class="left-area">

						                            </div>
						                          </div>
						                          <div class="col-lg-7">
						                            <button class="addProductSubmit-btn" type="submit">{{ __("Save") }}</button>
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
    //PLACE
$('.province').on('change',function(e){
    $(".district").empty();
    $(".ward").empty();
    var sldistrict = $(".district")[0];
    var option = document.createElement("option");
    option.text = "Chọn Quận/Huyện";
    option.value = "";
    sldistrict.add(option);

    var slward = $(".ward")[0];
    var option_ward = document.createElement("option");
    option_ward.text = "Chọn Phường/Xã";
    option_ward.value = "";
    slward.add(option_ward);

    var url = mainurl+'/districts/'+($(this).val());
    $.ajax({
        type:"GET",
           url:url,
           data:{},
           success:function(data)
           {
              if ((data.errors)) {
                console.log(data.errors);
              }
              else
              {
                data.sort(function(a, b) {
                    var x = a.name.toLowerCase(), y = b.name.toLowerCase();
                    return x < y ? -1 : x > y ? 1 : 0;
                });
                $.each(data, function( i, val ) {
                    var opt = document.createElement("option");
                    opt.text = val.name;
                    opt.value = val.id;
                    sldistrict.add(opt);
                });
              }
           }
        });
    });

//select district
$('.district').on('change',function(e){
    $(".ward").empty();
    var slward = $(".ward")[0];
    var option_ward = document.createElement("option");
    option_ward.text = "Chọn Phường/Xã";
    option_ward.value = "";
    slward.add(option_ward);

	var district_id = $(this).val();

    var url1 = mainurl+'/wards/'+district_id;
    $.ajax({
        type:"GET",
           url:url1,
           data:{},
           success:function(data)
           {
              if ((data.errors)) {
                console.log(data.errors);
              }
              else
              {
                data.sort(function(a, b) {
                    var x = a.name.toLowerCase(), y = b.name.toLowerCase();
                    return x < y ? -1 : x > y ? 1 : 0;
                });
                $.each(data, function( i, val ) {
                    var opt = document.createElement("option");
                    opt.text = val.name;
                    opt.value = val.id;
                    slward.add(opt);
                });
              }
           },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(XMLHttpRequest);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    });
//select district end
//PLACE END


</script>

@endsection
