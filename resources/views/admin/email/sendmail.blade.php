@extends('layouts.admin')

<script src="{{asset('assets/ckfinder/ckfinder.js')}}"></script>
<script src="{{asset('assets/ckeditor/ckeditor.js')}}"></script>

@section('content')

            <div class="content-area">

            <div class="mr-breadcrumb">
              <div class="row">
                <div class="col-lg-12">
                    <h4 class="heading">{{ __('Send Email') }}</h4>
                    <ul class="links">
                      <li>
                        <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                      </li>
                      <li>
                        <a href="javascript:;">{{ __('Email Settings') }}</a>
                      </li>
                      <li>
                        <a href="{{ route('admin-group-show') }}">{{ __('Send Email') }}</a>
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

                      <form  action="{{route('admin-send-mail-submit')}}" method="POST">
                        <!-- @include('includes.admin.form-both')   -->


                        {{csrf_field()}}
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">

                                      </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-lg-4"></div>
                          <div class="col-lg-7">
                            @if(session('success'))
                              <div class="alert alert-success">{{session('success')}}</div>
                            @endif

                            @if(count($errors)>0)
                            <div class="alert alert-danger">
                                @foreach($errors->all() as $error)
                                {{$error}} <br>
                                @endforeach
                              </div>
                            @endif
                          </div>
                          <div class="col-lg-4">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Subject') }} *</h4>
                                <p class="sub-heading">{{ __('(In Any Language)') }}</p>
                            </div>
                          </div>
                          <div class="col-lg-7">
                            <input type="text" class="input-field" name="subject" placeholder="{{ __('Subject') }}" value="" >
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">
                                <h4 class="heading">{{ __('To Email') }} *</h4>
                                <p class="sub-heading">{{ __('(In Any Language)') }}</p>
                            </div>
                          </div>
                          <div class="col-lg-7">
                            <div class="row">
                              <div class="col-lg-2">
                                <div class="custom-control custom-radio">
                                  <input type="radio" class="custom-control-input" id="default" onchange="changesTypeSend(this);" name="select" value="default" checked>
                                  <label class="custom-control-label" for="default">Default</label>
                                </div>
                              </div>
                              <div class="col-lg-5">
                                <div class="custom-control custom-radio">
                                  <input type="radio" class="custom-control-input" id="mailvendor" onchange="changesTypeSend(this);" name="select" value="sendvendor">
                                  <label class="custom-control-label" for="mailvendor">Mail User Vendor</label>
                                </div>
                              </div>
                              <script>
                                  function changesTypeSend(ele){
                                    if(ele.value == 'sendvendor'){
                                      document.getElementById('emailTo').style.display = "none";
                                    }else{
                                      document.getElementById('emailTo').style.display = "block";
                                    }
                                  }
                              </script>
                            </div>
                            <input type="text" class="input-field" id="emailTo" name="email" placeholder="{{ __('To Emails') }}" value="" >
                          </div>
                        </div>


                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">
                              <h4 class="heading">
                                   {{ __('Email Body') }} *
                              </h4>
                              <p class="sub-heading">{{ __('(In Any Language)') }}</p>
                            </div>
                          </div>
                          <div class="col-lg-7">
                              <textarea id="editor" name="body" placeholder="{{ __('Email Body') }}"></textarea>

                          </div>
                        </div>
                        <script>
                          var editor = CKEDITOR.replace( 'editor', {

                          } );
                          CKFinder.setupCKEditor(editor);
                        </script>

                        <div class="row">
                          <div class="col-lg-4">
                            <div class="left-area">

                            </div>
                          </div>
                          <div class="col-lg-7">
                            <button class="addProductSubmit-btn" type="submit">{{ __('Send Email') }}</button>
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

