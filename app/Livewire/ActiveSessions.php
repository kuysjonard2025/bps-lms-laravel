<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

class ActiveSessions extends Component
{
    public bool $showLogoutOthersModal = false;

    public string $currentPassword = '';

    public string $search = '';

    public function updatedSearch(): void
    {
        // Reactive re-render updates filtered sessions
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     user_id: int,
     *     user_name: string,
     *     user_email: string,
     *     user_username: string,
     *     user_role: string,
     *     browser: string,
     *     os: string,
     *     ip_address: string,
     *     last_activity_human: string,
     *     is_current: bool,
     *     is_own_user: bool
     * }>
     */
    public function getSessionsProperty(): array
    {
        $authUser = Auth::user();
        if (! $authUser) {
            return [];
        }

        try {
            $currentSessionId = session()->getId();
            $lifetimeMinutes = (int) config('session.lifetime', 120);
            $cutoffTimestamp = Carbon::now()->subMinutes($lifetimeMinutes)->timestamp;
            $canViewAllUsers = $this->canManageAllSessions($authUser);

            $driver = DB::connection()->getDriverName();
            $operator = $driver === 'pgsql' ? 'ilike' : 'like';
            $term = '%' . mb_strtolower(trim($this->search)) . '%';

            $query = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->select([
                    'sessions.id',
                    'sessions.user_id',
                    'sessions.ip_address',
                    'sessions.user_agent',
                    'sessions.last_activity',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name',
                    'users.suffix',
                    'users.email',
                    'users.username',
                    'users.role',
                ])
                ->whereNotNull('sessions.user_id')
                ->where('sessions.last_activity', '>=', $cutoffTimestamp);

            if (! $canViewAllUsers) {
                $query->where('sessions.user_id', $authUser->id);
            }

            if (trim($this->search) !== '') {
                $query->where(function ($subQuery) use ($operator, $term) {
                    $subQuery->where('users.first_name', $operator, $term)
                        ->orWhere('users.last_name', $operator, $term)
                        ->orWhere('users.username', $operator, $term)
                        ->orWhere('users.email', $operator, $term)
                        ->orWhere('users.role', $operator, $term)
                        ->orWhere('sessions.ip_address', $operator, $term);
                });
            }

            $rows = $query
                ->orderByDesc('sessions.last_activity')
                ->get();

            return $rows->map(function (object $row) use ($currentSessionId, $authUser): array {
                $parsed = $this->parseUserAgent((string) ($row->user_agent ?? ''));
                $timestamp = (int) ($row->last_activity ?? 0);
                $rowUserId = (int) ($row->user_id ?? 0);
                $email = mb_strtolower(trim((string) ($row->email ?? '')));
                $username = mb_strtolower(trim((string) ($row->username ?? '')));
                $rawRole = mb_strtolower(trim((string) ($row->role ?? 'assistant')));

                return [
                    'id'                  => (string) $row->id,
                    'user_id'             => $rowUserId,
                    'user_name'           => $this->formatUserDisplayName($row, $username, $email, $rowUserId),
                    'user_email'          => $email,
                    'user_username'       => $username,
                    'user_role'           => $rawRole !== '' ? Str::title(str_replace(['_', '-'], ' ', $rawRole)) : 'Assistant',
                    'browser'             => $parsed['browser'],
                    'os'                  => $parsed['os'],
                    'ip_address'          => (string) ($row->ip_address ?: 'Unknown IP'),
                    'last_activity_human' => $timestamp > 0
                        ? Carbon::createFromTimestamp($timestamp)->diffForHumans()
                        : 'Unknown',
                    'is_current'          => hash_equals($currentSessionId, (string) $row->id),
                    'is_own_user'         => $rowUserId === (int) $authUser->id,
                ];
            })->all();
        } catch (Throwable $e) {
            Log::error('Failed loading active sessions: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $this->dispatch('toast', message: 'Unable to load active sessions right now.', type: 'error');

            return [];
        }
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
        ], [
            'currentPassword' => 'current password',
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

        try {
            $currentSessionId = session()->getId();
            $canManageAll = $this->canManageAllSessions($user);

            $query = DB::table('sessions')
                ->select(['id', 'user_id', 'ip_address', 'user_agent'])
                ->where('id', '!=', $currentSessionId);

            if (! $canManageAll) {
                $query->where('user_id', $user->id);
            }

            $otherSessions = $query->get();

            $deletedCount = DB::transaction(function () use ($otherSessions, $user): int {
                $count = 0;

                foreach ($otherSessions as $target) {
                    $deleted = DB::table('sessions')
                        ->where('id', $target->id)
                        ->delete();

                    if ($deleted > 0) {
                        $count++;
                        $this->recordSessionRevoked((int) $user->id, $target, 'logout_other_devices');
                    }
                }

                return $count;
            });

            $this->currentPassword = '';
            $this->showLogoutOthersModal = false;

            $this->dispatch(
                'toast',
                message: $deletedCount > 0
                    ? "Logged out {$deletedCount} other session(s)."
                    : 'No other active sessions to log out.',
                type: 'success'
            );
        } catch (Throwable $e) {
            Log::error('Failed logging out other sessions: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $this->dispatch('toast', message: 'Could not log out other sessions. Please try again.', type: 'error');
        }
    }

    public function revokeSession(string $sessionId): void
    {
        $user = Auth::user();
        if (! $user || $sessionId === '') {
            return;
        }

        $currentSessionId = session()->getId();
        if (hash_equals($currentSessionId, $sessionId)) {
            $this->dispatch('toast', message: 'You cannot log out your current session here.', type: 'error');

            return;
        }

        try {
            $canManageAll = $this->canManageAllSessions($user);

            $targetQuery = DB::table('sessions')
                ->select(['id', 'user_id', 'ip_address', 'user_agent'])
                ->where('id', $sessionId);

            if (! $canManageAll) {
                $targetQuery->where('user_id', $user->id);
            }

            $target = $targetQuery->first();

            if (! $target) {
                $this->dispatch('toast', message: 'Session not found or not permitted.', type: 'error');

                return;
            }

            $deleted = DB::transaction(function () use ($sessionId, $user, $target): int {
                $count = DB::table('sessions')
                    ->where('id', $sessionId)
                    ->delete();

                if ($count > 0) {
                    $this->recordSessionRevoked((int) $user->id, $target, 'single_session');
                }

                return $count;
            });

            if ($deleted > 0) {
                $this->dispatch('toast', message: 'Session logged out successfully.', type: 'success');
            } else {
                $this->dispatch('toast', message: 'Session not found or already expired.', type: 'error');
            }
        } catch (Throwable $e) {
            Log::error('Failed revoking session: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            $this->dispatch('toast', message: 'Unable to revoke session right now.', type: 'error');
        }
    }

    private function canManageAllSessions(object $user): bool
    {
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'super_admin', 'librarian'])) {
            return true;
        }

        $role = mb_strtolower(trim((string) ($user->role ?? '')));

        return in_array($role, ['admin', 'super_admin', 'librarian'], true);
    }

