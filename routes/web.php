<?php

use App\Http\Controllers\PhotosController;
use App\Http\Controllers\PhotoTagsController;
use App\Http\Controllers\UploadPhotosController;
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

    Route::get('/my-photos', function () {
        return Inertia::render('Photos');
    })->name('my-photos');

    Route::get('/photos', [PhotosController::class, 'index']);
    Route::get('/photos/{photo}', [PhotosController::class, 'show']);

    Route::post('/photos/{photo}/tags', [PhotoTagsController::class, 'store']);
    Route::delete('/photos/{photo}/tags/{tag}', [PhotoTagsController::class, 'destroy']);

    Route::post('/upload', [UploadPhotosController::class, 'store']);
});
