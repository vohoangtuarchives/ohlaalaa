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
                                                    $l1_members = $user->affiliate_members()->get();
                                                    $l2_count = 0;
                                                    $l3_count = 0;
                                                    @endphp
                                                    <h4><span><strong style="color: magenta">{{ $user->name.' - '.$user->email . ' ('.$user->rank_name().($user->ranking_end_date != null ? ' - '.$user->rank_display_date() : '').')' }}</strong></span></h4>
                                                    <h6><span><i style="color: red">{{ 'L1 - '.$l1_members->count() }}</i>
                                                        | <i id="l2-view" style="color: green">{{ 'L2 - '.$l2_count }}</i>
                                                        | <i id="l3-view" style="color: blue">{{ 'L3 - '.$l3_count }}</i>
                                                    </span></h5>
                                                    <ol class="tree-structure">

                                                        @foreach ($l1_members as $l1)
                                                            @php
                                                            $l2_members = $l1->affiliate_members()->get();
                                                            @endphp
                                                            @if ($l2_members->count() > 0)
                                                            <li>
                                                                <span class="num">1</span>
                                                                <a href="#">{{ $l1->name.' - '.$l1->email . ' ('.$l1->rank_name().($l1->ranking_end_date != null ? ' - '.$l1->rank_display_date() : '').')' }} </a>
                                                                <ol class="tree-structure-lvl2">
                                                                    @foreach ($l2_members as $l2)
                                                                    @php
                                                                    $l2_count++;
                                                                    $l3_members = $l2->affiliate_members()->get();
                                                                    @endphp
                                                                    @if ($l3_members->count() > 0)

                                                                <li>
                                                                    <span class="num">2</span>
                                                                    <a href="#">{{ $l2->name.' - '.$l2->email . ' ('.$l2->rank_name().($l2->ranking_end_date != null ? ' - '.$l2->rank_display_date() : '').')' }}</a>
                                                                    <ol class="tree-structure-lvl3">
                                                                        @foreach ($l3_members as $l3)
                                                                        @php
                                                                            $l3_count++;
                                                                        @endphp
                                                                        <li>
                                                                            <span class="num">3</span>
                                                                            <a href="#">{{ $l3->name.' - '.$l3->email . ' ('.$l3->rank_name().($l3->ranking_end_date != null ? ' - '.$l3->rank_display_date() : '').')' }}</a>
                                                                        </li>
                                                                        @endforeach
                                                                    </ol>
                                                                </li>
                                                                @else
                                                                <li>
                                                                    <span class="num">2</span>
                                                                    <a href="#">{{ $l2->name.' - '.$l2->email. ' ('.$l2->rank_name().($l2->ranking_end_date != null ? ' - '.$l2->rank_display_date() : '').')' }}</a>
                                                                </li>
                                                                @endif

                                                                @endforeach
                                                                </ol>
                                                            </li>
                                                            @else
                                                            <li>
                                                                <span class="num">1</span>
                                                                <a href="#">{{ $l1->name.' - '.$l1->email. ' ('.$l1->rank_name().($l1->ranking_end_date != null ? ' - '.$l1->rank_display_date() : '').')' }} </a>
                                                            </li>
                                                            @endif
                                                        @endforeach
                                                   </ol>

                                                   <input type="hidden" name="l2_count" id="l2-count" value="{{ $l2_count }}">
                                                   <input type="hidden" name="l3_count" id="l3-count" value="{{ $l3_count }}">
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
