<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= lang('text_time_heading'); ?></h3>
    </div>

    <div class="panel-body">
        <p class="text-uppercase"><?= sprintf(lang('text_time_msg'), mdate('%l, %F %j, %Y', strtotime($date)), $guest_num); ?></p>

        <?php if ($time_slots) { ?>
            <div id="time-slots" class="col-xs-12 col-sm-8 wrap-none">
                <div class="btn-group" data-toggle="buttons">
                    <?php foreach ($time_slots as $key => $slot) { ?>
                        <?php if ($slot['time'] == $time) { ?>
                            <label class="btn btn-default col-xs-4 col-sm-2 active <?= $slot['state']; ?>"
                                   data-btn="btn-primary">
                                <input type="radio"
                                       name="selected_time"
                                       id="reserve_time<?= $key; ?>"
                                       value="<?= $slot['time']; ?>" <?= set_radio('selected_time', $slot['time'], TRUE); ?>/><?= $slot['formatted_time']; ?>
                            </label>
                        <?php }
                        else { ?>
                            <label class="btn btn-default col-xs-4 col-sm-2 <?= $slot['state']; ?>"
                                   data-btn="btn-primary">
                                <input type="radio"
                                       name="selected_time"
                                       id="reserve_time<?= $key; ?>"
                                       value="<?= $slot['time']; ?>" <?= set_radio('selected_time', $slot['time']); ?>/><?= $slot['formatted_time']; ?>
                            </label>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>

            <div class="col-xs-8 col-sm-2 wrap-none">
                <button type="submit"
                        class="btn btn-primary btn-block"><?= lang('button_select_time'); ?></button>
            </div>

            <div class="col-xs-4 col-sm-2">
                <a class="btn btn-default" onclick="backToFind()"><?= lang('button_back'); ?></a>
            </div>
        <?php }
        else { ?>
            <div class="col-xs-6 wrap-none"><?= lang('text_no_time_slot'); ?></div>

            <div class="col-xs-6">
                <a class="btn btn-default" onclick="backToFind()"><?= lang('button_back'); ?></a>
            </div>
        <?php } ?>

        <?= form_error('selected_time', '<span class="text-danger">', '</span>'); ?>
    </div>
</div>
