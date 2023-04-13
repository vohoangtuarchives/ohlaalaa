<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', 'Front\FrontendController@index')
    ->name('front.index');
Route::get('/extras', 'Front\FrontendController@extraIndex')
    ->name('front.extraIndex');
Route::get('/currency/{id}', 'Front\FrontendController@currency')
    ->name('front.currency');
Route::get('/language/{id}', 'Front\FrontendController@language')
    ->name('front.language');

// BLOG SECTION
Route::get('/trung-tam-nguon-luc', 'Front\FrontendController@blog')
    ->name('front.blog');
Route::get('/trung-tam-nguon-luc/{id}', 'Front\FrontendController@blogshow')
    ->name('front.blogshow');
Route::get('/trung-tam-nguon-luc/category/{slug}', 'Front\FrontendController@blogcategory')
    ->name('front.blogcategory');
Route::get('/trung-tam-nguon-luc/tag/{slug}', 'Front\FrontendController@blogtags')
    ->name('front.blogtags');
Route::get('/blog-search', 'Front\FrontendController@blogsearch')
    ->name('front.blogsearch');
Route::get('/trung-tam-nguon-luc/archive/{slug}', 'Front\FrontendController@blogarchive')
    ->name('front.blogarchive');
// BLOG SECTION ENDS
// BLOG SECTION
Route::get('/preferred', 'Front\FrontendController@preferred')
    ->name('front.preferred');
// BLOG SECTION ENDS
// BLOG SECTION
Route::get('/coupon', 'Front\FrontendController@coupon')
    ->name('front.coupon');
// BLOG SECTION ENDS
Route::get('/dai-su-ket-noi', 'Front\FrontendController@daisuketnoi')
    ->name('front.daisuketnoi');
// FAQ SECTION
Route::get('/faq', 'Front\FrontendController@faq')
    ->name('front.faq');
Route::get('/faq/category/{slug}', 'Front\FrontendController@faqCategory')
    ->name('front.faqcategory');

// FAQ SECTION ENDS
// CONTACT SECTION
Route::get('/contact', 'Front\FrontendController@contact')
    ->name('front.contact');
Route::post('/contact', 'Front\FrontendController@contactemail')
    ->name('front.contact.submit');
Route::get('/contact/refresh_code', 'Front\FrontendController@refresh_code');
// CONTACT SECTION  ENDS
// PRODCT AUTO SEARCH SECTION
Route::get('/autosearch/product/{slug}', 'Front\FrontendController@autosearch');
Route::get('/autoprovince/province/{selected_city_id}', 'Front\FrontendController@autoProvince');
// PRODCT AUTO SEARCH SECTION ENDS
// CATEGORY SECTION
Route::get('/category/{category?}/{subcategory?}/{childcategory?}', 'Front\CatalogController@category')
    ->name('front.category');
Route::get('/category/{slug1}/{slug2}', 'Front\CatalogController@subcategory')
    ->name('front.subcat');
Route::get('/category/{slug1}/{slug2}/{slug3}', 'Front\CatalogController@childcategory')
    ->name('front.childcat');
Route::get('/categories/', 'Front\CatalogController@categories')
    ->name('front.categories');
Route::get('/childcategories/{slug}', 'Front\CatalogController@childcategories')
    ->name('front.childcategories');
// CATEGORY SECTION ENDS
// TAG SECTION
Route::get('/tag/{slug}', 'Front\CatalogController@tag')
    ->name('front.tag');
// TAG SECTION ENDS
// TAG SECTION
Route::get('/search/', 'Front\CatalogController@search')
    ->name('front.search');
// TAG SECTION ENDS


// PRODCT SECTION
Route::get('/item/{slug}', 'Front\CatalogController@product')
    ->name('front.product');
//Route::get('/item/s/data','Front\CatalogController@search_data')->name('front.product.searchdata');
Route::get('/item/s/data/{keyword?}', 'Front\CatalogController@search_data_by_key')
    ->name('front.product.searchdatakey');
Route::get('/afbuy/{slug}', 'Front\CatalogController@affProductRedirect')
    ->name('affiliate.product');
Route::get('/item/quick/view/{id}/', 'Front\CatalogController@quick')
    ->name('product.quick');
Route::post('/item/review', 'Front\CatalogController@reviewsubmit')
    ->name('front.review.submit');
Route::get('/item/view/review/{id}', 'Front\CatalogController@reviews')
    ->name('front.reviews');
// PRODCT SECTION ENDS
// COMMENT SECTION
Route::post('/item/comment/store', 'Front\CatalogController@comment')
    ->name('product.comment');
Route::post('/item/comment/edit/{id}', 'Front\CatalogController@commentedit')
    ->name('product.comment.edit');
