<div 
	data-control="booking"
	data-datepicker-startdate="<?= $nextOpen ? $nextOpen->format('Y-m-d') : null ?>"
	data-datepicker-disableddaysofweek='<?= json_encode($disabledDaysOfWeek ?? []); ?>'
	data-datepicker-disableddates='<?= json_encode($disabledDates ?? []); ?>'
>
    <?php if ($__SELF__->pickerStep == 'info') { ?>
        <?= partial('@info') ?>

        <?= partial('@booking_form') ?>

    <?php } else if ($__SELF__->pickerStep == 'timeslot') { ?>
        <h1 class="h3"><?= lang('igniter.reservation::default.text_time_heading'); ?></h1>

        <?= partial('@timeslot') ?>
    <?php } else if ($__SELF__->pickerStep == 'dateselect') { ?>
        <h1 class="h3"><?= lang('igniter.reservation::default.text_heading'); ?></h1>

        <?= partial('@dateselect') ?>
    <?php } else { ?>
        <h1 class="h3"><?= lang('igniter.reservation::default.text_heading'); ?></h1>

        <?= partial('@locations') ?>
    <?php } ?>
</div>