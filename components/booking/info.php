<div class="form-row mb-4">
    <div class="col-sm-2">
        <?php if ($bookingLocation->hasMedia()) { ?>
            <img class="img-responsive img-rounded" src="<?= $bookingLocation->getThumb(); ?>">
        <?php } ?>
    </div>
    <div class="col-sm-3">
        <h5 class="text-muted"><?= lang('igniter.reservation::default.label_guest_num'); ?></h5>
        <h4 class="font-weight-normal"><?= $__SELF__->getGuestSizeOptions($guestSize); ?></h4>
    </div>
    <div class="col-sm-2">
        <h5 class="text-muted"><?= lang('igniter.reservation::default.label_date'); ?></h5>
        <h4 class="font-weight-normal"><?= $selectedDate->format('d M'); ?></h4>
    </div>
    <div class="col-sm-2">
        <h5 class="text-muted"><?= lang('igniter.reservation::default.label_time'); ?></h5>
        <h4 class="font-weight-normal"><?= $selectedDate->format($bookingTimeFormat); ?></h4>
    </div>
    <div class="col-sm-3">
        <h5 class="text-muted"><?= lang('igniter.reservation::default.label_location'); ?></h5>
        <h4 class="font-weight-normal"><?= $bookingLocation->getName(); ?></h4>
    </div>
</div>