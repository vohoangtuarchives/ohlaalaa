<div class="docname" >
    <a class="select-location" data-val="0" id="provinceText10" style="color: {{ $selected_city_id == 0 ? 'red' : 'black' }}">
        {{-- <img src="{{ asset('assets/images/thumbnails/'.$prod->thumbnail) }}" alt=""> --}}
        <div class="search-content" id="provinceText0">
            {{-- <p>{{ $p->name }} </p> --}}
            <span style="font-size: 14px; font-weight:600; display:block;">Tất cả</span>
        </div>
    </a>
</div>
@foreach($provinces as $p)
	<div class="docname" >
		<a class="select-location" data-val="{{ $p->id }}" id="provinceText1{{ $p->id }}" style="color: {{ $selected_city_id == $p->id ? 'red' : 'black' }}">
			{{-- <img src="{{ asset('assets/images/thumbnails/'.$prod->thumbnail) }}" alt=""> --}}
			<div class="search-content">
				{{-- <p>{{ $p->name }} </p> --}}
				<span style="font-size: 14px; font-weight:600; display:block;">{{ $p->name }}</span>
			</div>
		</a>
	</div>
@endforeach
