<div class="table-responsive">
    <table class="table table-borderless">
        <tr>
            <td><b>@lang('admin::lang.column_id'):</b></td>
            <td>{{ $customerReservation->reservation_id }}</td>
        </tr>
        <tr>
            <td><b>@lang('igniter.reservation::default.reservations.column_status'):</b></td>
            <td>
                <span style="color:{{$customerReservation->status_color}};">{{ $customerReservation->status_name }}</span>
            </td>
        </tr>
        <tr>
            <td><b>@lang('igniter.reservation::default.reservations.column_date'):</b></td>
            <td>
                {{ $customerReservation->reserve_date->setTimeFromTimeString($customerReservation->reserve_time)->isoFormat($reservationDateTimeFormat) }}
            </td>
        </tr>
        <tr>
            <td><b>@lang('igniter.reservation::default.reservations.column_table'):</b></td>
            <td>{{ implode(', ', $customerReservation->tables->pluck('table_name')->all()) }}</td>
        </tr>
        <tr>
            <td><b>@lang('igniter.reservation::default.reservations.column_guest'):</b></td>
            <td>{{ $customerReservation->guest_num }}</td>
        </tr>
        <tr>
            <td><b>@lang('igniter.reservation::default.reservations.column_location'):</b></td>
            <td>
                {{ $customerReservation->location->location_name }}<br />
                {{ format_address($customerReservation->location->getAddress()) }}
            </td>
        </tr>
        <tr>
            <td><b>@lang('admin::lang.label_name'):</b></td>
            <td>{{ $customerReservation->first_name}}{{ $customerReservation->last_name }}</td>
        </tr>
        <tr>
            <td><b>@lang('admin::lang.label_email'):</b></td>
            <td>{{ $customerReservation->email }}</td>
        </tr>
        <tr>
            <td><b>@lang('igniter.reservation::default.reservations.column_telephone'):</b></td>
            <td>{{ $customerReservation->telephone }}</td>
        </tr>
        <tr>
            <td><b>@lang('igniter.reservation::default.reservations.column_comment'):</b></td>
            <td>{{ $customerReservation->comment }}</td>
        </tr>
    </table>
</div>
@if ($__SELF__->showCancelButton())
    <hr>
    <div class="mt-3 text-center">
        @partial($__SELF__.'::cancel_modal')
    </div>
@endif
