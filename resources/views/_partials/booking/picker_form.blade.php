{!! form_open($__SELF__->getFormAction(), [
    'id' => 'picker-form',
    'role' => 'form',
    'method' => 'GET',
]) !!}
<input type="hidden" name="picker_step" value="2">
<input type="hidden" name="location" value="{{ optional($__SELF__->location)->getKey() }}">

<div class="form-row align-items-center progress-indicator-container">
    @if ($useCalendarView)
        <div class="col-md-9 pr-md-4">
            <div
                data-control="datepicker"
                data-start-date="{{ $__SELF__->getStartDate()->format('Y-m-d') }}"
                data-end-date="{{ $__SELF__->getEndDate()->format('Y-m-d') }}"
                data-days-of-week-disabled='@json($disabledDaysOfWeek ?? [])'
                data-dates-disabled='@json($disabledDates ?? [])'
                data-week-start="{{ $weekStartOn }}"
                data-format="yyyy-mm-dd"
                data-language={{ setting('default_language') }}
            ></div>
            <input type="hidden" name="date" value="{{ $__SELF__->getSelectedDate()->format('Y-m-d') }}"/>
        </div>
        <div class="col-md-3" id="ti-datepicker-options">
            <div class="form-group">
                <label for="noOfGuests">@lang('igniter.reservation::default.label_guest_num')</label>
                @partial('@input_guest')
            </div>
            <div class="form-group">
                <label for="time">@lang('igniter.reservation::default.label_time')</label>
                @partial('@input_time')
            </div>
            @if (count($timeOptions))
                <div class="form-group">
                    <button
                        type="submit"
                        class="btn btn-primary btn-block"
                    >@lang('igniter.reservation::default.button_find_table')</button>
                </div>
            @endif
        </div>
    @else
        <div class="col-sm-2 mb-3">
            <label
                class="sr-only"
                for="noOfGuests"
            >@lang('igniter.reservation::default.label_guest_num')</label>
            @partial('@input_guest')
        </div>
        <div class="col-sm-3 mb-3">
            <label
                class="sr-only"
                for="date"
            >@lang('igniter.reservation::default.label_date')</label>
            @partial('@input_date')
        </div>
        <div class="col-sm-2 mb-3">
            <label
                class="sr-only"
                for="time"
            >@lang('igniter.reservation::default.label_time')</label>
            @partial('@input_time')
        </div>
        <div class="col-sm-2 mb-3">
            <button
                type="submit"
                class="btn btn-primary btn-block"
            ><?= lang('igniter.reservation::default.button_find_table'); ?></button>
        </div>
    @endif
</div>
<div class="form-row">
    <div class="col">
        {!! form_error('guest', '<span class="help-block text-danger">', '</span>') !!}
        {!! form_error('date', '<span class="help-block text-danger">', '</span>') !!}
        {!! form_error('time', '<span class="help-block text-danger">', '</span>') !!}
    </div>
</div>

{!! form_close() !!}
