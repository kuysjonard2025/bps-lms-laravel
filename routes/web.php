<?php

use App\Http\Middleware\EnsureKioskAuthenticated;
use App\Livewire\AcademicInfo;
use App\Livewire\Accessions;
use App\Livewire\Acquisitions;
use App\Livewire\AssetDetails\Index as AssetDetails;
use App\Livewire\Auth\CompleteProfile;
use App\Livewire\Auth\Login as LoginComponent;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\AuthenticationLogs;
use App\Livewire\Catalogs;
use App\Livewire\Circulations;
use App\Livewire\CirculationPolicy;
use App\Livewire\Dashboard;
use App\Livewire\DatabaseBackups;
use App\Livewire\InventoryManagement;
use App\Livewire\Kiosk\KioskLogin;
use App\Livewire\Kiosk\PatronKiosk;
use App\Livewire\PatronAuth\Login as PatronLogin;
use App\Livewire\PatronLogs;
use App\Livewire\PatronPortal;
use App\Livewire\PatronRecords;
use App\Livewire\Registrations;
use App\Livewire\UserActivityLogs;
use App\Livewire\Vendors;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', LoginComponent::class)->name('login');
    Route::redirect('/login', '/');
});

// Patron Portal Routes (Session-based via Patron ID)
Route::prefix('patron')->name('patron.')->group(function () {
    Route::get('/login', PatronLogin::class)->name('login');
    Route::get('/portal', PatronPortal::class)->name('portal');
});

// Dedicated Standalone Kiosk Routes (PIN-Protected Session)
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/login', KioskLogin::class)->name('login');

    Route::middleware(EnsureKioskAuthenticated::class)->group(function () {
        Route::get('/patron-log', PatronKiosk::class)->name('patron-log');
    });
});

// Authenticated Routes (Staff / Admins)
Route::middleware('auth')->group(function () {

    // 1. Force Profile Setup route (Unified route name)
    Route::get('/complete-profile', CompleteProfile::class)->name('complete-profile');

    // 2. Email Verification Notice Route (Livewire Component)
    Route::get('/email/verify', VerifyEmail::class)->name('verification.notice');

    // 3. Email Verification Action Handler (Clicked from email link)
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        // Refresh user instance in session state
        $user = $request->user();
        if ($user) {
            $user->refresh();
        }

        // Redirect to dashboard with status
        return redirect()->route('dashboard')->with('status', 'Your email address has been verified successfully!');
    })->middleware('signed')->name('verification.verify');

    // 4. Protected App Routes (Requires Completed Profile + Verified Email)
    Route::middleware(['profile.completed', 'verified'])->group(function () {
        // Core Pages
        Route::get('/dashboard', Dashboard::class)->name('dashboard');

        // Maintenance Pages
        Route::get('/asset-details', AssetDetails::class)->name('asset-details');
        Route::get('/catalogs', Catalogs::class)->name('catalogs');
        Route::get('/vendors', Vendors::class)->name('vendors');
        Route::get('/accessions', Accessions::class)->name('accessions');
        Route::get('/academic-info', AcademicInfo::class)->name('academic-info');
        Route::get('/registrations', Registrations::class)->name('registrations');
        Route::get('/circulation-policy', CirculationPolicy::class)->name('circulation-policy');

        // Process Pages
        Route::get('/acquisitions', Acquisitions::class)->name('acquisitions');
        Route::get('/patron-logs', PatronLogs::class)->name('patron-logs');
        Route::get('/circulations', Circulations::class)->name('circulations');
        Route::get('/patron-records', PatronRecords::class)->name('patron-records');
        Route::get('/inventory-management', InventoryManagement::class)->name('inventory-management');

        // System Logs & Backup
        Route::get('/authentication-logs', AuthenticationLogs::class)->name('authentication-logs');
        Route::get('/user-activity-logs', UserActivityLogs::class)->name('user-activity-logs');
        Route::get('/database-backups', DatabaseBackups::class)->name('database-backups');
    });

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
