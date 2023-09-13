<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('users/{id}', function ($id) {
    return ['id' => $id];
});

Route::get('client/vnpay/ipn', 'Client\VNPayController@ipn');

Route::post('users/token', 'User\ApiTokenController@gettoken');

Route::post('admins/token', 'Admin\ApiTokenController@gettoken');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('alepay/webhook/return', 'Client\AlepayController@ipn')->name('alepay.ipn');


Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact info@ohlaalaa.com'], 404);
});

//  Route::get('client/homepage', 'Api\ApiController@index');

// Route::get('client/search', 'Api\ApiController@search');

// Route::get('client/shop/search', 'Api\ApiController@shopSearch');

// Route::get('client/product/{id}', 'Api\ApiController@product');
// Route::middleware('auth:admin-api')->get('/admin', function (Request $request) {
//     return $request->user();
// });

//Route::middleware('auth:admin-api')->get('/admin/user', 'Admin\UserController@getuser');

Route::group(['middleware'=>'auth:admin-api'],function(){
    Route::get('/admin/user', 'Admin\ApiUserController@getuser')->name('admin-001');
    Route::post('/admin/convertshoppingpoint', 'Admin\ApiUserController@convertshoppingpoint')->name('admin-002');
    Route::post('/admin/merchantsalebonus', 'Admin\ApiUserController@merchantsalebonus')->name('admin-003');
    Route::post('/admin/checkmembersranking', 'Admin\ApiUserController@check_all_memberships')->name('admin-004');
    Route::post('/admin/sendsubsexpirenotification', 'Admin\ApiUserController@send_subs_expire_notification')->name('admin-005');
    Route::post('/admin/sendmembershipexpirenotification', 'Admin\ApiUserController@send_membership_expire_notification')->name('admin-006');


    Route::get('/admin/vnpay/order/track/ipn/{id?}', 'Admin\VNPayController@view_order_ipn')->name('admin-vnpay-order-track-ipn');
    Route::get('/admin/vnpay/membership/track/ipn/{id?}', 'Admin\VNPayController@view_membership_ipn')->name('admin-vnpay-membership-track-ipn');
    Route::get('/admin/vnpay/track/request/{id?}', 'Admin\VNPayController@view_request')->name('admin-vnpay-track-request');
    Route::get('/admin/vnpay/track/hash_key', 'Admin\VNPayController@hash_key')->name('admin-vnpay-track-hashkey');
});

