<?= form_open($__SELF__->getFormAction(),
    [
        'id' => 'picker-form',
        'role' => 'form',
        'method' => 'GET',
    ]
); ?>
<input type="hidden" name="picker_step" value="2">
<input type="hidden" name="location" value="<?= $__SELF__->location->getKey(); ?>">

<div class="form-row align-items-center progress-indicator-container">
    <div class="col-md-9 pr-md-4">
        <div data-control="datepicker" data-date="<?= $__SELF__->getSelectedDate()->format('Y-m-d') ?>"></div>
        <input type="hidden" name="date" value="<?= $__SELF__->getSelectedDate()->format('Y-m-d') ?>"/>
    </div>
    <div class="col-md-3" id="ti-datepicker-options">
        <div class="form-group pt-4">
            <label for="locationSelect">
                <?= lang('igniter.reservation::default.label_location'); ?>
            </label>
            <select
                name="location"
                id="locationSelect"
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
        <div class="form-group">
            <label for="noOfGuests">
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
        <div class="form-group">
            <label for="time">
                <?= lang('igniter.reservation::default.label_time'); ?>
            </label>
            <select
                name="time"
                id="time"
                class="form-control"
            >
                <?php foreach ($timeOptions as $key => $value) { ?>
                    <option
                        value="<?= $value->rawTime; ?>"
                        <?= set_select('time', $value->rawTime); ?>
                        <?= $value->fullyBooked ? 'disabled="disabled"' : '' ?>
                    ><?= e($value->time); ?></option>
                <?php } ?>
            </select>
        </div>
        <?php if (count($timeOptions)) { ?>
            <div class="form-group">
                <button
                    type="submit"
                    class="btn btn-primary btn-block"
                ><?= lang('igniter.reservation::default.button_find_table'); ?></button>
            </div>
        <?php } ?>
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
