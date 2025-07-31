<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home.index');

Route::get('/resume/{name?}/{lang?}', [App\Http\Controllers\ResumeController::class, 'index'])->name('resume.index');

Route::get('/auth/redirect/{provider}', [App\Http\Controllers\AuthController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/callback/{provider}', [App\Http\Controllers\AuthController::class, 'callback'])->name('auth.callback');
Route::get('/auth/create-password', [App\Http\Controllers\AuthController::class, 'create_password'])->name('auth.create-password');
Route::post('/auth/create-password/update', [App\Http\Controllers\AuthController::class, 'create_password_update'])->name('auth.create-password.update');
Route::get('/auth/create-password/skip', [App\Http\Controllers\AuthController::class, 'create_password_skip'])->name('auth.create-password.skip');

Route::post('/loadMorePost', [App\Http\Controllers\HomeController::class, 'loadMorePost'])->name('home.loadMorePost');
Route::post('/send-message', [App\Http\Controllers\HomeController::class, 'sendMessage'])->name('home.sendMessage');

Route::get('/image-to-pdf', [App\Http\Controllers\ImageToPdfConverterController::class, 'index'])->name('image-to-pdf.index');
Route::post('/image-to-pdf/convert', [App\Http\Controllers\ImageToPdfConverterController::class, 'convert'])->name('image-to-pdf.convert');

Route::get('/tiktok-saver', [App\Http\Controllers\TikTokSaverController::class, 'index'])->name('tiktok-saver.index');
Route::post('/tiktok-saver/download', [App\Http\Controllers\TikTokSaverController::class, 'download'])->name('tiktok-saver.download');
Route::get('/tiktok-saver/stream', [App\Http\Controllers\TikTokSaverController::class, 'stream'])->name('tiktok-saver.stream');

Route::get('/chat-ai', [App\Http\Controllers\ChatAIController::class, 'index'])->name('chat-ai.index');
Route::post('/chat-ai/chat', [App\Http\Controllers\ChatAIController::class, 'chat'])->name('chat-ai.chat');
Route::delete('/chat-ai/clear-history', [App\Http\Controllers\ChatAIController::class, 'clearHistory'])->name('chat-ai.clearHistory');

Route::get('/word-to-pdf', [App\Http\Controllers\WordToPdfController::class, 'index'])->name('word-to-pdf.index');
Route::post('/word-to-pdf/convert', [App\Http\Controllers\WordToPdfController::class, 'convert'])->name('word-to-pdf.convert');

Route::get('/google-contact', [App\Http\Controllers\GoogleContactController::class, 'index'])->name('google-contact.index');
Route::get('/google-contact/redirect', [App\Http\Controllers\GoogleContactController::class, 'redirect'])->name('google-contact.redirect');
Route::get('/google-contact/callback', [App\Http\Controllers\GoogleContactController::class, 'callback'])->name('google-contact.callback');
Route::get('/google-contact/refresh', [App\Http\Controllers\GoogleContactController::class, 'refresh'])->name('google-contact.refresh');
Route::get('/google-contact/logout', [App\Http\Controllers\GoogleContactController::class, 'resetToken'])->name('google-contact.logout');
Route::post('/google-contact/bulk-update', [App\Http\Controllers\GoogleContactController::class, 'bulkUpdate'])->name('google-contact.bulk-update');
Route::get('/google-contact/backup', [App\Http\Controllers\GoogleContactController::class, 'backup'])->name('google-contact.backup');
