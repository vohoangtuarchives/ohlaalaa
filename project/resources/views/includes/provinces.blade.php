<option value="">{{ $langg->lang893 }}</option>
@foreach (DB::table('provinces')->orderBy('name', 'asc')->get() as $data)
	<option value="{{ $data->id }}" {{ Auth::check() ? (Auth::user()->CityID == $data->id ? 'selected' : '') : '' }} >{{ $data->name }}</option>
@endforeach
