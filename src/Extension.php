<?php

namespace Igniter\Reservation;

use Igniter\Admin\Models\StatusHistory;
use Igniter\Admin\Widgets\Form;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\Reservation\Classes\BookingManager;
use Igniter\Reservation\Listeners\MaxGuestSizePerTimeslotReached;
use Igniter\Reservation\Listeners\SendReservationConfirmation;
use Igniter\Reservation\Models\Concerns\LocationAction;
use Igniter\Reservation\Models\DiningArea;
use Igniter\Reservation\Models\DiningTable;
use Igniter\Reservation\Models\Observers\DiningTableObserver;
use Igniter\Reservation\Models\Observers\ReservationObserver;
use Igniter\Reservation\Models\Reservation;
use Igniter\Reservation\Models\Scopes\DiningTableScope;
use Igniter\Reservation\Models\Scopes\ReservationScope;
use Igniter\Reservation\Requests\ReservationSettingsRequest;
use Igniter\System\Models\Settings;
use Igniter\User\Http\Controllers\Customers;
use Igniter\User\Models\Customer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;

class Extension extends \Igniter\System\Classes\BaseExtension
{
    protected $listen = [
        'igniter.reservation.isFullyBookedOn' => [
            MaxGuestSizePerTimeslotReached::class,
        ],
        'igniter.reservation.confirmed' => [
            SendReservationConfirmation::class,
        ],
    ];

    protected $observers = [
        DiningTable::class => DiningTableObserver::class,
        Reservation::class => ReservationObserver::class,
    ];

    protected array $scopes = [
        DiningTable::class => DiningTableScope::class,
        Reservation::class => ReservationScope::class,
    ];

    public function register()
    {
        parent::register();

        $this->app->singleton(BookingManager::class);

        $this->registerSystemSettings();
    }

    public function boot()
    {
        $this->bindReservationEvent();

        Relation::enforceMorphMap([
            'reservations' => \Igniter\Reservation\Models\Reservation::class,
            'tables' => \Igniter\Reservation\Models\DiningTable::class,
            'dining_areas' => \Igniter\Reservation\Models\DiningArea::class,
        ]);

        Customers::extendFormFields(function (Form $form) {
            if (!$form->model instanceof Customer) {
                return;
            }

            $form->addTabFields([
                'reservations' => [
                    'tab' => 'lang:igniter.reservation::default.text_tab_reservations',
                    'type' => 'datatable',
                    'context' => ['edit', 'preview'],
                    'useAjax' => true,
                    'defaultSort' => ['reservation_id', 'desc'],
                    'columns' => [
                        'reservation_id' => [
                            'title' => 'lang:igniter::admin.column_id',
                        ],
                        'customer_name' => [
                            'title' => 'lang:igniter::admin.label_name',
                        ],
                        'status_name' => [
                            'title' => 'lang:igniter::admin.label_status',
                        ],
                        'table_name' => [
                            'title' => 'lang:igniter.reservation::default.column_table',
                        ],
                        'reserve_time' => [
                            'title' => 'lang:igniter.reservation::default.column_time',
                        ],
                        'reserve_date' => [
                            'title' => 'lang:igniter.reservation::default.column_date',
                        ],
                    ],
                ],
            ], 'primary');
        });

        LocationModel::implement(LocationAction::class);

        Location::extend(function (Location $model) {
            $model->relation['hasMany']['dining_areas'] = [DiningArea::class, 'delete' => true];
            $model->relation['morphedByMany']['tables'] = [DiningTable::class, 'name' => 'locationable'];
        });
    }

