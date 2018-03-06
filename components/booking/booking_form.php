<?= form_open(current_url(),
    [
        'id'           => 'booking-form',
        'role'         => 'form',
        'method'       => 'POST',
        'data-request' => $bookingEventHandler,
    ]
); ?>

<p>
    <?= $customer
        ? sprintf(lang('main::default.reservation.text_logout'), $customer->first_name, site_url('account/logout'))
        : sprintf(lang('main::default.reservation.text_registered'), site_url('account/login'));
    ?>
</p>

<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <input
                type="text"
                name="first_name"
                id="first-name"
                class="form-control"
                placeholder="<?= lang('main::default.reservation.label_first_name'); ?>"
                value="<?= set_value('first_name', $reservation->first_name); ?>"
            />
            <?= form_error('first_name', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>

    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <input
                type="text"
                name="last_name"
                id="last-name"
                class="form-control"
                placeholder="<?= lang('main::default.reservation.label_last_name'); ?>"
                value="<?= set_value('last_name', $reservation->last_name); ?>"
            />
            <?= form_error('last_name', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-6 col-md-6">
        <div class="form-group">
            <input
                type="text"
                name="email"
                id="email"
                class="form-control"
                placeholder="<?= lang('main::default.reservation.label_email'); ?>"
                value="<?= set_value('email', $reservation->email); ?>"
            />
            <?= form_error('email', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-6">
        <div class="form-group">
            <input
                type="text"
                name="telephone"
                id="telephone"
                class="form-control"
                placeholder="<?= lang('main::default.reservation.label_telephone'); ?>"
                value="<?= set_value('telephone', $reservation->telephone); ?>"
            />
            <?= form_error('telephone', '<span class="text-danger">', '</span>'); ?>
        </div>
    </div>
</div>

<div class="form-group">
    <textarea
        name="comment"
        id="comment"
        class="form-control"
        rows="2"
        placeholder="<?= lang('main::default.reservation.label_comment'); ?>"
    ><?= set_value('comment', $reservation->comment); ?></textarea>
    <?= form_error('comment', '<span class="text-danger">', '</span>'); ?>
</div>

<button
    type="submit"
    class="btn btn-primary btn-block btn-lg"
><?= lang('main::default.reservation.button_reservation'); ?></button>

<?= form_close(); ?>
