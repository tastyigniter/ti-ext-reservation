<p>
    <?= sprintf(lang('igniter.reservation::default.text_greetings'),
        $reservation->first_name.' '.$reservation->last_name); ?>
</p>

<p>
    <?= sprintf(lang('igniter.reservation::default.text_success_message'),
        $reservation->location->location_name,
        $reservation->guest_num,
        $reservation->reservation_datetime->isoFormat($bookingDateTimeFormat)); ?>
</p>