Route::get('/item/comment/delete/{id}', 'Front\CatalogController@commentdelete')
    ->name('product.comment.delete');
// COMMENT SECTION ENDS
// REPORT SECTION
Route::post('/item/report', 'Front\CatalogController@report')
    ->name('product.report');
// REPORT SECTION ENDS


// COMPARE SECTION
Route::get('/item/compare/view', 'Front\CompareController@compare')
    ->name('product.compare');
Route::get('/item/compare/add/{id}', 'Front\CompareController@addcompare')
    ->name('product.compare.add');
Route::get('/item/compare/remove/{id}', 'Front\CompareController@removecompare')
    ->name('product.compare.remove');
// COMPARE SECTION ENDS
// REPLY SECTION
Route::post('/item/reply/{id}', 'Front\CatalogController@reply')
    ->name('product.reply');
Route::post('/item/reply/edit/{id}', 'Front\CatalogController@replyedit')
    ->name('product.reply.edit');
Route::get('/item/reply/delete/{id}', 'Front\CatalogController@replydelete')
    ->name('product.reply.delete');
// REPLY SECTION ENDS
// CART SECTION
Route::get('/carts/view', 'Front\CartController@cartview');
Route::get('/carts/', 'Front\CartController@cart')
    ->name('front.cart');
Route::get('/addcart/{id}', 'Front\CartController@addcart')
    ->name('product.cart.add');
Route::get('/cart/coupon/apply/{id}/{code}', 'Front\CartController@apply_shop_coupon')
    ->name('product.cart.apply_shop_coupon');
Route::get('/cart/coupon/clear/{id}', 'Front\CartController@clear_shop_coupon')
    ->name('product.cart.clear_shop_coupon');
Route::get('/cart/sp/{id}/{point}', 'Front\CartController@updateSP')
    ->name('product.cart.updatesp');
Route::get('/cart/rmsp/{id}', 'Front\CartController@removeSP')
    ->name('product.cart.removeSP');
Route::get('/addtocart/{id}', 'Front\CartController@addtocart')
    ->name('product.cart.quickadd');
Route::get('/addnumcart', 'Front\CartController@addnumcart');
Route::get('/addtonumcart', 'Front\CartController@addtonumcart');
Route::get('/addbyone', 'Front\CartController@addbyone');
Route::get('/reducebyone', 'Front\CartController@reducebyone');
Route::get('/upcolor', 'Front\CartController@upcolor');
Route::get('/removecart/{id}', 'Front\CartController@removecart')
    ->name('product.cart.remove');
Route::get('/carts/coupon', 'Front\CartController@coupon');
Route::get('/carts/coupon/check', 'Front\CartController@couponcheck');
Route::get('/carts/coupon/clear', 'Front\CartController@couponclear');
// CART SECTION ENDS
// PLACE SECTION
Route::get('/districts/{province_id}', 'Front\PlaceController@getdistrictsbyprovinceid');
Route::get('/wards/{district_id}', 'Front\PlaceController@getwardsbydistrictid');
// PLACE SECTION ENDS
// VIETTEL POST SECTION
Route::get('/viettelpost', 'Front\ViettelPostController@index');
Route::get('/viettelpost/token', 'Front\ViettelPostController@gettoken');
Route::get('/viettelpost/fee/{to_province}/{to_district}/{tax?}/{discount?}/{is_online_payment?}', 'Front\ViettelPostController@getfee');
// VIETTEL POST SECTION ENDS
// CHECKOUT SECTION
Route::get('/checkout/', 'Front\CheckoutController@checkout')
    ->name('front.checkout');
Route::get('/checkout/payment/{slug1}/{slug2}', 'Front\CheckoutController@loadpayment')
    ->name('front.load.payment');
Route::get('/order/track/{id}', 'Front\FrontendController@trackload')
    ->name('front.track.search');
Route::get('/order/paymentstatus/{id}', 'Front\FrontendController@track_payment_status')
    ->name('front.track.paymentstatus');
Route::get('/checkout/payment/return', 'Front\PaymentController@payreturn')
    ->name('payment.return');
Route::get('/checkout/payment/cancle', 'Front\PaymentController@paycancle')
    ->name('payment.cancle');
Route::post('/checkout/payment/notify', 'Front\PaymentController@notify')
    ->name('payment.notify');
Route::get('/checkout/instamojo/notify', 'Front\InstamojoController@notify')
    ->name('instamojo.notify');

Route::post('/paystack/submit', 'Front\PaystackController@store')
    ->name('paystack.submit');
Route::post('/instamojo/submit', 'Front\InstamojoController@store')
    ->name('instamojo.submit');
