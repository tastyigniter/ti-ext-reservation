<div
    data-control="booking"
    data-datepicker-startdate="{{ isset($nextOpen) ? $nextOpen->format('Y-m-d') : null }}"
    data-datepicker-disableddaysofweek='@json($disabledDaysOfWeek ?? [])'
    data-datepicker-disableddates='@json($disabledDates ?? [])'
>
    @if ($__SELF__->pickerStep == 'info')
        @partial('@info')

        @partial('@booking_form')

    @else
        <h1 class="h3">@lang('igniter.reservation::default.text_heading')</h1>

        @partial('@picker_form')
    @endif
</div>