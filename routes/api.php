<?php

use App\Http\Controllers\Api\AcademyController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\JoinController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SportController;
use App\Http\Controllers\Api\TrainingController;
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
        Route::post('/login','login');
        Route::post('/resendCode','resendCode');
        Route::post('/verifyCode','verifyCode');
    });
});

Route::controller(SportController::class)->group(function () {
    Route::get('/sports', 'getSports');
});

Route::controller(AddressController::class)->group(function () {
    Route::get('/countries', 'getCountries');
    Route::post('/cities', 'getCitiesByCountry');
    Route::post('/areas', 'getAreasByCity');
});

Route::group(['middleware' => 'auth:api'], function () {

    Route::get('delete/account', [AuthController::class, 'deleteAccount']);
    Route::post('update/user-profile', [AuthController::class, 'updateProfile']);

    Route::controller(HomeController::class)->group(function (){
        Route::get('/home','home');
    });

    Route::controller(TrainingController::class)->group(function (){
        Route::post('/explore','index');
        Route::get('trainingDetails/{id}', 'trainingDetails');
    });

    Route::controller(AcademyController::class)->group(function (){
       Route::get('academyDetails/{id}','academyDetails');
    });

    Route::controller(CoachController::class)->group(function (){
        Route::get('coachProfile/{id}','coachProfile');
    });

    Route::controller(FollowController::class)->group(function (){
        Route::get('follows','followList');
        Route::post('follow/addFollow','addFollow');
        Route::post('follow/deleteFollow','deleteFollow');
    });
    Route::controller(JoinController::class)->group(function (){
        Route::get('joins','joinList');
        Route::post('addJoin','addJoin');
        Route::get('join/{id}','join');
    });

    Route::controller(FavoriteController::class)->group(function (){
        Route::get('favorites','favoriteList');
        Route::post('addFavorite','addFavorite');
        Route::post('deleteFavorite/{id}','deleteFavorite');
    });
    Route::get('/profile/{id}', [ProfileController::class,'getProfile']);

    Route::post('/logout',[AuthController::class,'logout']);

});


//Route::group(
//    [
//        'prefix' => LaravelLocalization::setLocale(),
//        'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
//    ], function(){ //...
//});
