<?php

use App\Livewire\AssetDetails\Index as AssetDetailsIndex;
use App\Livewire\Auth\CompleteProfile;
use App\Livewire\Auth\Login as LoginComponent;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Dashboard;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', LoginComponent::class)->name('login');
    Route::redirect('/login', '/');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // 1. Force Profile Setup first
    Route::get('/complete-profile', CompleteProfile::class)->name('profile.complete');

    // 2. Email Verification Notice Route (Livewire Component)
    Route::get('/email/verify', VerifyEmail::class)->name('verification.notice');

    // 3. Email Verification Action Handler (Clicked from email)
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard');
    })->middleware('signed')->name('verification.verify');

    // 4. Protected App Routes (Requires Completed Profile + Verified Email)
    Route::middleware(['profile.completed', 'verified'])->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');

        // Asset Details & References Route
        Route::get('/asset-details', AssetDetailsIndex::class)->name('asset-details');
    });

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
