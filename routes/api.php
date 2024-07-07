<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserOrganisationController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:api'])->group(function () {
    Route::get('/users/{user}', [UserController::class, 'get_users']);
    Route::get('/organisations', [UserOrganisationController::class, 'get_organisations']);
    Route::get('/organisations/{organisation}', [UserOrganisationController::class, 'get_organisation']);
    Route::post('/organisations/{organisation}/users', [UserOrganisationController::class, 'add_member']);
    Route::post('/organisations/{user}', [UserOrganisationController::class, 'add']);
});
