<?php

namespace Igniter\Reservation\Models;

use Carbon\Carbon;
use Igniter\Admin\Models\Concerns\GeneratesHash;
use Igniter\Admin\Models\StatusHistory;
use Igniter\Admin\Traits\LogsStatusHistory;
use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Main\Classes\MainController;
use Igniter\Reservation\Events\ReservationCanceledEvent;
use Igniter\System\Traits\SendsMailTemplate;
use Igniter\User\Models\Concerns\Assignable;
use Igniter\User\Models\Concerns\HasCustomer;

/**
 * Reservation Model Class
 */
class Reservation extends Model
{
    use Assignable;
    use GeneratesHash;
    use HasCustomer;
    use HasFactory;
    use Locationable;
    use LogsStatusHistory;
    use Purgeable;
    use SendsMailTemplate;

    /**
     * @var string The database table name
     */
    protected $table = 'reservations';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'reservation_id';

    public $timestamps = true;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d';

    protected $timeFormat = 'H:i';

    public $guarded = ['ip_address', 'user_agent', 'hash'];

    protected $casts = [
        'location_id' => 'integer',
        'table_id' => 'integer',
        'guest_num' => 'integer',
        'occasion_id' => 'integer',
        'assignee_id' => 'integer',
        'reserve_time' => 'time',
        'reserve_date' => 'date',
        'notify' => 'boolean',
        'duration' => 'integer',
        'processed' => 'boolean',
    ];

    public $relation = [
        'belongsTo' => [
            'customer' => \Igniter\User\Models\Customer::class,
            'location' => \Igniter\Local\Models\Location::class,
        ],
        'belongsToMany' => [
            'tables' => [\Igniter\Reservation\Models\DiningTable::class, 'table' => 'reservation_tables'],
        ],
    ];

    protected $purgeable = ['tables'];

    public $appends = ['customer_name', 'duration', 'table_name', 'reservation_datetime', 'reservation_end_datetime'];

    protected array $queryModifierFilters = [
        'customer' => 'applyCustomer',
        'location' => 'whereHasLocation',
        'status' => 'whereStatus',
        'dateTimeFilter' => 'applyDateTimeFilter',
    ];

    protected array $queryModifierSorts = [
        'reservation_id asc', 'reservation_id desc',
        'reserve_date asc', 'reserve_date desc',
        'created_at asc', 'created_at desc',
    ];

    protected array $queryModifierSearchableFields = ['reservation_id', 'first_name', 'last_name', 'email', 'telephone'];

    //
    // Accessors & Mutators
    //

    public function getCustomerNameAttribute($value)
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getDurationAttribute($value)
    {
        if (!is_null($value)) {
            return $value;
        }

        if (!$location = $this->location) {
            return $value;
        }

        return $location->getReservationStayTime();
    }

    public function getReserveEndTimeAttribute($value)
    {
        if (!$this->reservation_datetime) {
            return null;
        }

        if ($this->duration) {
            return $this->reservation_datetime->copy()->addMinutes($this->duration);
        }

        return $this->reservation_datetime->copy()->endOfDay();
    }

    public function getReservationDatetimeAttribute($value)
    {
        if (!isset($this->attributes['reserve_date'])
            && !isset($this->attributes['reserve_time'])
        ) {
            return null;
        }

        return make_carbon($this->attributes['reserve_date'])
            ->setTimeFromTimeString($this->attributes['reserve_time']);
    }

    public function getReservationEndDatetimeAttribute($value)
    {
        return $this->reserve_end_time;
    }

    public function getOccasionAttribute()
    {
        $occasions = $this->getOccasionOptions();

        return $occasions[$this->occasion_id] ?? $occasions[0];
    }

    public function getTableNameAttribute()
    {
        return ($this->tables && $this->tables->isNotEmpty())
            ? implode(', ', $this->tables->pluck('name')->all())
            : '';
    }

    public function setDurationAttribute($value)
    {
        if (empty($value)) {
            $value = optional($this->location()->first())->getReservationStayTime();
        }

        $this->attributes['duration'] = $value;
    }

    //
    // Helpers
    //

    public function isCompleted()
    {
        return $this->hasStatus(setting('confirmed_reservation_status'));
    }

    public function isCanceled()
    {
        return $this->hasStatus(setting('canceled_reservation_status'));
    }

    public function isCancelable()
    {
        if (!$timeout = $this->location->getReservationCancellationTimeout()) {
            return false;
        }

        if (!$this->reservation_datetime->isFuture()) {
            return false;
        }

        return $this->reservation_datetime->diffInRealMinutes() > $timeout;
    }

