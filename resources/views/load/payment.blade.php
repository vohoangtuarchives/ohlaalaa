@if($payment == 'cod')
                                <input type="hidden" name="method" value="Cash On Delivery">


@endif
@if($payment == 'paypal')
                                <input type="hidden" name="method" value="Paypal">
                                <input type="hidden" name="cmd" value="_xclick">
                                <input type="hidden" name="no_note" value="1">
                                <input type="hidden" name="lc" value="UK">
                                <input type="hidden" name="currency_code" value="{{$curr->name}}">
                                <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest">

@endif

@if($payment == 'stripe')
                                	<input type="hidden" name="method" value="Stripe">
                                  <div class="row" >
                                    <div class="col-lg-6">
                                      <input class="form-control card-elements" name="cardNumber" type="text" placeholder="{{ $langg->lang163 }}" autocomplete="off"  autofocus oninput="validateCard(this.value);" />
                                      <span id="errCard"></span>
                                    </div>
                                    <div class="col-lg-6">
                                      <input class="form-control card-elements" name="cardCVC" type="text" placeholder="{{ $langg->lang164 }}" autocomplete="off"  oninput="validateCVC(this.value);" />
                                      <span id="errCVC"></span>
                                    </div>
                                    <div class="col-lg-6">
                                      <input class="form-control card-elements" name="month" type="text" placeholder="{{ $langg->lang165 }}"  />
                                    </div>
                                    <div class="col-lg-6">
                                      <input class="form-control card-elements" name="year" type="text" placeholder="{{ $langg->lang166 }}"  />
                                    </div>
                                </div>


                                <script type="text/javascript" src="{{ asset('assets/front/js/payvalid.js') }}"></script>
                                <script type="text/javascript" src="{{ asset('assets/front/js/paymin.js') }}"></script>
                                <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
                                <script type="text/javascript" src="{{ asset('assets/front/js/payform.js') }}"></script>


                                <script type="text/javascript">
                                  var cnstatus = false;
                                  var dateStatus = false;
                                  var cvcStatus = false;

                                  function validateCard(cn) {
                                    cnstatus = Stripe.card.validateCardNumber(cn);
                                    if (!cnstatus) {
                                      $("#errCard").html('{{ $langg->lang781 }}');
                                    } else {
                                      $("#errCard").html('');
                                    }



                                  }

                                  function validateCVC(cvc) {
                                    cvcStatus = Stripe.card.validateCVC(cvc);
                                    if (!cvcStatus) {
                                      $("#errCVC").html('{{ $langg->lang782 }}');
                                    } else {
                                      $("#errCVC").html('');
                                    }

                                  }

                                </script>


@endif


@if($payment == 'instamojo')
                                	<input type="hidden" name="method" value="Instamojo">

@endif


{{-- @if($payment == 'paystack')

        <input type="hidden" name="ref_id" id="ref_id" value="">
        <input type="hidden" name="sub" id="sub" value="0">
		    <input type="hidden" name="method" value="Paystack">





@endif --}}

@if($payment == 'razorpay')

                                  <input type="hidden" name="method" value="Razorpay">

@endif

@if($payment == 'molly')
                                  <input type="hidden" name="method" value="Molly">

@endif


@if($payment == 'other')

                                <input type="hidden" name="method" value="{{ $gateway->title }}">

                                  <div class="row" >

<div class="col-lg-12 pb-2">
@if ($gateway->title == 'VNPay')

