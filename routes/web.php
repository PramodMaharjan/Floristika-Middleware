<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\LalamoveController;
use App\Http\Controllers\LineClearController;
use App\Http\Controllers\DetrackController;
use App\Http\Controllers\ServerSideListController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect()->route('login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/orders/{order}', [DashboardController::class, 'show'])->name('admin.orders.show');
    Route::get('/admin/test-shopify', [DashboardController::class, 'testShopifyConnection'])->name('admin.test-shopify');
    
    Route::post('/admin/orders/update-card', [DashboardController::class, 'updateCard'])->name('admin.orders.updateCard');
    Route::post('/admin/orders/update-delivery', [DashboardController::class, 'updateDelivery'])->name('admin.orders.updateDelivery');
    Route::post('/admin/orders/update-shipping', [DashboardController::class, 'updateShipping'])->name('admin.orders.updateShipping');
    Route::post('/admin/orders/update-sender', [DashboardController::class, 'updateSender'])->name('admin.orders.updateSender');
    Route::post('/admin/orders/update-delivery-partner', [DashboardController::class, 'updateDeliveryPartner'])->name('admin.orders.updateDeliveryPartner');
    
    Route::post('/admin/orders/set-order-field', [DashboardController::class, 'setOrderField'])->name('admin.orders.setOrderField');
    Route::post('/admin/orders/product-images', [DashboardController::class, 'productImages'])->name('admin.orders.productImages');

    Route::post('/orders/list', [ServerSideListController::class, 'getOrdersList'])->name('orders.list');
    // Line Clear
    Route::post('/lineclear/create-shipment', [LineClearController::class, 'createLineclearShipment'])->name('lineclear.create-shipment');
    Route::post('/lineclear/download-waybill', [LineClearController::class, 'downloadLineClearWaybill'])->name('lineclear.download-waybill');
    Route::post('/lineclear/download-pod', [LineClearController::class, 'downloadPOD'])->name('lineclear.download-pod');
    // Lalamove
    Route::post('/lalamove/get-quote', [LalamoveController::class, 'getLalamoveQuote'])->name('lalamove.get-quote');
    Route::post('/lalamove/create-shipment', [LalamoveController::class, 'placeLalamoveOrder'])->name('lalamove.place-order');
    Route::get('/lalamove/order/{orderId}', [LalamoveController::class, 'viewOrder'])->name('lalamove.view-order');
    Route::get('/lalamove/pod-image', [LalamoveController::class, 'downloadPodImage'])->name('lalamove.download-pod-image');
    // Detrack
    Route::post('/detrack/create-jobs', [DetrackController::class, 'createJobs'])->name('detrack.create-jobs');
    Route::post('/detrack/download-pod', [DetrackController::class, 'downloadDetrackPOD'])->name('detrack.download-detrack-pod');
    Route::get('/detrack/vehicles', [DetrackController::class, 'vehicles'])->name('detrack.vehicles');;


});

require __DIR__.'/auth.php';
