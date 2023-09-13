<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// ************************************ ADMIN SECTION **********************************************
//Route::get('/', 'Front\DataController@data');
use App\Http\Controllers\Front\VendorController;;

include __DIR__ . "/admin.php";

// ************************************ ADMIN SECTION ENDS**********************************************
// ************************************ USER SECTION **********************************************
Route::prefix('user')->group(function ()
{
    // User Dashboard
    Route::get('/dashboard', 'User\UserController@index')
        ->name('user-dashboard');

    // User Login
    Route::get('/login', 'User\LoginController@showLoginForm')
        ->name('user.login');
    Route::post('/login', 'User\LoginController@login')
        ->name('user.login.submit');
    // User Login End
    // User Register
    Route::get('/r', 'User\RegisterController@showRegisterForm')
        ->name('user-register');
    Route::post('/r', 'User\RegisterController@register')
        ->name('user-register-submit');
    Route::get('/register/verify/{token}', 'User\RegisterController@token')
        ->name('user-register-token');
    // User Register End
    // User Reset
    Route::get('/reset', 'User\UserController@resetform')
        ->name('user-reset');
    Route::post('/reset', 'User\UserController@reset')
        ->name('user-reset-submit');
    // User Reset End
    //User track
    Route::get('/track/membership/notification', 'User\UserController@checkToShowMembershipNotification')
        ->name('user-track-membership-notification');
    //User track end
    // User Profile
    Route::get('/profile', 'User\UserController@profile')
        ->name('user-profile');
    Route::post('/profile', 'User\UserController@profileupdate')
        ->name('user-profile-update');
    // User Profile Ends
    // User Forgot
    Route::get('/forgot', 'User\ForgotController@showforgotform')
        ->name('user-forgot');
    Route::post('/forgot', 'User\ForgotController@forgot')
        ->name('user-forgot-submit');
    // User Forgot Ends
    // COMET SECTION
    Route::get('/comet/createuser/{id}', 'User\UserController@create_comet_user')
        ->name('user-comet-createuser');
    // COMET SECTION ENDS
    // User Wishlist
    Route::get('/wishlists', 'User\WishlistController@wishlists')
        ->name('user-wishlists');
    Route::get('/wishlist/add/{id}', 'User\WishlistController@addwish')
        ->name('user-wishlist-add');
    Route::get('/wishlist/remove/{id}', 'User\WishlistController@removewish')
        ->name('user-wishlist-remove');
    // User Wishlist Ends
    // User Review
    Route::post('/review/submit', 'User\UserController@reviewsubmit')
        ->name('front.review.submit');
    // User Review Ends
    // User Orders
    Route::get('/orders', 'User\OrderController@orders')
        ->name('user-orders');
    Route::get('/order/tracking', 'User\OrderController@ordertrack')
        ->name('user-order-track');

    Route::get('/order/trackings/{id}', 'User\OrderController@trackload')
        ->name('user-order-track-search');
    Route::get('/order/received/{id}', 'User\OrderController@order_received')
        ->name('user-order-received');
    Route::get('/order/{id}', 'User\OrderController@order')
        ->name('user-order');
    Route::get('/download/order/{slug}/{id}', 'User\OrderController@orderdownload')
        ->name('user-order-download');
    Route::get('print/order/print/{id}', 'User\OrderController@orderprint')
        ->name('user-order-print');
    Route::get('/json/trans', 'User\OrderController@trans');

    // User Orders Ends
    //sending shopping point
    Route::get('/sp/sending', 'User\ShoppingPointController@sending_index')
        ->name('user-sending-sp');
    Route::post('/sp/sending', 'User\ShoppingPointController@send_global')
        ->name('user-sending-sp-global');
    Route::get('/sp/sending/{phonenumber}', 'User\ShoppingPointController@sending_check')
        ->name('user-sending-sp-check');
    //sending shopping point end


    // User Subscription
    Route::get('/package', 'User\UserController@package')
        ->name('user-package');
    Route::get('/subscription/{id}', 'User\UserController@vendorrequest')
        ->name('user-vendor-request');
    Route::post('/vendor-request', 'User\UserController@vendorrequestsub')
        ->name('user-vendor-request-submit');

    Route::post('/paypal/submit', 'User\PaypalController@store')
        ->name('user.paypal.submit');
    Route::get('/paypal/cancle', 'User\PaypalController@paycancle')
        ->name('user.payment.cancle');
    Route::get('/paypal/return', 'User\PaypalController@payreturn')
        ->name('user.payment.return');
    Route::post('/paypal/notify', 'User\PaypalController@notify')
        ->name('user.payment.notify');
    Route::post('/stripe/submit', 'User\StripeController@store')
        ->name('user.stripe.submit');

    Route::get('/instamojo/notify', 'User\InstamojoController@notify')
        ->name('user.instamojo.notify');
    Route::post('/instamojo/submit', 'User\InstamojoController@store')
        ->name('user.instamojo.submit');

    Route::get('/molly/notify', 'User\MollyController@notify')
        ->name('user.molly.notify');
    Route::post('/molly/submit', 'User\MollyController@store')
        ->name('user.molly.submit');

    Route::get('/paystack/check', 'User\PaystackController@check')
        ->name('user.paystack.check');
    Route::post('/paystack/submit', 'User\PaystackController@store')
        ->name('user.paystack.submit');

    //member package
    Route::get('/memberpackage', 'User\UserController@member_package')
        ->name('user-member-package');
    Route::get('/memberpackage/tnc/{id}', 'User\UserController@member_package_tnc')
        ->name('user-member-package-tnc');
    Route::post('/memberpackage/tnc/{id}', 'User\UserController@ranking_register')
        ->name('user-membership-request');
    Route::get('/memberpackage/banks', 'User\UserController@banks')
        ->name('user-member-package-banks');
    Route::get('/vnpay', 'User\VNPayController@index')
        ->name('user-member-package-vnpay-return');

    //PayTM Routes
    Route::post('/paytm/submit', 'User\PaytmController@store')
        ->name('user.paytm.submit');;
    Route::post('/paytm/notify', 'User\PaytmController@notify')
        ->name('user.paytm.notify');

    //PayTM Routes
    Route::post('/razorpay/submit', 'User\RazorpayController@store')
        ->name('user.razorpay.submit');;
    Route::post('/razorpay/notify', 'User\RazorpayController@notify')
        ->name('user.razorpay.notify');

    // User Subscription Ends
    // User Vendor Send Message
    Route::post('/user/contact', 'User\MessageController@usercontact');
    Route::get('/messages', 'User\MessageController@messages')
        ->name('user-messages');
    Route::get('/messages/inbox', 'User\MessageController@inbox')
        ->name('user-messages-inbox');
    Route::get('/message/{id}', 'User\MessageController@message')
        ->name('user-message');
    Route::post('/message/post', 'User\MessageController@postmessage')
        ->name('user-message-post');
    Route::get('/message/{id}/delete', 'User\MessageController@messagedelete')
        ->name('user-message-delete');
    Route::get('/message/load/{id}', 'User\MessageController@msgload')
        ->name('user-vendor-message-load');

    // User Vendor Send Message Ends
    // User Admin Send Message


    // Tickets
    Route::get('admin/tickets', 'User\MessageController@adminmessages')
        ->name('user-message-index');
    // Disputes
    Route::get('admin/disputes', 'User\MessageController@adminDiscordmessages')
        ->name('user-dmessage-index');

    Route::get('admin/message/{id}', 'User\MessageController@adminmessage')
        ->name('user-message-show');
    Route::post('admin/message/post', 'User\MessageController@adminpostmessage')
        ->name('user-message-store');
    Route::get('admin/message/{id}/delete', 'User\MessageController@adminmessagedelete')
        ->name('user-message-delete1');
    Route::post('admin/user/send/message', 'User\MessageController@adminusercontact')
        ->name('user-send-message');
    Route::get('admin/message/load/{id}', 'User\MessageController@messageload')
        ->name('user-message-load');
    // User Admin Send Message Ends
    Route::get('/affilate/code', 'User\WithdrawController@affilate_code')
        ->name('user-affilate-code');
    Route::get('/affilate/members', 'User\WithdrawController@affilate_members')
        ->name('user-affilate-members');
    Route::get('/affilate/tree', 'User\WithdrawController@affilate_tree')
        ->name('user-affilate-tree');
    Route::get('/affilate/members/datatable/{from?}/{to?}', 'User\WithdrawController@affilate_members_datatable')
        ->name('user-affilate-members-datatable');
    Route::get('/affilate/withdraw', 'User\WithdrawController@index')
        ->name('user-wwt-index');
    Route::get('/affilate/withdraw/create', 'User\WithdrawController@create')
        ->name('user-wwt-create');
    Route::post('/affilate/withdraw/create', 'User\WithdrawController@store')
        ->name('user-wwt-store');

    // User Favorite Seller
    Route::get('/favorite/seller', 'User\UserController@favorites')
        ->name('user-favorites');
    Route::get('/favorite/{id1?}/{id2?}', 'User\UserController@favorite')
        ->name('user-favorite');
    Route::get('/favorite/seller/{id}/delete', 'User\UserController@favdelete')
        ->name('user-favorite-delete');

    // User Favorite Seller Ends
    // User point log
    Route::get('/pointlog', 'User\UserPointLogController@index')
        ->name('user-point-logs-index');
    Route::get('/pointlog/{type}/{from?}/{to?}', 'User\UserPointLogController@datatable')
        ->name('user-point-logs-datatable');

    // User point log Ends
    // User Logout
    Route::get('/logout', 'User\LoginController@logout')
        ->name('user-logout');
    // User Logout Ends
    //KOL bonus
    Route::get('/general-settings/getkol/{date}', 'User\OrderController@getKol')
        ->name('user-get-kol');
    Route::get('/orders/reports/kolconsumerkolbonus', 'User\OrderController@kolConsumerBonusForUser')
        ->name('user-order-kol-bonus');
    Route::get('/orders/reports/kolconsumerbonus/datatables/{from}', 'User\OrderController@datatablesKOLConsumerBonusForUser')
        ->name('user-order-kol-bonus-datatables');
    Route::get('/orders/reports/kolconsumerbonus/export/{from}', 'User\OrderController@exportKOLConsumerBonusForUser')
        ->name('user-order-report-kol-consumerbonus-export');

    Route::get('/transfer-point', 'User\PointController@show')
        ->name('user-transfer-point');

    Route::post('/transfer-point', 'User\PointController@transfer');

});

