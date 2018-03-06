<?= form_open($booking->getFormAction(),
    [
        'id'      => 'picker-form',
        'role'    => 'form',
        'method'  => 'GET',
    ]
); ?>

<input type="hidden" name="hash" value="<?= $booking->uniqueHash ?>">

<div class="row wrap-vertical">
    <div class="col-xs-12 col-sm-3 wrap-none">
        <div class="form-group <?= (form_error('location')) ? 'has-error' : ''; ?>">
            <label class="sr-only" for="location">
                <?= lang('sampoyigi.reservation::default.label_location'); ?>
            </label>
            <select
                name="location"
                id="location"
                class="form-control"
            >
                <?php foreach ($booking->getLocations() as $key => $value) { ?>
                    <option
                        value="<?= $key; ?>"
                        <?= set_select('location', $key); ?>
                    ><?= e($value); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-xs-12 col-sm-2 wrap-none">
        <div class="form-group <?= (form_error('guest')) ? 'has-error' : ''; ?>">
            <label class="sr-only" for="noOfGuests">
                <?= lang('sampoyigi.reservation::default.label_guest_num'); ?>
            </label>
            <select
                name="guest"
                id="noOfGuests"
                class="form-control"
            >
                <?php foreach ($booking->getGuestSizeOptions() as $key => $value) { ?>
                    <option
                        value="<?= $key; ?>"
                        <?= set_select('guest', $key); ?>
                    ><?= e($value); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-xs-12 col-sm-3 wrap-none">
        <div class="form-group <?= (form_error('date')) ? 'has-error' : ''; ?>">
            <label class="sr-only" for="date">
                <?= lang('sampoyigi.reservation::default.label_date'); ?>
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
                    value="<?= set_value('date', $selectedDate); ?>"
                >
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-2 wrap-none">
        <div class="form-group <?= (form_error('time')) ? 'has-error' : ''; ?>">
            <label class="sr-only" for="time">
                <?= lang('sampoyigi.reservation::default.label_time'); ?>
            </label>
            <select
                name="time"
                id="time"
                class="form-control"
            >
                <?php foreach ($booking->getTimePickerOptions() as $key => $value) { ?>
                    <option
                        value="<?= $key; ?>"
                        <?= set_select('time', $key); ?>
                    ><?= e($value); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="col-xs-12 col-sm-2 wrap-none">
        <button
            type="submit"
            class="btn btn-primary btn-block"
        ><?= lang('sampoyigi.reservation::default.button_find_table'); ?></button>
    </div>
</div>
<div class="row">
    <?= form_error('location', '<span class="text-danger">', '</span>'); ?>
    <?= form_error('guest', '<span class="text-danger">', '</span>'); ?>
    <?= form_error('date', '<span class="text-danger">', '</span>'); ?>
    <?= form_error('time', '<span class="text-danger">', '</span>'); ?>
</div>

<?= form_close(); ?>
