<p>
    <?= sprintf(lang('sampoyigi.reservation::default.text_time_msg'), $longDateTime, $guestSize); ?>
</p>

<?php if (count($timeSlots = $booking->getTimeSlots())) { ?>
    <ul class="list-group list-inline">
        <?php foreach ($timeSlots as $key => $slot) { ?>
            <?php if (!$slot->fullyBooked) { ?>
                <li>
                    <a
                        href="<?= $slot->actionUrl ?>"
                        class="timeslot btn btn-primary"
                        data-control="timeslot"
                        data-location="<?= $bookingLocation->location_id ?>"
                        data-datetime="<?= $slot->rawTime ?>"
                    ><?= $slot->time; ?></a>
                </li>
            <?php } else { ?>
                <li>
                    <a class="timeslot btn btn-default unavailable"></a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
<?php } else { ?>
    <?= lang('sampoyigi.reservation::default.text_no_time_slot'); ?>
<?php } ?>