    public function markAsCanceled(array $statusData = [])
    {
        $canceled = false;
        if ($this->addStatusHistory(setting('canceled_reservation_status'), $statusData)) {
            $canceled = true;
            ReservationCanceledEvent::dispatch($this);
        }

        return $canceled;
    }

    public static function findReservedTables($locationId, $dateTime)
    {
        return self::with('tables')
            ->whereHas('tables', function($query) use ($locationId) {
                $query->whereHasLocation($locationId);
            })
            ->whereLocationId($locationId)
            ->whereBetweenStayTime($dateTime)
            ->where('status_id', setting('confirmed_reservation_status'))
            ->get()
            ->pluck('tables')
            ->flatten()
            ->keyBy('table_id');
    }

    public static function listCalendarEvents($startAt, $endAt, $locationId = null)
    {
        $query = self::whereBetween('reserve_date', [
            date('Y-m-d H:i:s', strtotime($startAt)),
            date('Y-m-d H:i:s', strtotime($endAt)),
        ]);

        if (!is_null($locationId)) {
            $query->whereHasLocation($locationId);
        }

        $collection = $query->get();

        $collection->transform(function($reservation) {
            return $reservation->getEventDetails();
        });

        return $collection->toArray();
    }

    public function getEventDetails()
    {
        $status = $this->status;
        $tables = $this->tables;

        return [
            'id' => $this->getKey(),
            'title' => $this->table_name.' ('.$this->guest_num.')',
            'start' => $this->reservation_datetime->toIso8601String(),
            'end' => $this->reservation_end_datetime->toIso8601String(),
            'allDay' => $this->isReservedAllDay(),
            'color' => $status ? $status->status_color : null,
            'location_name' => ($location = $this->location) ? $location->location_name : null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'guest_num' => $this->guest_num,
            'reserve_date' => $this->reserve_date->toDateString(),
            'reserve_time' => $this->reserve_time,
            'reserve_end_time' => $this->reserve_end_time->toTimeString(),
            'duration' => $this->duration,
            'status' => $status ? $status->toArray() : [],
            'tables' => $tables ? $tables->toArray() : [],
        ];
    }

    public function isReservedAllDay()
    {
        $diffInMinutes = (int)floor($this->reservation_datetime->diffInMinutes($this->reservation_end_datetime));

        return $diffInMinutes >= (60 * 23) || $diffInMinutes == 0;
    }

    public function getOccasionOptions()
    {
        return [
            'not applicable',
            'birthday',
            'anniversary',
            'general celebration',
            'hen party',
            'stag party',
        ];
    }

    public function getDiningTableOptions()
    {
        if (!$location = $this->location) {
            return [];
        }

        return DiningTable::whereHasLocation($location)->pluck('name', 'id');
    }

    /**
     * Return the dates of all reservations
     *
     * @return array
     */
    public function getReservationDates()
    {
        return $this->pluckDates('reserve_date');
    }

    /**
     * Create new or update existing reservation tables
     *
     * @param array $tableIds if empty all existing records will be deleted
     *
     * @return bool
     */
    public function addReservationTables(array $tableIds = [])
    {
        if (!$this->exists) {
            return false;
        }

        $this->tables()->sync($tableIds);
    }

    /**
     * @return \Illuminate\Support\Collection|null
     */
    public function getNextBookableTable()
    {
        $diningTables = DiningTable::query()
            ->with(['dining_section'])
            ->withCount(['reservations' => function($query) {
                $query->where('reserve_date', $this->reserve_date)
                    ->whereNotIn('status_id', [0, setting('canceled_reservation_status')]);
            }])
            ->reservable([
                'locationId' => $this->location_id,
                'dateTime' => $this->reservation_datetime,
                'guestNum' => $this->guest_num,
                'duration' => $this->duration,
            ])->get();

        if (!$diningTable = $this->getNextBookableTableInSection($diningTables)) {
            $diningTable = $diningTables->first();
        }

        return collect($diningTable ? [$diningTable] : []);
    }

    public function assignTable()
    {
        $diningTables = $this->getNextBookableTable();
        if ($diningTables->isEmpty()) {
            return false;
        }

        $this->addReservationTables($diningTables->pluck('id')->all());

        return true;
    }

