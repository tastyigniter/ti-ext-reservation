<?php

declare(strict_types=1);

namespace Igniter\Reservation\BulkActionWidgets;

use Override;
use Igniter\Admin\Classes\BaseBulkActionWidget;
use Illuminate\Support\Collection;

class AssignTable extends BaseBulkActionWidget
{
    #[Override]
    public function handleAction(array $requestData, Collection $records): void
    {
        $noTablesFound = [];
        $tablesAssigned = [];

        foreach ($records->sortBy('reservation_datetime') as $record) {
            if ($record->tables->count() > 0) {
                continue;
            }

            if ($record->assignTable()) {
                $tablesAssigned[] = $record->reservation_id;
            } else {
                $noTablesFound[] = $record->reservation_id;
            }
        }

        if ($noTablesFound !== []) {
            flash()->warning(
                lang('igniter.reservation::default.alert_no_assignable_table').' '.implode(', ', $noTablesFound),
            )->important();
        }

        if ($tablesAssigned !== []) {
            flash()->success(lang('igniter.reservation::default.alert_table_assigned'));
        }
    }
}
