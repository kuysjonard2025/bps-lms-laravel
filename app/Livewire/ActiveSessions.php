<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

class ActiveSessions extends Component
{
    public bool $showLogoutOthersModal = false;

    public string $currentPassword = '';

    /**
     * @return array<int, array{
     *     id: string,
     *     browser: string,
     *     os: string,
     *     ip_address: string,
     *     last_activity_human: string,
     *     is_current: bool
     * }>
     */
    public function getSessionsProperty(): array
    {
        $userId = Auth::id();
        if (! $userId) {
            return [];
        }

        $currentSessionId = session()->getId();

        $rows = DB::table('sessions')
            ->select(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get();

        return $rows->map(function (object $row) use ($currentSessionId): array {
            $parsed = $this->parseUserAgent((string) ($row->user_agent ?? ''));
            $timestamp = (int) ($row->last_activity ?? 0);

            return [
                'id'                  => (string) $row->id,
                'browser'             => $parsed['browser'],
                'os'                  => $parsed['os'],
                'ip_address'          => (string) ($row->ip_address ?: 'Unknown IP'),
                'last_activity_human' => $timestamp > 0
                    ? Carbon::createFromTimestamp($timestamp)->diffForHumans()
                    : 'Unknown',
                'is_current'          => hash_equals($currentSessionId, (string) $row->id),
            ];
        })->all();
    }

    public function openLogoutOthersModal(): void
    {
        $this->resetValidation();
        $this->currentPassword = '';
        $this->showLogoutOthersModal = true;
    }

    public function closeLogoutOthersModal(): void
    {
        $this->resetValidation();
        $this->currentPassword = '';
        $this->showLogoutOthersModal = false;
    }

    public function logoutOtherDevices(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'string'],
        ], [
            'currentPassword.required' => 'Please enter your current password to continue.',
        ]);

        $user = Auth::user();
        if (! $user) {
            return;
        }

        if (! Hash::check($this->currentPassword, (string) $user->password)) {
            $this->addError('currentPassword', 'The password provided is incorrect.');
            $this->dispatch('toast', message: 'Incorrect password. Other sessions were not logged out.', type: 'error');

            return;
        }

        $currentSessionId = session()->getId();

        $otherSessions = DB::table('sessions')
            ->select(['id', 'ip_address', 'user_agent'])
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->get();

        $deletedCount = 0;

        foreach ($otherSessions as $target) {
            $deleted = DB::table('sessions')
                ->where('id', $target->id)
                ->where('user_id', $user->id)
                ->delete();

            if ($deleted > 0) {
                $deletedCount++;
                $this->recordSessionRevoked((int) $user->id, $target, 'logout_other_devices');
            }
        }

        $this->currentPassword = '';
        $this->showLogoutOthersModal = false;

        $this->dispatch(
            'toast',
            message: $deletedCount > 0
                ? "Logged out {$deletedCount} other session(s)."
                : 'No other active sessions to log out.',
            type: 'success'
        );
    }

    public function revokeSession(string $sessionId): void
    {
        $userId = Auth::id();
        if (! $userId || $sessionId === '') {
            return;
        }

        $currentSessionId = session()->getId();
        if (hash_equals($currentSessionId, $sessionId)) {
            $this->dispatch('toast', message: 'You cannot log out your current session here.', type: 'error');

            return;
        }

        $target = DB::table('sessions')
            ->select(['id', 'ip_address', 'user_agent'])
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->first();

        if (! $target) {
            $this->dispatch('toast', message: 'Session not found or does not belong to your account.', type: 'error');

            return;
        }

        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted > 0) {
            $this->recordSessionRevoked((int) $userId, $target, 'single_session');
            $this->dispatch('toast', message: 'Session logged out successfully.', type: 'success');
        } else {
            $this->dispatch('toast', message: 'Session not found or does not belong to your account.', type: 'error');
        }
    }

    private function recordSessionRevoked(int $actorId, object $targetSession, string $mode): void
    {
        $parsed = $this->parseUserAgent((string) ($targetSession->user_agent ?? ''));
        $ipAddress = (string) ($targetSession->ip_address ?: 'Unknown IP');
        $timestamp = now();
        $modeLabel = $mode === 'logout_other_devices' ? 'logout all other devices' : 'single session logout';
        $description = "Revoked session ({$parsed['browser']} on {$parsed['os']}, IP: {$ipAddress}) via {$modeLabel} at {$timestamp->toDateTimeString()}.";

        try {
            ActivityLog::create([
                'user_id'     => $actorId,
                'log_name'    => 'active_sessions',
                'event'       => 'deleted',
                'description' => $description,
                'ip_address'  => $ipAddress,
                'created_at'  => $timestamp,
                'updated_at'  => $timestamp,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to record session revocation in ActivityLog: ' . $e->getMessage());
        }
    }

    /**
     * @return array{browser: string, os: string}
     */
    private function parseUserAgent(string $userAgent): array
    {
        if ($userAgent === '') {
            return [
                'browser' => 'Unknown Browser',
                'os'      => 'Unknown OS',
            ];
        }

        $os = match (true) {
            str_contains($userAgent, 'Windows NT 10.0') || str_contains($userAgent, 'Windows NT 11.0') => 'Windows',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || str_contains($userAgent, 'iOS') => 'iOS / iPadOS',
            str_contains($userAgent, 'Mac OS X') || str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown OS',
        };

        $browser = match (true) {
            str_contains($userAgent, 'Edg/') || str_contains($userAgent, 'Edge/') => 'Microsoft Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome/') || str_contains($userAgent, 'CriOS/') => 'Google Chrome',
            str_contains($userAgent, 'Firefox/') || str_contains($userAgent, 'FxiOS/') => 'Mozilla Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome') => 'Safari',
            default => 'Unknown Browser',
        };

        return [
            'browser' => $browser,
            'os'      => $os,
        ];
    }

    #[Layout('components.layouts.app')]
    #[Title('Active Sessions')]
    public function render(): View
    {
        return view('livewire.active-sessions', [
            'sessions' => $this->sessions,
        ]);
    }
}
