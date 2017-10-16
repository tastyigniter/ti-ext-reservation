<div class="panel panel-default panel-summary"
     style="margin-bottom:0;display:<?= ($find_table_action === 'view_summary') ? 'block' : 'none'; ?>">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('text_reservation'); ?></h3>
    </div>

    <div class="panel-body">
        <div class="col-xs-12 col-sm-1 wrap-none">
            <img class="img-responsive" src="<?= $location_image; ?>">
        </div>
        <div class="col-xs-12 col-sm-2 wrap-none">
            <label class="text-muted text-uppercase small"><?= lang('label_guest_num'); ?></label><br/>
            <span class="form-control-static"><?= $guest_num; ?></span>
        </div>
        <div class="col-xs-12 col-sm-2 wrap-none">
            <label class="text-muted text-uppercase small"><?= lang('label_date'); ?></label><br/>
            <span class="form-control-static"><?= mdate(lang('text_date_format'), strtotime($date)); ?></span>
        </div>
        <div class="col-xs-12 col-sm-1 wrap-none">
            <label class="text-muted text-uppercase small"><?= lang('label_time'); ?></label><br/>
            <span class="form-control-static"><?= $time; ?></span>
        </div>
        <div class="col-xs-12 col-sm-4 wrap-none">
            <label class="text-muted text-uppercase small"><?= lang('label_location'); ?></label><br/>
            <span class="form-control-static text-">
                        <?php foreach ($locations as $location) { ?>
                            <?php if ($location['id'] == $location_id) { ?>
                                <?= $location['name']; ?>
                            <?php } ?>
                        <?php } ?>
                    </span>
        </div>
    </div>
</div>
