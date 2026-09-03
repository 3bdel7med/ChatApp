<?php

use App\Http\Controllers\CallController;
use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Chat\UserSearchController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use OpenAI\Laravel\Facades\OpenAI;




// Test AI Route
Route::get('/test-ai', function () {
    $result = OpenAI::chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'user', 'content' => 'قل لي جملة ترحيبية قصيرة'],
        ],
    ]);

    return $result->choices[0]->message->content;
});

// Dashboard Route
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Auth Routes (Login, Register, etc.)
require __DIR__.'/auth.php';

// Protected Application Routes
Route::middleware(['auth'])->group(function () {

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/user/{user}', [UserController::class, 'show'])->name('users.show');

    // AI Simulation Route
    Route::post('/simulation/start', [SimulationController::class, 'start'])->name('simulation.start');

    // Main Chat & Messaging Routes
Route::get('/', [ConversationController::class, 'index'])->name('chat.index');
    Route::post('/chat/start', [ConversationController::class, 'storeDirect'])->name('chat.start');
    Route::post('/chat/group', [ConversationController::class, 'storeGroup'])->name('chat.group.create');
    Route::get('/chat/{conversation}', [ConversationController::class, 'show'])->name('chat.show');

    // Message management
    Route::post('/chat/{conversation}/messages', [MessageController::class, 'store'])->name('chat.messages.store');

    // User search & group creation lists
    Route::get('/chat/users/search', [UserSearchController::class, 'search'])->name('chat.users.search');
    Route::get('/chat/users/list', [UserSearchController::class, 'listForGroup'])->name('chat.users.list');
    // Unified WebRTC Call Signaling Routes
    Route::post('/conversations/{conversation}/signal', [CallController::class, 'signal'])->name('conversations.signal');

    Route::post('/chat/{conversation}/call/signal', [CallController::class, 'signal'])->name('chat.call.signal');
    Route::post('/chat/signal', [ChatController::class, 'sendSignal'])->name('chat.signal');

    // Notifications Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});