    protected function getLastSectionId()
    {
        $lastReservation = $this->newQuery()
            ->has('tables')
            ->where('location_id', $this->location_id)
            ->whereDate('reserve_date', $this->reserve_date)
            ->where(function($query) {
                $query->whereNotIn('status_id', [0, setting('canceled_reservation_status')])
                    ->orWhereNull('status_id');
            })
            ->orderBy('reservation_id', 'desc')
            ->first();

        $nextSectionId = null;
        if ($lastReservation && $lastReservation->tables && $lastReservation->tables->first()->dining_section) {
            $nextSectionId = $lastReservation->tables->first()->dining_section->id;
        }

        return $nextSectionId;
    }

    protected function getNextBookableTableInSection($diningTables)
    {
        if ($diningTables->isEmpty() || $diningTables->pluck('dining_section.id')->unique()->isEmpty()) {
            return null;
        }

        $diningSectionsIds = DiningSection::whereHasLocation($this->location_id)
            ->whereIsReservable()->orderBy('priority')->pluck('id');

        if ($diningSectionsIds->isEmpty()) {
            return null;
        }

        $diningSectionsIds = $diningSectionsIds->all();

        $lastSectionId = $this->getLastSectionId();
        if (($nextIndex = array_search($lastSectionId, $diningSectionsIds)) !== false) {
            $nextIndex++;
        }

        $sectionCount = count($diningSectionsIds);
        if ($nextIndex === false || $nextIndex >= $sectionCount) {
            $nextIndex = 0;
        }

        $diningTable = null;
        $diningSections = $diningTables->groupBy('dining_section.id')->all();
        for ($i = $nextIndex; $i < $sectionCount; $i++) {
            $sectionId = $diningSectionsIds[$i];
            $tables = array_pull($diningSections, $sectionId);
            if ($tables && $tables->isNotEmpty()) {
                $diningTable = $tables->sortBy('reservations_count')->first();
                break;
            }

            if (!count($diningSections)) {
                break;
            }

            if ($i == count($diningSectionsIds) - 1) {
                $i = -1;
            }
        }

        return $diningTable;
    }

    //
    // Mail
    //

    public function mailGetRecipients($type)
    {
        $emailSetting = setting('reservation_email', []);
        is_array($emailSetting) || $emailSetting = [];

        $recipients = [];
        if (in_array($type, $emailSetting)) {
            switch ($type) {
                case 'customer':
                    $recipients[] = [$this->email, $this->customer_name];
                    break;
                case 'location':
                    $recipients[] = [$this->location->location_email, $this->location->location_name];
                    break;
                case 'admin':
                    $recipients[] = [setting('site_email'), setting('site_name')];
                    break;
            }
        }

        return $recipients;
    }

    public function mailGetReplyTo($type)
    {
        $replyTo = [];
        if (in_array($type, (array)setting('order_email', []))) {
            switch ($type) {
                case 'location':
                case 'admin':
                    $replyTo = [$this->email, $this->customer_name];
                    break;
            }
        }

        return $replyTo;
    }

    /**
     * Return the order data to build mail template
     *
     * @return array
     */
    public function mailGetData()
    {
        $model = $this->fresh();

        $data = $model->toArray();
        $data['reservation'] = $model;
        $data['reservation_number'] = $model->reservation_id;
        $data['reservation_id'] = $model->reservation_id;
        $data['reservation_time'] = Carbon::createFromTimeString($model->reserve_time)->isoFormat(lang('system::lang.moment.time_format'));
        $data['reservation_date'] = $model->reserve_date->isoFormat(lang('system::lang.moment.date_format_long'));
        $data['reservation_guest_no'] = $model->guest_num;
        $data['first_name'] = $model->first_name;
        $data['last_name'] = $model->last_name;
        $data['email'] = $model->email;
        $data['telephone'] = $model->telephone;
        $data['reservation_comment'] = $model->comment;

        if ($model->location) {
            $data['location_logo'] = $model->location->thumb;
            $data['location_name'] = $model->location->location_name;
            $data['location_email'] = $model->location->location_email;
            $data['location_telephone'] = $model->location->location_telephone;
        }

        $statusHistory = StatusHistory::applyRelated($model)->whereStatusIsLatest($model->status_id)->first();
        $data['status_name'] = $statusHistory ? optional($statusHistory->status)->status_name : null;
        $data['status_comment'] = $statusHistory ? $statusHistory->comment : null;

        $controller = MainController::getController();
        $data['reservation_view_url'] = $controller->pageUrl('account/reservations', [
            'reservationId' => $model->reservation_id,
        ]);

        return $data;
    }
}
