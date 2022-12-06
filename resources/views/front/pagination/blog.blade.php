@php
$month = ['Jan' => 'Tháng 1', 'Feb' => 'Tháng 2', 'Mar' => 'Tháng 3', 'Apr' => 'Tháng 4', 'May' => 'Tháng 5', 'Jun' => 'Tháng 6', 'Jul' => 'Tháng 7'
            , 'Aug' => 'Tháng 8', 'Sep' => 'Tháng 9', 'Oct' => 'Tháng 10', 'Nov' => 'Tháng 11', 'Dec' => 'Tháng 12'];
@endphp
    <div class="row">

        @foreach($blogs as $blogg)
        <div class="col-md-6 col-lg-4">
              <div class="blog-box">
                <div class="blog-images">
                    <div class="img">
                    <img src="{{ $blogg->photo ? asset('assets/images/blogs/'.$blogg->photo):asset('assets/images/noimage.png') }}" class="img-fluid" alt="">
                    <div class="date d-flex justify-content-center">
                      <div class="box align-self-center">
                        <p>{{date('d', strtotime($blogg->created_at))}}</p>
                        <p>{{ $month[date('M', strtotime($blogg->created_at))]}}</p>
                      </div>
                    </div>
                    </div>
                </div>
                <div class="details">
                    <a href='{{route('front.blogshow',$blogg->id)}}'>
                      <h4 class="blog-title">
                        {{mb_strlen($blogg->title,'utf-8') > 50 ? mb_substr($blogg->title,0,50,'utf-8')."...":$blogg->title}}
                      </h4>
                    </a>
                  <p class="blog-text">
                    {{substr(strip_tags($blogg->details),0,120)}}
                  </p>
                  <a class="read-more-btn" href="{{route('front.blogshow',$blogg->id)}}">{{ $langg->lang38 }}</a>
                </div>
            </div>
        </div>


        @endforeach

      </div>

        <div class="page-center">
          {!! $blogs->links() !!}
        </div>
