<?php

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentApplicationController;
use App\Http\Controllers\StudentApplicationDocumentController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/registration', [StudentApplicationController::class, 'create'])->name('registration.create');
Route::post('/registration', [StudentApplicationController::class, 'store'])->middleware('throttle:10,1')->name('registration.store');
Route::get('/registration/confirmation', [StudentApplicationController::class, 'confirmation'])->name('registration.confirmation');
Route::get('/admin/student-applications/{studentApplication}/document', StudentApplicationDocumentController::class)
    ->middleware('can:manage-settings')
    ->name('student-applications.document');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{folder:slug}', [GalleryController::class, 'show'])->name('gallery.show');
Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{folder:slug}', [VideoController::class, 'show'])->name('videos.show');
