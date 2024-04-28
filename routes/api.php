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
use App\Http\Controllers\Api\UserController;
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
        Route::post('/auth/login-register','saveUserPhone');
        Route::post('/login','login');
//        Route::post('/resendCode','resendCode');
        Route::post('/verify-otp','verifyCode');
    });
});

Route::controller(SportController::class)->group(function () {
    Route::get('/sports', 'getSports');
});

Route::controller(AddressController::class)->group(function () {
    Route::get('/countries', 'getCountries');
    Route::post('/cities', 'getCitiesByCountry');
    Route::post('/areas', 'getAreasByCity');
    Route::get('/all-area', 'getAreas');
});

Route::get('home',[HomeController::class,'home']);
Route::get('terms',[HomeController::class,'terms']);


Route::controller(TrainingController::class)->group(function (){
    Route::get('trainings/list', 'getAllTrainings');
    Route::get('trainingDetails/{id}', 'trainingDetails');
    Route::post('explore','index');
});

Route::controller(AcademyController::class)->group(function (){
    Route::get('academies','getAllAcademies');
    Route::get('academyDetails/{id}','academyDetails');
    Route::get('academyTrainings/{id}','getTrainingsByAcademy');
});

Route::controller(CoachController::class)->group(function (){
    Route::get('coach/trainings/{id}','getTrainingsByCoach');
    Route::get('coachProfile/{id}','coachProfile');
});


Route::group(['middleware' => ['auth:api', 'setLang']], function () {
    Route::post('/user/update_personal_data', [AuthController::class, 'updatePersonalData']);
    Route::post('/user/update_sports_data',[AuthController::class, 'updateSportsData']);

    Route::get('delete/account', [AuthController::class, 'deleteAccount']);
    Route::post('update/user-profile', [AuthController::class, 'updateProfile']);

    Route::get('faqs',[HomeController::class,'getFaqs']);
    Route::controller(HomeController::class)->group(function (){
        Route::post('/language', 'changeLang');
    });

    Route::controller(FollowController::class)->group(function (){
        Route::get('follows','followList');
        Route::post('follow/addFollow','addFollow');
        Route::post('follow/deleteFollow','deleteFollow');
    });

    Route::controller(JoinController::class)->group(function (){
        Route::post('addJoin','addJoin');
        Route::get('join','join');
        Route::post('cancelBooking','cancelBooking');
    });

    Route::controller(FavoriteController::class)->group(function (){
        Route::get('favorites','favoriteList');
        Route::post('addFavorite','addFavorite');
        Route::post('deleteFavorite/{id}','deleteFavorite');
    });
    Route::get('/profile/{id}', [ProfileController::class,'getProfile']);

    Route::get('/user/sports', [SportController::class,'getSportsNotSelected']);
    Route::get('coach/user/sports', [UserController::class, 'coachSportByUserFavSports']);
    Route::get('user/notifications', [UserController::class, 'userNotifications']);
    Route::get('user/notification/{id}', [UserController::class, 'markAsRead']);

    Route::post('/logout',[AuthController::class,'logout']);

});
