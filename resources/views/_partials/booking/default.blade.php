<div
    data-control="booking"
>
    @if ($__SELF__->pickerStep == 'info')
        @partial('@info')

        @partial('@booking_form')

    @elseif ($__SELF__->pickerStep == 'timeslot')
        <h1 class="h3">@lang('igniter.reservation::default.text_time_heading')</h1>

        @partial('@timeslot')
    @else
        <h1 class="h3">@lang('igniter.reservation::default.text_heading')</h1>

        @partial('@picker_form')
    @endif
</div>
