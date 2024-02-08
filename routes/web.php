<?php

use App\Http\Controllers\CopyPhotoItemController;
use App\Http\Controllers\PhotoItemsController;
use App\Http\Controllers\PhotoItemTagsController;
use App\Http\Controllers\PhotosController;
use App\Http\Controllers\PhotoTagsController;
use App\Http\Controllers\UploadPhotosController;
use App\Http\Controllers\UserSettingsController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

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

Route::get('/auth/github/redirect', function () {
    return Socialite::driver('github')
        ->scopes(['read:user'])
        ->redirect();
});

Route::get('/auth/github/callback', function () {
    $githubUser = Socialite::driver('github')->user();

    $user = User::updateOrCreate([
        'email' => $githubUser->getEmail(),
    ], [
        'name' => $githubUser->getName(),
        'email_verified_at' => now(),
        'password' => Hash::make(Str::random(20)),
        'profile_photo_path' => $githubUser->getAvatar(),
    ]);

    Auth::login($user);

    return redirect('/dashboard');
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
    Route::delete('/photos/{photo}', [PhotosController::class, 'destroy'])->name('photos.destroy');

    Route::post('/photos/{photo}/tags', [PhotoTagsController::class, 'store']);
    Route::delete('/photos/{photo}/tags/{tag}', [PhotoTagsController::class, 'destroy']);

    Route::post('/photos/{photo}/items', [PhotoItemsController::class, 'store']);
    Route::post('/photo-items/{photoItem}', [PhotoItemsController::class, 'update']);
    Route::delete('/photo-items/{photoItem}', [PhotoItemsController::class, 'destroy']);
    Route::post('/photo-items/{photoItem}/tags', [PhotoItemTagsController::class, 'store']);
    Route::delete('/photo-items/{photoItem}/tags/{tag}', [PhotoItemTagsController::class, 'destroy']);

    Route::post('/photo-items/{photoItem}/copy', CopyPhotoItemController::class);

    Route::post('/upload', [UploadPhotosController::class, 'store']);

    Route::post('/settings', [UserSettingsController::class, 'update'])->name('user-settings.update');
});
