      <div class="row">

        @foreach($coupons as $couponn)
        <div class="col-md-6 col-lg-4">
              <div class="blog-box">
                <h4 class="blog-title" style="color: crimson">
                    Code: {{mb_strlen($couponn->code,'utf-8') > 50 ? mb_substr($couponn->code,0,50,'utf-8')."...":$couponn->code}}
                    <input type="hidden" name="code" id="code-copy{{ $couponn->id }}" value="{{ $couponn->code }}">
                </h4>
                <div class="blog-images">
                    <div class="img">
                        <img src="{{ asset('assets/images/coupons/coupon_default1.jpg') }}" class="img-fluid" alt="">
                        <div class="date d-flex justify-content-center">
                          <div class="box align-self-center">
                            <p>{{date('d', strtotime($couponn->start_date))}}</p>
                            <p>{{date('M', strtotime($couponn->start_date))}}</p>
                          </div>
                        </div>

                        <div class="date1 d-flex justify-content-center">
                            <div class="box align-self-center">
                              <p>{{date('d', strtotime($couponn->end_date))}}</p>
                              <p>{{date('M', strtotime($couponn->end_date))}}</p>
                            </div>
                          </div>

                          <div class="price d-flex justify-content-center">
                            <div class="box align-self-center">
                              <p>{{ $couponn->type == 0 ? $couponn->price.'%' : number_format($couponn->price).$curr->sign }}</p>
                            </div>
                          </div>
                    </div>
                </div>
                @if ($couponn->vendor_id > 0)
                    @php
                    $vendor = DB::table('users')->where('id',$couponn->vendor_id)->first();
                    @endphp
                    <div class="details">
                        <a href='{{ route('front.vendor',str_replace(' ', '-', $vendor->shop_name)) }}'>
                        <h4 class="blog-title">

                            {{mb_strlen($vendor->name,'utf-8') > 50 ? mb_substr($vendor->name,0,50,'utf-8')."...":$vendor->name}}
                        </h4>
                        </a>
                    <p class="blog-text">
                    </p>
                    <a class="copy-btn coupon-front-btn" data-val="{{ $couponn->id }}" href="javascript:;">Copy</a>
                    <a href="{{ route('front.vendor',str_replace(' ', '-', $vendor->shop_name)) }}" class="view-stor coupon-front-btn">{{ $langg->lang249 }}</a>
                    </div>
                @else
                    <div class="details">
                    <p class="blog-text">
                    </p>
                    <a class="copy-btn coupon-front-btn" data-val="{{ $couponn->id }}" href="javascript:;">Copy</a>
                    </div>
                @endif
            </div>
        </div>


        @endforeach

      </div>

        <div class="page-center">
          {!! $coupons->links() !!}
        </div>

