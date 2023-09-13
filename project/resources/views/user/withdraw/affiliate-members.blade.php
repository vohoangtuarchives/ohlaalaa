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
                                        <label for="from">From: </label>
                                        <input type="text" class="form-control-sm" name="from_date" id="from" placeholder="{{ __('Select a date') }}" value="">

                                        <label for="to">To: </label>
                                        <input type="text" class="form-control-sm" name="to_date" id="to" placeholder="{{ __('Select a date') }}"  value="">

                                        {{-- <a class="add-btn" id="add-data-1" data-toggle="modal" data-target="#modal1"> --}}
                                        <a class="mybtn1" id="add-find" href="#" >
                                            <i class="fas fa-search"></i> Find
                                            </a>
                                                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Ngày&emsp;&emsp;</th>
                                                            <th>Tên</th>
                                                            <th>Email</th>
                                                            <th>Điện thoại</th>
                                                            <th>Địa chỉ</th>
                                                            {{-- <th>{{ __('Options') }}&emsp;</th> --}}
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

{{-- DATA TABLE --}}

<script type="text/javascript">
    var table = $('#geniustable').DataTable({
        ordering: false,
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: "{{ route('user-affilate-members-datatable') }}",
        columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'address', name: 'address' },
                // { data: 'action', searchable: false, orderable: false }
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

        table.destroy();
        var url = mainurl+'/user/affilate/members/datatable'+'/'+sf+'/'+st;
        table = $('#geniustable').DataTable({
            ordering: false,
            processing: true,
            destroy: true,
            serverSide: true,
            ajax: url,
            columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'address', name: 'address' },
                // { data: 'action', searchable: false, orderable: false }
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
