      <div class="row">
        @php
        $month = ['Jan' => 'Tháng 1', 'Feb' => 'Tháng 2', 'Mar' => 'Tháng 3', 'Apr' => 'Tháng 4', 'May' => 'Tháng 5', 'Jun' => 'Tháng 6', 'Jul' => 'Tháng 7'
                    , 'Aug' => 'Tháng 8', 'Sep' => 'Tháng 9', 'Oct' => 'Tháng 10', 'Nov' => 'Tháng 11', 'Dec' => 'Tháng 12'];
        @endphp
        @foreach($preferrers as $pre)
        <div class="col-md-6 col-lg-4">
              <div class="blog-box">
                <div class="blog-images">
                    <div class="img">
                        {{-- <img src="{{ $pre->photo ? asset('assets/images/users/'.$pre->photo):asset('assets/images/noimage.png') }}" class="img-fluid img1" alt=""> --}}
                        <img src="{{ $pre->show_photo() }}" class="img-fluid img1" alt="">
                    <div class="date d-flex justify-content-center">
                      <div class="box align-self-center">
                        <p>{{date('d', strtotime($pre->preferred_at))}}</p>
                        <p>{{ $month[date('M', strtotime($pre->preferred_at))]}}</p>
                      </div>
                    </div>
                    </div>
                </div>
                <div class="details">
                    <a href="{{ route('front.vendor',str_replace(' ', '-', $pre->shop_name)) }}">
                      <h4 class="blog-title">
                        {{mb_strlen($pre->shop_name,'utf-8') > 50 ? mb_substr($pre->shop_name,0,50,'utf-8')."...":$pre->shop_name}}
                      </h4>
                    </a>
                  <p class="blog-text">
                    {{substr(strip_tags($pre->shop_details),0,120)}}
                  </p>
                  <a class="read-more-btn" href="{{ route('front.vendor',str_replace(' ', '-', $pre->shop_name)) }}">Xem Shop</a>
                </div>
            </div>
        </div>


        @endforeach

      </div>

        <div class="page-center">
          {!! $preferrers->links() !!}
        </div>
