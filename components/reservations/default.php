<?php if ($reservationIdParam) { ?>
    <?= partial('@preview') ?>
<?php } else { ?>
    <?= partial('@list') ?>
<?php } ?>
