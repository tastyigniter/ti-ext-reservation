<p>
    <?= sprintf(lang('sampoyigi.reservation::default.text_greetings'),
        $reservation->first_name.' '.$reservation->last_name); ?>
</p>

<p>
    <?= sprintf(lang('sampoyigi.reservation::default.text_success_message'),
        $reservation->location->location_name,
        $reservation->guest_num,
        $reservation->reservation_datetime->format($bookingDateTimeFormat)); ?>
</p>
