<div id="reservation-box">
    <form
        method="GET"
        accept-charset="utf-8"
        id="find-table-form"
        role="form"
    >
        <div class="panel panel-default panel-find-table">
            <div class="panel-heading">
                <h3 class="panel-title"><?= lang('text_heading'); ?></h3>
            </div>

            <div class="panel-body">
                <p><?= lang('text_find_msg'); ?></p>

                <div class="col-xs-12 col-sm-8">
                    <div class="col-xs-12 col-sm-3 wrap-none">
                        <div class="form-group <?= (form_error('location')) ? 'has-error' : ''; ?>">
                            <label class="sr-only" for="location"><?= lang('label_location'); ?></label>
                            <select name="location" id="location" class="form-control">
                                <?php foreach ($reservation->locations as $location) { ?>
                                    <option
                                        value="<?= $location['id']; ?>"
                                        <?= set_select('location', $location['id'], ($location['id'] == $location_id)); ?>
                                    ><?= $location['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3 wrap-none">
                        <div class="form-group <?= (form_error('guest_num')) ? 'has-error' : ''; ?>">
                            <label class="sr-only" for="guest-num"><?= lang('label_guest_num'); ?></label>
                            <?php if ($reservation->guest_numbers) { ?>
                                <select name="guest_num" id="guest-num" class="form-control">
                                    <?php foreach ($reservation->guest_numbers as $key => $value) { ?>
                                        <option
                                            value="<?= $value; ?>"
                                            <?= set_select('guest_num', $value, ($value == $guest_num)); ?>
                                        ><?= $value; ?></option>
                                    <?php } ?>
                                </select>
                            <?php }
                            else { ?>
                                <span><?= lang('text_no_table'); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3 wrap-none">
                        <div class="form-group <?= (form_error('reserve_date')) ? 'has-error' : ''; ?>">
                            <label class="sr-only" for="date"><?= lang('label_date'); ?></label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    name="reserve_date"
                                    id="date"
                                    class="form-control"
                                    value="<?= set_value('reserve_date', $reservation->reserve_date); ?>"/>
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3 wrap-none">
                        <div class="form-group <?= (form_error('reserve_time')) ? 'has-error' : ''; ?>">
                            <label class="sr-only" for="time"><?= lang('label_time'); ?></label>
                            <?php if ($reservationTimes = $reservation->listAvailableTimes()) { ?>
                                <select name="reserve_time" id="time" class="form-control">
                                    <?php foreach ($reservationTimes as $key => $value) { ?>
                                        <option
                                            value="<?= $value; ?>"
                                            <?= set_select('reserve_time', $value, ($value == $time)); ?>
                                        ><?= $value; ?></option>
                                    <?php } ?>
                                </select>
                            <?php }
                            else { ?>
                                <br/><?= lang('text_location_closed'); ?>
                            <?php } ?>
                        </div>
                    </div>
                    <?= form_error('location', '<span class="text-danger">', '</span>'); ?>
                    <?= form_error('guest_num', '<span class="text-danger">', '</span>'); ?>
                    <?= form_error('reserve_date', '<span class="text-danger">', '</span>'); ?>
                    <?= form_error('reserve_time', '<span class="text-danger">', '</span>'); ?>
                </div>

                <div class="col-xs-10 col-sm-2 wrap-none">
                    <button
                        type="submit"
                        class="btn btn-primary btn-block"
                    ><?= lang('button_find_table'); ?></button>
                </div>

                <div class="col-xs-2 col-sm-2 text-right">
                    <a
                        class="btn btn-default"
                        href="<?= site_url('reservation'); ?>"
                    ><?= lang('button_reset'); ?></a>
                </div>
            </div>
        </div>
    </form>

</div>
<script type="text/javascript"><!--
    //    $(document).ready(function () {
    //        $('#check-postcode').on('click', function () {
    //            $('.check-local').fadeIn()
    //            $('.display-local').fadeOut()
    //        })
    //
    //        $('#date').datepicker({
    //            <?//= "format: '{$date_format}'" ?>
    //        })
    //
    //        if ($('input[name="action"]').val() == 'view_summary') {
    //            $('html,body').animate({scrollTop: $("#reservation-box > .container").offset().top}, 'slow')
    //        }
    //
    //    })
    //
    //    function backToFind() {
    //        $('input[name="action"]').val('find_table')
    //        $('#find, .panel-find-table').fadeIn()
    //        $('.panel-time-slots').fadeOut().empty()
    //        $('#reservation-alert .alert p').fadeOut()
    //    }
    //
    //    function backToTime() {
    //        $('input[name="action"]').val('select_time')
    //        $('#find, .panel-time-slots').fadeIn()
    //        $('.panel-find-table').fadeOut()
    //        $('.panel-summary').fadeOut()
    //        $('#reservation-alert .alert p').fadeOut()
    //    }
    //--></script>