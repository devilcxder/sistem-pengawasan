<?php

use App\Events\ChartEvent;
use App\Preprocessing\PreprocessingService;
use Illuminate\Support\Facades\Route;
use Pusher\Pusher;
use Thujohn\Twitter\Facades\Twitter;

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

Route::get('/', function () {
    event(new ChartEvent('hello world'));
    dd('OK');
});
