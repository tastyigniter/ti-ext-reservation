<p>
    {!! sprintf(lang('igniter.reservation::default.text_greetings'),
        e($reservation->first_name).' '.e($reservation->last_name)) !!}
</p>

<p>
    {!! sprintf(lang('igniter.reservation::default.text_success_message'),
        e($reservation->location->location_name),
        e($reservation->guest_num),
        e($reservation->reservation_datetime->isoFormat($bookingDateTimeFormat))) !!}
</p>
