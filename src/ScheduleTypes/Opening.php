<?php

namespace Igniter\Reservation\ScheduleTypes;

use Igniter\Local\Classes\AbstractOrderType;

class Opening extends AbstractOrderType
{
    public function getOpenDescription(): string
    {
        return sprintf(
            lang('igniter.local::default.text_collection_time_info'),
            sprintf(lang('igniter.local::default.text_in_minutes'), $this->getLeadTime())
        );
    }

    public function getOpeningDescription(string $format): string
    {
        $starts = make_carbon($this->getSchedule()->getOpenTime());

        return sprintf(
            lang('igniter.local::default.text_collection_time_info'),
            sprintf(lang('igniter.local::default.text_starts'), '<b>'.$starts->isoFormat($format).'</b>')
        );
    }

    public function getClosedDescription(): string
    {
        return sprintf(
            lang('igniter.local::default.text_collection_time_info'),
            lang('igniter.local::default.text_is_closed')
        );
    }

    public function getDisabledDescription(): string
    {
        return lang('igniter.local::default.text_collection_is_disabled');
    }

    public function isActive(): bool
    {
        return $this->code === LocationFacade::orderType();
    }

    public function isDisabled(): bool
    {
        return !$this->model->hasCollection();
    }
}
