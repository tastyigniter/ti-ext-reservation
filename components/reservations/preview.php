<div class="table-responsive">
    <table class="table table-borderless">
        <tr>
            <td><b><?= lang('igniter.reservation::default.reservations.column_id'); ?>:</b></td>
            <td><?= $customerReservation->reservation_id; ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.reservation::default.reservations.column_date'); ?>:</b></td>
            <td>
                <?= $customerReservation->reserve_time; ?> -
                <?= day_elapsed($customerReservation->reserve_date); ?>
            </td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.reservation::default.reservations.column_table'); ?>:</b></td>
            <td><?= $customerReservation->related_table ? $customerReservation->related_table->table_name : null; ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.reservation::default.reservations.column_guest'); ?>:</b></td>
            <td><?= $customerReservation->guest_num; ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.reservation::default.reservations.column_location'); ?>:</b></td>
            <td>
                <?= $customerReservation->location->location_name; ?><br/>
                <?= format_address($customerReservation->location->getAddress()); ?>
            </td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.reservation::default.reservations.column_name'); ?>:</b></td>
            <td><?= $customerReservation->first_name; ?><?= $customerReservation->last_name; ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.reservation::default.reservations.column_email'); ?>:</b></td>
            <td><?= $customerReservation->email; ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.reservation::default.reservations.column_telephone'); ?>:</b></td>
            <td><?= $customerReservation->telephone; ?></td>
        </tr>
        <tr>
            <td><b><?= lang('igniter.reservation::default.reservations.column_comment'); ?>:</b></td>
            <td><?= $customerReservation->comment; ?></td>
        </tr>
    </table>
</div>
