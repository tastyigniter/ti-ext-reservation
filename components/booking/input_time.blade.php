<select
    name="time"
    id="time"
    class="form-select"
>
    @foreach ($timeOptions as $key => $value)
        @continue($value->fullyBooked)
        <option
            value="{{ $value->rawTime }}"
            {!! set_select('time', $value->rawTime) !!}
        >{{ $value->time }}</option>
    @endforeach
</select>
