<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use App\Models\AuthLog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Successful Login
        Event::listen(Login::class, function (Login $event) {
            AuthLog::create([
                'user_id'    => $event->user->id,
                'email'      => $event->user->email,
                'event'      => 'login_success',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard'      => $event->guard,
                'logged_at'  => now(),
            ]);
        });

        // Failed Login Attempt
        Event::listen(Failed::class, function (Failed $event) {
            AuthLog::create([
                'user_id'    => $event->user?->id,
                'email'      => $event->credentials['email'] ?? $event->credentials['username'] ?? 'Unknown',
                'event'      => 'login_failed',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard'      => $event->guard,
                'logged_at'  => now(),
                'metadata'   => ['reason' => 'Invalid credentials'],
            ]);
        });

        // Logout
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuthLog::create([
                    'user_id'    => $event->user->id,
                    'email'      => $event->user->email,
                    'event'      => 'logout',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'guard'      => $event->guard,
                    'logged_at'  => now(),
                ]);
            }
        });

        // Lockout (Too many failed attempts)
        Event::listen(Lockout::class, function (Lockout $event) {
            AuthLog::create([
                'email'      => $event->request->input('email', 'Unknown'),
                'event'      => 'lockout',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'logged_at'  => now(),
                'metadata'   => ['reason' => 'Rate limit exceeded'],
            ]);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
