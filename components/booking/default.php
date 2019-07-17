<div data-control="booking">
    <?php if ($__SELF__->pickerStep == 'info') { ?>
        <?= partial('@info') ?>

        <?= partial('@booking_form') ?>

    <?php } else if ($__SELF__->pickerStep == 'timeslot') { ?>
        <h1 class="h3"><?= lang('igniter.reservation::default.text_time_heading'); ?></h1>

        <?= partial('@timeslot') ?>
    <?php } else { ?>
        <h1 class="h3"><?= lang('igniter.reservation::default.text_heading'); ?></h1>

        <?= partial('@picker_form') ?>
    <?php } ?>
</div>