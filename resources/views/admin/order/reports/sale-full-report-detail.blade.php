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
                                        <h4 class="heading">{{ __('Reports') }}</h4>
                                        <ul class="links">
                                            <li>
                                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                                            </li>
                                            <li>
                                                <a href="javascript:;">{{ __('Reports') }}</a>
                                            </li>
                                            <li>
                                                <a href="{{route('admin-order-report-salefull-detail')}}">{{ __('Sale Full Report Detail') }}</a>
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
                                        <input type="text" class="form-control-sm" name="from_date" id="from" placeholder="{{ __('Select a date') }}" value="{{ $now }}" autocomplete="off" style="width: 100px;">
                                        <label for="to">To: </label>
                                        <input type="text" class="form-control-sm" name="to_date" id="to" placeholder="{{ __('Select a date') }}"  value="{{ $now }}" autocomplete="off" style="width: 100px;">
                                        <label for="status">Status: </label>
                                        <select id="status" style="display: inline; width: 150px;"  >
                                            <option value="all">All</option>
                                            <option value="pending">Pending</option>
                                            <option value="processing" >Processing</option>
                                            <option value="completed" selected >Completed</option>
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
                                        <a class="add-btn" id="add-find" > <i class="fas fa-search"></i> Find </a>
                                        <a class="add-btn" id="export-excel" > <i class="fas fa-file-excel"></i> Export </a>


                                        {{-- <input id="to" type="text" class="input-field" value=""> --}}
                                                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>1. Order Date &emsp;&emsp;&emsp;&emsp;</th>
                                                            <th>2. Merchant</th>
                                                            <th>3. Merchant Name &emsp;&emsp;&emsp;&emsp;</th>
                                                            <th>4. Address</th>
                                                            <th>5. Tax Code</th>
                                                            <th>6. Email</th>
                                                            <th>7. Bill No</th>
                                                            <th>8. Status Of Bill</th>
                                                            <th>9. Amount</th>
                                                            <th>10. Point</th>
                                                            <th>11. Rate</th>
                                                            <th>12. Amount of Point</th>
                                                            <th>13. Voucher of Merchant</th>
                                                            <th>14. Voucher of Techhub</th>
                                                            <th>15. Delivery Fee</th>
                                                            <th>18. Credit cards</th>
                                                            <th>19. Partner Must Pay</th>
                                                            <th>20. Partner Payment Date</th>
                                                            <th>21. Partner Name</th>
                                                            <th>22. Partner Payment to Techhub Amount</th>
                                                            <th>23. Charge Fee</th>
                                                            <th>24. Must Pay to Merchant</th>
                                                            <th>25. Payment to Merchant Date</th>
                                                            <th>26. Payment to Merchant Amount</th>
                                                            <th>27. Merchant Bank Account Name</th>
                                                            <th>28. Merchant Bank Name</th>
                                                            <th>29. Merchant Bank Branch</th>
                                                            <th>30. Merchant Bank Account No</th>
                                                            <th>31. Carrier Payment Date</th>
                                                            <th>32. Carrier Payment Amount</th>
                                                            <th>{{ __('Options') }} &emsp;&emsp;&emsp;&emsp;</th>
                                                            <th>Merchant Handling Fee</th>
                                                            <th>Tax</th>
                                                            <th>Phone</th>
                                                            <th>Delivery Code</th>
                                                            <th>Shipping Type</th>
                                                            <th>Handling Fee Collected</th>
                                                            <th>Is debt</th>
                                                            <th>Debt Amount</th>
                                                            <th>Product Name</th>
                                                            <th>Child Category</th>
                                                            <th>Sub Category</th>
                                                            <th>Main Category</th>
                                                            <th>Refund Date</th>
                                                            <th>Refund Amount</th>
                                                            <th>Refund Bank</th>
                                                            <th>Refund Note</th>
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
        processing: true,
        serverSide: true,
        pageLength: 50,
        ajax: '{{ route('admin-order-report-salefull-detail-datatables',['completed', 'all', \Carbon\Carbon::now()->format('Y-m-d'),\Carbon\Carbon::now()->format('Y-m-d')]) }}',
        columns: [
            { data: 'created_at', name: 'created_at' },
                { data: 'shop_id', name: 'shop_id' },
                { data: 'shop_name', name: 'shop_name' },
                { data: 'address', name: 'address' },
                { data: 'TaxCode', name: 'TaxCode' },
                { data: 'email', name: 'email' },
                { data: 'order_number', name: 'order_number' },
                { data: 'status', name: 'status' },
                { data: '_9_Amount', name: '_9_Amount' },
                { data: '_10_Point', name: '_10_Point' },
                { data: '_11_Rate', name: '_11_Rate' },
                { data: '_12_Amount_of_Point', name: '_12_Amount_of_Point' },
                { data: '_13_Voucher_of_Merchant', name: '_13_Voucher_of_Merchant' },
                { data: '_14_Voucher_of_Techhub', name: '_14_Voucher_of_Techhub' },
                { data: '_15_Delivery_fee', name: '_15_Delivery_fee' },
                { data: '_18_VISA_JCB_MASTERCARD_UPI_AMEX', name: '_18_VISA_JCB_MASTERCARD_UPI_AMEX' },
                { data: '_19_Amount_Partner_Must_Pay', name: '_19_Amount_Partner_Must_Pay' },
                { data: '_20_PAYMENT_TO_TECHHUB_Date', name: '_20_PAYMENT_TO_TECHHUB_Date' },
                { data: '_21_PAYMENT_TO_TECHHUB_PARTNER', name: '_21_PAYMENT_TO_TECHHUB_PARTNER' },
                { data: '_22_PAYMENT_TO_TECHHUB_AMOUNT', name: '_22_PAYMENT_TO_TECHHUB_AMOUNT' },
                { data: '_23_CHARGE_FEES_VND', name: '_23_CHARGE_FEES_VND' },
                { data: '_24_Amount_Must_Pay_to_Merchant', name: '_24_Amount_Must_Pay_to_Merchant' },
                { data: '_25_PAYMENT_to_Merchant_Date', name: '_25_PAYMENT_to_Merchant_Date' },
                { data: '_26_Payment_to_Merchant_Amount', name: '_26_Payment_to_Merchant_Amount' },
                { data: 'BankAccountName', name: 'BankAccountName' },
                { data: 'BankName', name: 'BankName' },
                { data: 'BankAccountNumber', name: 'BankAccountNumber' },
                { data: 'BankAddress', name: 'BankAddress' },
                { data: '_31_Payment_to_Carrier_Date', name: '_31_Payment_to_Carrier_Date' },
                { data: '_32_Payment_to_Carrier_Amount', name: '_32_Payment_to_Carrier_Amount' },
                { data: 'action', searchable: false, orderable: false },
                { data: 'handling_fee_rate', name: 'handling_fee_rate' },
                { data: 'tax', name: 'tax' },
                { data: 'phone', name: 'phone' },
                { data: 'shipping_partner_code', name: 'shipping_partner_code' },
                { data: 'shipping_type', name: 'shipping_type' },
                { data: 'is_handlingfee_collected', name: 'is_handlingfee_collected' },
                { data: 'merchant_is_debt', name: 'merchant_is_debt' },
                { data: 'merchant_debt_amount', name: 'merchant_debt_amount' },
                { data: 'product_name', name: 'product_name' },
                { data: 'child_category', name: 'child_category' },
                { data: 'sub_category', name: 'sub_category' },
                { data: 'main_category', name: 'main_category' },
                { data: 'refund_date', name: 'refund_date' },
                { data: 'refund_amount', name: 'refund_amount' },
                { data: 'refund_bank', name: 'refund_bank' },
                { data: 'refund_note', name: 'refund_note' },
                ],
        language : {
            processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
        }

    });

    $("#add-find" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var st = get_date_string($('#to').val());
        var status = $('#status').val();
        var iscollected = $('#is-collected').val();
        var url = mainurl+'/admin/orders/reports/salefulldetail/datatables/'+status+'/'+iscollected+'/'+sf+'/'+st;
        console.log('url',url);
        table.ajax.url( url ).load();
    });

    $("#export-excel" ).on('click' , function(e){
        var sf = get_date_string($('#from').val());
        var st = get_date_string($('#to').val());
        var status =  $('#status').val();
        var iscollected = $('#is-collected').val();
        var url = mainurl+'/admin/orders/reports/salefulldetail/export/'+status+'/'+iscollected+'/'+sf+'/'+st;
        window.open(url, '_blank');
    });
</script>
{{-- DATA TABLE --}}

<script type="text/javascript">
    var dateToday = new Date();
    var dates =  $( "#from,#to" ).datepicker({
        defaultDate: "+0w",
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
