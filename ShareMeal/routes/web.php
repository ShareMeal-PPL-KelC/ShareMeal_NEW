<?php

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Consumer\ConsumerDashboardController;
use App\Http\Controllers\Consumer\ConsumerCartController;
use App\Http\Controllers\Consumer\ConsumerActivityController;
use App\Http\Controllers\Consumer\ConsumerEducationController;
use App\Http\Controllers\Mitra\MitraDashboardController;
use App\Http\Controllers\Mitra\MitraInventoryController;
use App\Http\Controllers\Mitra\MitraDonationController;
use App\Http\Controllers\Mitra\MitraOrderController;
use App\Http\Controllers\Lembaga\LembagaDashboardController;
use App\Http\Controllers\Lembaga\LembagaDonationController;
use App\Http\Controllers\Lembaga\LembagaReportController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminVerificationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProblemReportController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\AdminEducationController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'landing'])->name('home');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'doLogin'])->middleware('throttle:5,1')->name('login.submit');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'doRegister'])->name('register.submit');

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetOtp'])->name('password.email');
Route::get('/verify-reset-otp', [PasswordResetController::class, 'verifyResetOtpForm'])->name('password.verify_otp_form');
Route::post('/verify-reset-otp', [PasswordResetController::class, 'verifyResetOtp'])->name('password.verify_otp');
Route::get('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'updatePassword'])->name('password.update');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markNotificationsRead'])->name('notifications.markRead');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markSingleNotificationRead'])->name('notifications.markSingleRead');
    Route::get('/notifications', [NotificationController::class, 'allNotifications'])->name('notifications.index');
    Route::get('/profile', [ProfileController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/phone/verify', [ProfileController::class, 'verifyProfilePhone'])->name('profile.phone.verify');
});

