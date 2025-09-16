<?php

use App\Http\Controllers\Admin\KnowledgeBaseController as AdminKnowledgeBaseController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\AIAnswerController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostmarkWebhookController;
use App\Http\Controllers\StatusController;
use App\LocalAi\Chat;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => Inertia::render('Welcome'))->name('home');

Route::prefix('knowledge-base')->as('knowledge-base.')->group(function () {
    Route::get('/', [KnowledgeBaseController::class, 'index'])->name('index');
    Route::get('/{slug}', [KnowledgeBaseController::class, 'show'])->name('show');
});

// Postmark webhook for inbound emails (no auth required)
Route::post('/webhooks/postmark/inbound', [PostmarkWebhookController::class, 'handleInbound'])
    ->name('postmark.webhook.inbound');

// Local AI development
Route::get('/pchat', function (Chat $chat) {
    $messages = [
        ['role' => 'user', 'content' => 'Always answer in top poetic rhymes. Today is Rhyme Thursday.'],
        ['role' => 'assistant', 'content' => 'Sure, I can rhyme for you!'],
        ['role' => 'system', 'content' => 'Just poetic rhymes, no introduction.'],
    ];

    $pchat = $chat->send($messages);

    return Inertia::render('Dashboard', ['pchat' => $pchat]);
})->name('pchat')->middleware(['auth:web', 'verified']);

Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    // Helpdesk routes
    Route::prefix('helpdesk')->as('helpdesk.')->middleware('permission:view-helpdesk')->group(function () {
        Route::get('/', [ConversationController::class, 'index'])->name('index');
        Route::get('/{conversation}', [ConversationController::class, 'index'])->name('show');
        Route::post('/{conversation}/messages', [MessageController::class, 'store'])
            ->name('messages.store')
            ->middleware('permission:manage-helpdesk');
        Route::patch('/{conversation}/status', [StatusController::class, 'update'])
            ->name('status.update')
            ->middleware('permission:manage-helpdesk');
        Route::post('/conversations/{conversation}/read', [ConversationController::class, 'markAsRead'])
            ->name('conversations.read')
            ->middleware('permission:manage-helpdesk');
        Route::post('/conversations/{conversation}/unread', [ConversationController::class, 'markAsUnread'])
            ->name('conversations.unread')
            ->middleware('permission:manage-helpdesk');
        Route::post('/conversations/{conversation}/assign', [ConversationController::class, 'assign'])
            ->name('conversations.assign')
            ->middleware('permission:manage-helpdesk');
    });

    // Admin Knowledge Base routes (requires manage-knowledge-base permission)
    Route::prefix('admin/knowledge-base')->as('admin.knowledge-base.')->middleware('permission:manage-knowledge-base')->group(function () {
        // Article management
        Route::get('/', [AdminKnowledgeBaseController::class, 'index'])->name('index');
        Route::get('/create', [AdminKnowledgeBaseController::class, 'create'])->name('create');
        Route::post('/', [AdminKnowledgeBaseController::class, 'store'])->name('store');
        Route::get('/{article}', [AdminKnowledgeBaseController::class, 'show'])->name('show');
        Route::get('/{article}/edit', [AdminKnowledgeBaseController::class, 'edit'])->name('edit');
        Route::put('/{article}', [AdminKnowledgeBaseController::class, 'update'])->name('update');
        Route::delete('/{article}', [AdminKnowledgeBaseController::class, 'destroy'])->name('destroy');
        // Restore and force delete for soft-deleted articles
        Route::post('/{id}/restore', [AdminKnowledgeBaseController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [AdminKnowledgeBaseController::class, 'forceDelete'])->name('force-delete');
        // File upload endpoints for TipTap editor
        Route::post('/upload-image', [AdminKnowledgeBaseController::class, 'uploadImage'])->name('upload-image');
        Route::post('/upload-file', [AdminKnowledgeBaseController::class, 'uploadFile'])->name('upload-file');
    });

    // Admin Tag Management routes (requires manage-knowledge-base permission)
    Route::prefix('admin/tags')->as('admin.tags.')->middleware('permission:manage-tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('index');
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::put('/{tag}', [TagController::class, 'update'])->name('update');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
        Route::get('/list', [TagController::class, 'list'])->name('list');
    });

    // AI Answer Generation routes
    Route::prefix('ai')->as('ai.')->middleware('permission:generate-ai-answers')->group(function () {
        Route::post('/answer', [AIAnswerController::class, 'generate'])->name('answer.generate');
        Route::post('/sources', [AIAnswerController::class, 'sources'])->name('sources');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
