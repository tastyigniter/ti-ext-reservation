<?php

namespace Igniter\Reservation\Http\Controllers;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Flame\Exception\FlashException;
use Igniter\Reservation\Models\DiningTable;

/**
 * Admin Controller Class Dining Areas
 */
class DiningAreas extends AdminController
{
    public array $implement = [
        \Igniter\Admin\Http\Actions\ListController::class,
        \Igniter\Admin\Http\Actions\FormController::class,
        \Igniter\Local\Http\Actions\LocationAwareController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => \Igniter\Reservation\Models\DiningArea::class,
            'title' => 'lang:igniter.reservation::default.dining_areas.text_title',
            'emptyMessage' => 'lang:igniter.reservation::default.dining_areas.text_empty',
            'defaultSort' => ['created_at', 'DESC'],
            'configFile' => 'dining_area',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:igniter.reservation::default.dining_areas.text_form_name',
        'model' => \Igniter\Reservation\Models\DiningArea::class,
        'request' => \Igniter\Reservation\Http\Requests\DiningAreaRequest::class,
        'create' => [
            'title' => 'lang:admin::lang.form.create_title',
            'redirect' => 'dining_areas/edit/{id}',
            'redirectClose' => 'dining_areas',
            'redirectNew' => 'dining_areas/create',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'dining_areas/edit/{id}',
            'redirectClose' => 'dining_areas',
            'redirectNew' => 'dining_areas/create',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'back' => 'dining_areas',
        ],
        'delete' => [
            'redirect' => 'dining_areas',
        ],
        'configFile' => 'dining_area',
    ];

    protected null|string|array $requiredPermissions = 'Admin.Tables';

    public static function getSlug()
    {
        return 'dining_areas';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('dining_areas', 'restaurant');
    }

    public function index_onDuplicate($context)
    {
        $model = $this->asExtension('FormController')->formFindModelObject(post('id'));

        $duplicate = $model->duplicate();

        flash()->success(sprintf(lang('admin::lang.alert_success'), 'Dining area duplicated'));

        return $this->redirect('dining_areas/edit/'.$duplicate->getKey());
    }

    public function edit_onCreateCombo($context, $recordId)
    {
        $checked = (array)post('DiningArea._select_dining_tables', []);
        throw_if(!$checked || count($checked) < 2,
            new FlashException(lang('igniter.reservation::default.dining_areas.alert_tables_not_checked'))
        );

        $model = $this->asExtension('FormController')->formFindModelObject($recordId);

        $checkedTables = $model->dining_tables()->whereIn('id', $checked)->get();

        $model->createCombo($checkedTables);

        flash()->success(sprintf(lang('admin::lang.alert_success'), 'Table combo created'));

        return $this->redirectBack();
    }

    public function listExtendQuery($query)
    {
        $query->with(['available_tables', 'dining_sections']);
    }

    public function formExtendFields($form)
    {
        if ($form->context != 'create') {
            $form->getField('location_id')->disabled = true;
        }
    }

    public function formBeforeSave($model)
    {
        if (DiningTable::isBroken()) {
            DiningTable::fixBrokenTreeQuietly();
        }
    }
}
