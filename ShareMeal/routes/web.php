<?php

use App\Http\Controllers\ConsumerController;
use App\Http\Controllers\ShareMealController;
use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShareMealController::class, 'landing'])->name('home');
Route::get('/login', [ShareMealController::class, 'login'])->name('login');
Route::post('/login', [ShareMealController::class, 'doLogin'])->middleware('throttle:5,1')->name('login.submit');
Route::get('/register', [ShareMealController::class, 'register'])->name('register');
Route::post('/register', [ShareMealController::class, 'doRegister'])->name('register.submit');

// Password Reset Routes
Route::get('/forgot-password', [ShareMealController::class, 'forgotPassword'])->name('password.request');
Route::post('/forgot-password', [ShareMealController::class, 'sendResetOtp'])->name('password.email');
Route::get('/verify-reset-otp', [ShareMealController::class, 'verifyResetOtpForm'])->name('password.verify_otp_form');
Route::post('/verify-reset-otp', [ShareMealController::class, 'verifyResetOtp'])->name('password.verify_otp');
Route::get('/reset-password', [ShareMealController::class, 'resetPassword'])->name('password.reset');
Route::post('/reset-password', [ShareMealController::class, 'updatePassword'])->name('password.update');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [ShareMealController::class, 'logout'])->name('logout');
    Route::post('/notifications/mark-as-read', [ShareMealController::class, 'markNotificationsRead'])->name('notifications.markRead');
    Route::post('/notifications/{id}/mark-as-read', [ShareMealController::class, 'markSingleNotificationRead'])->name('notifications.markSingleRead');
    Route::get('/notifications', [ShareMealController::class, 'allNotifications'])->name('notifications.index');
    Route::get('/profile', [ShareMealController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile', [ShareMealController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/phone/verify', [ShareMealController::class, 'verifyProfilePhone'])->name('profile.phone.verify');
});

Route::prefix('consumer')->name('consumer.')->middleware('role:consumer')->group(function () {
    Route::get('/', [ConsumerController::class, 'index'])->name('dashboard');
    Route::get('/search', [ConsumerController::class, 'search'])->name('search');
    Route::get('/history', [ConsumerController::class, 'history'])->name('history');
    Route::get('/orders/active', [ConsumerController::class, 'activeOrders'])->name('orders.active');
    Route::post('/orders/{orderId}/confirm-complete', [ConsumerController::class, 'confirmComplete'])->name('orders.confirm-complete');
    
    // PBI #32: Edit & Delete Review - Dikerjakan oleh: Muh Irfan Ubaidillah
    Route::post('/review', [ConsumerController::class, 'submitReview'])->name('review.submit');
    Route::put('/review/{review}', [ConsumerController::class, 'updateReview'])->name('review.update');
    Route::delete('/review/{review}', [ConsumerController::class, 'deleteReview'])->name('review.delete');
    
    Route::get('/education', [ConsumerController::class, 'education'])->name('education');
    Route::get('/education/{id}', [ConsumerController::class, 'showArticle'])->name('education.show');
    Route::get('/cart', [ConsumerController::class, 'viewCart'])->name('cart.index');
    Route::post('/cart/add', [ConsumerController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove/{id}', [ConsumerController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/update/{id}', [ConsumerController::class, 'updateCartQuantity'])->name('cart.update');
    
    Route::get('/checkout', [ConsumerController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [ConsumerController::class, 'storeOrder'])->name('checkout.store');
    Route::post('/report', [ConsumerController::class, 'submitProblemReport'])->name('report.submit');
    // Route::get('/favorites', [ConsumerController::class, 'favorites'])->name('favorites');
    Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

Route::prefix('mitra')->name('mitra.')->middleware(['role:mitra', 'profile.complete'])->group(function () {
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
    Route::post('/orders/{orderId}/delay', [ShareMealController::class, 'delayOrder'])->name('orders.delay');
    Route::post('/orders/{orderId}/confirm', [ShareMealController::class, 'mitraOrdersConfirm'])->name('orders.confirm');
    Route::get('/reviews', [ShareMealController::class, 'mitraReviews'])->name('reviews');
    Route::get('/history', [ShareMealController::class, 'mitraHistory'])->name('history');
    Route::get('/donations', [ShareMealController::class, 'mitraDonations'])->name('donations');
    Route::post('/donations', [ShareMealController::class, 'mitraDonationStore'])->name('donations.store');
    Route::post('/donations/{donationId}/prepare', [ShareMealController::class, 'mitraDonationPrepare'])->name('donations.prepare');
    Route::post('/donations/{donationId}/complete', [ShareMealController::class, 'mitraDonationComplete'])->name('donations.complete');
    Route::post('/donations/{donationId}/cancel', [ShareMealController::class, 'mitraDonationCancel'])->name('donations.cancel');
    Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

Route::prefix('lembaga')->name('lembaga.')->middleware(['role:lembaga', 'profile.complete'])->group(function () {
    Route::get('/', [ShareMealController::class, 'lembagaDashboard'])->name('dashboard');
    Route::post('/upload-document', [ShareMealController::class, 'uploadBusinessDocument'])->name('upload.document');
    Route::get('/donations', [ShareMealController::class, 'lembagaDonations'])->name('donations');
    Route::get('/history', [ShareMealController::class, 'lembagaHistory'])->name('history');
    Route::post('/donations/{donationId}/claim', [ShareMealController::class, 'lembagaClaimDonation'])->name('donations.claim');
    Route::post('/donations/{donationId}/complete', [ShareMealController::class, 'lembagaCompleteDonation'])->name('donations.complete');
    Route::post('/report', [ShareMealController::class, 'lembagaSubmitProblemReport'])->name('report.submit');
    Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
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
    Route::get('/transactions/export-csv', [ShareMealController::class, 'adminExportTransactionsCsv'])->name('transactions.export-csv');
    Route::get('/reports', [ShareMealController::class, 'adminReports'])->name('reports');
    Route::get('/reports/export-pdf', [ShareMealController::class, 'adminExportReportsPdf'])->name('reports.export-pdf');
    Route::get('/reports/export-excel', [ShareMealController::class, 'adminExportReportsExcel'])->name('reports.export-excel');
    
    // PBI #47 & #48: Moderation Reports
    Route::get('/problem-reports', [ShareMealController::class, 'adminProblemReports'])->name('problem-reports.index');
    Route::post('/problem-reports/{report}/dismiss', [ShareMealController::class, 'adminDismissReport'])->name('problem-reports.dismiss');
    Route::post('/problem-reports/{report}/warn', [ShareMealController::class, 'adminWarnMitraReport'])->name('problem-reports.warn');
    Route::post('/problem-reports/{report}/block', [ShareMealController::class, 'adminBlockMitraReport'])->name('problem-reports.block');
    Route::get('/logs', [ShareMealController::class, 'adminLogs'])->name('logs');
    Route::get('/feedbacks', [FeedbackController::class, 'adminIndex'])->name('feedbacks.index');
    Route::delete('/feedbacks/{feedback}', [FeedbackController::class, 'adminDelete'])->name('feedbacks.delete');
    Route::post('/feedbacks/{feedback}/toggle-status', [FeedbackController::class, 'adminToggleStatus'])->name('feedbacks.toggle-status');
});

Route::get('/test-broadcast', function () {
    event(new \App\Events\TestBroadcast('Koneksi real-time berhasil!'));
    return 'Broadcast terkirim ke channel "test-channel"!';
});
