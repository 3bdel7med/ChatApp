<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Middleware;


/*
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});*/

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});
Route::get('/user/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show')->middleware('auth');

require __DIR__.'/auth.php';
Route::middleware('auth')->group(function(){
    Route::get('/', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/start', [ChatController::class, 'getOrCreateConversation'])->name('chat.start');
    Route::post('/chat/group', [ChatController::class, 'createGroup'])->name('chat.group.create');
    Route::get('/chat/{conversation}', [ChatController::class, 'showConversation'])->name('chat.show');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'storeMessage'])->name('chat.messages.store');
    Route::post('/chat/{conversation}/call/signal', [\App\Http\Controllers\CallController::class, 'signal'])->name('chat.call.signal');
    Route::get('/chat/users/search', [SearchController::class, 'searchUsers'])->name('chat.users.search');
    Route::get('/chat/users/list', [ChatController::class, 'getUsersForGroup'])->name('chat.users.list');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});
