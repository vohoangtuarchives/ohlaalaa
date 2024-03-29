<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// ************************************ ADMIN SECTION **********************************************
//Route::get('/', 'Front\DataController@data');
use App\Http\Controllers\Front\VendorController;;

Route::prefix('admin')->group(function ()
{

    //------------ ADMIN LOGIN SECTION ------------
    Route::get('/login', 'Admin\LoginController@showLoginForm')
        ->name('admin.login');
    Route::post('/login', 'Admin\LoginController@login')
        ->name('admin.login.submit');
    Route::get('/forgot', 'Admin\LoginController@showForgotForm')
        ->name('admin.forgot');
    Route::post('/forgot', 'Admin\LoginController@forgot')
        ->name('admin.forgot.submit');
    Route::get('/change-password/{token}', 'Admin\LoginController@showChangePassForm')
        ->name('admin.change.token');
    Route::post('/change-password', 'Admin\LoginController@changepass')
        ->name('admin.change.password');
    Route::get('/logout', 'Admin\LoginController@logout')
        ->name('admin.logout');

    //------------ ADMIN LOGIN SECTION ENDS ------------
    //------------ ADMIN NOTIFICATION SECTION ------------
    // Notification Count
    Route::get('/all/notf/count', 'Admin\NotificationController@all_notf_count')
        ->name('all-notf-count');
    // Notification Count Ends
    // User Notification
    Route::get('/user/notf/show', 'Admin\NotificationController@user_notf_show')
        ->name('user-notf-show');
    Route::get('/user/notf/clear', 'Admin\NotificationController@user_notf_clear')
        ->name('user-notf-clear');
    // User Notification Ends
    // Order Notification
    Route::get('/order/notf/show', 'Admin\NotificationController@order_notf_show')
        ->name('order-notf-show');
    Route::get('/order/notf/clear', 'Admin\NotificationController@order_notf_clear')
        ->name('order-notf-clear');
    // Order Notification Ends
    // Product Notification
    Route::get('/product/notf/show', 'Admin\NotificationController@product_notf_show')
        ->name('product-notf-show');
    Route::get('/product/notf/clear', 'Admin\NotificationController@product_notf_clear')
        ->name('product-notf-clear');
    // Product Notification Ends
    // Product Notification
    Route::get('/conv/notf/show', 'Admin\NotificationController@conv_notf_show')
        ->name('conv-notf-show');
    Route::get('/conv/notf/clear', 'Admin\NotificationController@conv_notf_clear')
        ->name('conv-notf-clear');
    // Product Notification Ends
    //------------ ADMIN NOTIFICATION SECTION ENDS ------------
    //------------ ADMIN DASHBOARD & PROFILE SECTION ------------
    Route::get('/', 'Admin\DashboardController@index')
        ->name('admin.dashboard');
    Route::get('/profile', 'Admin\DashboardController@profile')
        ->name('admin.profile');
    Route::post('/profile/update', 'Admin\DashboardController@profileupdate')
        ->name('admin.profile.update');
    Route::get('/profile/token', 'Admin\DashboardController@profiletoken')
        ->name('admin.profile.token');
    Route::get('/password', 'Admin\DashboardController@passwordreset')
        ->name('admin.password');
    Route::post('/password/update', 'Admin\DashboardController@changepass')
        ->name('admin.password.update');
    //------------ ADMIN DASHBOARD & PROFILE SECTION ENDS ------------
    //------------ API TOKEN ------------
    Route::get('/token', 'User\ApiTokenController@getnewtoken')
        ->name('admin.apitoken');
    //------------ API TOKEN ENDS ------------


    //------------ ADMIN ORDER SECTION ------------
    Route::group(['middleware' => 'permissions:orders'], function ()
    {
        Route::get('/orders/datatables/{slug}/{from?}/{to?}/{email?}/{delivery_code?}', 'Admin\OrderController@datatables')
            ->name('admin-order-datatables'); //JSON REQUEST
        Route::get('/orders/datatablesv2/remainingp/{status}/{from?}/{to?}/{email?}/{delivery_code?}', 'Admin\OrderController@datatables_remainingp')
            ->name('admin-order-datatables-remaining-payment'); //JSON REQUEST
        Route::get('/orders/datatablesreport/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@datatablesReport')
            ->name('admin-order-datatables-report');

        Route::get('/orders', 'Admin\OrderController@index')
            ->name('admin-order-index');
        Route::get('/order/edit/{id}', 'Admin\OrderController@edit')
            ->name('admin-order-edit');
        Route::get('/order/edit/invoice/{id}', 'Admin\OrderController@edit_invoice')
            ->name('admin-order-edit-invoice');
        Route::get('/order/report', 'Admin\OrderController@ordersreport')
            ->name('admin-order-ordersreport');

        // Route::get('/order/report/merchantp', 'Admin\OrderController@merchantp_report')
        //     ->name('admin-order-report-merchant-payment');
        // Route::get('/orders/datatablesv2/report/merchantp/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@datatables_report_merchantp')
        // ->name('admin-order-datatables-report-merchantp');
        // Route::get('/orders/export/report/merchantp/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@export_report_merchantp')->name('admin-order-export-report-merchantp');

        Route::get('/order/deliverypartner', 'Admin\OrderController@delivery_partner')
            ->name('admin-order-deliverypartner');
        Route::get('/order/datatable/deliverypartner/{status}/{from?}/{to?}', 'Admin\OrderController@datatable_delivery_partner')
            ->name('admin-order-deliverypartner-datatable');
        Route::post('/order/update/{id}', 'Admin\OrderController@update')
            ->name('admin-order-update');
        Route::post('/order/update/invoiceinfo/{id}', 'Admin\OrderController@update_invoice_info')
            ->name('admin-order-update-invoice-info');
        Route::post('/order/collecthandlingfee/{id}/{shopid}', 'Admin\OrderController@collecthandlingfee')
            ->name('admin-order-collect-handling-fee');
        Route::post('/order/collecthandlingfeenew/{id}/{shopid}', 'Admin\OrderController@collecthandlingfee_new')
            ->name('admin-order-collect-handling-fee-new');
        Route::post('/order/rollbackhandlingfee/{id}/{shopid}', 'Admin\OrderController@rollbackhandlingfee')
            ->name('admin-order-rollback-handling-fee');
        Route::get('/orders/pending', 'Admin\OrderController@pending')
            ->name('admin-order-pending');
        Route::get('/orders/processing', 'Admin\OrderController@processing')
            ->name('admin-order-processing');
        Route::get('/orders/completed', 'Admin\OrderController@completed')
            ->name('admin-order-completed');
        Route::get('/orders/remainingp', 'Admin\OrderController@remainingp')
            ->name('admin-order-remaining-payment');
        Route::get('/orders/declined', 'Admin\OrderController@declined')
            ->name('admin-order-declined');
        Route::get('/orders/ondelivery', 'Admin\OrderController@ondelivery')
            ->name('admin-order-ondelivery');
        Route::get('/order/{id}/show', 'Admin\OrderController@show')
            ->name('admin-order-show');
        Route::get('/order/{id}/invoice', 'Admin\OrderController@invoice')
            ->name('admin-order-invoice');
        Route::get('/order/{id}/print', 'Admin\OrderController@printpage')
            ->name('admin-order-print');
        Route::get('/order/{id1}/status/{status}', 'Admin\OrderController@status')
            ->name('admin-order-status');
        Route::post('/order/email/', 'Admin\OrderController@emailsub')
            ->name('admin-order-emailsub');
        Route::post('/order/{id}/license', 'Admin\OrderController@license')
            ->name('admin-order-license');
        Route::get('/order/vnpaylog/{id?}', 'Admin\OrderController@view_vnpay_log')
            ->name('admin-vnpay-log');

        // // Order salefull Report
        // Route::get('/order/report/salefull', 'Admin\OrderController@sale_full_report')
        //     ->name('admin-order-report-sale-full');
        // Route::get('/orders/datatablesv2/report/salefull/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@datatables_report_sale_full')
        //     ->name('admin-order-datatables-report-sale-full');
        // Route::get('/orders/export/report/salefull/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@export_report_sale_full')
        //     ->name('admin-order-export-report-sale-full');

        // // Order Report
        // Route::get('/orders/reports/merchantperformance/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformance')
        //     ->name('admin-order-report-merchant-performance-datatables');
        // Route::get('/orders/reports/merchantperformance/export/{from}/{to}', 'Admin\OrderController@exportMerchantPerformance')
        //     ->name('admin-order-report-merchant-performance-export');
        // Route::get('/orders/reports/merchantperformance', 'Admin\OrderController@merchantPerformance')
        //     ->name('admin-order-report-merchant-performance');

        // //FILE2.1 -  Summaries  - Top 10 Performance Sales by Shop
        // Route::get('/orders/reports/merchantperformance/summaries/top10byshop', 'Admin\OrderController@merchantPerformanceSummariesTop10ByShop')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byshop');
        // Route::get('/orders/reports/merchantperformance/summaries/top10byshop/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformanceSummariesTop10ByShop')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byshop-datatables');
        // Route::get('/orders/reports/merchantperformance/summaries/top10byshop/export/{from}/{to}/{top?}/{isBySales?}', 'Admin\OrderController@exportMerchantPerformanceSummariesTop10ByShop')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byshop-export');

        // //FILE2.2 -  Summaries  - Top 10 Performance Sales by Product
        // Route::get('/orders/reports/merchantperformance/summaries/top10byproduct', 'Admin\OrderController@merchantPerformanceSummariesTop10ByProduct')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byproduct');
        // Route::get('/orders/reports/merchantperformance/summaries/top10byproduct/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformanceSummariesTop10ByProduct')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byproduct-datatables');
        // Route::get('/orders/reports/merchantperformance/summaries/top10byproduct/export/{from}/{to}', 'Admin\OrderController@exportMerchantPerformanceSummariesTop10ByProduct')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byproduct-export');

        // //FILE2.3 -  Summaries  - Top 10 Performance Sales by Product category
        // Route::get('/orders/reports/merchantperformance/summaries/top10byproductcategory', 'Admin\OrderController@merchantPerformanceSummariesTop10ByProductCategory')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byproductcategory');
        // Route::get('/orders/reports/merchantperformance/summaries/top10byproductcategory/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformanceSummariesTop10ByProductCategory')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byproductcategory-datatables');
        // Route::get('/orders/reports/merchantperformance/summaries/top10byproductcategory/export/{from}/{to}', 'Admin\OrderController@exportMerchantPerformanceSummariesTop10ByProductCategory')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byproductcategory-export');

        // //FILE2.4 -  Summaries  - Top 10 Performance Sales by Province
        // Route::get('/orders/reports/merchantperformance/summaries/top10byprovince', 'Admin\OrderController@merchantPerformanceSummariesTop10ByProvince')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byprovince');
        // Route::get('/orders/reports/merchantperformance/summaries/top10byprovince/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformanceSummariesTop10ByProvince')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byprovince-datatables');
        // Route::get('/orders/reports/merchantperformance/summaries/top10byprovince/export/{from}/{to}', 'Admin\OrderController@exportMerchantPerformanceSummariesTop10ByProvince')
        //     ->name('admin-order-report-merchant-performance-summaries-top10byprovince-export');

        //Order refund detail
        Route::get('/orders/reports/refunddetail/datatables/{from}/{to}', 'Admin\OrderController@datatablesOrderRefundDetail')
            ->name('admin-order-report-refund-detail-datatables');
        Route::get('/orders/reports/refunddetail', 'Admin\OrderController@orderRefundDetail')
            ->name('admin-order-report-refund-detail');
        Route::get('/orders/reports/refunddetail/export/{from}/{to}', 'Admin\OrderController@exportOrderRefundDetail')
            ->name('admin-order-report-refund-detail-export');

        // //Order Sale Full Report detail
        // Route::get('/orders/reports/salefulldetail/datatables/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@datatablesSaleFullReportDetail')
        //     ->name('admin-order-report-salefull-detail-datatables');
        // Route::get('/orders/reports/salefulldetail', 'Admin\OrderController@saleFullReportDetail')
        //     ->name('admin-order-report-salefull-detail');
        // Route::get('/orders/reports/salefulldetail/export/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@exportSaleFullReportDetail')
        //     ->name('admin-order-report-salefull-detail-export');

        //thannd
        // Route::get('/orders/reports/kolconsumerbonus/datatables/{from}/{to}', 'Admin\OrderController@datatablesKOLConsumerBonus2Weeks')->name('admin-order-report-kol-consumerbonus-datatables-2w');
        // Route::get('/orders/reports/kolconsumerbonus2w', 'Admin\OrderController@kolConsumerBonus2w')->name('admin-order-report-kol-consumerbonus-2w');
        // Route::get('/orders/reports/kolconsumerbonus2w/export/{from}', 'Admin\OrderController@exportKOLConsumerBonus2w')->name('admin-order-report-kol-consumerbonus-export-2w');
        // Route::get('/orders/reports/kolconsumerbonus/processpay/{from}', 'Admin\OrderController@processPayKOLConsumerBonus')->name('admin-order-report-kol-consumerbonus-processpay-2w');

        Route::get('/orders/reports/kolconsumerbonus/datatables/{from}', 'Admin\OrderController@datatablesKOLConsumerBonus')
            ->name('admin-order-report-kol-consumerbonus-datatables');
        Route::get('/orders/reports/kolconsumerbonus', 'Admin\OrderController@kolConsumerBonus')
            ->name('admin-order-report-kol-consumerbonus');
        Route::get('/orders/reports/kolconsumerbonus/export/{from}', 'Admin\OrderController@exportKOLConsumerBonus')
            ->name('admin-order-report-kol-consumerbonus-export');
        Route::get('/orders/reports/kolconsumerbonus/processpay/{from}', 'Admin\OrderController@processPayKOLConsumerBonus')
            ->name('admin-order-report-kol-consumerbonus-processpay');

        Route::get('/orders/reports/kolconsumerbonus/paid/datatables/{from?}/{to?}', 'Admin\OrderController@datatablesKOLConsumerBonusPaid')
            ->name('admin-order-report-kol-consumerbonus-paid-datatables');
        Route::get('/orders/reports/kolconsumerbonus/paid', 'Admin\OrderController@kolConsumerBonusPaid')
            ->name('admin-order-report-kol-consumerbonus-paid');
        Route::get('/orders/reports/kolconsumerbonus/paid/export/{from?}/{to?}', 'Admin\OrderController@exportKOLConsumerBonusPaid')
            ->name('admin-order-report-kol-consumerbonus-paid-export');

        // Order Tracking
        Route::get('/order/{id}/track', 'Admin\OrderTrackController@index')
            ->name('admin-order-track');
        Route::get('/order/{id}/trackload', 'Admin\OrderTrackController@load')
            ->name('admin-order-track-load');
        Route::post('/order/track/store', 'Admin\OrderTrackController@store')
            ->name('admin-order-track-store');
        Route::get('/order/track/add', 'Admin\OrderTrackController@add')
            ->name('admin-order-track-add');
        Route::get('/order/track/edit/{id}', 'Admin\OrderTrackController@edit')
            ->name('admin-order-track-edit');
        Route::post('/order/track/update/{id}', 'Admin\OrderTrackController@update')
            ->name('admin-order-track-update');
        Route::get('/order/track/delete/{id}', 'Admin\OrderTrackController@delete')
            ->name('admin-order-track-delete');

        Route::get('/order/track/cart/items/{id}', 'Admin\OrderTrackController@cartItems')
            ->name('admin-order-track-cart-items');
        Route::get('/order/track/checkout/{id}', 'Admin\OrderTrackController@checkout')
            ->name('admin-order-track-checkout');
        Route::get('/order/track/recover/vendororder/{id}', 'Admin\OrderTrackController@recoverVendorOrder')
            ->name('admin-order-track-recover-vendororder');

        // Order Tracking Ends

    });

    //------------ ADMIN ORDER SECTION ENDS------------
    //------------ ADMIN EXTERNAL SECTION ENDS------------
    Route::group(['middleware' => 'permissions:externalreport'], function ()
    {
        //operation report
        Route::get('/external/operation/report', 'External\Operation\ReportController@index')
            ->name('admin-operation-report');
        Route::get('/external/operation/report/datatables/{from?}', 'External\Operation\ReportController@datatablesOperationReport')
            ->name('get-admin-operation-report');
        Route::get('/external/operation/report/export/{from}', 'External\Operation\ReportController@exportOperationReport')
            ->name('admin-operation-report-export');
        /////////////////

    });

    //------------ ADMIN PRODUCT SECTION ------------
    Route::group(['middleware' => 'permissions:products'], function ()
    {

        Route::get('/products/datatables/{category}/{highlight}/{from?}/{to?}/{key?}', 'Admin\ProductController@datatables')
            ->name('admin-prod-datatables'); //JSON REQUEST
        Route::get('/products', 'Admin\ProductController@index')
            ->name('admin-prod-index');

        Route::post('/products/upload/update/{id}', 'Admin\ProductController@uploadUpdate')
            ->name('admin-prod-upload-update');

        Route::get('/products/deactive/datatables', 'Admin\ProductController@deactivedatatables')
            ->name('admin-prod-deactive-datatables'); //JSON REQUEST
        Route::get('/products/deactive', 'Admin\ProductController@deactive')
            ->name('admin-prod-deactive');

        Route::get('/products/catalogs/datatables', 'Admin\ProductController@catalogdatatables')
            ->name('admin-prod-catalog-datatables'); //JSON REQUEST
        Route::get('/products/catalogs/', 'Admin\ProductController@catalogs')
            ->name('admin-prod-catalog-index');

        // CREATE SECTION
        Route::get('/products/types', 'Admin\ProductController@types')
            ->name('admin-prod-types');
        Route::get('/products/physical/create', 'Admin\ProductController@createPhysical')
            ->name('admin-prod-physical-create');
        Route::get('/products/digital/create', 'Admin\ProductController@createDigital')
            ->name('admin-prod-digital-create');
        Route::get('/products/license/create', 'Admin\ProductController@createLicense')
            ->name('admin-prod-license-create');
        Route::post('/products/store', 'Admin\ProductController@store')
            ->name('admin-prod-store');
        Route::get('/getattributes', 'Admin\ProductController@getAttributes')
            ->name('admin-prod-getattributes');
        // CREATE SECTION
        // EDIT SECTION
        Route::get('/products/edit/{id}', 'Admin\ProductController@edit')
            ->name('admin-prod-edit');
        Route::post('/products/edit/{id}', 'Admin\ProductController@update')
            ->name('admin-prod-update');
        // EDIT SECTION ENDS


        // DELETE SECTION
        Route::get('/products/delete/{id}', 'Admin\ProductController@destroy')
            ->name('admin-prod-delete');
        // DELETE SECTION ENDS


        Route::get('/products/catalog/{id1}/{id2}', 'Admin\ProductController@catalog')
            ->name('admin-prod-catalog');
        //------------ ADMIN PRODUCT SECTION ENDS------------

    });

    //------------ ADMIN AFFILIATE PRODUCT SECTION ------------
    Route::group(['middleware' => 'permissions:affilate_products'], function ()
    {

        Route::get('/products/import/create', 'Admin\ImportController@createImport')
            ->name('admin-import-create');
        Route::get('/products/import/edit/{id}', 'Admin\ImportController@edit')
            ->name('admin-import-edit');

        Route::get('/products/import/datatables', 'Admin\ImportController@datatables')
            ->name('admin-import-datatables'); //JSON REQUEST
        Route::get('/products/import/index', 'Admin\ImportController@index')
            ->name('admin-import-index');

        Route::post('/products/import/store', 'Admin\ImportController@store')
            ->name('admin-import-store');
        Route::post('/products/import/update/{id}', 'Admin\ImportController@update')
            ->name('admin-import-update');
        Route::get('/htd/migrations/order', 'Admin\ImportController@complete_htd_migration_order')
            ->name('admin-import-migration-complete-order');
        Route::get('/htd/migrations/order/{id}', 'Admin\ImportController@complete_htd_migration_order_id')
            ->name('admin-import-migration-complete-order-id');
        Route::get('/htd/migrations/orderupdate', 'Admin\ImportController@complete_htd_migration_order_update')
            ->name('admin-import-migration-complete-order-update');
        Route::get('/htd/migrations/user', 'Admin\ImportController@complete_htd_migration_user')
            ->name('admin-import-migration-complete-user');
        Route::get('/htd/migrations/shippingcost', 'Admin\ImportController@complete_htd_migration_shipping_cost')
            ->name('admin-import-migration-complete-shippingcost');

        // DELETE SECTION
        Route::get('/affiliate/products/delete/{id}', 'Admin\ProductController@destroy')
            ->name('admin-affiliate-prod-delete');
        // DELETE SECTION ENDS

    });

    //------------ ADMIN AFFILIATE PRODUCT SECTION ENDS ------------


    //------------ ADMIN USER SECTION ------------
    Route::group(['middleware' => 'permissions:customers'], function ()
    {

        Route::get('/users/datatables/{status}/{rank}/{from?}/{to?}/{key?}', 'Admin\UserController@datatables')
            ->name('admin-user-datatables');
        //Route::get('/users/datatables', 'Admin\UserController@datatables')->name('admin-user-datatables'); //JSON REQUEST
        Route::get('/users', 'Admin\UserController@index')
            ->name('admin-user-index');
        Route::get('/users/edit/{id}', 'Admin\UserController@edit')
            ->name('admin-user-edit');
        Route::post('/users/edit/{id}', 'Admin\UserController@update')
            ->name('admin-user-update');
        Route::get('/users/delete/{id}', 'Admin\UserController@destroy')
            ->name('admin-user-delete');
        Route::get('/users/kol/{id}', 'Admin\UserController@confirmKol')
            ->name('admin-user-confirm-kol');
        Route::get('/users/specialkol/{id}', 'Admin\UserController@confirmSpecialKol')
            ->name('admin-user-confirm-special-kol');

        Route::get('/users/set-tranfer-point/{id}', 'Admin\UserController@confirmTransferPoint')
            ->name('admin-user-confirmTransferPoint');

        Route::get('/user/{id}/show', 'Admin\UserController@show')
            ->name('admin-user-show');
        Route::get('/users/ban/{id1}/{id2}', 'Admin\UserController@ban')
            ->name('admin-user-ban');
        Route::get('/users/verify/{id}', 'Admin\UserController@verify')
            ->name('admin-user-verify');
        Route::get('/user/default/image', 'Admin\UserController@image')
            ->name('admin-user-image');
        Route::get('/user/rank/update/{id1}/{id2}', 'Admin\UserController@rank_update')
            ->name('admin-user-rank-update');
        Route::get('/user/rank/refresh/{id}', 'Admin\UserController@refreshRank')
            ->name('admin-user-rank-refresh');
        Route::get('/user/affiliate/findroot/{id}', 'Admin\UserController@findRootAffiliate')
            ->name('admin-user-affiliate-findroot');
        Route::get('/users/secret/login/{id}', 'Admin\UserController@secret')
            ->name('admin-user-secret');

        Route::get('/users/points/transfer', 'Admin\UserController@showTransferPoint')
            ->name('admin-user-transfer-point');

        Route::post('/users/points/transfer', 'Admin\UserController@adminSubmitTransferPoint');


        // MEMBER PACKAGE
        Route::get('/user/memberpackage', 'Admin\UserController@member_package')
            ->name('admin-user-member-package');
        Route::get('/user/memberpackage/datatables/{from}/{to}/{status}', 'Admin\UserController@member_package_datatables')
            ->name('admin-user-member-package-datatables');
        Route::get('/user/memberpackage/export/{from}/{to}/{status}', 'Admin\UserController@exportMemberPackage')
            ->name('admin-user-member-package-export');

        Route::get('/user/memberpackage/approve/{id}', 'Admin\UserController@member_package_approve')
            ->name('admin-user-member-package-approve');
        Route::get('/user/memberpackage/reject/{id}', 'Admin\UserController@member_package_reject')
            ->name('admin-user-member-package-reject');

        // WITHDRAW SECTION
        Route::get('/users/withdraws/datatables', 'Admin\UserController@withdrawdatatables')
            ->name('admin-withdraw-datatables'); //JSON REQUEST
        Route::get('/users/withdraws', 'Admin\UserController@withdraws')
            ->name('admin-withdraw-index');
        Route::get('/user/withdraw/{id}/show', 'Admin\UserController@withdrawdetails')
            ->name('admin-withdraw-show');
        Route::get('/users/withdraws/accept/{id}', 'Admin\UserController@accept')
            ->name('admin-withdraw-accept');
        Route::get('/user/withdraws/reject/{id}', 'Admin\UserController@reject')
            ->name('admin-withdraw-reject');
        // WITHDRAW SECTION ENDS
        //FILE3 - Weekly Sales Report - Customer
        Route::get('/users/reports/salereport/datatables/{from}/{to}/{isTop?}/{isOrder?}', 'Admin\UserController@datatablesSaleReport')
            ->name('admin-user-report-sale-report-datatables');
        Route::get('/users/reports/salereport/export/{from}/{to}/{isTop?}/{isOrder?}/{top?}', 'Admin\UserController@exportSaleReport')
            ->name('admin-user-report-sale-report-export');
        Route::get('/users/reports/salereport', 'Admin\UserController@saleReport')
            ->name('admin-user-report-sale-report');

        //FILE3 -  Summaries  - Top Spending Customer by Total Purchase Amount
        Route::get('/users/reports/salereport/summaries/top10spendingamount', 'Admin\UserController@saleReportSummariesTop10SpendingAmount')
            ->name('admin-user-report-sale-report-summaries-top10spendingamount');

        //FILE3 -  Summaries  - Top Spending Customer by Total Order
        Route::get('/users/reports/salereport/summaries/top10spendingorder', 'Admin\UserController@saleReportSummariesTop10SpendingOrder')
            ->name('admin-user-report-sale-report-summaries-top10spendingorder');

        //FILE3 -  Summaries  - SP earning member by Affiliate Bonus
        Route::get('/users/reports/salereport/summaries/top10spearningbyaffiliate/datatables/{from}/{to}', 'Admin\UserController@datatablesSaleReportSummariesTop10SPEarningByAffiliate')
            ->name('admin-user-report-sale-report-summaries-top10spearningbyaffiliate-datatables');
        Route::get('/users/reports/salereport/summaries/top10spearningbyaffiliate/export/{from}/{to}', 'Admin\UserController@exportSaleReportSummariesTop10SPEarningByAffiliate')
            ->name('admin-user-report-sale-report-summaries-top10spearningbyaffiliate-export');
        Route::get('/users/reports/salereport/summaries/top10spearningbyaffiliate', 'Admin\UserController@saleReportSummariesTop10SPEarningByAffiliate')
            ->name('admin-user-report-sale-report-summaries-top10spearningbyaffiliate');

        //FILE3 -  Summaries  - RP earning member by Merchant Bonus (2)
        Route::get('/users/reports/salereport/summaries/top10rpearningbymerchantbonus/datatables/{from}/{to}', 'Admin\UserController@datatablesSaleReportSummariesTop10RPEarningByMerchantBonus')
            ->name('admin-user-report-sale-report-summaries-top10rpearningbymerchantbonus-datatables');
        Route::get('/users/reports/salereport/summaries/top10rpearningbymerchantbonus/export/{from}/{to}', 'Admin\UserController@exportSaleReportSummariesTop10RPEarningByMerchantBonus')
            ->name('admin-user-report-sale-report-summaries-top10rpearningbymerchantbonus-export');
        Route::get('/users/reports/salereport/summaries/top10rpearningbymerchantbonus', 'Admin\UserController@saleReportSummariesTop10RPEarningByMerchantBonus')
            ->name('admin-user-report-sale-report-summaries-top10rpearningbymerchantbonus');

        //KOL AFFILIATE BONUS
        Route::get('/users/reports/kolaffiliatebonus/datatables/{from?}/{is_include_renew?}', 'Admin\UserController@datatablesKOLAffiliateBonus')
            ->name('admin-user-report-kol-affiliatebonus-datatables');
        Route::get('/users/reports/kolaffiliatebonus', 'Admin\UserController@kolAffiliateBonus')
            ->name('admin-user-report-kol-affiliatebonus');
        Route::get('/users/reports/kolaffiliatebonus/export/{from?}/{is_include_renew?}', 'Admin\UserController@exportKOLAffiliateBonus')
            ->name('admin-user-report-kol-affiliatebonus-export');
        Route::get('/users/reports/kolaffiliatebonus/processpay/{from?}/{is_include_renew?}', 'Admin\UserController@processPayKOLAffiliateBonus')
            ->name('admin-user-report-kol-affiliatebonus-processpay');

        //KOL AFFILIATE PAID
        Route::get('/users/reports/kolaffiliatebonus/paid/datatables/{from}/{to}', 'Admin\UserController@datatablesKOLAffiliateBonusPaid')
            ->name('admin-user-report-kol-affiliatebonus-paid-datatables');
        Route::get('/users/reports/kolaffiliatebonus/paid', 'Admin\UserController@kolAffiliateBonusPaid')
            ->name('admin-user-report-kol-affiliatebonus-paid');
        Route::get('/users/reports/kolaffiliatebonus/paid/export/{from}/{to}', 'Admin\UserController@exportKOLAffiliateBonusPaid')
            ->name('admin-user-report-kol-affiliatebonus-paid-export');
    });

    //------------ ADMIN USER SECTION ENDS ------------
    //------------ ADMIN VENDOR SECTION ------------
    Route::group(['middleware' => 'permissions:vendors'], function ()
    {

        Route::get('/vendors/datatables/{status}/{plan}/{preferred}/{from?}/{to?}/{email?}', 'Admin\VendorController@datatables')
            ->name('admin-vendor-datatables');
        Route::get('/vendors', 'Admin\VendorController@index')
            ->name('admin-vendor-index');
        //   Route::get('/vendors/subs/export/{status}/{plan}/{from?}/{to?}', 'Admin\VendorController@subs_export')->name('admin-vendor-subs-export');
        Route::get('/vendors/export/{status}/{plan}/{preferred}/{from?}/{to?}/{email?}', 'Admin\VendorController@vendor_export')
            ->name('admin-vendor-export');

        Route::get('/vendors/{id}/show', 'Admin\VendorController@show')
            ->name('admin-vendor-show');
        Route::get('/vendors/secret/login/{id}', 'Admin\VendorController@secret')
            ->name('admin-vendor-secret');
        Route::get('/vendor/edit/{id}', 'Admin\VendorController@edit')
            ->name('admin-vendor-edit');
        Route::post('/vendor/edit/{id}', 'Admin\VendorController@update')
            ->name('admin-vendor-update');

        Route::get('/vendor/verify/{id}', 'Admin\VendorController@verify')
            ->name('admin-vendor-verify');
        Route::post('/vendor/verify/{id}', 'Admin\VendorController@verifySubmit')
            ->name('admin-vendor-verify-submit');

        //   Route::get('/vendors', 'Admin\VendorController@index')->name('admin-vendor-index');
        Route::get('/vendor/color', 'Admin\VendorController@color')
            ->name('admin-vendor-color');
        Route::get('/vendors/status/{id1}/{id2}', 'Admin\VendorController@status')
            ->name('admin-vendor-st');
        Route::get('/vendors/preferred/{id1}/{id2}', 'Admin\VendorController@preferred')
            ->name('admin-vendor-preferred');
        Route::get('/vendors/delete/{id}', 'Admin\VendorController@destroy')
            ->name('admin-vendor-delete');

        Route::get('/vendors/withdraws/datatables', 'Admin\VendorController@withdrawdatatables')
            ->name('admin-vendor-withdraw-datatables'); //JSON REQUEST
        Route::get('/vendors/withdraws', 'Admin\VendorController@withdraws')
            ->name('admin-vendor-withdraw-index');
        Route::get('/vendors/withdraw/{id}/show', 'Admin\VendorController@withdrawdetails')
            ->name('admin-vendor-withdraw-show');
        Route::get('/vendors/withdraws/accept/{id}', 'Admin\VendorController@accept')
            ->name('admin-vendor-withdraw-accept');
        Route::get('/vendors/withdraws/reject/{id}', 'Admin\VendorController@reject')
            ->name('admin-vendor-withdraw-reject');

        Route::get('/vendors/reports/merchantapproval/summaries/datatables/{from}/{to}', 'Admin\VendorController@datatablesMerchantApprovalSummaries')
            ->name('admin-vendor-report-merchant-approval-summaries-datatables');
        Route::get('/vendors/reports/merchantapproval/summaries/export/{from}/{to}', 'Admin\VendorController@exportMerchantApprovalSummaries')
            ->name('admin-vendor-report-merchant-approval-summaries-export');
        Route::get('/vendors/reports/merchantapproval/summaries', 'Admin\VendorController@indexMerchantApprovalSummaries')
            ->name('admin-vendor-report-merchant-approval-summaries');

        Route::get('/vendors/reports/merchantapproval/datatables/{from}/{to}', 'Admin\VendorController@datatablesMerchantApproval')
            ->name('admin-vendor-report-merchant-approval-datatables');
        Route::get('/vendors/reports/merchantapproval/export/{from}/{to}', 'Admin\VendorController@exportMerchantApproval')
            ->name('admin-vendor-report-merchant-approval-export');
        Route::get('/vendors/reports/merchantapproval', 'Admin\VendorController@indexMerchantApproval')
            ->name('admin-vendor-report-merchant-approval');

        //  Vendor Registration Section
        Route::get('/general-settings/vendor-registration/{status}', 'Admin\GeneralSettingController@regvendor')
            ->name('admin-gs-regvendor');

        //  Vendor Registration Section Ends


        // Verification Section
        Route::get('/verificatons/datatables/{status}/{from?}/{to?}/{email?}', 'Admin\VerificationController@datatables')
            ->name('admin-vr-datatables');
        Route::get('/verificatons/export/{status}/{from?}/{to?}', 'Admin\VerificationController@export')
            ->name('admin-vr-export');
        Route::get('/verificatons', 'Admin\VerificationController@index')
            ->name('admin-vr-index');
        Route::get('/verificatons/pendings', 'Admin\VerificationController@pending')
            ->name('admin-vr-pending');

        Route::get('/verificatons/show', 'Admin\VerificationController@show')
            ->name('admin-vr-show');
        Route::get('/verificatons/edit/{id}', 'Admin\VerificationController@edit')
            ->name('admin-vr-edit');
        Route::post('/verificatons/edit/{id}', 'Admin\VerificationController@update')
            ->name('admin-vr-update');
        Route::get('/verificatons/status/{id1}/{id2}', 'Admin\VerificationController@status')
            ->name('admin-vr-st');
        Route::get('/verificatons/delete/{id}', 'Admin\VerificationController@destroy')
            ->name('admin-vr-delete');

        // Verification Section Ends



    });

    //------------ ADMIN VENDOR SECTION ENDS ------------


    //------------ ADMIN SUBSCRIPTION SECTION ------------
    Route::group(['middleware' => 'permissions:vendor_subscription_plans'], function ()
    {

        Route::get('/subscription/datatables', 'Admin\SubscriptionController@datatables')
            ->name('admin-subscription-datatables');
        Route::get('/subscription', 'Admin\SubscriptionController@index')
            ->name('admin-subscription-index');
        Route::get('/subscription/create', 'Admin\SubscriptionController@create')
            ->name('admin-subscription-create');
        Route::post('/subscription/create', 'Admin\SubscriptionController@store')
            ->name('admin-subscription-store');
        Route::get('/subscription/edit/{id}', 'Admin\SubscriptionController@edit')
            ->name('admin-subscription-edit');
        Route::post('/subscription/edit/{id}', 'Admin\SubscriptionController@update')
            ->name('admin-subscription-update');
        Route::get('/subscription/delete/{id}', 'Admin\SubscriptionController@destroy')
            ->name('admin-subscription-delete');

        Route::get('/vendors/subs/datatables/{status}/{plan}/{from?}/{to?}', 'Admin\VendorController@subsdatatables')
            ->name('admin-vendor-subs-datatables');
        Route::get('/vendors/subs/export/{status}/{plan}/{from?}/{to?}', 'Admin\VendorController@subs_export')
            ->name('admin-vendor-subs-export');
        Route::get('/vendors/subs', 'Admin\VendorController@subs')
            ->name('admin-vendor-subs');
        Route::get('/vendors/sub/{id}', 'Admin\VendorController@sub')
            ->name('admin-vendor-sub');
        Route::get('/vendors/sub/approve/{id}', 'Admin\VendorController@subscription_approve')
            ->name('admin-vendor-subs-approve');
        Route::get('/vendors/sub/reject/{id}', 'Admin\VendorController@subscription_reject')
            ->name('admin-vendor-subs-reject');

    });

    //------------ ADMIN SUBSCRIPTION SECTION ENDS ------------


    //------------ ADMIN CATEGORY SECTION ------------
    Route::group(['middleware' => 'permissions:categories'], function ()
    {

        Route::get('/category/datatables', 'Admin\CategoryController@datatables')
            ->name('admin-cat-datatables'); //JSON REQUEST
        Route::get('/category', 'Admin\CategoryController@index')
            ->name('admin-cat-index');
        Route::get('/category/create', 'Admin\CategoryController@create')
            ->name('admin-cat-create');
        Route::post('/category/create', 'Admin\CategoryController@store')
            ->name('admin-cat-store');
        Route::get('/category/edit/{id}', 'Admin\CategoryController@edit')
            ->name('admin-cat-edit');
        Route::post('/category/edit/{id}', 'Admin\CategoryController@update')
            ->name('admin-cat-update');
        Route::get('/category/delete/{id}', 'Admin\CategoryController@destroy')
            ->name('admin-cat-delete');
        Route::get('/category/status/{id1}/{id2}', 'Admin\CategoryController@status')
            ->name('admin-cat-status');
        Route::get('/category/export', 'Admin\CategoryController@export')
            ->name('admin-cat-export');

        //------------ ADMIN ATTRIBUTE SECTION ------------
        Route::get('/attribute/datatables', 'Admin\AttributeController@datatables')
            ->name('admin-attr-datatables'); //JSON REQUEST
        Route::get('/attribute', 'Admin\AttributeController@index')
            ->name('admin-attr-index');
        Route::get('/attribute/{catid}/attrCreateForCategory', 'Admin\AttributeController@attrCreateForCategory')
            ->name('admin-attr-createForCategory');
        Route::get('/attribute/{subcatid}/attrCreateForSubcategory', 'Admin\AttributeController@attrCreateForSubcategory')
            ->name('admin-attr-createForSubcategory');
        Route::get('/attribute/{childcatid}/attrCreateForChildcategory', 'Admin\AttributeController@attrCreateForChildcategory')
            ->name('admin-attr-createForChildcategory');
        Route::post('/attribute/store', 'Admin\AttributeController@store')
            ->name('admin-attr-store');
        Route::get('/attribute/{id}/manage', 'Admin\AttributeController@manage')
            ->name('admin-attr-manage');
        Route::get('/attribute/{attrid}/edit', 'Admin\AttributeController@edit')
            ->name('admin-attr-edit');
        Route::post('/attribute/edit/{id}', 'Admin\AttributeController@update')
            ->name('admin-attr-update');
        Route::get('/attribute/{id}/options', 'Admin\AttributeController@options')
            ->name('admin-attr-options');
        Route::get('/attribute/delete/{id}', 'Admin\AttributeController@destroy')
            ->name('admin-attr-delete');

        // SUBCATEGORY SECTION ------------
        Route::get('/subcategory/datatables', 'Admin\SubCategoryController@datatables')
            ->name('admin-subcat-datatables'); //JSON REQUEST
        Route::get('/subcategory', 'Admin\SubCategoryController@index')
            ->name('admin-subcat-index');
        Route::get('/subcategory/create', 'Admin\SubCategoryController@create')
            ->name('admin-subcat-create');
        Route::post('/subcategory/create', 'Admin\SubCategoryController@store')
            ->name('admin-subcat-store');
        Route::get('/subcategory/edit/{id}', 'Admin\SubCategoryController@edit')
            ->name('admin-subcat-edit');
        Route::post('/subcategory/edit/{id}', 'Admin\SubCategoryController@update')
            ->name('admin-subcat-update');
        Route::get('/subcategory/delete/{id}', 'Admin\SubCategoryController@destroy')
            ->name('admin-subcat-delete');
        Route::get('/subcategory/status/{id1}/{id2}', 'Admin\SubCategoryController@status')
            ->name('admin-subcat-status');
        Route::get('/load/subcategories/{id}/', 'Admin\SubCategoryController@load')
            ->name('admin-subcat-load'); //JSON REQUEST
        // SUBCATEGORY SECTION ENDS------------
        // CHILDCATEGORY SECTION ------------
        Route::get('/childcategory/datatables', 'Admin\ChildCategoryController@datatables')
            ->name('admin-childcat-datatables'); //JSON REQUEST
        Route::get('/childcategory', 'Admin\ChildCategoryController@index')
            ->name('admin-childcat-index');
        Route::get('/childcategory/create', 'Admin\ChildCategoryController@create')
            ->name('admin-childcat-create');
        Route::post('/childcategory/create', 'Admin\ChildCategoryController@store')
            ->name('admin-childcat-store');
        Route::get('/childcategory/edit/{id}', 'Admin\ChildCategoryController@edit')
            ->name('admin-childcat-edit');
        Route::post('/childcategory/edit/{id}', 'Admin\ChildCategoryController@update')
            ->name('admin-childcat-update');
        Route::get('/childcategory/delete/{id}', 'Admin\ChildCategoryController@destroy')
            ->name('admin-childcat-delete');
        Route::get('/childcategory/status/{id1}/{id2}', 'Admin\ChildCategoryController@status')
            ->name('admin-childcat-status');
        Route::get('/load/childcategories/{id}/', 'Admin\ChildCategoryController@load')
            ->name('admin-childcat-load'); //JSON REQUEST
        // CHILDCATEGORY SECTION ENDS------------

    });

    //------------ ADMIN CATEGORY SECTION ENDS------------

    //------------ ADMIN CSV IMPORT SECTION ------------
    Route::group(['middleware' => 'permissions:bulk_product_upload'], function ()
    {

        Route::get('/products/import', 'Admin\ProductController@import')
            ->name('admin-prod-import');
        Route::post('/products/import-submit', 'Admin\ProductController@importSubmit')
            ->name('admin-prod-importsubmit');

    });

    //------------ ADMIN CSV IMPORT SECTION ENDS ------------
    //------------ ADMIN PRODUCT DISCUSSION SECTION ------------
    Route::group(['middleware' => 'permissions:product_discussion'], function ()
    {

        // RATING SECTION ENDS------------
        Route::get('/ratings/datatables', 'Admin\RatingController@datatables')
            ->name('admin-rating-datatables'); //JSON REQUEST
        Route::get('/ratings', 'Admin\RatingController@index')
            ->name('admin-rating-index');
        Route::get('/ratings/delete/{id}', 'Admin\RatingController@destroy')
            ->name('admin-rating-delete');
        Route::get('/ratings/show/{id}', 'Admin\RatingController@show')
            ->name('admin-rating-show');

        // RATING SECTION ENDS------------
        // COMMENT SECTION ------------
        Route::get('/comments/datatables', 'Admin\CommentController@datatables')
            ->name('admin-comment-datatables'); //JSON REQUEST
        Route::get('/comments', 'Admin\CommentController@index')
            ->name('admin-comment-index');
        Route::get('/comments/delete/{id}', 'Admin\CommentController@destroy')
            ->name('admin-comment-delete');
        Route::get('/comments/show/{id}', 'Admin\CommentController@show')
            ->name('admin-comment-show');

        // COMMENT CHECK
        Route::get('/general-settings/comment/{status}', 'Admin\GeneralSettingController@comment')
            ->name('admin-gs-iscomment');
        // COMMENT CHECK ENDS


        // COMMENT SECTION ENDS ------------
        // REPORT SECTION ------------
        Route::get('/reports/datatables', 'Admin\ReportController@datatables')
            ->name('admin-report-datatables'); //JSON REQUEST
        Route::get('/reports', 'Admin\ReportController@index')
            ->name('admin-report-index');
        Route::get('/reports/delete/{id}', 'Admin\ReportController@destroy')
            ->name('admin-report-delete');
        Route::get('/reports/show/{id}', 'Admin\ReportController@show')
            ->name('admin-report-show');

        // REPORT CHECK
        Route::get('/general-settings/report/{status}', 'Admin\GeneralSettingController@isreport')
            ->name('admin-gs-isreport');
        // REPORT CHECK ENDS
        // REPORT SECTION ENDS ------------

    });

    //------------ ADMIN PRODUCT DISCUSSION SECTION ENDS ------------

    //------------ ADMIN COUPON SECTION ------------
    Route::group(['middleware' => 'permissions:set_coupons'], function ()
    {

        Route::get('/coupon/datatables', 'Admin\CouponController@datatables')
            ->name('admin-coupon-datatables'); //JSON REQUEST
        Route::get('/coupon', 'Admin\CouponController@index')
            ->name('admin-coupon-index');
        Route::get('/coupon/create', 'Admin\CouponController@create')
            ->name('admin-coupon-create');
        Route::post('/coupon/create', 'Admin\CouponController@store')
            ->name('admin-coupon-store');
        Route::get('/coupon/edit/{id}', 'Admin\CouponController@edit')
            ->name('admin-coupon-edit');
        Route::post('/coupon/edit/{id}', 'Admin\CouponController@update')
            ->name('admin-coupon-update');
        Route::get('/coupon/delete/{id}', 'Admin\CouponController@destroy')
            ->name('admin-coupon-delete');
        Route::get('/coupon/status/{id1}/{id2}', 'Admin\CouponController@status')
            ->name('admin-coupon-status');

        Route::get('/coupon/vendor/datatables', 'Admin\CouponController@vendor_datatables')
            ->name('admin-coupon-vendor-datatables'); //JSON REQUEST
        Route::get('/coupon/vendor', 'Admin\CouponController@vendor_index')
            ->name('admin-coupon-vendor-index');
        Route::get('/coupon/vendor/create', 'Admin\CouponController@vendor_create')
            ->name('admin-coupon-vendor-create');
        Route::post('/coupon/vendor/create', 'Admin\CouponController@vendor_store')
            ->name('admin-coupon-vendor-store');
        Route::get('/coupon/vendor/edit/{id}', 'Admin\CouponController@vendor_edit')
            ->name('admin-coupon-vendor-edit');
        Route::post('/coupon/vendor/edit/{id}', 'Admin\CouponController@vendor_update')
            ->name('admin-coupon-vendor-update');
        Route::get('/coupon/vendor/delete/{id}', 'Admin\CouponController@vendor_destroy')
            ->name('admin-coupon-vendor-delete');
        Route::get('/coupon/vendor/status/{id1}/{id2}', 'Admin\CouponController@vendor_status')
            ->name('admin-coupon-vendor-status');

    });

    //------------ ADMIN COUPON SECTION ENDS------------
    //------------ ADMIN BLOG SECTION ------------
    Route::group(['middleware' => 'permissions:blog'], function ()
    {

        Route::get('/blog/datatables', 'Admin\BlogController@datatables')
            ->name('admin-blog-datatables'); //JSON REQUEST
        Route::get('/blog', 'Admin\BlogController@index')
            ->name('admin-blog-index');
        Route::get('/blog/create', 'Admin\BlogController@create')
            ->name('admin-blog-create');
        Route::post('/blog/create', 'Admin\BlogController@store')
            ->name('admin-blog-store');
        Route::get('/blog/edit/{id}', 'Admin\BlogController@edit')
            ->name('admin-blog-edit');
        Route::post('/blog/edit/{id}', 'Admin\BlogController@update')
            ->name('admin-blog-update');
        Route::get('/blog/delete/{id}', 'Admin\BlogController@destroy')
            ->name('admin-blog-delete');

        Route::get('/blog/category/datatables', 'Admin\BlogCategoryController@datatables')
            ->name('admin-cblog-datatables'); //JSON REQUEST
        Route::get('/blog/category', 'Admin\BlogCategoryController@index')
            ->name('admin-cblog-index');
        Route::get('/blog/category/create', 'Admin\BlogCategoryController@create')
            ->name('admin-cblog-create');
        Route::post('/blog/category/create', 'Admin\BlogCategoryController@store')
            ->name('admin-cblog-store');
        Route::get('/blog/category/edit/{id}', 'Admin\BlogCategoryController@edit')
            ->name('admin-cblog-edit');
        Route::post('/blog/category/edit/{id}', 'Admin\BlogCategoryController@update')
            ->name('admin-cblog-update');
        Route::get('/blog/category/delete/{id}', 'Admin\BlogCategoryController@destroy')
            ->name('admin-cblog-delete');

    });

    //------------ ADMIN BLOG SECTION ENDS ------------


    //------------ ADMIN USER MESSAGE SECTION ------------
    Route::group(['middleware' => 'permissions:messages'], function ()
    {

        Route::get('/messages/datatables/{type}', 'Admin\MessageController@datatables')
            ->name('admin-message-datatables');
        Route::get('/tickets', 'Admin\MessageController@index')
            ->name('admin-message-index');
        Route::get('/disputes', 'Admin\MessageController@disputes')
            ->name('admin-message-dispute');
        Route::get('/message/{id}', 'Admin\MessageController@message')
            ->name('admin-message-show');
        Route::get('/message/load/{id}', 'Admin\MessageController@messageshow')
            ->name('admin-message-load');
        Route::post('/message/post', 'Admin\MessageController@postmessage')
            ->name('admin-message-store');
        Route::get('/message/{id}/delete', 'Admin\MessageController@messagedelete')
            ->name('admin-message-delete');
        Route::post('/user/send/message', 'Admin\MessageController@usercontact')
            ->name('admin-send-message');
        Route::post('/user/send/orderinfo/{id}', 'Admin\MessageController@send_order_info')
            ->name('admin-send-order-info');
        Route::post('/user/send/orderinfo/vendors/{id}', 'Admin\MessageController@send_order_info_vendors')
            ->name('admin-send-order-info-vendors');

    });

    //------------ ADMIN USER MESSAGE SECTION ENDS ------------
    //------------ ADMIN GENERAL SETTINGS SECTION ------------
    Route::group(['middleware' => 'permissions:general_settings'], function ()
    {

        Route::get('/general-settings/logo', 'Admin\GeneralSettingController@logo')
            ->name('admin-gs-logo');
        Route::get('/general-settings/favicon', 'Admin\GeneralSettingController@fav')
            ->name('admin-gs-fav');
        Route::get('/general-settings/loader', 'Admin\GeneralSettingController@load')
            ->name('admin-gs-load');
        Route::get('/general-settings/contents', 'Admin\GeneralSettingController@contents')
            ->name('admin-gs-contents');
        Route::get('/general-settings/footer', 'Admin\GeneralSettingController@footer')
            ->name('admin-gs-footer');
        Route::get('/general-settings/affilate', 'Admin\GeneralSettingController@affilate')
            ->name('admin-gs-affilate');
        Route::any('/general-settings/kol', 'Admin\GeneralSettingController@kol')
            ->name('admin-gs-kol');
        Route::get('/general-settings/getkol/{date}', 'Admin\GeneralSettingController@getKol')
            ->name('admin-get-kol');
        Route::post('/general-settings/kol/update', 'Admin\GeneralSettingController@kolupdate')
            ->name('admin-kol-update');

        Route::get('/general-settings/error-banner', 'Admin\GeneralSettingController@errorbanner')
            ->name('admin-gs-error-banner');
        Route::get('/general-settings/popup', 'Admin\GeneralSettingController@popup')
            ->name('admin-gs-popup');
        Route::get('/general-settings/maintenance', 'Admin\GeneralSettingController@maintain')
            ->name('admin-gs-maintenance');
        //------------ ADMIN PICKUP LOACTION ------------
        Route::get('/pickup/datatables', 'Admin\PickupController@datatables')
            ->name('admin-pick-datatables'); //JSON REQUEST
        Route::get('/pickup', 'Admin\PickupController@index')
            ->name('admin-pick-index');
        Route::get('/pickup/create', 'Admin\PickupController@create')
            ->name('admin-pick-create');
        Route::post('/pickup/create', 'Admin\PickupController@store')
            ->name('admin-pick-store');
        Route::get('/pickup/edit/{id}', 'Admin\PickupController@edit')
            ->name('admin-pick-edit');
        Route::post('/pickup/edit/{id}', 'Admin\PickupController@update')
            ->name('admin-pick-update');
        Route::get('/pickup/delete/{id}', 'Admin\PickupController@destroy')
            ->name('admin-pick-delete');

        //------------ ADMIN PICKUP LOACTION ENDS ------------
        //------------ ADMIN AFFILIATE LEVEL ------------
        Route::get('/affiliatelevel/datatables', 'Admin\AffiliateLevelController@datatables')
            ->name('admin-affiliatelevel-datatables'); //JSON REQUEST
        Route::get('/affiliatelevel', 'Admin\AffiliateLevelController@index')
            ->name('admin-affiliatelevel-index');
        Route::get('/affiliatelevel/create', 'Admin\AffiliateLevelController@create')
            ->name('admin-affiliatelevel-create');
        Route::post('/affiliatelevel/create', 'Admin\AffiliateLevelController@store')
            ->name('admin-affiliatelevel-store');
        Route::get('/affiliatelevel/edit/{id}', 'Admin\AffiliateLevelController@edit')
            ->name('admin-affiliatelevel-edit');
        Route::post('/affiliatelevel/edit/{id}', 'Admin\AffiliateLevelController@update')
            ->name('admin-affiliatelevel-update');
        Route::get('/affiliatelevel/delete/{id}', 'Admin\AffiliateLevelController@destroy')
            ->name('admin-affiliatelevel-delete');

        Route::get('/affiliatelevel/package/{id}', 'Admin\AffiliateLevelController@package_index')
            ->name('admin-affiliatelevel-package-index');
        Route::get('/affiliatelevel/package/datatables/{id}', 'Admin\AffiliateLevelController@package_datatables')
            ->name('admin-affiliatelevel-package-datatables'); //JSON REQUEST
        Route::get('/affiliatelevel/package/edit/{id}', 'Admin\AffiliateLevelController@package_edit')
            ->name('admin-affiliatelevel-package-edit');
        Route::post('/affiliatelevel/package/edit/{id}', 'Admin\AffiliateLevelController@package_update')
            ->name('admin-affiliatelevel-package-update');

        //------------ ADMIN AFFILIATE LEVEL ENDS ------------
        //------------ ADMIN PACKAGE CONFIG ------------
        Route::get('/memberpackage/datatables', 'Admin\PackageConfigController@datatables')
            ->name('admin-packageconfig-datatables'); //JSON REQUEST
        Route::get('/memberpackage', 'Admin\PackageConfigController@index')
            ->name('admin-packageconfig-index');
        Route::get('/memberpackage/create', 'Admin\PackageConfigController@create')
            ->name('admin-packageconfig-create');
        Route::post('/memberpackage/create', 'Admin\PackageConfigController@store')
            ->name('admin-packageconfig-store');
        Route::get('/memberpackage/edit/{id}', 'Admin\PackageConfigController@edit')
            ->name('admin-packageconfig-edit');
        Route::post('/memberpackage/edit/{id}', 'Admin\PackageConfigController@update')
            ->name('admin-packageconfig-update');
        Route::get('/memberpackage/delete/{id}', 'Admin\PackageConfigController@destroy')
            ->name('admin-packageconfig-delete');

        //------------ ADMIN PACKAGE CONFIG ENDS ------------
        //------------ ADMIN SHIPPING ------------
        Route::get('/shipping/datatables', 'Admin\ShippingController@datatables')
            ->name('admin-shipping-datatables');
        Route::get('/shipping', 'Admin\ShippingController@index')
            ->name('admin-shipping-index');
        Route::get('/shipping/create', 'Admin\ShippingController@create')
            ->name('admin-shipping-create');
        Route::post('/shipping/create', 'Admin\ShippingController@store')
            ->name('admin-shipping-store');
        Route::get('/shipping/edit/{id}', 'Admin\ShippingController@edit')
            ->name('admin-shipping-edit');
        Route::post('/shipping/edit/{id}', 'Admin\ShippingController@update')
            ->name('admin-shipping-update');
        Route::get('/shipping/delete/{id}', 'Admin\ShippingController@destroy')
            ->name('admin-shipping-delete');

        //------------ ADMIN SHIPPING ENDS ------------
        //------------ ADMIN PACKAGE ------------
        Route::get('/package/datatables', 'Admin\PackageController@datatables')
            ->name('admin-package-datatables');
        Route::get('/package', 'Admin\PackageController@index')
            ->name('admin-package-index');
        Route::get('/package/create', 'Admin\PackageController@create')
            ->name('admin-package-create');
        Route::post('/package/create', 'Admin\PackageController@store')
            ->name('admin-package-store');
        Route::get('/package/edit/{id}', 'Admin\PackageController@edit')
            ->name('admin-package-edit');
        Route::post('/package/edit/{id}', 'Admin\PackageController@update')
            ->name('admin-package-update');
        Route::get('/package/delete/{id}', 'Admin\PackageController@destroy')
            ->name('admin-package-delete');

        //------------ ADMIN PACKAGE ENDS------------


        //------------ ADMIN GENERAL SETTINGS JSON SECTION ------------
        // General Setting Section
        Route::get('/general-settings/home/{status}', 'Admin\GeneralSettingController@ishome')
            ->name('admin-gs-ishome');
        Route::get('/general-settings/disqus/{status}', 'Admin\GeneralSettingController@isdisqus')
            ->name('admin-gs-isdisqus');
        Route::get('/general-settings/loader/{status}', 'Admin\GeneralSettingController@isloader')
            ->name('admin-gs-isloader');
        Route::get('/general-settings/email-verify/{status}', 'Admin\GeneralSettingController@isemailverify')
            ->name('admin-gs-is-email-verify');
        Route::get('/general-settings/popup/{status}', 'Admin\GeneralSettingController@ispopup')
            ->name('admin-gs-ispopup');

        Route::get('/general-settings/admin/loader/{status}', 'Admin\GeneralSettingController@isadminloader')
            ->name('admin-gs-is-admin-loader');
        Route::get('/general-settings/talkto/{status}', 'Admin\GeneralSettingController@talkto')
            ->name('admin-gs-talkto');

        Route::get('/general-settings/multiple/shipping/{status}', 'Admin\GeneralSettingController@mship')
            ->name('admin-gs-mship');
        Route::get('/general-settings/multiple/packaging/{status}', 'Admin\GeneralSettingController@mpackage')
            ->name('admin-gs-mpackage');
        Route::get('/general-settings/security/{status}', 'Admin\GeneralSettingController@issecure')
            ->name('admin-gs-secure');
        Route::get('/general-settings/stock/{status}', 'Admin\GeneralSettingController@stock')
            ->name('admin-gs-stock');
        Route::get('/general-settings/maintain/{status}', 'Admin\GeneralSettingController@ismaintain')
            ->name('admin-gs-maintain');
        //  Affilte Section
        Route::get('/general-settings/affilate/{status}', 'Admin\GeneralSettingController@isaffilate')
            ->name('admin-gs-isaffilate');

        //  Capcha Section
        Route::get('/general-settings/capcha/{status}', 'Admin\GeneralSettingController@iscapcha')
            ->name('admin-gs-iscapcha');

        //------------ ADMIN GENERAL SETTINGS JSON SECTION ENDS------------



    });
    //------------ ADMIN GENERAL SETTINGS SECTION ENDS ------------


    //------------ ADMIN HOME PAGE SETTINGS SECTION ------------
    Route::group(['middleware' => 'permissions:home_page_settings'], function ()
    {

        //------------ ADMIN SLIDER SECTION ------------
        Route::get('/slider/datatables', 'Admin\SliderController@datatables')
            ->name('admin-sl-datatables'); //JSON REQUEST
        Route::get('/slider', 'Admin\SliderController@index')
            ->name('admin-sl-index');
        Route::get('/slider/create', 'Admin\SliderController@create')
            ->name('admin-sl-create');
        Route::post('/slider/create', 'Admin\SliderController@store')
            ->name('admin-sl-store');
        Route::get('/slider/edit/{id}', 'Admin\SliderController@edit')
            ->name('admin-sl-edit');
        Route::post('/slider/edit/{id}', 'Admin\SliderController@update')
            ->name('admin-sl-update');
        Route::get('/slider/delete/{id}', 'Admin\SliderController@destroy')
            ->name('admin-sl-delete');

        //------------ ADMIN SLIDER SECTION ENDS ------------
        //------------ ADMIN SERVICE SECTION ------------
        Route::get('/service/datatables', 'Admin\ServiceController@datatables')
            ->name('admin-service-datatables'); //JSON REQUEST
        Route::get('/service', 'Admin\ServiceController@index')
            ->name('admin-service-index');
        Route::get('/service/create', 'Admin\ServiceController@create')
            ->name('admin-service-create');
        Route::post('/service/create', 'Admin\ServiceController@store')
            ->name('admin-service-store');
        Route::get('/service/edit/{id}', 'Admin\ServiceController@edit')
            ->name('admin-service-edit');
        Route::post('/service/edit/{id}', 'Admin\ServiceController@update')
            ->name('admin-service-update');
        Route::get('/service/delete/{id}', 'Admin\ServiceController@destroy')
            ->name('admin-service-delete');

        //------------ ADMIN SERVICE SECTION ENDS ------------
        //------------ ADMIN BANNER SECTION ------------
        Route::get('/banner/datatables/{type}', 'Admin\BannerController@datatables')
            ->name('admin-sb-datatables'); //JSON REQUEST
        Route::get('top/small/banner/', 'Admin\BannerController@index')
            ->name('admin-sb-index');
        Route::get('large/banner/', 'Admin\BannerController@large')
            ->name('admin-sb-large');
        Route::get('bottom/small/banner/', 'Admin\BannerController@bottom')
            ->name('admin-sb-bottom');
        Route::get('top/small/banner/create', 'Admin\BannerController@create')
            ->name('admin-sb-create');
        Route::get('large/banner/create', 'Admin\BannerController@largecreate')
            ->name('admin-sb-create-large');
        Route::get('bottom/small/banner/create', 'Admin\BannerController@bottomcreate')
            ->name('admin-sb-create-bottom');

        Route::post('/banner/create', 'Admin\BannerController@store')
            ->name('admin-sb-store');
        Route::get('/banner/edit/{id}', 'Admin\BannerController@edit')
            ->name('admin-sb-edit');
        Route::post('/banner/edit/{id}', 'Admin\BannerController@update')
            ->name('admin-sb-update');
        Route::get('/banner/delete/{id}', 'Admin\BannerController@destroy')
            ->name('admin-sb-delete');

        //------------ ADMIN BANNER SECTION ENDS ------------
        //------------ ADMIN REVIEW SECTION ------------
        Route::get('/review/datatables', 'Admin\ReviewController@datatables')
            ->name('admin-review-datatables'); //JSON REQUEST
        Route::get('/review', 'Admin\ReviewController@index')
            ->name('admin-review-index');
        Route::get('/review/create', 'Admin\ReviewController@create')
            ->name('admin-review-create');
        Route::post('/review/create', 'Admin\ReviewController@store')
            ->name('admin-review-store');
        Route::get('/review/edit/{id}', 'Admin\ReviewController@edit')
            ->name('admin-review-edit');
        Route::post('/review/edit/{id}', 'Admin\ReviewController@update')
            ->name('admin-review-update');
        Route::get('/review/delete/{id}', 'Admin\ReviewController@destroy')
            ->name('admin-review-delete');

        //------------ ADMIN REVIEW SECTION ENDS ------------


        //------------ ADMIN PARTNER SECTION ------------
        Route::get('/partner/datatables', 'Admin\PartnerController@datatables')
            ->name('admin-partner-datatables');
        Route::get('/partner', 'Admin\PartnerController@index')
            ->name('admin-partner-index');
        Route::get('/partner/create', 'Admin\PartnerController@create')
            ->name('admin-partner-create');
        Route::post('/partner/create', 'Admin\PartnerController@store')
            ->name('admin-partner-store');
        Route::get('/partner/edit/{id}', 'Admin\PartnerController@edit')
            ->name('admin-partner-edit');
        Route::post('/partner/edit/{id}', 'Admin\PartnerController@update')
            ->name('admin-partner-update');
        Route::get('/partner/delete/{id}', 'Admin\PartnerController@destroy')
            ->name('admin-partner-delete');

        //------------ ADMIN PARTNER SECTION ENDS ------------


        //------------ ADMIN PAGE SETTINGS SECTION ------------
        Route::get('/page-settings/customize', 'Admin\PageSettingController@customize')
            ->name('admin-ps-customize');
        Route::get('/page-settings/big-save', 'Admin\PageSettingController@big_save')
            ->name('admin-ps-big-save');
        Route::get('/page-settings/best-seller', 'Admin\PageSettingController@best_seller')
            ->name('admin-ps-best-seller');

    });

    //------------ ADMIN HOME PAGE SETTINGS SECTION ENDS ------------
    Route::group(['middleware' => 'permissions:menu_page_settings'], function ()
    {

        //------------ ADMIN MENU PAGE SETTINGS SECTION ------------

        Route::get('/faq-category/datatables', 'Admin\FaqCategoryController@datatables')
            ->name('admin-faq-category-datatables');
        Route::get('/faq-category', 'Admin\FaqCategoryController@index')
            ->name('admin-faq-category-index');
        Route::get('/faq-category/create', 'Admin\FaqCategoryController@create')
            ->name('admin-faq-category-create');
        Route::post('/faq-category/create', 'Admin\FaqCategoryController@store')
            ->name('admin-faq-category-store');
        Route::get('/faq-category/edit/{id}', 'Admin\FaqCategoryController@edit')
            ->name('admin-faq-category-edit');
        Route::post('/faq-category/update/{id}', 'Admin\FaqCategoryController@update')
            ->name('admin-faq-category-update');
        Route::get('/faq-category/delete/{id}', 'Admin\FaqCategoryController@destroy')
            ->name('admin-faq-category-delete');

        //------------ ADMIN FAQ SECTION ------------
        Route::get('/faq/datatables', 'Admin\FaqController@datatables')
            ->name('admin-faq-datatables'); //JSON REQUEST
        Route::get('/faq', 'Admin\FaqController@index')
            ->name('admin-faq-index');
        Route::get('/faq/create', 'Admin\FaqController@create')
            ->name('admin-faq-create');
        Route::post('/faq/create', 'Admin\FaqController@store')
            ->name('admin-faq-store');
        Route::get('/faq/edit/{id}', 'Admin\FaqController@edit')
            ->name('admin-faq-edit');
        Route::post('/faq/update/{id}', 'Admin\FaqController@update')
            ->name('admin-faq-update');
        Route::get('/faq/delete/{id}', 'Admin\FaqController@destroy')
            ->name('admin-faq-delete');

        //------------ ADMIN FAQ SECTION ENDS ------------


        //------------ ADMIN PAGE SECTION ------------
        Route::get('/page/datatables', 'Admin\PageController@datatables')
            ->name('admin-page-datatables'); //JSON REQUEST
        Route::get('/page', 'Admin\PageController@index')
            ->name('admin-page-index');
        Route::get('/page/create', 'Admin\PageController@create')
            ->name('admin-page-create');
        Route::post('/page/create', 'Admin\PageController@store')
            ->name('admin-page-store');
        Route::get('/page/edit/{id}', 'Admin\PageController@edit')
            ->name('admin-page-edit');
        Route::post('/page/update/{id}', 'Admin\PageController@update')
            ->name('admin-page-update');
        Route::get('/page/delete/{id}', 'Admin\PageController@destroy')
            ->name('admin-page-delete');
        Route::get('/page/header/{id1}/{id2}', 'Admin\PageController@header')
            ->name('admin-page-header');
        Route::get('/page/footer/{id1}/{id2}', 'Admin\PageController@footer')
            ->name('admin-page-footer');

        //------------ ADMIN PAGE SECTION ENDS------------


        Route::get('/general-settings/contact/{status}', 'Admin\GeneralSettingController@iscontact')
            ->name('admin-gs-iscontact');
        Route::get('/general-settings/faq/{status}', 'Admin\GeneralSettingController@isfaq')
            ->name('admin-gs-isfaq');
        Route::get('/page-settings/contact', 'Admin\PageSettingController@contact')
            ->name('admin-ps-contact');
        Route::post('/page-settings/update/all', 'Admin\PageSettingController@update')
            ->name('admin-ps-update');

    });

    //------------ ADMIN MENU PAGE SETTINGS SECTION ENDS ------------

    //------------ ADMIN EMAIL SETTINGS SECTION ------------
    Route::group(['middleware' => 'permissions:emails_settings'], function ()
    {

        Route::get('/email-templates/datatables', 'Admin\EmailController@datatables')
            ->name('admin-mail-datatables');
        Route::get('/email-templates', 'Admin\EmailController@index')
            ->name('admin-mail-index');
        Route::get('/email-templates/{id}', 'Admin\EmailController@edit')
            ->name('admin-mail-edit');
        Route::post('/email-templates/{id}', 'Admin\EmailController@update')
            ->name('admin-mail-update');
        Route::get('/email-config', 'Admin\EmailController@config')
            ->name('admin-mail-config');
        Route::get('/groupemail', 'Admin\EmailController@groupemail')
            ->name('admin-group-show');
        Route::post('/groupemailpost', 'Admin\EmailController@groupemailpost')
            ->name('admin-group-submit');
        Route::get('/issmtp/{status}', 'Admin\GeneralSettingController@issmtp')
            ->name('admin-gs-issmtp');
        Route::get('/admin-send-mail','Admin\MailController@index')->name('admin-send-mail');
        Route::post('/admin-send-mail-submit','Admin\MailController@send')->name('admin-send-mail-submit');


    });

    //------------ ADMIN EMAIL SETTINGS SECTION ENDS ------------


    //------------ ADMIN PAYMENT SETTINGS SECTION ------------
    Route::group(['middleware' => 'permissions:payment_settings'], function ()
    {

        // Payment Informations
        Route::get('/payment-informations', 'Admin\GeneralSettingController@paymentsinfo')
            ->name('admin-gs-payments');

        Route::get('/general-settings/guest/{status}', 'Admin\GeneralSettingController@guest')
            ->name('admin-gs-guest');
        Route::get('/general-settings/paypal/{status}', 'Admin\GeneralSettingController@paypal')
            ->name('admin-gs-paypal');
        Route::get('/general-settings/instamojo/{status}', 'Admin\GeneralSettingController@instamojo')
            ->name('admin-gs-instamojo');
        Route::get('/general-settings/paystack/{status}', 'Admin\GeneralSettingController@paystack')
            ->name('admin-gs-paystack');
        Route::get('/general-settings/stripe/{status}', 'Admin\GeneralSettingController@stripe')
            ->name('admin-gs-stripe');
        Route::get('/general-settings/cod/{status}', 'Admin\GeneralSettingController@cod')
            ->name('admin-gs-cod');
        Route::get('/general-settings/paytm/{status}', 'Admin\GeneralSettingController@paytm')
            ->name('admin-gs-paytm');
        Route::get('/general-settings/molly/{status}', 'Admin\GeneralSettingController@molly')
            ->name('admin-gs-molly');
        Route::get('/general-settings/razor/{status}', 'Admin\GeneralSettingController@razor')
            ->name('admin-gs-razor');
        // Payment Gateways
        Route::get('/paymentgateway/datatables', 'Admin\PaymentGatewayController@datatables')
            ->name('admin-payment-datatables'); //JSON REQUEST
        Route::get('/paymentgateway', 'Admin\PaymentGatewayController@index')
            ->name('admin-payment-index');
        Route::get('/paymentgateway/create', 'Admin\PaymentGatewayController@create')
            ->name('admin-payment-create');
        Route::post('/paymentgateway/create', 'Admin\PaymentGatewayController@store')
            ->name('admin-payment-store');
        Route::get('/paymentgateway/edit/{id}', 'Admin\PaymentGatewayController@edit')
            ->name('admin-payment-edit');
        Route::post('/paymentgateway/update/{id}', 'Admin\PaymentGatewayController@update')
            ->name('admin-payment-update');
        Route::get('/paymentgateway/delete/{id}', 'Admin\PaymentGatewayController@destroy')
            ->name('admin-payment-delete');
        Route::get('/paymentgateway/status/{id1}/{id2}', 'Admin\PaymentGatewayController@status')
            ->name('admin-payment-status');

        // Currency Settings


        // MULTIPLE CURRENCY
        Route::get('/general-settings/currency/{status}', 'Admin\GeneralSettingController@currency')
            ->name('admin-gs-iscurrency');
        Route::get('/currency/datatables', 'Admin\CurrencyController@datatables')
            ->name('admin-currency-datatables'); //JSON REQUEST
        Route::get('/currency', 'Admin\CurrencyController@index')
            ->name('admin-currency-index');
        Route::get('/currency/create', 'Admin\CurrencyController@create')
            ->name('admin-currency-create');
        Route::post('/currency/create', 'Admin\CurrencyController@store')
            ->name('admin-currency-store');
        Route::get('/currency/edit/{id}', 'Admin\CurrencyController@edit')
            ->name('admin-currency-edit');
        Route::post('/currency/update/{id}', 'Admin\CurrencyController@update')
            ->name('admin-currency-update');
        Route::get('/currency/delete/{id}', 'Admin\CurrencyController@destroy')
            ->name('admin-currency-delete');
        Route::get('/currency/status/{id1}/{id2}', 'Admin\CurrencyController@status')
            ->name('admin-currency-status');

    });

    //------------ ADMIN PAYMENT SETTINGS SECTION ENDS------------

    //------------ ADMIN SOCIAL SETTINGS SECTION ------------
    Route::group(['middleware' => 'permissions:social_settings'], function ()
    {

        Route::get('/social', 'Admin\SocialSettingController@index')
            ->name('admin-social-index');
        Route::post('/social/update', 'Admin\SocialSettingController@socialupdate')
            ->name('admin-social-update');
        Route::post('/social/update/all', 'Admin\SocialSettingController@socialupdateall')
            ->name('admin-social-update-all');
        Route::get('/social/facebook', 'Admin\SocialSettingController@facebook')
            ->name('admin-social-facebook');
        Route::get('/social/google', 'Admin\SocialSettingController@google')
            ->name('admin-social-google');
        Route::get('/social/facebook/{status}', 'Admin\SocialSettingController@facebookup')
            ->name('admin-social-facebookup');
        Route::get('/social/google/{status}', 'Admin\SocialSettingController@googleup')
            ->name('admin-social-googleup');

    });
    //------------ ADMIN SOCIAL SETTINGS SECTION ENDS------------


    //------------ ADMIN LANGUAGE SETTINGS SECTION ------------
    Route::group(['middleware' => 'permissions:language_settings'], function ()
    {

        //  Multiple Language Section
        Route::get('/general-settings/language/{status}', 'Admin\GeneralSettingController@language')
            ->name('admin-gs-islanguage');

        //  Multiple Language Section Ends
        Route::get('/languages/datatables', 'Admin\LanguageController@datatables')
            ->name('admin-lang-datatables'); //JSON REQUEST
        Route::get('/languages', 'Admin\LanguageController@index')
            ->name('admin-lang-index');
        Route::get('/languages/create', 'Admin\LanguageController@create')
            ->name('admin-lang-create');
        Route::get('/languages/edit/{id}', 'Admin\LanguageController@edit')
            ->name('admin-lang-edit');
        Route::post('/languages/create', 'Admin\LanguageController@store')
            ->name('admin-lang-store');
        Route::post('/languages/edit/{id}', 'Admin\LanguageController@update')
            ->name('admin-lang-update');
        Route::get('/languages/status/{id1}/{id2}', 'Admin\LanguageController@status')
            ->name('admin-lang-st');
        Route::get('/languages/delete/{id}', 'Admin\LanguageController@destroy')
            ->name('admin-lang-delete');

        //------------ ADMIN PANEL LANGUAGE SETTINGS SECTION ------------
        Route::get('/adminlanguages/datatables', 'Admin\AdminLanguageController@datatables')
            ->name('admin-tlang-datatables'); //JSON REQUEST
        Route::get('/adminlanguages', 'Admin\AdminLanguageController@index')
            ->name('admin-tlang-index');
        Route::get('/adminlanguages/create', 'Admin\AdminLanguageController@create')
            ->name('admin-tlang-create');
        Route::get('/adminlanguages/edit/{id}', 'Admin\AdminLanguageController@edit')
            ->name('admin-tlang-edit');
        Route::post('/adminlanguages/create', 'Admin\AdminLanguageController@store')
            ->name('admin-tlang-store');
        Route::post('/adminlanguages/edit/{id}', 'Admin\AdminLanguageController@update')
            ->name('admin-tlang-update');
        Route::get('/adminlanguages/status/{id1}/{id2}', 'Admin\AdminLanguageController@status')
            ->name('admin-tlang-st');
        Route::get('/adminlanguages/delete/{id}', 'Admin\AdminLanguageController@destroy')
            ->name('admin-tlang-delete');

        //------------ ADMIN PANEL LANGUAGE SETTINGS SECTION ENDS ------------
        //------------ ADMIN LANGUAGE SETTINGS SECTION ENDS ------------

    });

    //------------ ADMIN SEOTOOL SETTINGS SECTION ------------
    Route::group(['middleware' => 'permissions:seo_tools'], function ()
    {

        Route::get('/seotools/analytics', 'Admin\SeoToolController@analytics')
            ->name('admin-seotool-analytics');
        Route::post('/seotools/analytics/update', 'Admin\SeoToolController@analyticsupdate')
            ->name('admin-seotool-analytics-update');
        Route::get('/seotools/keywords', 'Admin\SeoToolController@keywords')
            ->name('admin-seotool-keywords');
        Route::post('/seotools/keywords/update', 'Admin\SeoToolController@keywordsupdate')
            ->name('admin-seotool-keywords-update');
        Route::get('/products/popular/{id}', 'Admin\SeoToolController@popular')
            ->name('admin-prod-popular');

    });

    //------------ ADMIN SEOTOOL SETTINGS SECTION ------------
    //------------ ADMIN STAFF SECTION ------------
    Route::group(['middleware' => 'permissions:manage_staffs'], function ()
    {

        Route::get('/staff/datatables', 'Admin\StaffController@datatables')
            ->name('admin-staff-datatables');
        Route::get('/staff', 'Admin\StaffController@index')
            ->name('admin-staff-index');
        Route::get('/staff/create', 'Admin\StaffController@create')
            ->name('admin-staff-create');
        Route::post('/staff/create', 'Admin\StaffController@store')
            ->name('admin-staff-store');
        Route::get('/staff/edit/{id}', 'Admin\StaffController@edit')
            ->name('admin-staff-edit');
        Route::post('/staff/update/{id}', 'Admin\StaffController@update')
            ->name('admin-staff-update');
        Route::get('/staff/show/{id}', 'Admin\StaffController@show')
            ->name('admin-staff-show');
        Route::get('/staff/delete/{id}', 'Admin\StaffController@destroy')
            ->name('admin-staff-delete');

    });

    //------------ ADMIN STAFF SECTION ENDS------------
    //------------ ADMIN SUBSCRIBERS SECTION ------------
    Route::group(['middleware' => 'permissions:subscribers'], function ()
    {

        Route::get('/subscribers/datatables', 'Admin\SubscriberController@datatables')
            ->name('admin-subs-datatables'); //JSON REQUEST
        Route::get('/subscribers', 'Admin\SubscriberController@index')
            ->name('admin-subs-index');
        Route::get('/subscribers/download', 'Admin\SubscriberController@download')
            ->name('admin-subs-download');

    });
    //------------ ADMIN SUBSCRIBERS ENDS ------------

    //------------ ADMIN REPORT SECTION ------------
    Route::group(['middleware' => 'permissions:reports'], function ()
    {
        Route::get('/orders/reports/kolconsumerbonus/datatables/{from}/{to}', 'Admin\OrderController@datatablesKOLConsumerBonus2Weeks')->name('admin-order-report-kol-consumerbonus-datatables-2w');
        Route::get('/orders/reports/kolconsumerbonus2w', 'Admin\OrderController@kolConsumerBonus2w')->name('admin-order-report-kol-consumerbonus-2w');
        Route::get('/orders/reports/kolconsumerbonus2w/export/{from}', 'Admin\OrderController@exportKOLConsumerBonus2w')->name('admin-order-report-kol-consumerbonus-export-2w');
        Route::get('/orders/reports/kolconsumerbonus/processpay/{from}', 'Admin\OrderController@processPayKOLConsumerBonus')->name('admin-order-report-kol-consumerbonus-processpay-2w');

        Route::get('/order/report/merchantp', 'Admin\OrderController@merchantp_report')->name('admin-order-report-merchant-payment');
        Route::get('/orders/datatablesv2/report/merchantp/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@datatables_report_merchantp')->name('admin-order-datatables-report-merchantp');
        Route::get('/orders/export/report/merchantp/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@export_report_merchantp')->name('admin-order-export-report-merchantp');

        // Order salefull Report
        Route::get('/order/report/salefull', 'Admin\OrderController@sale_full_report')->name('admin-order-report-sale-full');
        Route::get('/orders/datatablesv2/report/salefull/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@datatables_report_sale_full')->name('admin-order-datatables-report-sale-full');
        Route::get('/orders/export/report/salefull/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@export_report_sale_full')->name('admin-order-export-report-sale-full');

        // Order Report
        Route::get('/orders/reports/merchantperformance/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformance')->name('admin-order-report-merchant-performance-datatables');
        Route::get('/orders/reports/merchantperformance/export/{from}/{to}', 'Admin\OrderController@exportMerchantPerformance')->name('admin-order-report-merchant-performance-export');
        Route::get('/orders/reports/merchantperformance', 'Admin\OrderController@merchantPerformance')->name('admin-order-report-merchant-performance');

        //Order Sale Full Report detail
        Route::get('/orders/reports/salefulldetail/datatables/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@datatablesSaleFullReportDetail')->name('admin-order-report-salefull-detail-datatables');
        Route::get('/orders/reports/salefulldetail', 'Admin\OrderController@saleFullReportDetail')->name('admin-order-report-salefull-detail');
        Route::get('/orders/reports/salefulldetail/export/{status}/{iscollected}/{from?}/{to?}', 'Admin\OrderController@exportSaleFullReportDetail')->name('admin-order-report-salefull-detail-export');

        //FILE2.1 -  Summaries  - Top 10 Performance Sales by Shop
        Route::get('/orders/reports/merchantperformance/summaries/top10byshop', 'Admin\OrderController@merchantPerformanceSummariesTop10ByShop')->name('admin-order-report-merchant-performance-summaries-top10byshop');
        Route::get('/orders/reports/merchantperformance/summaries/top10byshop/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformanceSummariesTop10ByShop')->name('admin-order-report-merchant-performance-summaries-top10byshop-datatables');
        Route::get('/orders/reports/merchantperformance/summaries/top10byshop/export/{from}/{to}/{top?}/{isBySales?}', 'Admin\OrderController@exportMerchantPerformanceSummariesTop10ByShop')->name('admin-order-report-merchant-performance-summaries-top10byshop-export');

        //FILE2.2 -  Summaries  - Top 10 Performance Sales by Product
        Route::get('/orders/reports/merchantperformance/summaries/top10byproduct', 'Admin\OrderController@merchantPerformanceSummariesTop10ByProduct')->name('admin-order-report-merchant-performance-summaries-top10byproduct');
        Route::get('/orders/reports/merchantperformance/summaries/top10byproduct/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformanceSummariesTop10ByProduct')->name('admin-order-report-merchant-performance-summaries-top10byproduct-datatables');
        Route::get('/orders/reports/merchantperformance/summaries/top10byproduct/export/{from}/{to}', 'Admin\OrderController@exportMerchantPerformanceSummariesTop10ByProduct')->name('admin-order-report-merchant-performance-summaries-top10byproduct-export');

        //FILE2.3 -  Summaries  - Top 10 Performance Sales by Product category
        Route::get('/orders/reports/merchantperformance/summaries/top10byproductcategory', 'Admin\OrderController@merchantPerformanceSummariesTop10ByProductCategory')->name('admin-order-report-merchant-performance-summaries-top10byproductcategory');
        Route::get('/orders/reports/merchantperformance/summaries/top10byproductcategory/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformanceSummariesTop10ByProductCategory')->name('admin-order-report-merchant-performance-summaries-top10byproductcategory-datatables');
        Route::get('/orders/reports/merchantperformance/summaries/top10byproductcategory/export/{from}/{to}', 'Admin\OrderController@exportMerchantPerformanceSummariesTop10ByProductCategory')->name('admin-order-report-merchant-performance-summaries-top10byproductcategory-export');

        //FILE2.4 -  Summaries  - Top 10 Performance Sales by Province
        Route::get('/orders/reports/merchantperformance/summaries/top10byprovince', 'Admin\OrderController@merchantPerformanceSummariesTop10ByProvince')->name('admin-order-report-merchant-performance-summaries-top10byprovince');
        Route::get('/orders/reports/merchantperformance/summaries/top10byprovince/datatables/{from}/{to}', 'Admin\OrderController@datatablesMerchantPerformanceSummariesTop10ByProvince')->name('admin-order-report-merchant-performance-summaries-top10byprovince-datatables');
        Route::get('/orders/reports/merchantperformance/summaries/top10byprovince/export/{from}/{to}', 'Admin\OrderController@exportMerchantPerformanceSummariesTop10ByProvince')->name('admin-order-report-merchant-performance-summaries-top10byprovince-export');
    });
    //------------ ADMIN REPORT ENDS ------------

    // ------------ GLOBAL ----------------------
    Route::post('/general-settings/update/all', 'Admin\GeneralSettingController@generalupdate')
        ->name('admin-gs-update');
    Route::post('/general-settings/update/payment', 'Admin\GeneralSettingController@generalupdatepayment')
        ->name('admin-gs-update-payment');

    // STATUS SECTION
    Route::get('/products/status/{id1}/{id2}', 'Admin\ProductController@status')
        ->name('admin-prod-status');
    // STATUS SECTION ENDS
    // FEATURE SECTION
    Route::get('/products/feature/{id}', 'Admin\ProductController@feature')
        ->name('admin-prod-feature');
    Route::post('/products/feature/{id}', 'Admin\ProductController@featuresubmit')
        ->name('admin-prod-feature');
    // FEATURE SECTION ENDS
    // GALLERY SECTION ------------
    Route::get('/gallery/show', 'Admin\GalleryController@show')
        ->name('admin-gallery-show');
    Route::post('/gallery/store', 'Admin\GalleryController@store')
        ->name('admin-gallery-store');
    Route::get('/gallery/delete', 'Admin\GalleryController@destroy')
        ->name('admin-gallery-delete');

    // GALLERY SECTION ENDS------------
    Route::post('/page-settings/update/all', 'Admin\PageSettingController@update')
        ->name('admin-ps-update');
    Route::post('/page-settings/update/home', 'Admin\PageSettingController@homeupdate')
        ->name('admin-ps-homeupdate');

    // ------------ GLOBAL ENDS ----------------------
    Route::group(['middleware' => 'permissions:super'], function ()
    {

        Route::get('/cache/clear', function ()
        {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            return redirect()
                ->route('admin.dashboard')
                ->with('cache', 'System Cache Has Been Removed.');
        })
            ->name('admin-cache-clear');

        Route::get('/check/movescript', 'Admin\DashboardController@movescript')
            ->name('admin-move-script');
        Route::get('/generate/backup', 'Admin\DashboardController@generate_bkup')
            ->name('admin-generate-backup');
        Route::get('/activation', 'Admin\DashboardController@activation')
            ->name('admin-activation-form');
        Route::post('/activation', 'Admin\DashboardController@activation_submit')
            ->name('admin-activate-purchase');
        Route::get('/clear/backup', 'Admin\DashboardController@clear_bkup')
            ->name('admin-clear-backup');

        // ------------ ROLE SECTION ----------------------
        Route::get('/role/datatables', 'Admin\RoleController@datatables')
            ->name('admin-role-datatables');
        Route::get('/role', 'Admin\RoleController@index')
            ->name('admin-role-index');
        Route::get('/role/create', 'Admin\RoleController@create')
            ->name('admin-role-create');
        Route::post('/role/create', 'Admin\RoleController@store')
            ->name('admin-role-store');
        Route::get('/role/edit/{id}', 'Admin\RoleController@edit')
            ->name('admin-role-edit');
        Route::post('/role/edit/{id}', 'Admin\RoleController@update')
            ->name('admin-role-update');
        Route::get('/role/delete/{id}', 'Admin\RoleController@destroy')
            ->name('admin-role-delete');

        // ------------ ROLE SECTION ENDS ----------------------



    });

});

