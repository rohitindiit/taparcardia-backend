<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\VendorController;

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

Route::post("user_signup",[UserAuthController::class,"userSignup"]);
Route::post("send_otp",[UserAuthController::class,"forgotPassword"]);
Route::post("verify_otp",[UserAuthController::class,"verifyOtp"]);
Route::post("reset_password",[UserAuthController::class,"resetPassword"]);
Route::post("login",[UserAuthController::class,"login"]);

Route::get('/all_vendor', [BuyerController::class, 'getAllVendor']);
Route::get('/get_products/{vendor_id}/{user_id?}', [BuyerController::class, 'getAllProducts']);

Route::middleware('auth:sanctum')->group(function () {  
Route::get('/account_details/{user_id}', [BuyerController::class, 'accountDetails']);

Route::post('/update_account', [BuyerController::class, 'updateAccount']);
Route::post('/update_profile', [BuyerController::class, 'update_profile']);

Route::post('/add_to_cart', [BuyerController::class, 'addToCart']);
Route::post('/remove_to_cart', [BuyerController::class, 'removeToCart']);
Route::post('/cart_count', [BuyerController::class, 'cartCount']);
Route::get('/get_cart/{user_id}', [BuyerController::class, 'UserCart']);
Route::get('/super_total/{user_id}', [BuyerController::class, 'superTotal']);
Route::post('/update_cart', [BuyerController::class, 'updateCart']);
Route::post('/add_user_address', [BuyerController::class, 'addUserAddress']);
Route::get('/get_user_address/{user_id}', [BuyerController::class, 'getUserAddress']);
Route::post('/place_order', [BuyerController::class, 'placeOrder']);
//Route::get('/user_orders/{user_id}', [BuyerController::class, 'userOrder']);
Route::post('/get_order_details', [BuyerController::class, 'getOrderDetails']);
Route::post('/get_user_orders', [BuyerController::class, 'getUserOrders']);
Route::post('/delete_user_address', [BuyerController::class, 'deleteUserAddress']);
Route::get('/get_user_single_address/{address_id}', [BuyerController::class, 'getUserSingleAddress']);
Route::post('/update_user_address', [BuyerController::class, 'updateUserAddress']);


// Vendor Routes

Route::prefix('vendor')->group(function () {
    Route::post('/products', [VendorController::class, 'products']);
    Route::post('/get_vendor_orders', [VendorController::class, 'getVendorOrders']);
    Route::post('/get_order_details', [VendorController::class, 'getOrderDetails']);
    Route::post('/get_product_details', [VendorController::class, 'getProductDetails']);
  
}); 

   
});


Route::post("add_user",[AdminController::class,"addUser"]);
Route::get("list_user",[AdminController::class,"list_user"]);
Route::post("delete_user",[AdminController::class,"deleteUser"]);
Route::post("view_user",[AdminController::class,"viewUser"]);
Route::post("add_vendor",[AdminController::class,"addVendor"]);
Route::post("add_vendor_product",[AdminController::class,"addVendorProduct"]);
Route::get("vendor_list",[AdminController::class,"vendorList"]);
Route::post("changepassword",[AdminController::class,"resetPassword"]);
