<?php

use App\Http\Controllers\ConsumerController;
use App\Http\Controllers\ShareMealController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShareMealController::class, 'landing'])->name('home');
Route::get('/login', [ShareMealController::class, 'login'])->name('login');
Route::post('/login', [ShareMealController::class, 'doLogin'])->name('login.submit');
Route::get('/register', [ShareMealController::class, 'register'])->name('register');
Route::post('/register', [ShareMealController::class, 'doRegister'])->name('register.submit');
Route::post('/logout', [ShareMealController::class, 'logout'])->name('logout');

Route::post('/notifications/mark-as-read', [ShareMealController::class, 'markNotificationsRead'])->name('notifications.markRead');
Route::get('/profile', [ShareMealController::class, 'editProfile'])->name('profile.edit');
Route::post('/profile', [ShareMealController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/phone/verify', [ShareMealController::class, 'verifyProfilePhone'])->name('profile.phone.verify');

Route::prefix('consumer')->name('consumer.')->group(function () {
    Route::get('/', [ConsumerController::class, 'index'])->name('dashboard');
    Route::get('/search', [ConsumerController::class, 'search'])->name('search');
    Route::get('/history', [ConsumerController::class, 'history'])->name('history');
    
    // PBI #32: Edit & Delete Review - Dikerjakan oleh: Muh Irfan Ubaidillah
    Route::post('/review', [ConsumerController::class, 'submitReview'])->name('review.submit');
    Route::put('/review/{review}', [ConsumerController::class, 'updateReview'])->name('review.update');
    Route::delete('/review/{review}', [ConsumerController::class, 'deleteReview'])->name('review.delete');
    
    Route::get('/education', [ConsumerController::class, 'education'])->name('education');
    Route::get('/education/{id}', [ConsumerController::class, 'showArticle'])->name('education.show');
    Route::get('/checkout', [ConsumerController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [ConsumerController::class, 'storeOrder'])->name('checkout.store');
    // Route::get('/favorites', [ConsumerController::class, 'favorites'])->name('favorites');
});

Route::prefix('mitra')->name('mitra.')->middleware('role:mitra')->group(function () {
    Route::get('/', [ShareMealController::class, 'mitraDashboard'])->name('dashboard');
    Route::get('/profile-usaha', [ShareMealController::class, 'editMitraBusinessProfile'])->name('profile');
    Route::post('/profile-usaha', [ShareMealController::class, 'updateMitraBusinessProfile'])->name('profile.update');
    Route::post('/profile-usaha/contact/verify', [ShareMealController::class, 'verifyMitraBusinessContact'])->name('profile.contact.verify');
    Route::post('/upload-document', [ShareMealController::class, 'uploadBusinessDocument'])->name('upload.document');
    Route::get('/inventory', [ShareMealController::class, 'mitraInventory'])->name('inventory');
    Route::post('/inventory', [ShareMealController::class, 'mitraInventoryStore'])->name('inventory.store');
    Route::post('/inventory/{productId}', [ShareMealController::class, 'mitraInventoryUpdate'])->name('inventory.update');
    Route::post('/inventory/{productId}/flash-sale', [ShareMealController::class, 'mitraInventoryFlashSale'])->name('inventory.flash-sale');
    Route::post('/inventory/{productId}/toggle-donation', [ShareMealController::class, 'mitraInventoryToggleDonation'])->name('inventory.toggle-donation');
    Route::post('/inventory/{productId}/delete', [ShareMealController::class, 'mitraInventoryDelete'])->name('inventory.delete');
    Route::get('/orders', [ShareMealController::class, 'mitraOrders'])->name('orders');
    Route::post('/orders/{orderId}/update-status', [ShareMealController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::post('/orders/{orderId}/confirm', [ShareMealController::class, 'mitraOrdersConfirm'])->name('orders.confirm');
    Route::get('/reviews', [ShareMealController::class, 'mitraReviews'])->name('reviews');
    Route::get('/donations', [ShareMealController::class, 'mitraDonations'])->name('donations');
    Route::post('/donations', [ShareMealController::class, 'mitraDonationStore'])->name('donations.store');
    Route::post('/donations/{donationId}/complete', [ShareMealController::class, 'mitraDonationComplete'])->name('donations.complete');
    Route::post('/donations/{donationId}/cancel', [ShareMealController::class, 'mitraDonationCancel'])->name('donations.cancel');
});

Route::prefix('lembaga')->name('lembaga.')->group(function () {
    Route::get('/', [ShareMealController::class, 'lembagaDashboard'])->name('dashboard');
    Route::post('/upload-document', [ShareMealController::class, 'uploadBusinessDocument'])->name('upload.document');
    Route::get('/donations', [ShareMealController::class, 'lembagaDonations'])->name('donations');
    Route::post('/donations/{donationId}/claim', [ShareMealController::class, 'lembagaClaimDonation'])->name('donations.claim');
    Route::post('/donations/{donationId}/complete', [ShareMealController::class, 'lembagaCompleteDonation'])->name('donations.complete');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [ShareMealController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/verification', [ShareMealController::class, 'adminVerification'])->name('verification');
    Route::post('/verification/{applicationId}/approve', [ShareMealController::class, 'adminApproveApplication'])->name('verification.approve');
    Route::post('/verification/{applicationId}/reject', [ShareMealController::class, 'adminRejectApplication'])->name('verification.reject');
    Route::get('/users', [ShareMealController::class, 'adminUsers'])->name('users');
    Route::get('/reviews', [ShareMealController::class, 'adminReviews'])->name('reviews');
    Route::post('/users/{userId}/warn', [ShareMealController::class, 'adminWarnUser'])->name('users.warn');
    Route::post('/users/{userId}/block', [ShareMealController::class, 'adminBlockUser'])->name('users.block');
    Route::post('/users/{userId}/unblock', [ShareMealController::class, 'adminUnblockUser'])->name('users.unblock');
    Route::get('/education', [ShareMealController::class, 'adminEducation'])->name('education');
    Route::post('/education', [ShareMealController::class, 'adminEducationStore'])->name('education.store');
    Route::post('/education/{articleId}', [ShareMealController::class, 'adminEducationUpdate'])->name('education.update');
    Route::post('/education/{articleId}/delete', [ShareMealController::class, 'adminEducationDelete'])->name('education.delete');
    Route::get('/transactions', [ShareMealController::class, 'adminTransactions'])->name('transactions');
    Route::get('/reports', [ShareMealController::class, 'adminReports'])->name('reports');
});