Route::post('/paypal-submit', 'Front\PaymentController@store')
    ->name('paypal.submit');
Route::post('/stripe-submit', 'Front\StripeController@store')
    ->name('stripe.submit');

// Molly Routes
Route::post('/molly/submit', 'Front\MollyController@store')
    ->name('molly.submit');
Route::get('/molly/notify', 'Front\MollyController@notify')
    ->name('molly.notify');
// Molly Routes Ends
//PayTM Routes
Route::post('/paytm-submit', 'Front\PaytmController@store')
    ->name('paytm.submit');;
Route::post('/paytm-callback', 'Front\PaytmController@paytmCallback')
    ->name('paytm.notify');

//RazorPay Routes
Route::post('/razorpay-submit', 'Front\RazorpayController@store')
    ->name('razorpay.submit');;
Route::post('/razorpay-callback', 'Front\RazorpayController@razorCallback')
    ->name('razorpay.notify');

Route::post('/cashondelivery', 'Front\CheckoutController@cashondelivery')
    ->name('cash.submit');
Route::post('/gateway', 'Front\CheckoutController@gateway')
    ->name('gateway.submit');

//vnpay
Route::get('/vnpay', 'Front\VNPayController@index')
    ->name('gateway.vnpay.index');

//elepay
// Route::get('/alepay', 'Front\AlePayController@index')
//     ->name('gateway.elepay.index');


//alepay
Route::get('/alepay/return', 'Client\AlepayController@index')->name('alepay.return');
Route::get('/alepay/check/{orderId}', 'Client\AlepayController@check')->name('alepay.check');
Route::post('/alepay', 'Front\CheckoutController@alepay')->name("alepay.submit");
Route::get('/alepay-cancel', 'Front\AlepayController@cancel')->name("alepay.cancel");
Route::get('/alepay-usermembership-cancel', 'User\AlepayController@cancel')->name("alepay.usermembership.cancel");
Route::get('/alepay-update-payment-to', 'client\AlepayController@updatePaymentTo')->name("alepay.paymentto.update");
// Route::get('/alepay-return', 'Front\AlepayController@returnInfo')->name('alepay.return');

//Onepay
Route::post('/onepay', 'Front\CheckoutController@onepay')
    ->name('onepay.submit');
Route::get('/onepay-checkout', 'Client\OnepayController@index')
    ->name('onepay.checkout');
Route::get('/onepay-return', 'Client\OnepayController@returnResult')
    ->name('onepay.return');
Route::get('/onepay-cancel', 'Client\OnepayController@returnCancelView')
    ->name('onepay.cancel');
Route::get('/onepay-query/{order_id}', 'Client\OnepayController@queryDRResult')
    ->name('onepay.query');
// CHECKOUT SECTION ENDS
// TAG SECTION
Route::get('/search/', 'Front\CatalogController@search')
    ->name('front.search');
// TAG SECTION ENDS
// VENDOR SECTION
//   Route::get('/store/{category?}/{slug1?}/{slug2?}/{slug3?}','Front\VendorController@index')->name('front.vendor');
Route::get('/store/{name?}/{slug1?}/{slug2?}/{slug3?}', 'Front\VendorController@index')
    ->name('front.vendor');
Route::get('/shop/{name}/{slug1?}/{slug2?}/{slug3?}', [VendorController::class , 'getProducts'])
    ->name('front.shop');
Route::get('/shops/{search?}', [VendorController::class , 'shopList'])
    ->name('front.shop-list');
//   Route::get('/category/{category?}/{subcategory?}/{childcategory?}','Front\CatalogController@category')->name('front.category');
//   Route::get('/store/{category}/{slug1?}/{slug2?}/{slug3?}','Front\VendorController@index')->name('front.vendor'); Thannd
Route::post('/vendor/contact', 'Front\VendorController@vendorcontact');
// TAG SECTION ENDS
// SUBSCRIBE SECTION
Route::post('/subscriber/store', 'Front\FrontendController@subscribe')
    ->name('front.subscribe');

// SUBSCRIBE SECTION ENDS


// LOGIN WITH FACEBOOK OR GOOGLE SECTION
Route::get('auth/{provider}', 'User\SocialRegisterController@redirectToProvider')
    ->name('social-provider');
Route::get('auth/{provider}/callback', 'User\SocialRegisterController@handleProviderCallback');
// LOGIN WITH FACEBOOK OR GOOGLE SECTION ENDS
//  CRONJOB
Route::get('/vendor/subscription/check', 'Front\FrontendController@subcheck');
// CRONJOB ENDS
// PAGE SECTION
Route::get('/{slug}', 'Front\FrontendController@page')
    ->name('front.page');