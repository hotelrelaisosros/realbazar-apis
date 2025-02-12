<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{Api, RingEnumController};
use App\Http\Controllers;
use Illuminate\Support\Facades\File;

/*use Illuminate\Support\Facades\File;

|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::group(['middleware' => 'cors'], function () {


Route::post('/signup', [Api\AuthController::class, 'signup']);
Route::post('/signup/valid/page1', [Api\AuthController::class, 'signupValidPage1']);
Route::post('/signup/valid/page2', [Api\AuthController::class, 'signupValidPage2']);
Route::post('/signup/valid/page3', [Api\AuthController::class, 'signupValidPage3']);
Route::post('/signup/defaulter', [Api\AuthController::class, 'signupDefaulter']);
Route::post('/check/referral', [Api\AuthController::class, 'checkReferral']);
Route::post('/login', [Api\AuthController::class, 'login']);
Route::post('/reset', [Api\AuthController::class, 'reset']);
Route::post('/forgot', [Api\AuthController::class, 'forgot']);

Route::post('/confirmOtp', [Api\AuthController::class, 'confirmOTP']);
Route::post('/checkToken', [Api\AuthController::class, 'checkToken']);




//subscriber routes
Route::post('/subscribe-email', action: [Api\SubscriberController::class, 'index']);

Route::get('/subscriber/verify/{token}/{email}', [Api\SubscriberController::class, 'verify'])->name('subscriber.verify');

Route::get('/return-token-missing', function () {
    return response()->json([
        'status' => false,
        'message' => 'Token is invalid or expired. Please log in again.'
    ], 401);
})->name('return-token-missing');

Route::get('/return-verification-missing', function () {
    return response()->json([
        'status' => false,
        'message' => 'Your email is not verified. Please check your email to verify it'
    ], 401);
})->name('return-verification-missing');


//auth link email verification
// verify email

Route::get('/email/verify/{id}/{hash}', [Api\AuthController::class, 'verifyLinkEmail'])->name('verifications.verify');

// limited for 6 requests per ip appserviceprovider.php

Route::post('/resend-verification-link', [Api\AuthController::class, 'resendVerificationLink'])->name('verifications.resend');

//send verification again
// Route::post('/email/verification-notification', function (Request $request) {
//     $request->user()->sendEmailVerificationNotification();

//     return back()->with('message', 'Verification link sent!');
// })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Route::middleware('auth:api')->group(callback: function () {
//auth
Route::get('show/shop/{id}', [Api\AuthController::class, 'show']);
Route::get('profile', [Api\AuthController::class, 'edit_profile']);
Route::post('profile/update', [Api\AuthController::class, 'update_profile']);
Route::post('shop/followers', [Api\AuthController::class, 'shopFollow']);
Route::get('user/block/{id}/{message?}', [Api\AuthController::class, 'userBlock']);
Route::get('user/delete', [Api\AuthController::class, 'userDelete']);
Route::get('/logout', [Api\AuthController::class, 'logout']);

//category
Route::post('add/category', [Api\CategoryController::class, 'add']);
Route::post('update/category', [Api\CategoryController::class, 'update']);
Route::post('delete/category', [Api\CategoryController::class, 'delete']);

//subcategory
Route::post('add/subcategory', [Api\SubCategoryController::class, 'add']);
Route::post('update/subcategory', [Api\SubCategoryController::class, 'update']);
Route::post('delete/subcategory', [Api\SubCategoryController::class, 'delete']);

//product
Route::post('show/admin/products', [Api\ProductController::class, 'showAdminProduct']);
Route::get('show/seller/products/{skip?}/{take?}', [Api\ProductController::class, 'showSellerProduct']);
Route::post('add/product', [Api\ProductController::class, 'add']);
Route::post('update/product', [Api\ProductController::class, 'update']);
Route::post('delete/product', [Api\ProductController::class, 'delete']);
Route::get('hard/delete/product/{id}', [Api\ProductController::class, 'hardDelete']);
Route::get('all/hard/delete/product', [Api\ProductController::class, 'allHardDelete']);
Route::post('show/delete/product', [Api\ProductController::class, 'showDeleteProduct']);

//product image crud
Route::get('image/product_id/{id}', [Api\ProductController::class, 'image']);
Route::get('image/product_image/{id}', [Api\ProductController::class, 'product_image_search']);

Route::post('delete/image', [Api\ProductController::class, 'deleteImage']);
Route::post('add/image', [Api\ProductController::class, 'addImage']);
Route::post('update/image', [Api\ProductController::class, 'updateImage']);

Route::post('image/search_variation_product', [Api\ProductController::class, 'image_by_variant']);

//delete individual images/
Route::post('delete/images/one_image', [Api\ProductController::class, 'deleteOneImageRecord']);

Route::post('add/images/one_image', [Api\ProductController::class, 'addImageDynamic']);


//product image crud
Route::get('enum/product_id/{id}', [Api\ProductEnumController::class, 'image']);
// Route::get('image/product_image/{id}', [Api\ProductEnumController::class, 'product_image_search']);

// Route::post('delete/image', [Api\ProductEnumController::class, 'deleteImage']);
// Route::post('add/image', [Api\ProductEnumController::class, 'addImage']);
// Route::post('update/image', [Api\ProductEnumController::class, 'updateImage']);


// product status
Route::get('history/products', [Api\ProductController::class, 'historyProduct']);
Route::post('add/history/products', [Api\ProductController::class, 'addHistoryProduct']);
Route::post('like/products', [Api\ProductController::class, 'likeProduct']);
Route::post('review/products', [Api\ProductController::class, 'reviewProduct']);
Route::post('status/change/product', [Api\ProductController::class, 'statusChangeProduct']);
Route::get('status/trending/product/{id}', [Api\ProductController::class, 'productStatusTrending']);
Route::post('product/status/change', [Api\ProductController::class, 'productStatusChange']);

//order
Route::post('order', [Api\OrderController::class, 'order'])->middleware('auth:api');
Route::post('get/order', [Api\OrderController::class, 'show']);

Route::get('users/order/{status?}', [Api\OrderController::class, 'userOrder']);
Route::get('sellers/order/{status?}', [Api\OrderController::class, 'sellerOrder']);
Route::post('status/change/order', [Api\OrderController::class, 'orderStatusChange']);
Route::get('get/refund/order', [Api\OrderController::class, 'orderRefundGet']);
Route::post('refund/order', [Api\OrderController::class, 'orderRefund']);
Route::post('status/change/refund/order', [Api\OrderController::class, 'orderRefundStatusChange']);
Route::post('payment/inquiry', [Api\OrderController::class, 'paymentInquiry']);

Route::get('get_user/order', [Api\OrderController::class, 'get_user_orders'])->middleware('auth:api');
Route::get('get_user/order/{orderId}', [Api\OrderController::class, 'get_user_orders_by_id'])->middleware('auth:api');



//package
Route::get('/package', [Api\PackageController::class, 'show']);
Route::post('add/package', [Api\PackageController::class, 'add']);
Route::post('update/package', [Api\PackageController::class, 'update']);
Route::post('delete/package', [Api\PackageController::class, 'delete']);
Route::post('buy/package', [Api\PackageController::class, 'payment']);
Route::post('exist/package/payment', [Api\PackageController::class, 'existPayment']);
Route::post('subscription/expired', [Api\PackageController::class, 'packageExpiredPeriod']);
Route::get('package/expired/period', [Api\PackageController::class, 'subsPackageExpiredPeriod']);
Route::post('/package/defaulter', [Api\PackageController::class, 'packageDefaulter']);

//chat
Route::get('/allMessages', [Api\ChatController::class, 'allMessages']);
Route::get('/adminShowChat/{id}', [Api\ChatController::class, 'adminShowChat']);
Route::post('/message', [Api\ChatController::class, 'message']);
Route::post('/chats', [Api\ChatController::class, 'chat']);

//banner
Route::get('/banner/{section}', [Api\BannerController::class, 'banner']);
Route::post('/add/banner', [Api\BannerController::class, 'addBanner']);
Route::post('/update/banner', [Api\BannerController::class, 'updateBanner']);
Route::post('/delete/banner', [Api\BannerController::class, 'deleteBanner']);

// homePageImage
Route::get('/homePageImage/{section}/{is_app}/{role?}', [Api\HomePageImageController::class, 'homePageImage']);
Route::post('/add/homePageImage', [Api\HomePageImageController::class, 'addhomePageImage']);
Route::post('/update/homePageImage', [Api\HomePageImageController::class, 'updatehomePageImage']);
Route::post('/delete/homePageImage', [Api\HomePageImageController::class, 'deletehomePageImage']);

//notification
Route::get('/allNotification/{skip}/{take}/{status}', [Api\NotificationController::class, 'allNotification']);
Route::get('/showNotification', [Api\NotificationController::class, 'notification']);
Route::get('/changeNotification/{id?}', [Api\NotificationController::class, 'notification_change']);
Route::post('/sendAllNotification', [Api\NotificationController::class, 'sendAllNotification']);
Route::post('/sendNotification', [Api\NotificationController::class, 'sendNotification']);
Route::post('/singleNotification', [Api\NotificationController::class, 'singleNotification']);
Route::get('/check_push_notification', [Api\NotificationController::class, 'notisend2']);



//report
Route::post('/report', [Api\ReportController::class, 'report']);
Route::post('/reports', [Api\ReportController::class, 'reports']);
Route::post('/add/report', [Api\ReportController::class, 'addReport']);
Route::get('/delete/report/{id}', [Api\ReportController::class, 'deleteReport']);
Route::get('/delete/all/report', [Api\ReportController::class, 'deleteAllReport']);
Route::get('/delete/all/user/report/{user_id}', [Api\ReportController::class, 'deleteAllUserReport']);

//Demandproducts
Route::post('/add/demand/product', [Api\DemandProductController::class, 'addDemandProduct']);
Route::get('/demand/products', [Api\DemandProductController::class, 'demandProduct']);
Route::post('/complete/demand', [Api\DemandProductController::class, 'completeDemand']);
Route::get('/complete/demand/products', [Api\DemandProductController::class, 'completeDemandProduct']);
Route::get('/user/pending/demand/products', [Api\DemandProductController::class, 'userPendingDemandProduct']);
Route::get('/user/active/demand/products', [Api\DemandProductController::class, 'userActiveDemandProduct']);

//referralUser
Route::get('/referralUser', [Api\ReferralController::class, 'referralUsers']);
Route::get('/show/referralUser/{id}', [Api\ReferralController::class, 'showReferralUsers']);
Route::post('/add/referralUser', [Api\ReferralController::class, 'addReferralUsers']);
Route::post('/update/referralUser', [Api\ReferralController::class, 'updateReferralUsers']);
Route::get('/delete/referralUser/{id}', [Api\ReferralController::class, 'deleteReferralUsers']);
// });

//users
Route::post('wholesalers', [Api\AuthController::class, 'wholesaler']);
Route::post('users', [Api\AuthController::class, 'user']);
Route::post('retailers', [Api\AuthController::class, 'retailer']);

//new api
Route::prefix('api/v1')->group(function () {

    // product
    Route::prefix('products')->group(function () {
        Route::get('/get_all_products', [Api\ProductController::class, 'get_all_products']);
        Route::get('/get_all_products/{productId}', [Api\ProductController::class, 'get_one_product']);
        Route::get('/rings', [Api\ProductController::class, 'get_all_ring_product']);
        Route::get('/rings/{productId}', [Api\ProductController::class, 'get_one_ring_product']);
        Route::get('/search_by_category', [Api\ProductController::class, 'get_by_category']);
        Route::get('/testfunc', [Api\ProductController::class, 'testfunc']);

        Route::get('/add_images', [Api\ProductController::class, 'add_images_all_products']);
        Route::get('/add_images_rings', [Api\ProductController::class, 'add_images_rings']);

        Route::post('/update_customization', [Api\ProductController::class, 'updateProductEnums']);


        Route::prefix('search')->group(function () {
            Route::post('/search_bar', [Api\ProductController::class, 'search_products_bar']);
            Route::get('/step_one', [Api\ProductController::class, 'search_products_step_one']);
            Route::get('/home_page', [Api\ProductController::class, 'search_products_homepage']);
        });

        Route::prefix('gemshapes')->group(function () {
            Route::get('/', [Api\GemshapeController::class, 'index']);
            Route::get('/{id}', [Api\GemshapeController::class, 'show']);
            Route::post('/', [Api\GemshapeController::class, 'store']);
            Route::post('/{id}', [Api\GemshapeController::class, 'update']);
            Route::delete('/{id}', [Api\GemshapeController::class, 'destroy']);
        });
        Route::prefix('birthstones')->group(function () {
            Route::get('/', [Api\BirthStoneController::class, 'index']);
            Route::get('{id}', [Api\BirthStoneController::class, 'show']);
            Route::post('/', [Api\BirthStoneController::class, 'store']);
            Route::post('{id}', [Api\BirthStoneController::class, 'update']);
            Route::delete('{id}', [Api\BirthStoneController::class, 'destroy']);
        });

        Route::prefix('gem_stones_colors')->group(function () {
            Route::get('/', [Api\GemStoneColorController::class, 'index']);
            Route::get('{id}', [Api\GemStoneColorController::class, 'show']);
            Route::post('/', [Api\GemStoneColorController::class, 'store']);
            Route::post('{id}', [Api\GemStoneColorController::class, 'update']);
            Route::delete('{id}', [Api\GemStoneColorController::class, 'destroy']);
        });


        Route::prefix('gem_stones')->group(function () {
            Route::get('/', [Api\GemStoneController::class, 'index']);
            Route::get('{id}', [Api\GemStoneController::class, 'show']);
            Route::post('/', [Api\GemStoneController::class, 'store']);
            Route::put('{id}', [Api\GemStoneController::class, 'update']);
            Route::delete('{id}', [Api\GemStoneController::class, 'destroy']);
        });
        Route::prefix('bespoke_customization')->group(function () {
            Route::get('/', [Api\BespokeCustomizationController::class, 'index']);
            Route::get('{id}', [Api\BespokeCustomizationController::class, 'show']);
            Route::post('/', [Api\BespokeCustomizationController::class, 'store']);
            Route::post('{id}', [Api\BespokeCustomizationController::class, 'update']);
            Route::delete('{id}', [Api\BespokeCustomizationController::class, 'destroy']);
        });

        Route::prefix('bespoke_customization_types')->group(function () {
            Route::get('/', [Api\BespokeCustomizationTypeController::class, 'index']);
            Route::get('/child/{id}', [Api\BespokeCustomizationTypeController::class, 'by_bsp']);

            Route::get('{id}', [Api\BespokeCustomizationTypeController::class, 'show']);
            Route::post('/', [Api\BespokeCustomizationTypeController::class, 'store']);
            Route::put('{id}',  [Api\BespokeCustomizationTypeController::class, 'update']);
            Route::delete('{id}', [Api\BespokeCustomizationTypeController::class, 'destroy']);
        });

        Route::prefix('metal_type_category')->group(function () {
            Route::get('/', [Api\MetalTypeCategoryController::class, 'index']);
            Route::get('{id}', [Api\MetalTypeCategoryController::class, 'show']);
            Route::post('/', [Api\MetalTypeCategoryController::class, 'store']);
            Route::post('{id}', [Api\MetalTypeCategoryController::class, 'update']);
            Route::delete('{id}', [Api\MetalTypeCategoryController::class, 'destroy']);
        });


        Route::prefix('front')->group(function () {
            Route::post('/step1', [Api\ProductController::class, 'getAllRingProducts']);
            Route::post('/step2', [Api\ProductController::class, 'showSpecificRingVarition']);
        });

        Route::prefix('simple')->group(function () {
            Route::post('/get_product', [Api\ProductController::class, 'getSpecificNonRing']);
            Route::post('/get_all_products', [Api\ProductController::class, 'getAllNonRingProducts']);
        });
    });

    Route::prefix("customization")->group(function () {
        Route::get('/gem_shapes', [RingEnumController::class, 'getGemShapes']);
        Route::get('/band_widths', [RingEnumController::class, 'getBandWidth']);
        Route::get('/accent_stone_type', [RingEnumController::class, 'getAccentStoneType']);
        Route::get('/setting_height', [RingEnumController::class, 'getSettingHeights']);
        Route::get('/prong_style', [RingEnumController::class, 'getProngStyles']);
        Route::get('/ring_size', [RingEnumController::class, 'getRingSize']);
        Route::get('/bespoke_customization', [RingEnumController::class, 'getBespokeCustomization']);
        Route::get('/bespoke_customization_type', [RingEnumController::class, 'getBespokeCustomizationType']);
        Route::get('/bespoke_with_types', [RingEnumController::class, 'bespoke_with_types']);

        Route::get('/birth_stone', [RingEnumController::class, 'getBirthStone']);
        Route::get('/gem_stone_faceting', [RingEnumController::class, 'getGemStoneFaceting']);
    });

    Route::prefix('variations')->group(function () {
        Route::post('/getall', [Api\ProductVariationController::class, 'index']);
        Route::get('/{id}', [Api\ProductVariationController::class, 'show']);
        Route::post('/', [Api\ProductVariationController::class, 'store']);
        Route::post('/update/{id}', [Api\ProductVariationController::class, 'update']);
        Route::delete('/{id}', [Api\ProductVariationController::class, 'destroy']);
        Route::post('/connect-image', [Api\ProductVariationController::class, 'connectProductImage']);
    });

    Route::prefix('subcategory')->group(function () {
        Route::get('/show/{id}', [Api\SubCategoryController::class, 'showSubCategoryFront']);
    });

    Route::prefix('category')->group(function () {
        Route::get('/show', [Api\SubCategoryController::class, 'showCategoryFront']);
    });
});
// Route::get('/', function () {
//     return "hello from laravel";
// });
// });
// });


//might be useful
Route::get('home/{role?}', [Api\ProductController::class, 'home']);
// Route::get('search/{name}/{role?}', [Api\ProductController::class, 'search']);
Route::post('show/product', [Api\ProductController::class, 'showProduct']);


//useless
Route::get('products/{role?}/{skip?}/{take?}', [Api\ProductController::class, 'show']);
Route::post('shop/product', [Api\ProductController::class, 'vendorProduct']);
Route::get('discount/product/{role?}/{skip?}/{take?}', [Api\ProductController::class, 'discountProduct']);
Route::get('featured/product/{role?}/{skip?}/{take?}', [Api\ProductController::class, 'featuredProduct']);
Route::get('newArrival/product/{role?}/{skip?}/{take?}', [Api\ProductController::class, 'newArrivalProduct']);
Route::get('topRating/product/{role?}/{skip?}/{take?}', [Api\ProductController::class, 'topRatingProduct']);
Route::get('trending/product/{role?}/{skip?}/{take?}', [Api\ProductController::class, 'trendingProduct']);
Route::get('wholesaler/products', [Api\ProductController::class, 'wholesalerProducts']);
Route::get('app/wholesaler/products', [Api\ProductController::class, 'appWholesalerProducts']);




//category
Route::get('category', [Api\CategoryController::class, 'show']);
Route::get('subcategory', [Api\SubCategoryController::class, 'show']);
Route::get('show/subcategory/{id}', [Api\SubCategoryController::class, 'fetchSubCategory']);
Route::post('search/category', [Api\CategoryController::class, 'searchCategory']);
Route::post('subscribe', [Api\SettingController::class, 'subscribe']);



//banner
Route::get('/banners/{section}', [Api\BannerController::class, 'banners']);

//homePageImage
Route::get('/homePageImages/{section}', [Api\HomePageImageController::class, 'homePageImages']);

//sales
Route::get('/seller/top/sales/{role?}', [Api\ProductController::class, 'seller_top_sales']);



// SOCIAL lOGIN
Route::post('google/login', [Api\SocialLoginController::class, "googleLogin"]);
Route::post('facebook/login', [Api\SocialLoginController::class, "facebookLogin"]);
Route::post('apple/login', [Api\SocialLoginController::class, "appleLogin"]);

//payment
Route::post('jazzcash/checkout', [Api\OrderController::class, "jazzcashCheckout"]);
Route::post('jazzcash/card/refund', [Api\OrderController::class, "jazzcashCardRefund"]);
Route::post('jazzcash/mobile/refund', [Api\OrderController::class, "jazzcashMobileRefund"]);
Route::post('jazzcash/status/inquiry', [Api\OrderController::class, "jazzcashStatusInquiry"]);
Route::post('easypaisa/checkout', [Api\OrderController::class, "easypaisaCheckout"]);
Route::post('payment/status', [Api\OrderController::class, "jazzcashPaymentStatus"]);


//stripe payment
Route::get('/checkout', [Api\StripeController::class, "checkout"])->name("checkout");
Route::post('/stripe/test', [Api\StripeController::class, "test"])->name("test");
Route::post('/stripe/live', [Api\StripeController::class, "live"])->name("live");
Route::post('/stripe/getAllTransaction',  [Api\StripeController::class, "getStripeTransactions"]);
Route::post('/stripe/getSingleCharge',  [Api\StripeController::class, "getSingleCharge"]);
Route::post('/stripe/getCustomerTransactions',  [Api\StripeController::class, "getCustomerTransactions"]);
Route::post('/stripe/refundTransaction',  [Api\StripeController::class, "refundTransaction"]);
Route::get('/stripe/getUserCards',  action: [Api\StripeController::class, "getUserCards"]);




//refund
Route::post('/stripe/refundTransaction',  [Api\StripeController::class, "refundTransaction"]);



//send email on successful transaction
// 127.0.0.1:8000/stripe/webhook
Route::post('/stripe/webhook', [Api\StripeController::class, 'handleWebhook']);
Route::post('stripe/status/inquiry', [Api\StripeController::class, "jazzcashStatusInquiry"]);
Route::post('easypaisa/checkout', [Api\StripeController::class, "easypaisaCheckout"]);
Route::post('payment/status', [Api\StripeController::class, "jazzcashPaymentStatus"]);


//



//order & wishlist 

Route::group(['middleware' => ['auth:api']], function () {

    //only one ring can go to cart
    Route::prefix('cart')->group(function () {
        Route::post('/add-to-cart', [Api\CartController::class, 'addToCart']);
        Route::post('/update-cart-customization', [Api\CartController::class, 'updateCartCustomization']);

        Route::prefix('table')->group(function () {
            Route::post('/update-cart', [Api\CartController::class, 'updateCartTable']);
            Route::post('/remove-cart', [Api\CartController::class, 'removeCartTable']);
            Route::post('/clear-cart', [Api\CartController::class, 'clearCartTable']);
            Route::get('/show-cart', [Api\CartController::class, 'showCartTable']);
        });
        Route::prefix('session')->group(function () {
            Route::post('/update-cart', [Api\CartController::class, 'updateCartSession']);
            Route::post('/remove-cart', [Api\CartController::class, 'removeCartSession']);
            Route::post('/clear-cart', [Api\CartController::class, 'clearCartSession']);
            Route::get('/show-cart', [Api\CartController::class, 'showCartSession']);
        });
    });
    Route::prefix('wishlist')->group(function () {
        Route::get('/view', [Api\WishListController::class, 'viewWishlist']);
        Route::post('/add', [Api\WishListController::class, 'addToWishlist']);
        Route::post('/back', [Api\WishListController::class, 'backToCart']);
        Route::post('/empty', [Api\WishListController::class, 'removeAllFromWishlist']);
        Route::post('/remove', [Api\WishListController::class, 'removeFromWishlist']);
    });
});

Route::get('/enums/getFaceting', [Api\ProductEnumController::class, 'getFaceting'])->name('getfaceing');

Route::get('/serve-file/{filename}', [Api\FileController::class, 'serveFile'])->name('serve-file');

    //ring_customizaiton_page1:

    // step1 crud
    // Your routes here
// });//auth
