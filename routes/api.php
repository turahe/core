<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\TagController;
use Modules\Core\Http\Controllers\Api\CardController;
use Modules\Core\Http\Controllers\Api\LogoController;
use Modules\Core\Http\Controllers\Api\RoleController;
use Modules\Core\Http\Controllers\Api\VoIPController;
use Modules\Core\Http\Controllers\Api\FieldController;
use Modules\Core\Http\Controllers\Api\FilterController;
use Modules\Core\Http\Controllers\Api\SystemController;
use Modules\Core\Http\Controllers\Api\CountryController;
use Modules\Core\Http\Controllers\Api\CalendarController;
use Modules\Core\Http\Controllers\Api\CurrencyController;
use Modules\Core\Http\Controllers\Api\MailableController;
use Modules\Core\Http\Controllers\Api\SettingsController;
use Modules\Core\Http\Controllers\Api\TimezoneController;
use Modules\Core\Http\Controllers\Api\DashboardController;
use Modules\Core\Http\Controllers\Api\HighlightController;
use Modules\Core\Http\Controllers\Api\PermissionController;
use Modules\Core\Http\Controllers\Api\ZapierHookController;
use Modules\Core\Http\Controllers\Api\CustomFieldController;
use Modules\Core\Http\Controllers\Api\TimelinePinController;
use Modules\Core\Http\Controllers\Api\UpdateTagDisplayOrder;
use Modules\Core\Http\Controllers\Api\OAuthAccountController;
use Modules\Core\Http\Controllers\Api\PendingMediaController;
use Modules\Core\Http\Controllers\Api\Updater\PatchController;
use Modules\Core\Http\Controllers\Api\Resource\CloneController;
use Modules\Core\Http\Controllers\Api\Resource\MediaController;
use Modules\Core\Http\Controllers\Api\Resource\TableController;
use Modules\Core\Http\Controllers\Api\Updater\UpdateController;
use Modules\Core\Http\Controllers\Api\Resource\ActionController;
use Modules\Core\Http\Controllers\Api\Resource\ExportController;
use Modules\Core\Http\Controllers\Api\Resource\ImportController;
use Modules\Core\Http\Controllers\Api\Resource\SearchController;
use Modules\Core\Http\Controllers\Api\Workflow\WorkflowTriggers;
use Modules\Core\Http\Controllers\Api\Resource\TrashedController;
use Modules\Core\Http\Controllers\Api\Resource\TimelineController;
use Modules\Core\Http\Controllers\Api\Workflow\WorkflowController;
use Modules\Core\Http\Controllers\Api\Resource\EmailSearchController;
use Modules\Core\Http\Controllers\Api\Resource\FilterRulesController;
use Modules\Core\Http\Controllers\Api\Resource\ResourcefulController;
use Modules\Core\Http\Controllers\Api\Resource\AssociationsController;
use Modules\Core\Http\Controllers\Api\Resource\GlobalSearchController;
use Modules\Core\Http\Controllers\Api\Resource\PlaceholdersController;
use Modules\Core\Http\Controllers\Api\Resource\ImportSkipFileController;
use Modules\Core\Http\Controllers\Api\Resource\AssociationsSyncController;
use Modules\Core\Http\Controllers\Api\Resource\FieldController as ResourceFieldController;

Route::post('/voip/events', [VoIPController::class, 'events'])->name('voip.events');
Route::post('/voip/call', [VoIPController::class, 'newCall'])->name('voip.call');

