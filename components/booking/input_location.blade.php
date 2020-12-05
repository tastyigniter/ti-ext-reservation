<select
    name="location"
    id="locationSelect"
    class="form-control"
>
    @foreach ($__SELF__->getLocations() as $key => $value)
        <option
            value="{{ $key }}"
            {!! $key == $bookingLocation->getKey() ? 'selected="selected"' : '' !!}
        >{{ $value }}</option>
    @endforeach
</select>
