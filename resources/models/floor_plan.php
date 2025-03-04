<?php

return [
    'floor_plan' => [
        'filter' => [
            'scopes' => [
                'dining_area' => [
                    'label' => 'lang:igniter.reservation::default.text_filter_dining_area',
                    'type' => 'select',
                    'modelClass' => \Igniter\Reservation\Models\DiningArea::class,
                    'nameFrom' => 'name',
                    'scope' => 'whereHasDiningArea',
                ],
                'reserve_date' => [
                    'label' => 'lang:igniter.reservation::default.text_filter_date',
                    'type' => 'date',
                    'conditions' => "reserve_date = DATE(':filtered')",
                ],
                'reserve_time' => [
                    'label' => 'lang:igniter.reservation::default.text_filter_time',
                    'type' => 'select',
                    'options' => [\Igniter\Reservation\Models\Reservation::class, 'getReserveTimeOptions'],
                    'scope' => 'whereBetweenStayTime',
                ],
                'assignee' => [
                    'label' => 'lang:igniter.reservation::default.text_filter_assignee',
                    'type' => 'select',
                    'scope' => 'filterAssignedTo',
                    'options' => [
                        1 => 'lang:admin::lang.statuses.text_unassigned',
                        2 => 'lang:admin::lang.statuses.text_assigned_to_self',
                        3 => 'lang:admin::lang.statuses.text_assigned_to_others',
                    ],
                ],
                'status' => [
                    'label' => 'lang:admin::lang.text_filter_status',
                    'type' => 'selectlist',
                    'conditions' => 'status_id IN(:filtered)',
                    'modelClass' => \Igniter\Admin\Models\Status::class,
                    'options' => 'getDropdownOptionsForReservation',
                ],
            ],
        ],
        'toolbar' => [
            'context' => 'floor_plan',
            'buttons' => [
                'create' => [
                    'label' => 'lang:admin::lang.button_new',
                    'class' => 'btn btn-primary',
                    'href' => 'reservations/create',
                ],
                'list' => [
                    'label' => 'lang:igniter.reservation::default.text_view_floor_plan',
                    'class' => 'btn btn-default',
                    'type' => 'dropdown',
                    'context' => 'floor_plan',
                    'menuItems' => [
                        'list' => [
                            'label' => 'igniter.reservation::default.text_view_list',
                            'class' => 'dropdown-item',
                            'href' => 'reservations',
                        ],
                        'calendar' => [
                            'label' => 'igniter.reservation::default.text_view_calendar',
                            'class' => 'dropdown-item',
                            'href' => 'reservations/calendar',
                        ],
                        'floor_plan' => [
                            'label' => 'igniter.reservation::default.text_view_floor_plan',
                            'class' => 'dropdown-item',
                            'href' => 'reservations/floor_plan',
                        ],
                    ],
                ],
            ],
        ],
        'columns' => [
            'edit' => [
                'type' => 'button',
                'iconCssClass' => 'fa fa-pencil',
                'attributes' => [
                    'class' => 'btn btn-edit',
                    'href' => 'reservations/edit/{reservation_id}',
                ],
            ],
            'reserve_time' => [
                'label' => '',
                'type' => 'partial',
                'path' => 'reservations/lists/floor_plan_column',
                'sortable' => false,
            ],
            'status_name' => [
                'label' => 'lang:admin::lang.label_status',
                'relation' => 'status',
                'select' => 'status_name',
                'type' => 'partial',
                'path' => 'statuses/form/status_column',
                'sortable' => false,
            ],
        ],
    ],
];
