<?php

use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\TwitterController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Photos\BulkPhotoItemsController;
use App\Http\Controllers\Photos\CopyPhotoItemController;
use App\Http\Controllers\Photos\ExportPhotosController;
use App\Http\Controllers\Photos\PhotoItemsController;
use App\Http\Controllers\Photos\PhotoItemTagsController;
use App\Http\Controllers\Photos\PhotosController;
use App\Http\Controllers\Photos\UploadPhotosController;
use App\Http\Controllers\TagShortcuts\ApplyTagShortcutController;
use App\Http\Controllers\TagShortcuts\CopyTagShortcutController;
use App\Http\Controllers\TagShortcuts\CopyTagShortcutItemController;
use App\Http\Controllers\TagShortcuts\TagShortcutItemsController;
use App\Http\Controllers\TagShortcuts\TagShortcutItemTagsController;
use App\Http\Controllers\TagShortcuts\TagShortcutsController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/docs/en/', DocsController::class)->name('docs');
Route::get('/events', EventsController::class)->name('events');

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
Route::get('/auth/github/redirect', [GitHubController::class, 'redirect'])->name('auth.github.redirect');
Route::get('/auth/github/callback', [GitHubController::class, 'callback']);
Route::get('/auth/twitter/redirect', [TwitterController::class, 'redirect'])->name('auth.twitter.redirect');
Route::get('/auth/twitter/callback', [TwitterController::class, 'callback']);

Route::middleware(['auth', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::impersonate();
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/upload', [UploadPhotosController::class, 'show'])->name('upload');
    Route::post('/upload', [UploadPhotosController::class, 'store']);

    Route::post('/photos/items', [BulkPhotoItemsController::class, 'store'])->name('bulk-photo-items.store');
    Route::delete('/photos/items', [BulkPhotoItemsController::class, 'destroy'])->name('bulk-photo-items.destroy');

    Route::get('/my-photos', [PhotosController::class, 'index'])->name('my-photos');
    Route::get('/photos/export', ExportPhotosController::class)->name('photos.export');
    Route::get('/photos/{photo}', [PhotosController::class, 'show'])->name('photos.show');
    Route::delete('/photos/{photo}', [PhotosController::class, 'destroy'])->name('photos.destroy');

    Route::post('/photos/{photo}/tag-shortcuts/{tagShortcut}', ApplyTagShortcutController::class);
    Route::post('/photos/{photo}/items', [PhotoItemsController::class, 'store']);
    Route::post('/photo-items/{photoItem}', [PhotoItemsController::class, 'update']);
    Route::delete('/photo-items/{photoItem}', [PhotoItemsController::class, 'destroy']);
    Route::post('/photo-items/{photoItem}/tags', [PhotoItemTagsController::class, 'store']);
    Route::delete('/photo-items/{photoItem}/tags/{tag}', [PhotoItemTagsController::class, 'destroy']);

    Route::post('/photo-items/{photoItem}/copy', CopyPhotoItemController::class);

    Route::post('/settings', [UserSettingsController::class, 'update'])->name('user-settings.update');

    Route::get('/user/tag-shortcuts', [TagShortcutsController::class, 'index'])->name('tag-shortcuts.index');
    Route::post('/user/tag-shortcuts', [TagShortcutsController::class, 'store'])->name('tag-shortcuts.store');
    Route::get('/user/tag-shortcuts/{tagShortcut}', [TagShortcutsController::class, 'show'])->name('tag-shortcuts.show');
    Route::post('/user/tag-shortcuts/{tagShortcut}', [TagShortcutsController::class, 'update'])->name('tag-shortcuts.update');
    Route::delete('/user/tag-shortcuts/{tagShortcut}', [TagShortcutsController::class, 'destroy'])->name('tag-shortcuts.destroy');
    Route::post('/user/tag-shortcuts/{tagShortcut}/copy', CopyTagShortcutController::class)->name('tag-shortcuts.copy');
    Route::post('/user/tag-shortcuts/{tagShortcut}/items', [TagShortcutItemsController::class, 'store'])->name('tag-shortcut-items.store');
    Route::post('/user/tag-shortcut-items/{tagShortcutItem}', [TagShortcutItemsController::class, 'update'])->name('tag-shortcut-items.update');
    Route::delete('/user/tag-shortcut-items/{tagShortcutItem}', [TagShortcutItemsController::class, 'destroy'])->name('tag-shortcut-items.destroy');
    Route::post('/user/tag-shortcut-items/{tagShortcutItem}/tags', [TagShortcutItemTagsController::class, 'store'])->name('tag-shortcut-item-tags.store');
    Route::delete('/user/tag-shortcut-items/{tagShortcutItem}/tags/{tag}', [TagShortcutItemTagsController::class, 'destroy'])->name('tag-shortcut-item-tags.destroy');
    Route::post('/user/tag-shortcut-items/{tagShortcutItem}/copy', CopyTagShortcutItemController::class)->name('tag-shortcut-items.copy');
});
