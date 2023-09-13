

		<li>
			<div class="single-box">
				<div class="left-area">
					{{-- <img class="front-index-hot-and-new-item-img" src="{{ $prod->thumbnail ? asset('assets/images/thumbnails/'.$prod->thumbnail):asset('assets/images/noimage.png') }}" alt=""> --}}
					<img class="front-index-hot-and-new-item-img" src="{{ $prod->show_thumbnail() }}" alt="">
				</div>
				<div class="right-area">
						<div class="stars">
							<div class="ratings">
								<div class="empty-stars"></div>
								<div class="full-stars" style="width:{{App\Models\Rating::ratings($prod->id)}}%"></div>
							</div>
							</div>
							<h4 class="price">{{ $prod->showPrice() }} @if ($prod->price < $prod->previous_price) <del>{{ $prod->showPreviousPrice() }}</del> @endif </h4>
                            @if ($prod->price_shopping_point > 0)
                                <h4 class="price">+ SP {{ number_format($prod->price_shopping_point) }} <small>{{ $prod->percent_shopping_point }}%</small></h4>
                            @endif
							<p class="text"><a href="{{ route('front.product',$prod->slug) }}">{{ mb_strlen($prod->name,'utf-8') > 35 ? mb_substr($prod->name,0,35,'utf-8').'...' : $prod->name }}</a></p>
				</div>
			</div>
		</li>




