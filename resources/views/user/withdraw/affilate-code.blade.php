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
                                    {{ $langg->lang322 }}
                                </h4>
                            </div>
                            <div class="edit-info-area">

                                <div class="body">
                                        <div class="edit-info-area-form">
                                                <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                                                <form>
                                                    @include('includes.admin.form-both')

                                                    <div class="row">
                                                        <div class="col-lg-4 text-right pt-2 f-14">
                                                            <label>Mã Affilate *<a id="affilate_code_click" data-toggle="tooltip" data-placement="top" title="Copy"  href="javascript:;" class="mybtn1 copy"><i class="fas fa-copy"></i></a></label>
                                                            <br>
                                                            <small></small>
                                                        </div>
                                                        <div class="col-lg-8 pt-2">
                                                             <input id="affilate_code" class="input-field affilate" name="affilate_code" readonly="" value="{{ $user->affilate_code }}">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-4 text-right pt-2 f-14">
                                                            <label>{{ $langg->lang323 }} <a id="affilate_click" data-toggle="tooltip" data-placement="top" title="Copy"  href="javascript:;" class="mybtn1 copy"><i class="fas fa-copy"></i></a></label>
                                                            <br>
                                                            <small>{{ $langg->lang324 }}</small>
                                                        </div>
                                                        <div class="col-lg-8 pt-2">
                                                             <textarea id="affilate_address" class="input-field affilate" name="address" readonly="" row="5">{{ url('/').'/user/r?r='.$user->affilate_code}}</textarea>
                                                        </div>
                                                    </div>

                                                    <div class="row pb-5">
                                                        <div class="col-lg-4 text-right pt-2 f-14">
                                                            <label>{{ $langg->lang325 }}</label>
                                                            <br>
                                                            <small>{{ $langg->lang326 }}</small>
                                                        </div>
                                                        <div class="col-lg-8 pt-2 pl-5">
                                                             <a href="{{ url('/').'/user/r?r='.$user->affilate_code}}" target="_blank"><img src="{{asset('assets/images/'.$gs->affilate_banner)}}"></a>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-lg-4 text-right pt-2 f-14">
                                                            <label>{{ $langg->lang327 }} <a id="affilate_html_click" data-toggle="tooltip" data-placement="top" title="Copy"  href="javascript:;" class="mybtn1 copy"><i class="fas fa-copy"></i></a></label>
                                                            <br>
                                                            <small>{{ $langg->lang328 }}</small>
                                                        </div>
                                                        <div class="col-lg-8 pt-2">
                                                             <textarea id="affilate_html" class="input-field affilate" name="address" readonly="" row="5"><a href="{{ url('/').'/user/r?r='.$user->affilate_code}}" target="_blank"><img src="{{asset('assets/images/'.$gs->affilate_banner)}}"></a></textarea>
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

@endsection

@section('scripts')

<script src="{{asset('assets/front/js/htdnew3.js')}}"></script>


@endsection