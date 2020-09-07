<?= form_open($__SELF__->getFormAction(),
    [
        'id' => 'picker-form',
        'role' => 'form',
        'method' => 'GET',
    ]
); ?>

<input type="hidden" name="picker_step" value="3">
<input type="hidden" name="location" value="<?= $__SELF__->location->getKey(); ?>">
<input type="hidden" name="date" value="<?= $__SELF__->getSelectedDate()->format('Y-m-d'); ?>">

<div class="form-row">
    <div class="col-sm-3 mb-3">
        <label class="sr-only" for="time">
            <?= lang('igniter.reservation::default.label_time'); ?>
        </label>
        <select
            name="time"
            id="time"
            class="form-control"
        >
            <?php foreach ($__SELF__->getTimePickerOptions() as $key => $value) { ?>
                <option
                    value="<?= $key; ?>"
                    <?= set_select('time', $key); ?>
                ><?= e($value); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-sm-3 mb-3">
        <label class="sr-only" for="noOfGuests">
            <?= lang('igniter.reservation::default.label_guest_num'); ?>
        </label>
        <select
            name="guest"
            id="noOfGuests"
            class="form-control"
        >
            <?php foreach ($__SELF__->getGuestSizeOptions() as $key => $value) { ?>
                <option
                    value="<?= $key; ?>"
                    <?= set_select('guest', $key); ?>
                ><?= e($value); ?></option>
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
        <?= form_error('time', '<span class="help-block text-danger">', '</span>'); ?>
        <?= form_error('guest', '<span class="help-block text-danger">', '</span>'); ?>
    </div>
</div>

<?= form_close(); ?>
