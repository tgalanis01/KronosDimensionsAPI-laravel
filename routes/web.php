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
    return view('dashboard');
});
Route::group(['middleware' => ['auth']], function () {


    Route::get('overtimelistdetails','AdminController@overtimeListDetails');
    Route::post('overtimelistdetails/dt','AdminController@overtimeListDataDetails');
    //Route::get('admin/overtimelistdetails/dt','AdminController@overtimeListDataDetails'); //testing

// employee availability
    Route::post('overtimelist/employee-availability','AdminController@employeeAvailabilityStore');
    Route::view('overtimelist/employee-availability','employee-availability');
    Route::post('overtimelist/employee-availability/dt','AdminController@employeeAvailabilityData');
    Route::post('overtimelist/employee-availability/status','AdminController@updateEmployeeAvailabilityReviewStatus');

});
Auth::routes();

Route::get('overtimelist','AdminController@overtimeList');
Route::post('overtimelist/dt','AdminController@overtimeListData');
//Route::get('admin/overtimelist/dt','AdminController@overtimeListData'); //testing

Route::get('/signin', 'AuthController@signin');

Route::get('/authorize', 'AuthController@gettoken')->name('authorize');
Route::get('/home', 'HomeController@index')->name('home');

// tracking unavailable
/* commenting out for now so no one can access this, leaving here for potential future use.
Route::post('overtimelist/unavailable','AdminController@overtimeListUnavailableStore');
Route::view('overtimelist/unavailable','track-unavailable');
Route::post('overtimelist/unavailable/dt','AdminController@overtimeListUnavailableData');
*/

//Route::get('kronostest','AdminController@kronosDimensionsTest'); //testing
//Route::get('/test', 'AdminController@index');

