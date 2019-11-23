<?php if ($customerReservation) { ?>
    <?php if ($showReviews AND !empty($reviewable)) { ?>
        <div class="mb-3">
            <?= partial('localReview::form') ?>
        </div>
    <?php } ?>

    <?= partial($__SELF__.'::preview') ?>
<?php } else { ?>
    <?= partial('@list') ?>
<?php } ?>
