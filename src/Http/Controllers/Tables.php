<?php

namespace Igniter\Reservation\Http\Controllers;

use Igniter\Admin\Facades\AdminMenu;

/**
 * Admin Controller Class Tables
 */
class Tables extends \Igniter\Admin\Classes\AdminController
{
    public $implement = [
        \Igniter\Admin\Http\Actions\ListController::class,
        \Igniter\Admin\Http\Actions\FormController::class,
        \Igniter\Local\Http\Actions\LocationAwareController::class,
    ];

    public $listConfig = [
        'list' => [
            'model' => \Igniter\Reservation\Models\Table::class,
            'title' => 'lang:igniter.reservation::default.tables.text_title',
            'emptyMessage' => 'lang:igniter.reservation::default.tables.text_empty',
            'defaultSort' => ['table_id', 'DESC'],
            'configFile' => 'table',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:igniter.reservation::default.tables.text_form_name',
        'model' => \Igniter\Reservation\Models\Table::class,
        'request' => \Igniter\Reservation\Requests\TableRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'tables/edit/{table_id}',
            'redirectClose' => 'tables',
            'redirectNew' => 'tables/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'tables/edit/{table_id}',
            'redirectClose' => 'tables',
            'redirectNew' => 'tables/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'redirect' => 'tables',
        ],
        'delete' => [
            'redirect' => 'tables',
        ],
        'configFile' => 'table',
    ];

    protected $requiredPermissions = 'Admin.Tables';

    public static function getSlug()
    {
        return 'tables';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('tables', 'restaurant');
    }
}
