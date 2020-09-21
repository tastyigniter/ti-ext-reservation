<div 
	data-control="booking"
	data-datepicker-startdate="<?= isset($nextOpen) ? $nextOpen->format('Y-m-d') : null ?>"
	data-datepicker-disableddaysofweek='<?= json_encode($disabledDaysOfWeek ?? []); ?>'
	data-datepicker-disableddates='<?= json_encode($disabledDates ?? []); ?>'
>
    <?php if ($__SELF__->pickerStep == 'info') { ?>
        <?= partial('@info') ?>

        <?= partial('@booking_form') ?>

    <?php } else { ?>
        <h1 class="h3"><?= lang('igniter.reservation::default.text_heading'); ?></h1>

        <?= partial('@dateselect') ?>
    <?php } ?>
</div>