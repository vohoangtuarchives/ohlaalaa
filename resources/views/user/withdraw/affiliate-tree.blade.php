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
                                <div class="col-lg-12">
                                    <div class="mr-table allproduct">
                                        @include('includes.admin.form-success')

                                        <div class="container">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    @php
                                                    $l1_members = $user->affiliate_members()->orderByDesc('created_at')->get();
                                                    $num = 1;
                                                    @endphp
                                                    <h4><span><strong style="color: magenta">{{ $user->name.' - '.$user->email . ' ('.$user->rank_name().($user->ranking_end_date != null ? ' - '.$user->rank_display_date() : '').')' }}</strong></span></h4>
                                                    <h6><span><i style="color: red">{{ 'Total members: '.$l1_members->count() }}</i>
                                                    </span></h5>
                                                    <ol class="tree-structure">
                                                        @foreach ($l1_members as $l1)
                                                        <li>
                                                            <span class="num">{{ $num }}</span>
                                                            <span >{{ $l1->name.' - '.$l1->email. ' ('.$l1->rank_name().($l1->ranking_end_date != null ? ' - '.$l1->rank_display_date() : '').')('.$l1->phone.')' }} </span>
                                                        </li>
                                                        @php
                                                            $num++;
                                                        @endphp
                                                        @endforeach
                                                   </ol>
                                                </div>
                                            </div>
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

$("#l2-view").text('L2 - ' + $("#l2-count").val());
$("#l3-view").text('L3 - ' + $("#l3-count").val());


</script>


@endsection