<div class="sub_show sub_showpayment payment_method_c" style="display: block;">
    <div class="col-pd">
        <div class="title_head text-left no-border-radius clearfix">Ngân hàng thanh toán</div>
        <ul class="list_cart clearfix">
            <li data-val="VIETCOMBANK" class="vnpay_bank">
                <label for="VIETCOMBANK">
                    <img src="{{asset('assets/images/ATM/vietcombank_logo.png') }}" alt="VIETCOMBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="VIETINBANK" class="vnpay_bank">
                <label for="VIETINBANK">
                    <img src="{{asset('assets/images/ATM/vietinbank_logo.png') }}" alt="VIETINBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="BIDV" class="vnpay_bank">
                <label for="BIDV">
                    <img src="{{asset('assets/images/ATM/bidv_logo.png') }}" alt="BIDV"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="AGRIBANK" class="vnpay_bank">
                <label for="AGRIBANK">
                    <img src="{{asset('assets/images/ATM/agribank_logo.png') }}" alt="AGRIBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="SACOMBANK" class="vnpay_bank">
                <label for="SACOMBANK">
                    <img src="{{asset('assets/images/ATM/sacombank_logo.png') }}" alt="SACOMBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="TECHCOMBANK" class="vnpay_bank">
                <label for="TECHCOMBANK">
                    <img src="{{asset('assets/images/ATM/techcombank_logo.png') }}" alt="TECHCOMBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="ACB" class="vnpay_bank">
                <label for="ACB">
                    <img src="{{asset('assets/images/ATM/acb_logo.png') }}" alt="ACB"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="VPBANK" class="vnpay_bank">
                <label for="VPBANK">
                    <img src="{{asset('assets/images/ATM/vpbank_logo.png') }}" alt="VPBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="SHB" class="vnpay_bank">
                <label for="SHB">
                    <img src="{{asset('assets/images/ATM/shb_logo.png') }}" alt="SHB"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="DONGABANK" class="vnpay_bank">
                <label for="DONGABANK">
                    <img src="{{asset('assets/images/ATM/dongabank_logo.png') }}" alt="DONGABANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="EXIMBANK" class="vnpay_bank">
                <label for="EXIMBANK">
                    <img src="{{asset('assets/images/ATM/eximbank_logo.png') }}" alt="EXIMBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="TPBANK" class="vnpay_bank">
                <label for="TPBANK">
                    <img src="{{asset('assets/images/ATM/tpbank_logo.png') }}" alt="TPBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="NCB" class="vnpay_bank active">
                <label for="NCB">
                    <img src="{{asset('assets/images/ATM/ncb_logo.png') }}" alt="NCB"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="OJB" class="vnpay_bank">
                <label for="OJB">
                    <img src="{{asset('assets/images/ATM/oceanbank_logo.png') }}" alt="OJB"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="MSBANK" class="vnpay_bank">
                <label for="MSBANK">
                    <img src="{{asset('assets/images/ATM/msbank_logo.png') }}" alt="MSBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="HDBANK" class="vnpay_bank" >
                <label for="HDBANK">
                    <img src="{{asset('assets/images/ATM/hdbank_logo.png') }}" alt="HDBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="NAMABANK" class="vnpay_bank" >
                <label for="NAMABANK">
                    <img src="{{asset('assets/images/ATM/namabank_logo.png') }}" alt="NAMABANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="OCB" class="vnpay_bank" >
                <label for="OCB">
                    <img src="{{asset('assets/images/ATM/ocb_logo.png') }}" alt="OCB"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="SCB" class="vnpay_bank">
                <label for="SCB">
                    <img src="{{asset('assets/images/ATM/scb_logo.png') }}" alt="SCB"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="ABBANK" class="vnpay_bank">
                <label for="ABBANK">
                    <img src="{{asset('assets/images/ATM/abbank_logo.png') }}" alt="ABBANK"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="IVB" class="vnpay_bank">
                <label for="IVB">
                    <img src="{{asset('assets/images/ATM/ivb_logo.png') }}" alt="IVB"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="VIB" class="vnpay_bank">
                <label for="VIB">
                    <img src="{{asset('assets/images/ATM/vib_logo.png') }}" alt="VIB"></label>
                <i class="fa fa-check"></i>
            </li>
            <li data-val="MBBANK" class="vnpay_bank">
                <label for="MBBANK">
                    <img src="{{asset('assets/images/ATM/mbbank_logo.png') }}" alt="MBBANK"></label>
                <i class="fa fa-check"></i>
            </li>
        </ul>
    </div>
</div>

@endif


</div>


<div class="col-lg-6 d-none">
    <label>{{ $langg->lang167 }} *</label>
	<input class="form-control BankPay" value="NCB" name="txn_id4" type="hidden" placeholder="{{ $langg->lang167 }}"  />
</div>


  </div>
@endif
{{-- <script type="text/javascript">
$('.vnpay_bank').on('click',function(){
    $('.vnpay_bank').removeClass('active');
    var data = $(this).data('val');
    $('.BankPay').val(data);
    $(this).addClass('active');
});
</script> --}}
