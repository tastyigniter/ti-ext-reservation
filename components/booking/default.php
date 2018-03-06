<div data-control="booking">
    <?php if ($booking->pickerStep == 'info') { ?>
        <h3><?= lang('sampoyigi.reservation::default.text_reservation'); ?></h3>

        <?= partial('@info') ?>

        <?= partial('@booking_form') ?>

    <?php } else if ($booking->pickerStep == 'timeslot') { ?>
        <h3><?= lang('sampoyigi.reservation::default.text_time_heading'); ?></h3>

        <?= partial('@timeslot') ?>
    <?php } else { ?>
        <h3><?= lang('sampoyigi.reservation::default.text_heading'); ?></h3>

        <?= partial('@picker_form') ?>
    <?php } ?>
</div>