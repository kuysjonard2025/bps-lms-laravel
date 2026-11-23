<?php // app/Livewire/AuthenticationLogs.php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\AuthLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AuthenticationLogs extends Component
{
    use WithPagination, SanitizesInputs;

    public string $search = '';
    public string $eventFilter = 'all';
    public string $dateRange = 'all';

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    private function parseDeviceBrowserOs(?string $userAgent): string
    {
        $ua = strtolower((string) $userAgent);

        if ($ua === '') {
            return 'Unknown';
        }

        $browser = match (true) {
            str_contains($ua, 'edg/') || str_contains($ua, 'edge/') => 'Edge',
            str_contains($ua, 'opr/') || str_contains($ua, 'opera') => 'Opera',
            str_contains($ua, 'firefox') || str_contains($ua, 'fxios') => 'Firefox',
            str_contains($ua, 'crios') || str_contains($ua, 'chrome') || str_contains($ua, 'chromium') => 'Chrome',
            str_contains($ua, 'safari') && ! str_contains($ua, 'chrome') && ! str_contains($ua, 'chromium') => 'Safari',
            default => 'Browser',
        };

        $os = match (true) {
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod') => 'iOS',
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'macintosh') || str_contains($ua, 'mac os x') => 'macOS',
            str_contains($ua, 'cros') => 'ChromeOS',
            str_contains($ua, 'linux') => 'Linux',
            default => 'Other OS',
        };

        return "{$browser} ({$os})";
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        if (mb_strlen($this->search) > 100) {
            $this->search = mb_substr($this->search, 0, 100);
        }
        $this->resetPage();
    }

    public function updatedEventFilter(): void
    {
        if (! in_array($this->eventFilter, ['all', 'login_success', 'login_failed', 'logout', 'lockout'], true)) {
            $this->eventFilter = 'all';
        }
        $this->resetPage();
    }

    public function updatedDateRange(): void
    {
        if (! in_array($this->dateRange, ['all', 'today', '7_days', '30_days'], true)) {
            $this->dateRange = 'all';
        }
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->eventFilter = 'all';
        $this->dateRange = 'all';
        $this->resetErrorBag();
        $this->resetPage();
    }

    private function validateFilters(): void
    {
        $rules = [
            'search'      => ['nullable', 'string', 'max:100'],
            'eventFilter' => ['required', 'string', 'in:all,login_success,login_failed,logout,lockout'],
            'dateRange'   => ['required', 'string', 'in:all,today,7_days,30_days'],
        ];

        $messages = [
            'search.max'     => 'Search query cannot exceed 100 characters.',
            'eventFilter.in' => 'Selected event filter is invalid.',
            'dateRange.in'   => 'Selected date range filter is invalid.',
        ];

        $attributes = [
            'search'      => 'Search Filter',
            'eventFilter' => 'Event Type Filter',
            'dateRange'   => 'Date Range Filter',
        ];

        $this->validate($rules, $messages, $attributes);
    }

    private function getFilteredLogsQuery(): Builder
    {
        $likeOperator = $this->likeOperator();
        $term = '%' . addcslashes(trim($this->search), '%_\\') . '%';

        return AuthLog::query()
            ->with('user')
            ->when($this->eventFilter !== 'all', fn (Builder $q) => $q->where('event', $this->eventFilter))
            ->when($this->dateRange !== 'all', function (Builder $q): void {
                match ($this->dateRange) {
                    'today'   => $q->whereDate('logged_at', today()),
                    '7_days'  => $q->where('logged_at', '>=', now()->subDays(7)),
                    '30_days' => $q->where('logged_at', '>=', now()->subDays(30)),
                    default   => null,
                };
            })
            ->when(trim($this->search) !== '', function (Builder $q) use ($likeOperator, $term): void {
                $q->where(function (Builder $sub) use ($likeOperator, $term): void {
                    $sub->where('email', $likeOperator, $term)
                        ->orWhere('ip_address', $likeOperator, $term)
                        ->orWhereHas('user', function (Builder $u) use ($likeOperator, $term): void {
                            $u->where('name', $likeOperator, $term)
                                ->orWhere('first_name', $likeOperator, $term)
                                ->orWhere('last_name', $likeOperator, $term);
                        });
                });
            })
            ->orderByDesc('logged_at');
    }

    private function getStats(): array
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            $row = AuthLog::query()
                ->selectRaw('count(*) as total_logs')
                ->selectRaw("count(*) filter (where event = 'login_success') as login_success")
                ->selectRaw("count(*) filter (where event = 'login_failed') as login_failed")
                ->selectRaw("count(*) filter (where event = 'lockout') as lockouts")
                ->first();

            return [
                'total_logs'    => (int) ($row?->total_logs ?? 0),
                'login_success' => (int) ($row?->login_success ?? 0),
                'login_failed'  => (int) ($row?->login_failed ?? 0),
                'lockouts'      => (int) ($row?->lockouts ?? 0),
            ];
        }

        return [
            'total_logs'    => AuthLog::query()->count(),
            'login_success' => AuthLog::query()->where('event', 'login_success')->count(),
            'login_failed'  => AuthLog::query()->where('event', 'login_failed')->count(),
            'lockouts'      => AuthLog::query()->where('event', 'lockout')->count(),
        ];
    }

    private function resolveLogUserName(AuthLog $log): string
    {
        $user = $log->user;
        if (! $user) {
            return 'Unregistered User';
        }

        if (method_exists($user, 'getFullNameAttribute') && filled($user->full_name)) {
            return (string) $user->full_name;
        }

        $parts = array_filter([
            $user->first_name ?? null,
            $user->middle_name ? mb_substr(trim((string) $user->middle_name), 0, 1) . '.' : null,
            $user->last_name ?? null,
        ]);

        if (! empty($parts)) {
            return trim(implode(' ', $parts) . ($user->suffix ? ' ' . $user->suffix : ''));
        }

        return (string) ($user->name ?? 'Unregistered User');
    }

    public function exportExcel(): ?StreamedResponse
    {
        $this->validateFilters();

        try {
            $filename = 'authentication-logs-' . now()->format('Y-m-d') . '.csv';
            $logs = $this->getFilteredLogsQuery()->get();
            $stats = $this->getStats();
            $generatedBy = Auth::user()?->name ?? 'System User';
            $searchLabel = trim($this->search) !== '' ? trim($this->search) : 'None';

            return response()->stream(function () use ($logs, $stats, $generatedBy, $searchLabel): void {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, ['TFVC LIBRARY MANAGEMENT SYSTEM']);
                fputcsv($file, ['AUTHENTICATION LOGS AUDIT REPORT']);
                fputcsv($file, ['Generated On: ' . now()->format('F d, Y h:i A'), '', 'Generated By: ' . $generatedBy]);
                fputcsv($file, ['Event Filter: ' . $this->eventFilter, 'Date Range: ' . $this->dateRange, 'Search: ' . $searchLabel]);
                fputcsv($file, []);

                fputcsv($file, ['Total Auth Events', 'Successful Logins', 'Failed Attempts', 'Lockouts']);
                fputcsv($file, [$stats['total_logs'], $stats['login_success'], $stats['login_failed'], $stats['lockouts']]);
                fputcsv($file, []);

                fputcsv($file, ['User Name', 'Targeted Email', 'Event', 'IP Address', 'Device (Browser / OS)', 'User Agent', 'Timestamp']);

                foreach ($logs as $log) {
                    fputcsv($file, [
                        $this->resolveLogUserName($log),
                        (string) ($log->email ?? 'N/A'),
                        (string) ($log->event ?? 'unknown'),
                        (string) ($log->ip_address ?? 'N/A'),
                        $this->parseDeviceBrowserOs($log->user_agent),
                        (string) ($log->user_agent ?? 'N/A'),
                        $log->logged_at ? $log->logged_at->format('Y-m-d H:i:s') : 'N/A',
                    ]);
                }

                fclose($file);
            }, 200, [
                'Content-type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename={$filename}",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ]);
        } catch (Throwable $e) {
            Log::error('Error exporting authentication logs CSV: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to export authentication logs CSV.', type: 'error');

            return null;
        }
    }

    public function exportPdf(): ?StreamedResponse
    {
        $this->validateFilters();

        try {
            $logs = $this->getFilteredLogsQuery()->get()->map(function (AuthLog $log): AuthLog {
                $log->setAttribute('device_browser_os', $this->parseDeviceBrowserOs($log->user_agent));

                return $log;
            });
            $stats = $this->getStats();

            $pdf = Pdf::loadView('pdf.authentication-logs', [
                'logs'        => $logs,
                'stats'       => $stats,
                'eventFilter' => $this->eventFilter,
                'dateRange'   => $this->dateRange,
                'search'      => $this->search,
            ])->setPaper('a4', 'landscape');

            $filename = 'authentication-logs-' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return response()->streamDownload(function () use ($pdf): void {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (Throwable $e) {
            Log::error('Error exporting authentication logs PDF: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to export authentication logs PDF.', type: 'error');

            return null;
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Authentication Logs')]
    public function render()
    {
        $stats = [
            'total_logs'    => 0,
            'login_success' => 0,
            'login_failed'  => 0,
            'lockouts'      => 0,
        ];
        $logs = new LengthAwarePaginator(collect(), 0, 10);

        try {
            $stats = $this->getStats();
            $logs = $this->getFilteredLogsQuery()->paginate(10);
            $logs->getCollection()->transform(function (AuthLog $log): AuthLog {
                $log->setAttribute('device_browser_os', $this->parseDeviceBrowserOs($log->user_agent));

                return $log;
            });
        } catch (Throwable $e) {
            Log::error('Error loading authentication logs: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to load authentication logs.', type: 'error');
        }

        return view('livewire.authentication-logs', [
            'logs'  => $logs,
            'stats' => $stats,
        ]);
    }
}
