
<?php 
//$a = Carbon\Carbon::createFromFormat('m-d-Y', $prod->discount_date) ;
//$a = Carbon\Carbon::createFromFormat('Y-m-d', $prod->discount_date)\->format('Y-m-d');
$a = str_replace(['_','/','.'],'-',$prod->discount_date);
//dd( $prod->discount_date, Carbon\Carbon::now()->format('m-d-Y'),$a );
//Carbon\Carbon::parse(str_replace(['_','/','.'],['-'],$prod->discount_date)
?>
@if(strpos($prod->discount_date, '-') !== false || strpos($prod->discount_date, '/') !== false)
    @if(Carbon\Carbon::now()->format('m-d-Y') < str_replace(['_','/','.'],'-',$prod->discount_date))
        <div class="item">
            @if (!isMobileDevice())
            <a href="{{ route('front.product', $prod->slug) }}">
            @endif
                <div class="item-img">
                    @if(!empty($prod->features))
                        <div class="sell-area">
                        @foreach($prod->features as $key => $data1)
                            <span class="sale" style="background-color:{{ (isset($prod->colors[$key]) ? $prod->colors[$key] : 'none') }}">{{ (isset($prod->features[$key]) ? $prod->features[$key] : '') }}</span>
                            @endforeach
                        </div>
                    @endif
                        <div class="extra-list">
                            <ul>
                                <li>
                                    @if(Auth::guard('web')->check())

                                    <span class="add-to-wish" data-href="{{ route('user-wishlist-add',$prod->id) }}" data-toggle="tooltip" data-placement="right" title="{{ $langg->lang54 }}" data-placement="right"><i class="icofont-heart-alt" ></i>
                                    </span>

                                    @else

                                    <span rel-toggle="tooltip" title="{{ $langg->lang54 }}" data-toggle="modal" id="wish-btn" data-target="#comment-log-reg" data-placement="right">
                                        <i class="icofont-heart-alt"></i>
                                    </span>

                                    @endif
                                </li>
                                <li>
                                <span class="quick-view" rel-toggle="tooltip" title="{{ $langg->lang55 }}" href="javascript:;" data-href="{{ route('product.quick',$prod->id) }}" data-toggle="modal" data-target="#quickview" data-placement="right"> <i class="icofont-eye"></i>
                                </span>
                                </li>
                                <li>
                                    <span class="add-to-compare" data-href="{{ route('product.compare.add',$prod->id) }}"  data-toggle="tooltip" data-placement="right" title="{{ $langg->lang57 }}" data-placement="right">
                                        <i class="icofont-exchange"></i>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    {{-- <img class="img-fluid front-index-flash-img" src="{{ $prod->thumbnail ? asset('assets/images/thumbnails/'.$prod->thumbnail):asset('assets/images/noimage.png') }}" alt=""> --}}
                    <img class="img-fluid front-index-flash-img" src="{{ $prod->show_thumbnail() }}" alt="">
                </div>
            @if (isMobileDevice())
                <a href="{{ route('front.product', $prod->slug) }}">
            @endif
                <div class="info">
                    <div class="stars">
                                                                        <div class="ratings">
                                                                                <div class="empty-stars"></div>
                                                                                <div class="full-stars" style="width:{{App\Models\Rating::ratings($prod->id)}}%"></div>
                                                                        </div>
                    </div>

                    <h4 class="price">{{ $prod->showPrice() }} @if ($prod->price < $prod->previous_price) <del><small>{{ $prod->showPreviousPrice() }}</small></del> @endif</h4>
                    @if ($prod->price_shopping_point > 0)
                    <h4 class="price">+ SP {{ number_format($prod->price_shopping_point) }} <small>{{ $prod->percent_shopping_point }}%</small></h4>
                    @endif
                            <h5 class="name">{{ $prod->showName() }}</h5>
                            <div class="item-cart-area">
                                                        @if($prod->product_type == "affiliate")
                                                            <span class="add-to-cart-btn affilate-btn" data-href="{{ route('affiliate.product', $prod->slug) }}"><i class="icofont-cart"></i> {{ $langg->lang251 }}
                                                            </span>
                                                        @else
                                                            <span class="add-to-cart add-to-cart-btn" data-href="{{ route('product.cart.add',$prod->id) }}">
                                                                <i class="icofont-cart"></i> {{ $langg->lang56 }}
                                                            </span>
                                                            <span class="add-to-cart-quick add-to-cart-btn" data-href="{{ route('product.cart.quickadd',$prod->id) }}">
                                                                <i class="icofont-cart"></i> {{ $langg->lang251 }}
                                                            </span>
                                                        @endif
                            </div>
                </div>

                <div class="deal-counter">
                <div data-countdown="{{ $prod->discount_date }}"></div>
                </div>
            </a>
        </div>
    @endif
@endif
