<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'api'], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register','register');
        Route::post('/logout','login');
        Route::post('/login','login');
        Route::post('/resendCode','resendCode');
        Route::post('/verifyCode','verifyCode');
    });
});

Route::group(['middleware' => ['auth:api']], function () {

    Route::controller(SportController::class)->group(function () {
        Route::get('/sports', 'getSports');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::group(
//    [
//        'prefix' => LaravelLocalization::setLocale(),
//        'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
//    ], function(){ //...
//});
