<p>
    <?= sprintf(lang('igniter.reservation::default.text_time_msg'), $longDateTime, $guestSize); ?>
</p>

<?php if (count($timeSlots = $__SELF__->getTimeSlots())) { ?>
    <?php foreach ($timeSlots as $key => $slot) { ?>
        <a
            href="<?= !$slot->fullyBooked ? $slot->actionUrl : '' ?>"
            class="timeslot btn btn-primary d-block d-sm-inline-block<?= $slot->fullyBooked ? ' disabled' : '' ?>"
            data-control="timeslot"
            data-location="<?= $bookingLocation->location_id ?>"
            data-datetime="<?= $slot->rawTime ?>"
        ><?= $slot->time; ?></a>
    <?php } ?>
<?php } else { ?>
    <?= lang('igniter.reservation::default.text_no_time_slot'); ?>
<?php } ?>

<div class="form-row">
    <div class="col">
        <?= form_error('sdateTime', '<span class="help-block text-danger">', '</span>'); ?>
    </div>
</div>
