<?php

use App\Http\Controllers\LogoutController;
use App\Models\Media;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('private/files/{uuid}', function ($uuid, Request $request) {
    $media = Media::findByUuid($uuid);

    abort_if(! $media, 404);

    return response()
        ->download($media->getPath(), $media->file_name, [
            'Content-Type' => $media->mime_type,
            'Content-Length' => $media->size,
        ]);
})->name('private.files');

Route::get('local/temp/{path}', function (string $path, Request $request) {
    abort_if(! $request->hasValidSignature(), 401);

    $storage = Storage::disk('local');

    abort_if(! $storage->exists($path), 404);

    return $storage->download($path);
})->where('path', '.*')->name('local.temp');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('livewirePageGroup.website.pages.home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::any('logout', LogoutController::class)->name('logout');
