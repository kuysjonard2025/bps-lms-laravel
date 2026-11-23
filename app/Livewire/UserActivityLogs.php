<?php // app/Livewire/UserActivityLogs.php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class UserActivityLogs extends Component
{
    use WithPagination, SanitizesInputs;

    public string $search = '';
    public string $eventFilter = 'all';
    public string $moduleFilter = 'all';
    public string $moduleSearch = '';

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
        if (! in_array($this->eventFilter, ['all', 'created', 'updated', 'deleted'], true)) {
            $this->eventFilter = 'all';
        }
        $this->resetPage();
    }

    public function updatedModuleSearch(mixed $value): void
    {
        $clean = trim(strip_tags((string) $value));
        if (mb_strlen($clean) > 100) {
            $clean = mb_substr($clean, 0, 100);
        }
        $this->moduleSearch = $clean;

        if ($clean === '' || strtoupper($clean) === 'ALL MODULES') {
            $this->moduleFilter = 'all';
            $this->resetPage();

            return;
        }

        try {
            $matched = ActivityLog::query()
                ->whereNotNull('log_name')
                ->where('log_name', '!=', '')
                ->where('log_name', $this->likeOperator(), $clean)
                ->value('log_name');

            if ($matched) {
                $this->moduleFilter = (string) $matched;
                $this->resetPage();
            }
        } catch (Throwable $e) {
            Log::error('Error matching activity module search: ' . $e->getMessage());
        }
    }

    public function selectModule(string $module): void
    {
        $clean = trim(strip_tags($module));

        if ($clean === '' || $clean === 'all' || strtoupper($clean) === 'ALL MODULES') {
            $this->moduleFilter = 'all';
            $this->moduleSearch = '';
            $this->resetPage();

            return;
        }

        $this->moduleFilter = $clean;
        $this->moduleSearch = ucfirst($clean);
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->eventFilter = 'all';
        $this->moduleFilter = 'all';
        $this->moduleSearch = '';
        $this->resetErrorBag();
        $this->resetPage();
    }

    private function validateFilters(): void
    {
        $rules = [
            'search'       => ['nullable', 'string', 'max:100'],
            'eventFilter'  => ['required', 'string', 'in:all,created,updated,deleted'],
            'moduleFilter' => ['required', 'string', 'max:100'],
            'moduleSearch' => ['nullable', 'string', 'max:100'],
        ];

        $messages = [
            'search.max'       => 'Search keyword cannot exceed 100 characters.',
            'eventFilter.in'   => 'Selected activity event filter is invalid.',
            'moduleSearch.max' => 'Module filter cannot exceed 100 characters.',
        ];

        $attributes = [
            'search'       => 'Activity Search',
            'eventFilter'  => 'Event Filter',
            'moduleFilter' => 'Module Filter',
            'moduleSearch' => 'Module Search',
        ];

        $this->validate($rules, $messages, $attributes);
    }

    private function getFilteredLogsQuery(): Builder
    {
        $likeOperator = $this->likeOperator();
        $term = '%' . addcslashes(trim($this->search), '%_\\') . '%';

        return ActivityLog::query()
            ->with('user')
            ->when($this->eventFilter !== 'all', fn (Builder $q) => $q->where('event', $this->eventFilter))
            ->when($this->moduleFilter !== 'all', fn (Builder $q) => $q->where('log_name', $this->moduleFilter))
            ->when(trim($this->search) !== '', function (Builder $q) use ($likeOperator, $term): void {
                $q->where(function (Builder $sub) use ($likeOperator, $term): void {
                    $sub->where('description', $likeOperator, $term)
                        ->orWhere('ip_address', $likeOperator, $term)
                        ->orWhereHas('user', function (Builder $u) use ($likeOperator, $term): void {
                            $u->where('name', $likeOperator, $term)
                                ->orWhere('email', $likeOperator, $term);
                        });
                });
            })
            ->latest('created_at');
    }

    private function getStats(): array
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            $row = ActivityLog::query()
                ->selectRaw('count(*) as total_logs')
                ->selectRaw("count(*) filter (where event = 'created') as total_created")
                ->selectRaw("count(*) filter (where event = 'updated') as total_updated")
                ->selectRaw("count(*) filter (where event = 'deleted') as total_deleted")
                ->first();

            return [
                'total_logs'    => (int) ($row?->total_logs ?? 0),
                'total_created' => (int) ($row?->total_created ?? 0),
                'total_updated' => (int) ($row?->total_updated ?? 0),
                'total_deleted' => (int) ($row?->total_deleted ?? 0),
            ];
        }

        return [
            'total_logs'    => ActivityLog::query()->count(),
            'total_created' => ActivityLog::query()->where('event', 'created')->count(),
            'total_updated' => ActivityLog::query()->where('event', 'updated')->count(),
            'total_deleted' => ActivityLog::query()->where('event', 'deleted')->count(),
        ];
    }

    private function getModules(): Collection
    {
        return ActivityLog::query()
            ->whereNotNull('log_name')
            ->where('log_name', '!=', '')
            ->select('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name');
    }

    public function exportExcel(): ?StreamedResponse
    {
        $this->validateFilters();

        try {
            $filename = 'user-activity-logs-' . now()->format('Y-m-d') . '.csv';
            $logs = $this->getFilteredLogsQuery()->get();
            $stats = $this->getStats();
            $generatedBy = Auth::user()?->name ?? 'System User';
            $searchLabel = trim($this->search) !== '' ? trim($this->search) : 'None';

            return response()->stream(function () use ($logs, $stats, $generatedBy, $searchLabel): void {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, ['TFVC LIBRARY MANAGEMENT SYSTEM']);
                fputcsv($file, ['USER ACTIVITY LOGS AUDIT REPORT']);
                fputcsv($file, ['Generated On: ' . now()->format('F d, Y h:i A'), '', 'Generated By: ' . $generatedBy]);
                fputcsv($file, ['Event Filter: ' . $this->eventFilter, 'Module Filter: ' . $this->moduleFilter, 'Search: ' . $searchLabel]);
                fputcsv($file, []);

                fputcsv($file, ['Total Actions', 'Created', 'Updated', 'Deleted']);
                fputcsv($file, [$stats['total_logs'], $stats['total_created'], $stats['total_updated'], $stats['total_deleted']]);
                fputcsv($file, []);

                fputcsv($file, ['User Name', 'Email', 'Event', 'Module', 'Description', 'IP Address', 'Device (Browser / OS)', 'User Agent', 'Timestamp']);

                foreach ($logs as $log) {
                    fputcsv($file, [
                        (string) ($log->user?->name ?? 'System/Automated'),
                        (string) ($log->user?->email ?? 'N/A'),
                        (string) ($log->event ?? 'unknown'),
                        (string) ($log->log_name ?? 'general'),
                        (string) ($log->description ?? ''),
                        (string) ($log->ip_address ?? 'N/A'),
                        $this->parseDeviceBrowserOs($log->user_agent),
                        (string) ($log->user_agent ?? 'N/A'),
                        $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A',
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
            Log::error('Error exporting user activity logs CSV: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to export user activity logs CSV.', type: 'error');

            return null;
        }
    }

    public function exportPdf(): ?StreamedResponse
    {
        $this->validateFilters();

        try {
            $logs = $this->getFilteredLogsQuery()->get()->map(function (ActivityLog $log): ActivityLog {
                $log->setAttribute('device_browser_os', $this->parseDeviceBrowserOs($log->user_agent));

                return $log;
            });
            $stats = $this->getStats();

            $pdf = Pdf::loadView('pdf.user-activity-logs', [
                'logs'         => $logs,
                'stats'        => $stats,
                'eventFilter'  => $this->eventFilter,
                'moduleFilter' => $this->moduleFilter,
                'search'       => $this->search,
            ])->setPaper('a4', 'landscape');

            $filename = 'user-activity-logs-' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return response()->streamDownload(function () use ($pdf): void {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (Throwable $e) {
            Log::error('Error exporting user activity logs PDF: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to export user activity logs PDF.', type: 'error');

            return null;
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('User Activity Logs')]
    public function render()
    {
        $stats = [
            'total_logs'    => 0,
            'total_created' => 0,
            'total_updated' => 0,
            'total_deleted' => 0,
        ];
        $modules = new Collection();
        $logs = new LengthAwarePaginator(collect(), 0, 10);

        try {
            $stats = $this->getStats();
            $modules = $this->getModules();
            $logs = $this->getFilteredLogsQuery()->paginate(10);
            $logs->getCollection()->transform(function (ActivityLog $log): ActivityLog {
                $log->setAttribute('device_browser_os', $this->parseDeviceBrowserOs($log->user_agent));

                return $log;
            });
        } catch (Throwable $e) {
            Log::error('Error loading user activity logs: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Unable to load user activity logs.', type: 'error');
        }

        return view('livewire.user-activity-logs', [
            'logs'    => $logs,
            'stats'   => $stats,
            'modules' => $modules,
        ]);
    }
}
