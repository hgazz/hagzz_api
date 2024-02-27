<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProfileController;
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

    Route::controller(AddressController::class)->group(function () {
        Route::get('/countries', 'getCountries');
        Route::post('/cities', 'getCitiesByCountry');
        Route::post('/areas', 'getAreasByCity');
    });

    Route::get('delete/account', [AuthController::class, 'deleteAccount']);
    Route::post('update/user-profile', [AuthController::class, 'updateProfile']);

    Route::controller(HomeController::class)->group(function (){
        Route::get('/banners','banners');
        Route::get('/sport','sports');
        Route::get('/academy','academy');
        Route::get('/training','training');
    });

    Route::get('/profile', [ProfileController::class,'getProfile']);

});


//Route::group(
//    [
//        'prefix' => LaravelLocalization::setLocale(),
//        'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
//    ], function(){ //...
//});
