<?= form_open($__SELF__->getFormAction(),
    [
        'id' => 'picker-form',
        'role' => 'form',
        'method' => 'GET',
    ]
); ?>

<input type="hidden" name="picker_step" value="2">
<input type="hidden" name="location" value="<?=  $__SELF__->location->getKey(); ?>">

<div class="form-row">
    <div class="col-sm-3 mb-3">
        <label class="sr-only" for="date">
            <?= lang('igniter.reservation::default.label_date'); ?>
        </label>
        <select
            name="date"
            id="date"
            class="form-control"
        >
            <?php foreach ($__SELF__->getDatePickerOptions() as $date) { ?>
                <option
                    value="<?= $date->format('Y-m-d'); ?>"
                    <?= set_select('date', $date->format('Y-m-d')); ?>
                ><?= $date->isoFormat($bookingDateFormat); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-sm-3 mb-3">
        <button
            type="submit"
            class="btn btn-primary btn-block"
        ><?= lang('igniter.reservation::default.button_find_table'); ?></button>
    </div>
</div>
<div class="form-row">
    <div class="col">
        <?= form_error('date', '<span class="help-block text-danger">', '</span>'); ?>
    </div>
</div>

<?= form_close(); ?>
