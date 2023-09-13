@extends('layouts.admin')

@section('styles')

<link href="{{asset('assets/admin/css/jquery-ui.css')}}" rel="stylesheet" type="text/css">

@endsection

@section('content')

<input type="hidden" id="headerdata" value="{{ __('ORDER') }}">

                    <div class="content-area">
                        <div class="mr-breadcrumb">
                            <div class="row">
                                <div class="col-lg-12">
                                        <h4 class="heading">Order Report</h4>
                                        <ul class="links">
                                            <li>
                                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                                            </li>
                                            <li>
                                                <a href="javascript:;">{{ __('Orders') }}</a>
                                            </li>
                                            <li>
                                                <a href="#">Order Report</a>
                                            </li>
                                        </ul>
                                </div>
                            </div>
                        </div>
                        <div class="product-area">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mr-table allproduct">
                                        @include('includes.admin.form-success')
                                        <div class="table-responsiv">
                                        <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                                        <label for="from">From: </label>
                                        <input type="text" class="form-control-sm" name="from_date" id="from" placeholder="{{ __('Select a date') }}" value="" autocomplete="off">

                                        <label for="to">To: </label>
                                        <input type="text" class="form-control-sm" name="to_date" id="to" placeholder="{{ __('Select a date') }}"  value="" autocomplete="off">

                                        <label for="status">Status: </label>
                                        <select id="status" style="display: inline; width: 150px;" >
                                            <option value="all" selected>All</option>
                                            <option value="pending">Pending</option>
                                            <option value="processing" >Processing</option>
                                            <option value="completed" >Completed</option>
                                            <option value="declined" >Declined</option>
                                            <option value="on delivery" >On Delivery</option>
                                        </select>
                                        <label for="is-collected">Handling Fee: </label>
                                        <select id="is-collected" style="display: inline; width: 150px;" >
                                            <option value="all" selected>All</option>
                                            <option value="1">Đã thu</option>
                                            <option value="0" >Chưa thu</option>
                                        </select>
                                        {{-- <a class="add-btn" id="add-data-1" data-toggle="modal" data-target="#modal1"> --}}
                                        <a class="add-btn" id="add-find" >
                                            <i class="fas fa-search"></i> Find
                                            </a>


                                        {{-- <input id="to" type="text" class="input-field" value=""> --}}
                                                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Merchant Name&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</th>
                                                            <th>Order Date</th>
                                                            <th>Order Number</th>
                                                            <th>Status</th>
                                                            <th>Order Total Amount</th>
                                                            <th>Order Total Cash Amount</th>
                                                            <th>Order Total Credit Card Amount</th>
                                                            <th>Order Total Collected SP Amount</th>
                                                            <th>Order Total Payment Amount</th>
                                                            <th>Merchant Handling Fee Amount</th>
                                                            <th>{{ __('Options') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                                            <th>Merchant Handling Fee</th>
                                                            <th>Order Total Collected SP</th>
                                                            <th>Email</th>
                                                            <th>Phone</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



{{-- ORDER MODAL --}}

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="submit-loader">
            <img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
    </div>
    <div class="modal-header d-block text-center">
        <h4 class="modal-title d-inline-block">Thông báo</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
    </div>

      <!-- Modal body -->
      <div class="modal-body">
        <p class="text-center">Merchant Handling Order Fee!</p>
        <p class="text-center">{{ __('Do you want to proceed?') }}</p>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
            <a class="btn btn-success btn-ok1" data-dismiss="modal">{{ __('Proceed') }}</a>
            <input type="hidden" name="access-link" id="access-link" readonly>
      </div>

    </div>
  </div>
</div>

{{-- ORDER MODAL ENDS --}}

{{-- ADD / EDIT MODAL --}}

<div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
                                <div class="submit-loader">
                                        <img  src="{{asset('assets/images/'.$gs->admin_loader)}}" alt="">
                                </div>
                            <div class="modal-header">
                            <h5 class="modal-title"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            </div>
                            <div class="modal-body">

                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                            </div>



        </div>
    </div>

</div>

{{-- ADD / EDIT MODAL ENDS --}}


@endsection

@section('scripts')



{{-- DATA TABLE --}}

<script type="text/javascript">

    var table = $('#geniustable').DataTable({
        ordering: false,
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin-order-datatables-report',['all','all']) }}",
        columns: [
                { data: 'shop_name', name: 'shop_name' },
                { data: 'created_at', name: 'created_at' },
                { data: 'order_number', name: 'order_number' },
                { data: 'status', name: 'status' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'total_cash_amount', name: 'total_cash_amount' },
                { data: 'total_creditcard_amount', name: 'total_creditcard_amount' },
                { data: 'total_collected_sp_amount', name: 'total_collected_sp_amount' },
                { data: 'total_payment_amount', name: 'total_payment_amount' },
                { data: 'merchant_handling_fee', name: 'merchant_handling_fee' },
                { data: 'action', searchable: false, orderable: false },
                { data: 'handling_fee_rate', name: 'handling_fee_rate' },
                { data: 'total_collected_sp', name: 'total_collected_sp' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        },
        drawCallback : function( settings ) {
                $('.select').niceSelect();
        }
    });

    $("#add-find" ).on('click' , function(e){
        var df = new Date(1900,0,1);
        if($('#from').val() != ''){
            df = new Date($('#from').val());
        }
        const yef = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(df);
        const mof = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(df);
        const daf = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(df);
        var sf = `${yef}-${mof}-${daf}`;

        var dt = new Date();
        if($('#to').val() != ''){
            dt = new Date($('#to').val());
        }
        const yet = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(dt);
        const mot = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(dt);
        const dat = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(dt);
        var st = `${yet}-${mot}-${dat}`;

        var status = $('#status').val();
        var iscollected = $('#is-collected').val();
        table.destroy();
        var url = mainurl+'/admin/orders/datatablesreport/'+status+'/'+iscollected+'/'+sf+'/'+st;
        table = $('#geniustable').DataTable({
            ordering: false,
            processing: true,
            destroy: true,
            serverSide: true,
            ajax: url,
            columns: [
                    { data: 'shop_name', name: 'shop_name' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'order_number', name: 'order_number' },
                    { data: 'status', name: 'status' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'total_cash_amount', name: 'total_cash_amount' },
                    { data: 'total_creditcard_amount', name: 'total_creditcard_amount' },
                    { data: 'total_collected_sp_amount', name: 'total_collected_sp_amount' },
                    { data: 'total_payment_amount', name: 'total_payment_amount' },
                    { data: 'merchant_handling_fee', name: 'merchant_handling_fee' },
                    { data: 'action', searchable: false, orderable: false },
                    { data: 'handling_fee_rate', name: 'handling_fee_rate' },
                    { data: 'total_collected_sp', name: 'total_collected_sp' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },

                    ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            },
            drawCallback : function( settings ) {
                    $('.select').niceSelect();
            }
        });
    });
</script>
{{-- DATA TABLE --}}

<script type="text/javascript">
    var dateToday = new Date();
    var dates =  $( "#from,#to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        //minDate: dateToday,
        onSelect: function(selectedDate) {
        var option = this.id == "from" ? "minDate" : "maxDate",
          instance = $(this).data("datepicker"),
          date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
          dates.not(this).datepicker("option", option, date);
        }
    });


</script>

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