Route::prefix('consumer')->name('consumer.')->middleware('role:consumer')->group(function () {
    Route::get('/', [ConsumerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [ConsumerDashboardController::class, 'search'])->name('search');
    Route::get('/history', [ConsumerDashboardController::class, 'history'])->name('history');
    Route::get('/orders/active', [ConsumerDashboardController::class, 'activeOrders'])->name('orders.active');
    Route::post('/orders/{orderId}/confirm-complete', [ConsumerDashboardController::class, 'confirmComplete'])->name('orders.confirm-complete');
    
    // PBI #32: Edit & Delete Review - Dikerjakan oleh: Muh Irfan Ubaidillah
    Route::post('/review', [ConsumerActivityController::class, 'submitReview'])->name('review.submit');
    Route::put('/review/{review}', [ConsumerActivityController::class, 'updateReview'])->name('review.update');
    Route::delete('/review/{review}', [ConsumerActivityController::class, 'deleteReview'])->name('review.delete');
    
    Route::get('/education', [ConsumerEducationController::class, 'education'])->name('education');
    Route::get('/education/{id}', [ConsumerEducationController::class, 'showArticle'])->name('education.show');
    Route::get('/cart', [ConsumerCartController::class, 'viewCart'])->name('cart.index');
    Route::post('/cart/add', [ConsumerCartController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove/{id}', [ConsumerCartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/update/{id}', [ConsumerCartController::class, 'updateCartQuantity'])->name('cart.update');
    
    Route::get('/checkout', [ConsumerCartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [ConsumerCartController::class, 'storeOrder'])->name('checkout.store');
    Route::post('/report', [ConsumerActivityController::class, 'submitProblemReport'])->name('report.submit');
    // Route::get('/favorites', [ConsumerDashboardController::class, 'favorites'])->name('favorites');
    Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

Route::prefix('mitra')->name('mitra.')->middleware(['role:mitra', 'profile.complete'])->group(function () {
    Route::get('/', [MitraDashboardController::class, 'mitraDashboard'])->name('dashboard');
    Route::get('/profile-usaha', [MitraDashboardController::class, 'editMitraBusinessProfile'])->name('profile');
    Route::post('/profile-usaha', [MitraDashboardController::class, 'updateMitraBusinessProfile'])->name('profile.update');
    Route::post('/profile-usaha/contact/verify', [MitraDashboardController::class, 'verifyMitraBusinessContact'])->name('profile.contact.verify');
    Route::post('/upload-document', [ProfileController::class, 'uploadBusinessDocument'])->name('upload.document');
    Route::get('/inventory', [MitraInventoryController::class, 'mitraInventory'])->name('inventory');
    Route::post('/inventory', [MitraInventoryController::class, 'mitraInventoryStore'])->name('inventory.store');
    Route::post('/inventory/{productId}', [MitraInventoryController::class, 'mitraInventoryUpdate'])->name('inventory.update');
    Route::post('/inventory/{productId}/flash-sale', [MitraInventoryController::class, 'mitraInventoryFlashSale'])->name('inventory.flash-sale');
    Route::post('/inventory/{productId}/toggle-donation', [MitraInventoryController::class, 'mitraInventoryToggleDonation'])->name('inventory.toggle-donation');
    Route::post('/inventory/{productId}/delete', [MitraInventoryController::class, 'mitraInventoryDelete'])->name('inventory.delete');
    Route::get('/orders', [MitraOrderController::class, 'mitraOrders'])->name('orders');
    Route::post('/orders/{orderId}/update-status', [MitraOrderController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::post('/orders/{orderId}/delay', [MitraOrderController::class, 'delayOrder'])->name('orders.delay');
    Route::post('/orders/{orderId}/confirm', [MitraOrderController::class, 'mitraOrdersConfirm'])->name('orders.confirm');
    Route::get('/reviews', [MitraOrderController::class, 'mitraReviews'])->name('reviews');
    Route::get('/history', [MitraOrderController::class, 'mitraHistory'])->name('history');
    Route::get('/donations', [MitraDonationController::class, 'mitraDonations'])->name('donations');
    Route::post('/donations', [MitraDonationController::class, 'mitraDonationStore'])->name('donations.store');
    Route::post('/donations/{donationId}/prepare', [MitraDonationController::class, 'mitraDonationPrepare'])->name('donations.prepare');
    Route::post('/donations/{donationId}/complete', [MitraDonationController::class, 'mitraDonationComplete'])->name('donations.complete');
    Route::post('/donations/{donationId}/cancel', [MitraDonationController::class, 'mitraDonationCancel'])->name('donations.cancel');
    Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

Route::prefix('lembaga')->name('lembaga.')->middleware(['role:lembaga', 'profile.complete'])->group(function () {
    Route::get('/', [LembagaDashboardController::class, 'lembagaDashboard'])->name('dashboard');
    Route::post('/upload-document', [ProfileController::class, 'uploadBusinessDocument'])->name('upload.document');
    Route::get('/donations', [LembagaDonationController::class, 'lembagaDonations'])->name('donations');
    Route::get('/history', [LembagaDonationController::class, 'lembagaHistory'])->name('history');
    Route::post('/donations/{donationId}/claim', [LembagaDonationController::class, 'lembagaClaimDonation'])->name('donations.claim');
    Route::post('/donations/{donationId}/complete', [LembagaDonationController::class, 'lembagaCompleteDonation'])->name('donations.complete');
    Route::post('/report', [LembagaReportController::class, 'lembagaSubmitProblemReport'])->name('report.submit');
    Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/verification', [AdminVerificationController::class, 'adminVerification'])->name('verification');
    Route::post('/verification/{applicationId}/approve', [AdminVerificationController::class, 'adminApproveApplication'])->name('verification.approve');
    Route::post('/verification/{applicationId}/reject', [AdminVerificationController::class, 'adminRejectApplication'])->name('verification.reject');
    Route::get('/users', [AdminUserController::class, 'adminUsers'])->name('users');
    Route::get('/reviews', [AdminTransactionController::class, 'adminReviews'])->name('reviews');
    Route::post('/users/{userId}/warn', [AdminUserController::class, 'adminWarnUser'])->name('users.warn');
    Route::post('/users/{userId}/block', [AdminUserController::class, 'adminBlockUser'])->name('users.block');
    Route::post('/users/{userId}/unblock', [AdminUserController::class, 'adminUnblockUser'])->name('users.unblock');
    Route::get('/education', [AdminEducationController::class, 'adminEducation'])->name('education');
    Route::post('/education', [AdminEducationController::class, 'adminEducationStore'])->name('education.store');
    Route::post('/education/{articleId}', [AdminEducationController::class, 'adminEducationUpdate'])->name('education.update');
    Route::post('/education/{articleId}/delete', [AdminEducationController::class, 'adminEducationDelete'])->name('education.delete');
    Route::get('/transactions', [AdminTransactionController::class, 'adminTransactions'])->name('transactions');
    Route::get('/transactions/export-csv', [AdminTransactionController::class, 'adminExportTransactionsCsv'])->name('transactions.export-csv');
    Route::get('/reports', [AdminDashboardController::class, 'adminReports'])->name('reports');
    Route::get('/reports/export-pdf', [AdminDashboardController::class, 'adminExportReportsPdf'])->name('reports.export-pdf');
    Route::get('/reports/export-excel', [AdminDashboardController::class, 'adminExportReportsExcel'])->name('reports.export-excel');
    
    // PBI #47 & #48: Moderation Reports
    Route::get('/problem-reports', [AdminProblemReportController::class, 'adminProblemReports'])->name('problem-reports.index');
    Route::post('/problem-reports/{report}/dismiss', [AdminProblemReportController::class, 'adminDismissReport'])->name('problem-reports.dismiss');
    Route::post('/problem-reports/{report}/warn', [AdminProblemReportController::class, 'adminWarnMitraReport'])->name('problem-reports.warn');
    Route::post('/problem-reports/{report}/block', [AdminProblemReportController::class, 'adminBlockMitraReport'])->name('problem-reports.block');
    Route::get('/logs', [AdminDashboardController::class, 'adminLogs'])->name('logs');
    Route::get('/feedbacks', [AdminFeedbackController::class, 'adminIndex'])->name('feedbacks.index');
    Route::delete('/feedbacks/{feedback}', [AdminFeedbackController::class, 'adminDelete'])->name('feedbacks.delete');
    Route::post('/feedbacks/{feedback}/toggle-status', [AdminFeedbackController::class, 'adminToggleStatus'])->name('feedbacks.toggle-status');
});

Route::get('/test-broadcast', function () {
    event(new \App\Events\TestBroadcast('Koneksi real-time berhasil!'));
    return 'Broadcast terkirim ke channel "test-channel"!';
});
