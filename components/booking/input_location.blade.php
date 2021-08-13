<select
    name="location"
    id="locationSelect"
    class="form-control"
>
    @foreach ($__SELF__->getLocations() as $key => $value)
        <option
            value="{{ $key }}"
            data-url="{{ page_url($__SELF__->property('bookingPage'), ['location' => $key]) }}"
            {!! $key == $bookingLocation->permalink_slug ? 'selected="selected"' : '' !!}
        >{{ $value }}</option>
    @endforeach
</select>
