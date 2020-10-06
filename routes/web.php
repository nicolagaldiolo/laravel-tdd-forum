<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('threads', 'ThreadsController@index')->name('threads');
Route::get('threads/create', 'ThreadsController@create');
Route::get('threads/{channel}/{thread}', 'ThreadsController@show');
Route::delete('threads/{channel}/{thread}', 'ThreadsController@destroy');
Route::post('threads', 'ThreadsController@store')->middleware('custom-must-be-confirmed');
Route::get('threads/{channel?}', 'ThreadsController@index');
Route::get('/threads/{channel}/{thread}/replies', 'RepliesController@index');
Route::post('/threads/{channel}/{thread}/replies', 'RepliesController@store');
Route::patch('/replies/{reply}', 'RepliesController@update');
Route::delete('/replies/{reply}', 'RepliesController@destroy');

Route::post('/replies/{reply}/best', 'BestReplyController@store')->name('best-replies.store');

Route::post('/threads/{channel}/{thread}/subscriptions', 'ThreadSubscriptionController@store')->middleware('auth');
Route::delete('/threads/{channel}/{thread}/subscriptions', 'ThreadSubscriptionController@destroy')->middleware('auth');

Route::post('/replies/{reply}/favorites', 'FavoritesController@store');
Route::delete('/replies/{reply}/favorites', 'FavoritesController@destroy');

Route::get('/profiles/{user}', 'ProfileController@show')->name('profile');
Route::get('/profiles/{user}/notification', 'UserNotificationController@index');
Route::delete('/profiles/{user}/notification/{notification}', 'UserNotificationController@destroy');

Route::get('/register/custom_confirm', 'Api\RegisterCustomConfirmedController@index')->name('register.confirm');

Route::get('api/users', 'Api\UsersController@index');
Route::post('api/users/{user}/avatar', 'Api\UserAvatarController@store')->middleware('auth')->name('avatar');