@if (count($shops) > 0)
    @foreach ($shops as $key => $shop)
        <div class="col-lg-4 col-md-4 col-6 remove-padding">

            @php
            $shop_link = route('front.vendor', str_replace(' ', '-', $shop->shop_name));
            @endphp
            <a href="{{ $shop_link }}" class="item">
                <div class="item-img">
                    @if(!empty($shop->features))
                        <div class="sell-area">
                            @foreach($shop->features as $key => $data1)
                            <span class="sale" style="background-color:{{ $shop->colors[$key] }}">{{ $shop->features[$key] }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- <img class="img-fluid front-index-featured-img" src="{{ $shop->thumbnail ? asset('assets/images/thumbnails/'.$shop->thumbnail):asset('assets/images/noimage.png') }}" alt=""> --}}
                    <img class="img-fluid front-index-featured-img" src="{{ $shop->show_photo() }}" alt="">
                </div>
                <div class="info">
                    {{-- <div class="stars">
                        <div class="ratings">
                            <div class="empty-stars"></div>
                            <div class="full-stars" style="width:{{App\Models\Rating::ratings($shop->id)}}%"></div>
                        </div>
                    </div> --}}
                    <h5 class="price"> <small>{{ "Phone Number" }}</small> {{ $shop->shop_number}}</h5>
                        <h4 class="price">{{ $shop->showName() }}</h4>
                        <div class="item-cart-area">
                            @if($shop->product_type == "affiliate")
                                <span class="add-to-cart-btn affilate-btn"
                                    data-href="{{ route('affiliate.product', $shop->slug) }}"><i class="icofont-cart"></i>
                                    {{ $langg->lang251 }}
                                </span>
                            @else
                                {{-- @if($shop->emptyStock())
                                <span class="add-to-cart-btn cart-out-of-stock">
                                    <i class="icofont-close-circled"></i> {{ $langg->lang78 }}
                                </span>
                                @else
                                <span class="add-to-cart add-to-cart-btn" data-href="{{ route('product.cart.add',$shop->id) }}">
                                    <i class="icofont-cart"></i> {{ $langg->lang56 }}
                                </span>
                                <span class="add-to-cart-quick add-to-cart-btn"
                                    data-href="{{ route('product.cart.quickadd',$shop->id) }}">
                                    <i class="icofont-cart"></i> {{ $langg->lang251 }}
                                </span>
                                @endif --}}
                            @endif
                        </div>
                </div>
            </a>

        </div>
    @endforeach
@endif



