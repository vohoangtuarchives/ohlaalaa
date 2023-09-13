@extends('layouts.front')

@section('content')

<section class="user-dashbord">
    <div class="container">
      <div class="row">
        @include('includes.user-dashboard-sidebar')
<div class="col-lg-9">

<input type="hidden" id="headerdata" value="{{ __('ORDER') }}">

                    <div class="content-area">
                        <div class="product-area">
                            <div class="row">
                                <div class="col-lg-14">
                                    <div class="mr-table allproduct">
                                        @include('includes.admin.form-success')
                                        <div class="table-responsiv">
                                        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                                        <label for="from">{{ $langg->lang839 }}: </label>
                                        <input type="text" class="form-control-sm" name="from_date" id="from" placeholder="{{ __('Select a date') }}" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" style="width: 100px;">
                                        <label for="to">{{ $langg->lang840 }}: </label>
                                        <input type="text" class="form-control-sm" name="to_date" id="to" placeholder="{{ __('Select a date') }}"  value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" autocomplete="off" style="width: 100px;">
                                        <label for="status">{{ $langg->lang841 }}: </label>
                                        <select id="status" style="display: inline; width: 200px;" >
                                            <option value="all" selected>{{ $langg->lang848 }}</option>
                                            <option value="Daily Convert">{{ $langg->lang849 }}</option>
                                            <option value="Rebate Bonus" >{{ $langg->lang850 }}</option>
                                            <option value="Use Shopping" >{{ $langg->lang851 }}</option>
                                            <option value="Affiliate Bonus" >{{ $langg->lang852 }}</option>
                                            <option value="Merchant Sales Bonus" >{{ $langg->lang853 }}</option>
                                            <option value="Order Declined" >{{ $langg->lang854 }}</option>
                                            <option value="Global Transfer" >{{ $langg->lang855 }}</option>
                                            <option value="Order Completed" >{{ $langg->lang856 }}</option>
                                            <option value="Order - Shop Declined" >{{ $langg->lang857 }}</option>
                                            <option value="Buying Package Bonus" >{{ $langg->lang912 }}</option>
                                            <option value="KOL Consumer Bonus" >{{ $langg->lang913 }}</option>
                                            <option value="KOL Affiliate Bonus" >{{ $langg->lang916 }}</option>
                                            <option value="Admin Transfer Point" >Transfer Point</option>
                                        </select>
                                        <input type="hidden" id="admin_loader" value="{{ $gs->admin_loader }}">
                                        {{-- <a class="add-btn" id="add-data-1" data-toggle="modal" data-target="#modal1"> --}}
                                        <a class="mybtn1" id="add-find" href="javascript:;" >
                                            <i class="fas fa-search"></i> {{ $langg->lang842 }}
                                            </a>
                                                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 80px;">{{ $langg->lang843 }}</th>
                                                            <th>{{ $langg->lang844 }}</th>
                                                            <th>{{ $langg->lang845 }}</th>
                                                            <th>{{ $langg->lang915 }}</th>
                                                            <th>{{ $langg->lang914 }}</th>
                                                            <th style="width: 180px;">{{ $langg->lang846 }}</th>
                                                            <th>{{ $langg->lang847 }}&emsp;</th>
                                                        </tr>
                                                    </thead>
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
</section>


@endsection

@section('scripts')

<script src="{{asset('assets/front/js/userpointlog.js')}}"></script>

<script type="text/javascript">

$('.btn-ok1').on('click',function(){
    var url = $('#access-link').val();
    $.ajax({
        url: url,
        type: "POST",
        data: {
            "_token": "{{ csrf_token() }}"
        },
        success: function (data) {
            $('.alert-success').show();
            $('.alert-success p').html(data);
            $("#add-find" ).click();
        }
    });
});

</script>


@endsection