Route::middleware('auth:api')->group(function () {
    Route::post('/zapier/hooks/{resourceName}/{action}', [ZapierHookController::class, 'store']);
    Route::delete('/zapier/hooks/{hookId}', [ZapierHookController::class, 'destroy']);

    // Calendar routes
    Route::get('/calendar', [CalendarController::class, 'index']);

    // OAuth accounts controller
    Route::apiResource('/oauth/accounts', OAuthAccountController::class, ['as' => 'oauth'])
        ->except(['store', 'update']);

    // Highlighs
    Route::get('highlights', HighlightController::class);

    // Available timezones route
    Route::get('/timezones', [TimezoneController::class, 'handle']);

    // Available countries route
    Route::get('/countries', [CountryController::class, 'handle']);

    // Resource fields route
    Route::get('/fields/{group}/{view}', [FieldController::class, 'index']);

    // App available currencies
    Route::get('currencies', CurrencyController::class);

    Route::middleware('admin')->group(function () {
        Route::post('/tags/order', UpdateTagDisplayOrder::class);
        Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
        Route::post('/tags/{type?}', [TagController::class, 'store']);
        Route::put('/tags/{tag}', [TagController::class, 'update']);

        Route::get('/system/logs', [SystemController::class, 'logs']);
        Route::get('/system/info', [SystemController::class, 'info']);
        Route::post('/system/info', [SystemController::class, 'downloadInfo']);

        // General Settings
        Route::get('/settings', [SettingsController::class, 'index']);
        Route::post('/settings', [SettingsController::class, 'save']);

        // Application update management
        Route::get('/patches', [PatchController::class, 'index']);
        Route::post('/patches/{token}/{purchase_key?}', [PatchController::class, 'apply']);
        Route::get('/update', [UpdateController::class, 'index']);
        Route::post('/update/{purchase_key?}', [UpdateController::class, 'update']);

        // Custom fields routes
        Route::apiResource('/custom-fields', CustomFieldController::class);

        // Settings intended fields
        Route::prefix('fields/settings')->group(function () {
            Route::post('{group}/{view}', [FieldController::class, 'update']);
            Route::get('bulk/{view}', [FieldController::class, 'bulkSettings']);
            Route::get('{group}/{view}', [FieldController::class, 'settings']);
            Route::delete('{group}/{view}/reset', [FieldController::class, 'destroy']);
        });

        // Workflows
        Route::get('/workflows/triggers', WorkflowTriggers::class);
        Route::apiResource('workflows', WorkflowController::class);

        // Mailable templates
        Route::get('/mailables', [MailableController::class, 'index']);
        Route::get('/mailables/{locale}/locale', [MailableController::class, 'forLocale']);
        Route::get('/mailables/{template}', [MailableController::class, 'show']);
        Route::put('/mailables/{template}', [MailableController::class, 'update']);

        // Settings roles and permissions
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::apiResource('roles', RoleController::class);

        // Logo
        Route::post('/logo/{type}', [LogoController::class, 'store'])->where('type', 'dark|light');
        Route::delete('/logo/{type}', [LogoController::class, 'destroy'])->where('type', 'dark|light');
    });

    // Filters management
    Route::prefix('filters')->group(function () {
        Route::put('{filter}/{view}/default', [FilterController::class, 'markAsDefault']);
        Route::delete('{filter}/{view}/default', [FilterController::class, 'unmarkAsDefault']);
        Route::get('{identifier}', [FilterController::class, 'index']);
        Route::post('/', [FilterController::class, 'store']);
        Route::put('{filter}', [FilterController::class, 'update']);
        Route::delete('{filter}', [FilterController::class, 'destroy']);
    });

    // Media routes
    Route::post('/media/pending/{draftId}', [PendingMediaController::class, 'store']);
    Route::delete('/media/pending/{pendingMediaId}', [PendingMediaController::class, 'destroy']);

    // Cards controller
    Route::get('/cards', [CardController::class, 'forDashboards']);
    Route::get('/cards/{card}', [CardController::class, 'show'])->name('cards.show');

    // Dashboard controller
    Route::apiResource('dashboards', DashboardController::class);

    // Cards controller
    Route::get('/{resource}/cards/', [CardController::class, 'index']);

    // Timeline pins management
    Route::post('/timeline/pin', [TimelinePinController::class, 'store']);
    Route::post('/timeline/unpin', [TimelinePinController::class, 'destroy']);

    // Resource placeholders management
    Route::get('/placeholders', [PlaceholdersController::class, 'index']);
    Route::post('/placeholders/input-fields', [PlaceholdersController::class, 'parseViaInputFields']);
    Route::post('/placeholders/interpolation', [PlaceholdersController::class, 'parseViaInterpolation']);

    // Filters management
    Route::get('/{resource}/rules', [FilterRulesController::class, 'index']);

    // Resource import handling
    Route::get('/{resource}/import', [ImportController::class, 'index']);
    Route::post('/{resource}/import/upload', [ImportController::class, 'upload']);
    Route::post('/{resource}/import/{id}', [ImportController::class, 'handle']);
    Route::delete('/{resource}/import/{id}', [ImportController::class, 'destroy']);
    Route::get('/{resource}/import/sample', [ImportController::class, 'sample']);
    Route::get('/{resource}/import/{id}/skip-file', [ImportSkipFileController::class, 'download']);
    Route::post('/{resource}/import/{id}/skip-file', [ImportSkipFileController::class, 'upload']);

    Route::post('/{resource}/export', [ExportController::class, 'handle']);

    // Searches
    Route::get('/search', [GlobalSearchController::class, 'handle']);
    Route::get('/search/email-address', [EmailSearchController::class, 'handle']);
    Route::get('/{resource}/search', [SearchController::class, 'handle']);

    // Resource associations routes
    Route::put('associations/{resource}/{resourceId}', [AssociationsSyncController::class, 'attach']);
    Route::post('associations/{resource}/{resourceId}', [AssociationsSyncController::class, 'sync']);
    Route::delete('associations/{resource}/{resourceId}', [AssociationsSyncController::class, 'detach']);

    // Resource media routes
    Route::post('{resource}/{resourceId}/media', [MediaController::class, 'store']);
    Route::delete('{resource}/{resourceId}/media/{media}', [MediaController::class, 'destroy']);

    // Resource trash
    Route::get('/trashed/{resource}/search', [TrashedController::class, 'search']);
    Route::post('/trashed/{resource}/{resourceId}', [TrashedController::class, 'restore']);
    Route::get('/trashed/{resource}', [TrashedController::class, 'index']);
    Route::get('/trashed/{resource}/{resourceId}', [TrashedController::class, 'show']);
    Route::delete('/trashed/{resource}/{resourceId}', [TrashedController::class, 'destroy']);

    // Resource management
    Route::get('/{resource}/table', [TableController::class, 'index']);
    Route::get('/{resource}/table/settings', [TableController::class, 'settings']);
    Route::post('/{resource}/table/settings', [TableController::class, 'customize']);

    Route::post('/{resource}/actions/{action}/run', [ActionController::class, 'handle']);

    Route::get('/{resource}/{resourceId}/update-fields', [ResourceFieldController::class, 'update']);
    Route::get('/{resource}/{resourceId}/detail-fields', [ResourceFieldController::class, 'detail']);
    Route::get('/{resource}/{resourceId}/timeline', [TimelineController::class, 'index']);
    Route::post('/{resource}/{resourceId}/clone', [CloneController::class, 'handle']);
    Route::get('/{resource}/{resourceId}/{associated}', [AssociationsController::class, '__invoke']);
    Route::get('/{resource}/create-fields', [ResourceFieldController::class, 'create']);

    Route::get('/{resource}', [ResourcefulController::class, 'index']);
    Route::get('/{resource}/{resourceId}', [ResourcefulController::class, 'show']);
    Route::post('/{resource}', [ResourcefulController::class, 'store']);
    Route::put('/{resource}/{resourceId}', [ResourcefulController::class, 'update']);
    Route::delete('/{resource}/{resourceId}', [ResourcefulController::class, 'destroy']);
});
