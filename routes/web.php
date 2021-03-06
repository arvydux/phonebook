<?php

use App\Http\Controllers\PhoneController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhoneNumberController;

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

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/', [PhoneNumberController::class, 'index']);
    Route::resource('phone-numbers', PhoneNumberController::class);
    Route::get('phone-numbers/{id}/share', [PhoneNumberController::class, 'showShare'])->name('phone-numbers.showShare');
    Route::patch('phone-numbers/{id}/make-share', [PhoneNumberController::class, 'makeShare'])->name('phone-numbers.makeShare');
    Route::post('photos/{id}/update', [PhoneNumberController::class, 'updatePersonPhoto'])->name('photos.update');
    Route::get('photos/{id}/delete', [PhoneNumberController::class, 'deletePersonPhoto'])->name('photos.delete');
    Route::resource('phone-numbers/{id}/phones', PhoneController::class);
});

Route::get('qr-code-g', function () {
    \QrCode::size(500)
        ->format('png')
        ->generate('ItSolutionStuff.com', public_path('images/qrcode.png'));
    return view('qrCode');
});

require __DIR__.'/auth.php';
