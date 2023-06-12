<?php

namespace Igniter\Reservation\Subscribers;

use Igniter\Local\Events\LocationDefineOptionsFieldsEvent;
use Igniter\Local\Requests\LocationRequest;
use Igniter\System\Classes\FormRequest;
use Illuminate\Contracts\Events\Dispatcher;

class DefineOptionsFormFieldsSubscriber
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            LocationDefineOptionsFieldsEvent::class => 'handle',
            'system.formRequest.extendValidator' => 'handleValidation',
        ];
    }

    public function handle(LocationDefineOptionsFieldsEvent $event): array
    {
        return [
            'offer_reservation' => [
                'label' => 'lang:igniter.reservation::default.label_offer_reservation',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'default' => 1,
                'type' => 'switch',
                'span' => 'left',
            ],
            'auto_allocate_table' => [
                'label' => 'lang:igniter.reservation::default.label_auto_allocate_table',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'default' => 1,
                'type' => 'switch',
                'span' => 'right',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_reservation',
                    'condition' => 'checked',
                ],
            ],
            'reservation_time_interval' => [
                'label' => 'lang:igniter.reservation::default.label_reservation_time_interval',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'default' => 15,
                'type' => 'number',
                'span' => 'left',
                'comment' => 'lang:igniter.reservation::default.help_reservation_time_interval',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_reservation',
                    'condition' => 'checked',
                ],
            ],
            'reservation_stay_time' => [
                'label' => 'lang:igniter.reservation::default.label_reservation_stay_time',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'default' => 45,
                'type' => 'number',
                'span' => 'right',
                'comment' => 'lang:igniter.reservation::default.help_reservation_stay_time',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_reservation',
                    'condition' => 'checked',
                ],
            ],
            'min_reservation_advance_time' => [
                'label' => 'lang:igniter.reservation::default.label_min_reservation_advance_time',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'default' => 2,
                'type' => 'number',
                'span' => 'left',
                'comment' => 'lang:igniter.reservation::default.help_min_reservation_advance_time',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_reservation',
                    'condition' => 'checked',
                ],
            ],
            'max_reservation_advance_time' => [
                'label' => 'lang:igniter.reservation::default.label_max_reservation_advance_time',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'default' => 30,
                'type' => 'number',
                'span' => 'right',
                'comment' => 'lang:igniter.reservation::default.help_max_reservation_advance_time',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_reservation',
                    'condition' => 'checked',
                ],
            ],
            'limit_guests' => [
                'label' => 'lang:igniter.reservation::default.label_limit_guests',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'default' => 0,
                'type' => 'switch',
                'span' => 'left',
            ],
            'limit_guests_count' => [
                'label' => 'lang:igniter.reservation::default.label_limit_guests_count',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'default' => 20,
                'type' => 'number',
                'span' => 'right',
                'comment' => 'lang:igniter.reservation::default.help_limit_guests_count',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'limit_guests',
                    'condition' => 'checked',
                ],
            ],
            'reservation_cancellation_timeout' => [
                'label' => 'lang:igniter.reservation::default.label_reservation_cancellation_timeout',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'type' => 'number',
                'span' => 'left',
                'default' => 0,
                'comment' => 'lang:igniter.reservation::default.help_reservation_cancellation_timeout',
            ],
            'reservation_include_start_time' => [
                'label' => 'lang:igniter.reservation::default.label_reservation_include_start_time',
                'accordion' => 'lang:igniter.reservation::default.text_tab_reservation',
                'type' => 'switch',
                'span' => 'right',
                'default' => 1,
                'comment' => 'lang:igniter.reservation::default.help_reservation_include_start_time',
            ],
        ];
    }

    public function handleValidation(FormRequest $formRequest, object $dataHolder)
    {
        if (!$formRequest instanceof LocationRequest) {
            return;
        }

        $dataHolder->attributes = array_merge($dataHolder->attributes, [
            'options.limit_guests' => lang('igniter.reservation::default.label_limit_guests'),
            'options.limit_guests_count' => lang('igniter.reservation::default.label_limit_guests_count'),
            'options.reservation_time_interval' => lang('igniter.reservation::default.label_reservation_time_interval'),
            'options.reservation_stay_time' => lang('igniter.reservation::default.reservation_stay_time'),
            'options.auto_allocate_table' => lang('igniter.reservation::default.label_auto_allocate_table'),
            'options.min_reservation_advance_time' => lang('igniter.reservation::default.label_min_reservation_advance_time'),
            'options.max_reservation_advance_time' => lang('igniter.reservation::default.label_max_reservation_advance_time'),
            'options.reservation_cancellation_timeout' => lang('igniter.reservation::default.label_reservation_cancellation_timeout'),
        ]);

        $dataHolder->rules = array_merge($dataHolder->rules, [
            'options.limit_guests' => ['boolean'],
            'options.limit_guests_count' => ['integer', 'min:1', 'max:999'],
            'options.reservation_time_interval' => ['min:5', 'integer'],
            'options.reservation_stay_time' => ['min:5', 'integer'],
            'options.auto_allocate_table' => ['integer'],
            'options.min_reservation_advance_time' => ['integer', 'min:0', 'max:999'],
            'options.max_reservation_advance_time' => ['integer', 'min:0', 'max:999'],
            'options.reservation_cancellation_timeout' => ['integer', 'min:0', 'max:999'],
        ]);
    }
}
