<?= form_open($__SELF__->getFormAction(),
    [
        'id' => 'picker-form',
        'role' => 'form',
        'method' => 'GET',
    ]
); ?>

<input type="hidden" name="hash" value="<?= $__SELF__->uniqueHash ?>">

<div class="form-row">
    <div class="col-sm-3 mb-3">
        <label class="sr-only" for="location">
            <?= lang('igniter.reservation::default.label_location'); ?>
        </label>
        <select
            name="location"
            id="location"
            class="form-control"
        >
            <?php foreach ($__SELF__->getLocations() as $key => $value) { ?>
                <option
                    value="<?= $key; ?>"
                    <?= set_select('location', $key); ?>
                ><?= e($value); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-sm-2 mb-3">
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
        <label class="sr-only" for="date">
            <?= lang('igniter.reservation::default.label_date'); ?>
        </label>
        <div class="input-group date">
            <input
                type="text"
                id="date"
                class="form-control"
                value=""
                data-control="booking-datepicker"
                data-format="<?= $bookingDateFormat ?>"
                data-value-element="[name='date']"
            />
            <input
                type="hidden"
                name="date"
                value="<?= set_value('date', $selectedDate->format('Y-m-d')); ?>"
            >
            <span class="input-group-addon">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
            </span>
        </div>
    </div>
    <div class="col-sm-2 mb-3">
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
    <div class="col-sm-2 mb-3">
        <button
            type="submit"
            class="btn btn-primary btn-block"
        ><?= lang('igniter.reservation::default.button_find_table'); ?></button>
    </div>
</div>
<div class="form-row">
    <div class="col">
        <?= form_error('location', '<span class="help-block text-danger">', '</span>'); ?>
        <?= form_error('guest', '<span class="help-block text-danger">', '</span>'); ?>
        <?= form_error('date', '<span class="help-block text-danger">', '</span>'); ?>
        <?= form_error('time', '<span class="help-block text-danger">', '</span>'); ?>
    </div>
</div>

<?= form_close(); ?>
