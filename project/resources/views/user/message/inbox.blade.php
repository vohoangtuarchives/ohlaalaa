@extends('layouts.front')
@section('content')


<section class="user-dashbord">
    <div class="container">
        <div class="row">
            @include('includes.user-dashboard-sidebar')
                <div class="col-lg-9">
					<div class="user-profile-details">
						<div class="order-history">
							<div class="mr-table allproduct message-area ">
								@include('includes.form-success')
                                    <div class="table-responsiv" style="height: 600px;">
                                        <inbox-component></inbox-component>
                                    </div>
								</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection


