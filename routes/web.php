<?php

use App\Http\Controllers\PhotoItemPickedUpController;
use App\Http\Controllers\PhotoItemsController;
use App\Http\Controllers\PhotoItemTagsController;
use App\Http\Controllers\PhotosController;
use App\Http\Controllers\PhotoTagsController;
use App\Http\Controllers\UploadPhotosController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/upload', function () {
        return Inertia::render('Upload');
    })->name('upload');

    Route::get('/docs/en/', function () {
        return Inertia::render('Docs');
    })->name('docs');

    Route::get('/my-photos', [PhotosController::class, 'index'])->name('my-photos');
    Route::get('/photos/{photo}', [PhotosController::class, 'show'])->name('photos.show');

    Route::post('/photos/{photo}/tags', [PhotoTagsController::class, 'store']);
    Route::delete('/photos/{photo}/tags/{tag}', [PhotoTagsController::class, 'destroy']);

    Route::post('/photos/{photo}/items', [PhotoItemsController::class, 'store']);
    Route::delete('/photo-items/{photoItem}', [PhotoItemsController::class, 'destroy']);
    Route::post('/photo-items/{photoItem}/tags', [PhotoItemTagsController::class, 'store']);
    Route::delete('/photo-items/{photoItem}/tags/{tag}', [PhotoItemTagsController::class, 'destroy']);

    Route::post('/photo-items/{photoItem}/picked-up', [PhotoItemPickedUpController::class, 'store']);

    Route::post('/upload', [UploadPhotosController::class, 'store']);

    Route::post('/settings', [UserSettingsController::class, 'update'])->name('user-settings.update');
});
