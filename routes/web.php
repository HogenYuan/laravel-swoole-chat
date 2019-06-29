<?php

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
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $fds = $redis->GET('user1');
    $fds = json_decode($fds, true);
    var_dump($fds);
    return view('login');
});
Route::get('/{sid}/{nickname}', function () {
    $host = $_SERVER['HTTP_HOST'];
    return view('chat', ['host' => $host]);
});
