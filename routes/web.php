<?php

use Illuminate\Support\Facades\Route;
use Assure\Workflow\Controllers\WorkflowController;

Route::group([
    'prefix' => 'workflow',
    'as' => 'workflow.',
    'middleware' => ['web']
], function () {
    Route::get('/', 'Assure\\Workflow\\Controllers\\WorkflowController@index')->name('index');
    Route::get('/{id}', 'Assure\\Workflow\\Controllers\\WorkflowController@show')->name('show');
    Route::post('/{id}/start', 'Assure\\Workflow\\Controllers\\WorkflowController@start')->name('start');
    Route::get('/instances/{id}', 'Assure\\Workflow\\Controllers\\WorkflowController@showInstance')->name('instances.show');
});

Route::group([
    'prefix' => 'api/workflow',
    'as' => 'workflow.api.',
    'middleware' => ['web']
], function () {
    Route::get('/', 'Assure\\Workflow\\Controllers\\WorkflowController@apiIndex')->name('index');
    Route::get('/{id}', 'Assure\\Workflow\\Controllers\\WorkflowController@apiShow')->name('show');
    Route::post('/', 'Assure\\Workflow\\Controllers\\WorkflowController@apiStore')->name('store');
    Route::put('/{id}', 'Assure\\Workflow\\Controllers\\WorkflowController@apiUpdate')->name('update');
    Route::delete('/{id}', 'Assure\\Workflow\\Controllers\\WorkflowController@apiDestroy')->name('destroy');
    // Clone workflow with steps and conditions
    Route::post('/{id}/clone', 'Assure\\Workflow\\Controllers\\WorkflowController@apiClone')->name('clone');
    // Export/Import workflows
    Route::get('/{id}/export', 'Assure\\Workflow\\Controllers\\WorkflowController@apiExport')->name('export');
    Route::post('/import', 'Assure\\Workflow\\Controllers\\WorkflowController@apiImport')->name('import');
    // Steps
    Route::post('/{workflowId}/steps', 'Assure\\Workflow\\Controllers\\WorkflowController@apiStoreStep')->name('steps.store');
    Route::put('/{workflowId}/steps/{stepId}', 'Assure\\Workflow\\Controllers\\WorkflowController@apiUpdateStep')->name('steps.update');
    Route::delete('/{workflowId}/steps/{stepId}', 'Assure\\Workflow\\Controllers\\WorkflowController@apiDestroyStep')->name('steps.destroy');
    Route::post('/{workflowId}/steps/reorder', 'Assure\\Workflow\\Controllers\\WorkflowController@apiReorderSteps')->name('steps.reorder');
    // Conditions
    Route::post('/{workflowId}/steps/{stepId}/conditions', 'Assure\\Workflow\\Controllers\\WorkflowController@apiStoreCondition')->name('conditions.store');
    Route::put('/{workflowId}/steps/{stepId}/conditions/{conditionId}', 'Assure\\Workflow\\Controllers\\WorkflowController@apiUpdateCondition')->name('conditions.update');
    Route::delete('/delete-conditions/{conditionId}', 'Assure\\Workflow\\Controllers\\WorkflowController@apiDestroyCondition')->name('conditions.destroy');
    // Form components for a step
    Route::get('/{workflowId}/steps/{stepId}/form-components', 'Assure\\Workflow\\Controllers\\WorkflowController@apiFormComponents')->name('steps.form_components');
});

