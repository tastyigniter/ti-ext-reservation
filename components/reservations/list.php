<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th><?= lang('igniter.reservation::default.reservations.column_id'); ?></th>
            <th><?= lang('igniter.reservation::default.reservations.column_status'); ?></th>
            <th><?= lang('igniter.reservation::default.reservations.column_location'); ?></th>
            <th><?= lang('igniter.reservation::default.reservations.column_date'); ?></th>
            <th><?= lang('igniter.reservation::default.reservations.column_table'); ?></th>
            <th><?= lang('igniter.reservation::default.reservations.column_guest'); ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($customerReservations)) { ?>
            <?php foreach ($customerReservations as $reservation) { ?>
                <tr>
                    <td>
                        <a
                            href="<?= site_url($reservationsPage, ['reservationId' => $reservation->reservation_id]); ?>"
                        >
                            <?= $reservation->reservation_id; ?>
                        </a>
                    </td>
                    <td><?= $reservation->status->status_name; ?></td>
                    <td><?= $reservation->location ? $reservation->location->location_name : null; ?></td>
                    <td><?= $reservation->reserve_time; ?> - <?= day_elapsed($reservation->reserve_date); ?></td>
                    <td><?= $reservation->related_table ? $reservation->related_table->table_name : null; ?></td>
                    <td><?= $reservation->guest_num; ?></td>
                    <td>
                        <a
                            title="<?= lang('igniter.reservation::default.reservations.text_leave_review'); ?>"
                            href="<?= site_url($addReviewsPage, [
                                'saleType' => 'reservation',
                                'saleId' => $reservation->reservation_id,
                            ]); ?>"
                        ><i class="fa fa-heart"></i></a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="9999"><?= lang('igniter.reservation::default.reservations.text_empty'); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<div class="pagination-bar text-right">
    <div class="links"><?= $customerReservations->links(); ?></div>
</div>
