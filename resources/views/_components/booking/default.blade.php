<div
    data-control="booking"
>
    @if ($__SELF__->pickerStep == 'info')
        @themePartial('@info')

        @themePartial('@booking_form')

    @elseif ($__SELF__->pickerStep == 'timeslot')
        <h1 class="h3">@lang('igniter.reservation::default.text_time_heading')</h1>

        @themePartial('@timeslot')
    @else
        <h1 class="h3">@lang('igniter.reservation::default.text_booking_title')</h1>

        @themePartial('@picker_form')
    @endif
</div>
