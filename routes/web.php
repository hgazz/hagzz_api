<?php

use App\Services\Firebase\NotificationService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    $title = 'Booking Confirmed gouda';
    $body = 'your booking with  is confirmed Training - ';
    $data = [
        'title' => $title,
        'body' => $body,
        'image' => 'i.ph',
        'details' => '$details'
    ];
    dump($data);

    $x = NotificationService::firebaseNotification($data, 'dVjsLZV3TkAhpr2FcBlKH5:APA91bHSewCTM-OvM0pw4igsqnK_jHCO4myr-49Lrt2I9hrsFMdtU3od8tTQ79Jj2xq1JRq4OwDKDU2UtTgTQ1gM44Nks13kl6MJKlp9fzdNIBaVZRUsbxe5LhDJRcFztXDKrd7iLkDU');
    dd($x);

});
