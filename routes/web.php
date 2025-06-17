<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('messages.index');
});

Auth::routes();

Route::middleware('auth')->group(function () {
    // Main messages routes
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    
    // API routes for AJAX calls
    Route::prefix('api/messages')->group(function () {
        Route::get('/get/{user_id}', [MessageController::class, 'getMessages'])->name('api.messages.get');
        Route::post('/mark-read', [MessageController::class, 'markAsRead'])->name('api.messages.mark-read');
        Route::get('/unread-count', [MessageController::class, 'getUnreadCount'])->name('api.messages.unread-count');
        Route::delete('/delete', [MessageController::class, 'deleteMessage'])->name('api.messages.delete');
        Route::get('/search', [MessageController::class, 'searchMessages'])->name('api.messages.search');
        Route::get('/users-with-last-message', [MessageController::class, 'getUsersWithLastMessage'])->name('api.messages.users-with-last-message');
    });
    
    // Additional user routes (if needed)
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile.index');
    
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');
});

Route::get('/health', function () {
    return response('ok', 200);
});
