@extends('layouts.admin')
@section('styles')
    <link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">
@endsection
@section('content')

    <div class="container">
        <div class="mr-breadcrumb mt-5">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="heading">{{ __('Transfer Point') }}</h4>
                    <ul class="links">
                        <li>
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                        </li>
                        <li>
                            <a href="javascript:;">{{ __('Transfer Point') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('admin-group-show') }}">{{ __('Transfer Point') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">

            </div>
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                            class="nav-link active" id="home-tab"
                            data-toggle="tab"
                            data-target="#home"
                            type="button"
                            role="tab"
                            aria-controls="home"
                            aria-selected="true">From Customer</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">FROM SP Storage</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                    <form class="card-body" action="" id="geniusform" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('includes.admin.form-both')
                        <div class="form-group row mt-4">
                            <label for="staticEmail" class="col-sm-2 col-form-label">FROM Customer</label>
                            <div class="col-sm-10">
                                <input type="email" class="input-field" id="staticEmail" name="from_customer">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="staticEmail" class="col-sm-2 col-form-label">TO Customer</label>
                            <div class="col-sm-10">
                                <input type="email" class="input-field" id="staticEmail" name="to_customer">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="staticEmail" class="col-sm-2 col-form-label">SP points</label>
                            <div class="col-sm-10">
                                <input type="text" class="input-field" id="staticEmail" name="amount">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-10">

                                <button class="addProductSubmit-btn btn btn-primary" type="submit">Chuyển</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <form class="card-body" action="" id="geniusform" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" class="input-field" id="staticEmail" name="from_customer" value="demo@demo.com">

                        <div class="form-group row mt-4">
                            <label for="staticEmail" class="col-sm-2 col-form-label">TO Customer</label>
                            <div class="col-sm-10">
                                <input type="email" class="input-field" id="staticEmail" name="to_customer">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="staticEmail" class="col-sm-2 col-form-label">SP points</label>
                            <div class="col-sm-10">
                                <input type="text" class="input-field" id="staticEmail" name="amount">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-10">

                                <button class="addProductSubmit-btn btn btn-primary" type="submit">Chuyển</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <div class="card mt-4">
            <div class="card-header">
                Transaction History
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush py-4">
                    @foreach($transactions as $transaction)
                        <li class="list-group-item">{{ $transaction->admin_name }}: {{$transaction->content}}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection