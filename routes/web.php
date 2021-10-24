<?php

use App\Http\Controllers\CommissionsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [CommissionsController::class, 'index']);
Route::get('/invoice/{id}', [CommissionsController::class, 'invoice']);
Route::get('/sales/{id}', [CommissionsController::class, 'sales']);
Route::match(['get', 'post'], '/transactions', [CommissionsController::class, 'report']);
Route::match(['get', 'post'], '/top100', [CommissionsController::class, 'top100']);
Route::match(['get', 'post'], '/autocomplete', [CommissionsController::class, 'autocomplete']);
