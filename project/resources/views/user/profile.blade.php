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
                                    {{ $langg->lang262 }}
                                </h4>
                            </div>
                            <div class="edit-info-area">

                                <div class="body">
                                    <div class="edit-info-area-form">
                                        <div class="gocover"
                                            style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);">
                                        </div>
                                        <form id="userform" action="{{route('user-profile-update')}}" method="POST"
                                            enctype="multipart/form-data">

                                            {{ csrf_field() }}

                                            @include('includes.admin.form-both')
                                            <div class="upload-img">
                                                @if($user->is_provider == 1)
                                                <div class="img"><img
                                                        src="{{ $user->photo ? asset($user->photo):asset('assets/images/'.$gs->user_image) }}">
                                                </div>
                                                @else
                                                <div class="img"><img
                                                        {{-- src="{{ $user->photo ? asset('assets/images/users/'.$user->photo):asset('assets/images/'.$gs->user_image) }}"> --}}
                                                        src="{{ $user->show_photo() }}">
                                                </div>
                                                @endif
                                                @if($user->is_provider != 1)
                                                <div class="file-upload-area">
                                                    <div class="upload-file">
                                                        <input type="file" name="photo" class="upload">
                                                        <span>{{ $langg->lang263 }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <input name="name" type="text" class="input-field"
                                                        placeholder="{{ $langg->lang264 }}" required=""
                                                        value="{{ $user->name }}">
                                                </div>
                                                <div class="col-lg-6">
                                                    <input name="email" type="email" class="input-field"
                                                        placeholder="{{ $langg->lang265 }}" required=""
                                                        value="{{ $user->email }}" disabled>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <input name="phone" type="text" class="input-field"
                                                        placeholder="{{ $langg->lang266 }}" required=""
                                                        value="{{ $user->phone }}">
                                                </div>
                                                <div class="col-lg-6">
                                                    <input name="fax" type="text" class="input-field"
                                                        placeholder="{{ $langg->lang267 }}" value="{{ $user->fax }}">
                                                </div>
                                            </div>
                                            <div class="row">

                                                <div class="col-lg-6">
                                                    <select class="input-field province" name="province">
                                                        <option value="">{{ $langg->lang893 }}</option>
                                                        @foreach (DB::table('provinces')->get() as $data)
                                                            <option value="{{ $data->id }}" {{ $user->CityID == $data->id ? 'selected' : '' }}>
                                                                {{ $data->name }}
                                                            </option>
                                                         @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-lg-6">
                                                    <select class="input-field district" name="district">
                                                        <option value="">{{ $langg->lang894 }}</option>
                                                        @foreach (DB::table('districts')->where('province_id','=',$user->CityID)->get() as $data)
                                                            <option value="{{ $data->id }}" {{ $user->DistrictID == $data->id ? 'selected' : '' }}>
                                                                {{ $data->name }}
                                                            </option>
                                                         @endforeach
                                                    </select>
                                                </div>



                                            </div>

                                            <div class="row" hidden>
                                                <div class="col-lg-6">
                                                    <input name="city" type="text" class="input-field"
                                                        placeholder="{{ $langg->lang268 }}" value="{{ $user->city }}">
                                                </div>

                                                <div class="col-lg-6">
                                                    <select class="input-field" name="country">
                                                        <option value="">{{ $langg->lang157 }}</option>
                                                        @foreach (DB::table('countries')->get() as $data)
                                                            <option value="{{ $data->country_name }}" {{ $user->country == $data->country_name ? 'selected' : '' }}>
                                                                {{ $data->country_name }}
                                                            </option>
                                                         @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                        <div class="col-lg-6">
                                                            <select class="input-field ward" name="ward">
                                                                <option value="">{{ $langg->lang895 }}</option>
                                                                @foreach (DB::table('wards')->where('district_id','=',$user->DistrictID)->get() as $data)
                                                                    <option value="{{ $data->id }}" {{ $user->ward_id == $data->id ? 'selected' : '' }}>
                                                                        {{ $data->name }}
                                                                    </option>
                                                                 @endforeach
                                                            </select>
                                                        </div>

                                                <div class="col-lg-6">
                                                    <textarea class="input-field" name="address" required=""
                                                        placeholder="{{ $langg->lang270 }}">{{ $user->address }}</textarea>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <input name="BankAccountNumber" type="text" class="input-field"
                                                        placeholder="số tài khoản" value="{{ $user->BankAccountNumber }}">
                                                </div>

                                            <div class="col-lg-6">
                                                <input name="BankAccountName" type="text" class="input-field"
                                                        placeholder="tên tài khoản" value="{{ $user->BankAccountName }}">
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <input name="BankName" type="text" class="input-field"
                                                    placeholder="tên ngân hàng" value="{{ $user->BankName }}">
                                            </div>

                                        <div class="col-lg-6">
                                            <input name="Bank Address" type="text" class="input-field"
                                                    placeholder="chi nhánh" value="{{ $user->BankAddress }}">
                                        </div>
                                    </div>

                                            <div class="form-links">
                                                <button class="submit-btn" type="submit">{{ $langg->lang271 }}</button>
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

@endsection