    private function formatUserDisplayName(object $row, string $username, string $email, int $userId): string
    {
        $parts = array_filter([
            trim((string) ($row->first_name ?? '')),
            trim((string) ($row->middle_name ?? '')),
            trim((string) ($row->last_name ?? '')),
            trim((string) ($row->suffix ?? '')),
        ]);

        if ($parts !== []) {
            return Str::title(implode(' ', $parts));
        }

        if ($username !== '') {
            return $username;
        }

        if ($email !== '') {
            return $email;
        }

        return 'User #' . $userId;
    }

    private function recordSessionRevoked(int $actorId, object $targetSession, string $mode): void
    {
        $parsed = $this->parseUserAgent((string) ($targetSession->user_agent ?? ''));
        $ipAddress = (string) ($targetSession->ip_address ?: 'Unknown IP');
        $targetUserId = (int) ($targetSession->user_id ?? $actorId);
        $timestamp = now();
        $modeLabel = $mode === 'logout_other_devices' ? 'logout all other devices' : 'single session logout';
        $description = "Revoked session for user #{$targetUserId} ({$parsed['browser']} on {$parsed['os']}, IP: {$ipAddress}) via {$modeLabel} at {$timestamp->toDateTimeString()}.";

        ActivityLog::create([
            'user_id'     => $actorId,
            'log_name'    => 'active_sessions',
            'event'       => 'deleted',
            'description' => $description,
            'ip_address'  => $ipAddress,
            'created_at'  => $timestamp,
            'updated_at'  => $timestamp,
        ]);
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
