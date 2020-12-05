<p>
    {{ sprintf(lang('igniter.reservation::default.text_time_msg'), $longDateTime, $guestSize) }}
</p>

{!! form_open($__SELF__->getFormAction(), [
    'id' => 'picker-form',
    'role' => 'form',
    'method' => 'GET',
]) !!}
<input type="hidden" name="picker_step" value="2">
<input type="hidden" name="location" value="{{ $__SELF__->location->getKey() }}">
<input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">
<input type="hidden" name="time" value="{{ $selectedDate->format('H:i') }}">
<input type="hidden" name="guest" value="{{ $guestSize }}">

@if (count($timeSlots = $__SELF__->getReducedTimeSlots()))
    @foreach ($timeSlots as $key => $slot)
        <button
            type="{{ !$slot->fullyBooked ? 'submit' : 'button' }}"
            name="sdateTime"
            value="{{ $selectedDate->format('Y-m-d').' '.$slot->rawTime }}"
            class="timeslot btn btn-primary d-block d-sm-inline-block{{ $slot->fullyBooked ? ' disabled' : '' }}"
        >{{ $slot->time }}</button>
    @endforeach
@else
    @lang('igniter.reservation::default.text_no_time_slot')
@endif

<div class="form-row">
    <div class="col">
        {!! form_error('sdateTime', '<span class="help-block text-danger">', '</span>') !!}
    </div>
</div>

{!! form_close() !!}