    public function registerComponents()
    {
        return [
            \Igniter\Reservation\Components\Booking::class => [
                'code' => 'booking',
                'name' => 'lang:igniter.reservation::default.text_component_title',
                'description' => 'lang:igniter.reservation::default.text_component_desc',
            ],
            \Igniter\Reservation\Components\Reservations::class => [
                'code' => 'accountReservations',
                'name' => 'lang:igniter.reservation::default.reservations.component_title',
                'description' => 'lang:igniter.reservation::default.reservations.component_desc',
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'igniter.reservation::mail.reservation' => 'lang:igniter.reservation::default.text_mail_reservation',
            'igniter.reservation::mail.reservation_alert' => 'lang:igniter.reservation::default.text_mail_reservation_alert',
            'igniter.reservation::mail.reservation_update' => 'lang:igniter.reservation::default.text_mail_reservation_update',
        ];
    }

    public function registerAutomationRules()
    {
        return [
            'events' => [
                'igniter.reservation.confirmed' => \Igniter\Reservation\AutomationRules\Events\NewReservation::class,
                'igniter.reservation.statusAdded' => \Igniter\Reservation\AutomationRules\Events\NewReservationStatus::class,
                'igniter.reservation.assigned' => \Igniter\Reservation\AutomationRules\Events\ReservationAssigned::class,
            ],
            'actions' => [],
            'conditions' => [
                \Igniter\Reservation\AutomationRules\Conditions\ReservationAttribute::class,
                \Igniter\Reservation\AutomationRules\Conditions\ReservationStatusAttribute::class,
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Admin.Tables' => [
                'label' => 'igniter.reservation::default.text_permission_tables',
                'group' => 'reservation',
            ],
            'Admin.Reservations' => [
                'label' => 'igniter.reservation::default.text_permission_reservations',
                'group' => 'reservation',
            ],
            'Admin.DeleteReservations' => [
                'label' => 'igniter.reservation::default.text_permission_delete_reservations',
                'group' => 'reservation',
            ],
            'Admin.AssignReservations' => [
                'label' => 'igniter.reservation::default.text_permission_assign_reservations',
                'group' => 'reservation',
            ],
            'Admin.AssignReservationTables' => [
                'label' => 'admin::lang.permissions.assign_reservation_tables', 'group' => 'admin::lang.permissions.name',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'restaurant' => [
                'child' => [
                    'dining_areas' => [
                        'priority' => 50,
                        'class' => 'dining_areas',
                        'href' => admin_url('dining_areas'),
                        'title' => lang('igniter.reservation::default.text_side_menu_tables'),
                        'permission' => 'Admin.Tables',
                    ],
                ],
            ],
            'sales' => [
                'child' => [
                    'reservations' => [
                        'priority' => 20,
                        'class' => 'reservations',
                        'href' => admin_url('reservations'),
                        'title' => lang('igniter.reservation::default.text_side_menu_reservation'),
                        'permission' => 'Admin.Reservations',
                    ],
                ],
            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            \Igniter\Reservation\FormWidgets\FloorPlanner::class => [
                'label' => 'Floor planner',
                'code' => 'floorplanner',
            ],
        ];
    }

    public function registerListActionWidgets()
    {
        return [
            \Igniter\Reservation\BulkActionWidgets\AssignTable::class => [
                'code' => 'assign_table',
            ],
        ];
    }

    public function registerLocationSettings()
    {
        return [
            'booking' => [
                'label' => 'igniter.reservation::default.settings.text_tab_booking',
                'description' => 'igniter.reservation::default.settings.text_tab_desc_booking',
                'icon' => 'fa fa-sliders',
                'priority' => 0,
                'form' => 'igniter.reservation::/models/bookingsettings',
                'request' => \Igniter\Reservation\Requests\BookingSettingsRequest::class,
            ],
        ];
    }

    protected function bindReservationEvent()
    {
        Event::listen('admin.statusHistory.beforeAddStatus', function ($model, $object, $statusId, $previousStatus) {
            if (!$object instanceof Reservation) {
                return;
            }

            if (StatusHistory::alreadyExists($object, $statusId)) {
                return;
            }

            Event::fire('igniter.reservation.beforeAddStatus', [$model, $object, $statusId, $previousStatus], true);
        });

        Event::listen('admin.statusHistory.added', function ($model, $statusHistory) {
            if (!$model instanceof Reservation) {
                return;
            }

            Event::fire('igniter.reservation.statusAdded', [$model, $statusHistory], true);
        });

        Event::listen('admin.assignable.assigned', function ($model, $assignableLog) {
            if (!$model instanceof Reservation) {
                return;
            }

            Event::fire('igniter.reservation.assigned', [$model, $assignableLog], true);
        });
    }

    protected function registerSystemSettings()
    {
        Settings::registerCallback(function (Settings $manager) {
            $manager->registerSettingItems('core', [
                'reservation' => [
                    'label' => 'lang:igniter.reservation::default.text_setting_reservation',
                    'description' => 'lang:igniter.reservation::default.help_setting_reservation',
                    'icon' => 'fa fa-chair',
                    'priority' => 1,
                    'permission' => ['Site.Settings'],
                    'url' => admin_url('settings/edit/reservation'),
                    'form' => 'igniter.reservation::/models/reservationsettings',
                    'request' => ReservationSettingsRequest::class,
                ],
            ]);
        });
    }
}