// ************************************ USER SECTION ENDS**********************************************


Route::post('the/genius/ocean/2441139', 'Front\FrontendController@subscription');
Route::get('finalize', 'Front\FrontendController@finalize');

Route::get('/under-maintenance', 'Front\FrontendController@maintenance')
    ->name('front-maintenance');

Route::group(['middleware' => 'maintenance'], function ()
{
    // ************************************ VENDOR SECTION **********************************************
    Route::prefix('vendor')->group(function ()
    {

        Route::group(['middleware' => 'vendor'], function ()
        {
            // Vendor Dashboard
            Route::get('/dashboard', 'Vendor\VendorController@index')
                ->name('vendor-dashboard');
            
            //IMPORT SECTION
            Route::get('/products/import/create', 'Vendor\ImportController@createImport')
                ->name('vendor-import-create');
            Route::get('/products/import/edit/{id}', 'Vendor\ImportController@edit')
                ->name('vendor-import-edit');
            Route::get('/products/import/csv', 'Vendor\ImportController@importCSV')
                ->name('vendor-import-csv');
            Route::get('/products/import/datatables', 'Vendor\ImportController@datatables')
                ->name('vendor-import-datatables');
            Route::get('/products/import/index', 'Vendor\ImportController@index')
                ->name('vendor-import-index');
            Route::post('/products/import/store', 'Vendor\ImportController@store')
                ->name('vendor-import-store');
            Route::post('/products/import/update/{id}', 'Vendor\ImportController@update')
                ->name('vendor-import-update');
            Route::post('/products/import/csv/store', 'Vendor\ImportController@importStore')
                ->name('vendor-import-csv-store');

            //IMPORT SECTION


            //------------ ADMIN ORDER SECTION ------------
            // Route::any('/orders', 'Vendor\OrderController@index')->name('vendor-order-index');
            // Route::any('/orders/{status}', 'Vendor\OrderController@ordersStatus')->name('vendor-get-order-status');
            Route::any('/orders/{status?}', 'Vendor\OrderController@ordersStatus')->name('vendor-order-index');
            Route::get('/orders/export/{status}/{from?}/{to?}', 'Vendor\OrderController@exportOrder')->name('vendor-export-order-status');

            Route::get('/order/{id}/show', 'Vendor\OrderController@show')
                ->name('vendor-order-show');
            Route::get('/order/{id}/invoice', 'Vendor\OrderController@invoice')
                ->name('vendor-order-invoice');
            Route::get('/order/{id}/print', 'Vendor\OrderController@printpage')
                ->name('vendor-order-print');
            Route::get('/order/{id1?}/status/{status?}', 'Vendor\OrderController@status')
                ->name('vendor-order-status');
            Route::post('/order/email/', 'Vendor\OrderController@emailsub')
                ->name('vendor-order-emailsub');
            Route::post('/order/{slug}/license', 'Vendor\OrderController@license')
                ->name('vendor-order-license');

            //------------ ADMIN CATEGORY SECTION ENDS------------


            //------------ VENDOR SUBCATEGORY SECTION ------------
            Route::get('/load/subcategories/{id}/', 'Vendor\VendorController@subcatload')
                ->name('vendor-subcat-load'); //JSON REQUEST
            //------------ VENDOR SUBCATEGORY SECTION ENDS------------
            //------------ VENDOR CHILDCATEGORY SECTION ------------
            Route::get('/load/childcategories/{id}/', 'Vendor\VendorController@childcatload')
                ->name('vendor-childcat-load'); //JSON REQUEST
            //------------ VENDOR CHILDCATEGORY SECTION ENDS------------
            //------------ VENDOR PRODUCT SECTION ------------
            Route::get('/products/datatables', 'Vendor\ProductController@datatables')
                ->name('vendor-prod-datatables'); //JSON REQUEST
            Route::get('/products', 'Vendor\ProductController@index')
                ->name('vendor-prod-index');

            Route::post('/products/upload/update/{id}', 'Vendor\ProductController@uploadUpdate')
                ->name('vendor-prod-upload-update');

            // FEATURE SECTION
            Route::get('/products/feature/{id}', 'Vendor\ProductController@feature')
                ->name('vendor-prod-feature');
            Route::post('/products/feature/{id}', 'Vendor\ProductController@featuresubmit')
                ->name('vendor-prod-feature');
            // FEATURE SECTION ENDS
            // CREATE SECTION
            Route::get('/products/types', 'Vendor\ProductController@types')
                ->name('vendor-prod-types');
            Route::get('/products/physical/create', 'Vendor\ProductController@createPhysical')
                ->name('vendor-prod-physical-create');
            Route::get('/products/digital/create', 'Vendor\ProductController@createDigital')
                ->name('vendor-prod-digital-create');
            Route::get('/products/license/create', 'Vendor\ProductController@createLicense')
                ->name('vendor-prod-license-create');
            Route::post('/products/store', 'Vendor\ProductController@store')
                ->name('vendor-prod-store');
            Route::get('/getattributes', 'Vendor\ProductController@getAttributes')
                ->name('vendor-prod-getattributes');
            Route::get('/products/import', 'Vendor\ProductController@import')
                ->name('vendor-prod-import');
            Route::post('/products/import-submit', 'Vendor\ProductController@importSubmit')
                ->name('vendor-prod-importsubmit');

            Route::get('/products/catalog/datatables', 'Vendor\ProductController@catalogdatatables')
                ->name('admin-vendor-catalog-datatables');
            Route::get('/products/catalogs', 'Vendor\ProductController@catalogs')
                ->name('admin-vendor-catalog-index');

            // CREATE SECTION
            // EDIT SECTION
            Route::get('/products/edit/{id}', 'Vendor\ProductController@edit')
                ->name('vendor-prod-edit');
            Route::post('/products/edit/{id}', 'Vendor\ProductController@update')
                ->name('vendor-prod-update');

            Route::get('/products/catalog/{id}', 'Vendor\ProductController@catalogedit')
                ->name('vendor-prod-catalog-edit');
            Route::post('/products/catalog/{id}', 'Vendor\ProductController@catalogupdate')
                ->name('vendor-prod-catalog-update');

            // EDIT SECTION ENDS
            // STATUS SECTION
            Route::get('/products/status/{id1}/{id2}', 'Vendor\ProductController@status')
                ->name('vendor-prod-status');
            // STATUS SECTION ENDS
            // DELETE SECTION
            Route::get('/products/delete/{id}', 'Vendor\ProductController@destroy')
                ->name('vendor-prod-delete');
            // DELETE SECTION ENDS
            //------------ VENDOR PRODUCT SECTION ENDS------------
            //------------ VENDOR COUPON SECTION ENDS------------
            Route::get('/coupon/datatables', 'Vendor\CouponController@datatables')
                ->name('vendor-coupon-datatables'); //JSON REQUEST
            Route::get('/coupon', 'Vendor\CouponController@index')
                ->name('vendor-coupon-index');
            Route::get('/coupon/create', 'Vendor\CouponController@create')
                ->name('vendor-coupon-create');
            Route::post('/coupon/create', 'Vendor\CouponController@store')
                ->name('vendor-coupon-store');
            Route::get('/coupon/edit/{id}', 'Vendor\CouponController@edit')
                ->name('vendor-coupon-edit');
            Route::post('/coupon/edit/{id}', 'Vendor\CouponController@update')
                ->name('vendor-coupon-update');
            Route::get('/coupon/delete/{id}', 'Vendor\CouponController@destroy')
                ->name('vendor-coupon-delete');
            Route::get('/coupon/status/{id1}/{id2}', 'Vendor\CouponController@status')
                ->name('vendor-coupon-status');

            //------------ VENDOR COUPON SECTION ENDS------------
            //------------ VENDOR GALLERY SECTION ------------
            Route::get('/gallery/show', 'Vendor\GalleryController@show')
                ->name('vendor-gallery-show');
            Route::post('/gallery/store', 'Vendor\GalleryController@store')
                ->name('vendor-gallery-store');
            Route::get('/gallery/delete', 'Vendor\GalleryController@destroy')
                ->name('vendor-gallery-delete');

            //------------ VENDOR GALLERY SECTION ENDS------------
            //------------ ADMIN SHIPPING ------------
            Route::get('/shipping/datatables', 'Vendor\ShippingController@datatables')
                ->name('vendor-shipping-datatables');
            Route::get('/shipping', 'Vendor\ShippingController@index')
                ->name('vendor-shipping-index');
            Route::get('/shipping/create', 'Vendor\ShippingController@create')
                ->name('vendor-shipping-create');
            Route::post('/shipping/create', 'Vendor\ShippingController@store')
                ->name('vendor-shipping-store');
            Route::get('/shipping/edit/{id}', 'Vendor\ShippingController@edit')
                ->name('vendor-shipping-edit');
            Route::post('/shipping/edit/{id}', 'Vendor\ShippingController@update')
                ->name('vendor-shipping-update');
            Route::get('/shipping/delete/{id}', 'Vendor\ShippingController@destroy')
                ->name('vendor-shipping-delete');

            //------------ ADMIN SHIPPING ENDS ------------


            //------------ ADMIN PACKAGE ------------
            Route::get('/package/datatables', 'Vendor\PackageController@datatables')
                ->name('vendor-package-datatables');
            Route::get('/package', 'Vendor\PackageController@index')
                ->name('vendor-package-index');
            Route::get('/package/create', 'Vendor\PackageController@create')
                ->name('vendor-package-create');
            Route::post('/package/create', 'Vendor\PackageController@store')
                ->name('vendor-package-store');
            Route::get('/package/edit/{id}', 'Vendor\PackageController@edit')
                ->name('vendor-package-edit');
            Route::post('/package/edit/{id}', 'Vendor\PackageController@update')
                ->name('vendor-package-update');
            Route::get('/package/delete/{id}', 'Vendor\PackageController@destroy')
                ->name('vendor-package-delete');

            //------------ ADMIN PACKAGE ENDS------------


            //------------ VENDOR NOTIFICATION SECTION ------------
            // Order Notification
            Route::get('/order/notf/show/{id}', 'Vendor\NotificationController@order_notf_show')
                ->name('vendor-order-notf-show');
            Route::get('/order/notf/wallet', 'Vendor\NotificationController@order_notf_wallet')
                ->name('vendor-order-notf-wallet');
            Route::get('/order/notf/count/{id}', 'Vendor\NotificationController@order_notf_count')
                ->name('vendor-order-notf-count');
            Route::get('/order/notf/clear/{id}', 'Vendor\NotificationController@order_notf_clear')
                ->name('vendor-order-notf-clear');
            // Order Notification Ends
            // Product Notification Ends
            //------------ VENDOR NOTIFICATION SECTION ENDS ------------
            // Vendor Profile
            Route::get('/profile', 'Vendor\VendorController@profile')
                ->name('vendor-profile');
            Route::post('/profile', 'Vendor\VendorController@profileupdate')
                ->name('vendor-profile-update');
            // Vendor Profile Ends
            // Vendor Shipping Cost
            Route::get('/shipping-cost', 'Vendor\VendorController@ship')
                ->name('vendor-shop-ship');

            // Vendor Shipping Cost
            Route::get('/banner', 'Vendor\VendorController@banner')
                ->name('vendor-banner');

            // Vendor Social
            Route::get('/social', 'Vendor\VendorController@social')
                ->name('vendor-social-index');
            Route::post('/social/update', 'Vendor\VendorController@socialupdate')
                ->name('vendor-social-update');

            Route::get('/withdraw/datatables', 'Vendor\WithdrawController@datatables')
                ->name('vendor-wt-datatables');
            Route::get('/withdraw', 'Vendor\WithdrawController@index')
                ->name('vendor-wt-index');
            Route::get('/withdraw/create', 'Vendor\WithdrawController@create')
                ->name('vendor-wt-create');
            Route::post('/withdraw/create', 'Vendor\WithdrawController@store')
                ->name('vendor-wt-store');

            Route::get('/service/datatables', 'Vendor\ServiceController@datatables')
                ->name('vendor-service-datatables');
            Route::get('/service', 'Vendor\ServiceController@index')
                ->name('vendor-service-index');
            Route::get('/service/create', 'Vendor\ServiceController@create')
                ->name('vendor-service-create');
            Route::post('/service/create', 'Vendor\ServiceController@store')
                ->name('vendor-service-store');
            Route::get('/service/edit/{id}', 'Vendor\ServiceController@edit')
                ->name('vendor-service-edit');
            Route::post('/service/edit/{id}', 'Vendor\ServiceController@update')
                ->name('vendor-service-update');
            Route::get('/service/delete/{id}', 'Vendor\ServiceController@destroy')
                ->name('vendor-service-delete');

            Route::get('/verify', 'Vendor\VendorController@verify')
                ->name('vendor-verify');
            Route::get('/warning/verify/{id}', 'Vendor\VendorController@warningVerify')
                ->name('vendor-warning');
            Route::post('/verify', 'Vendor\VendorController@verifysubmit')
                ->name('vendor-verify-submit');

        });

    });

    // ************************************ VENDOR SECTION ENDS**********************************************
    // ************************************ FRONT SECTION **********************************************
    include __DIR__ . "/front.php";
    // PAGE SECTION ENDS
    // ************************************ FRONT SECTION ENDS**********************************************
});

