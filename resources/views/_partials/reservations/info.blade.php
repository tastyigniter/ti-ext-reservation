<div class="d-flex">
    <div class="mr-3 flex-fill">
        <label class="form-label">
            @lang('igniter.reservation::default.label_reservation_id')
        </label>
        <h3>#{{ $formModel->reservation_id }}</h3>
    </div>
    <div class="mr-3 flex-fill text-center">
        <label class="form-label">
            @lang('igniter.reservation::default.label_table_name')
        </label>
        <h3>{{ $formModel->table_name }}</h3>
    </div>
    <div class="flex-fill text-center">
        <label class="form-label">
            @lang('igniter.reservation::default.label_guest')
        </label>
        <h3>{{ $formModel->guest_num }}</h3>
    </div>
</div>
