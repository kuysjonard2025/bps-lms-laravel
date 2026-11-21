<?php // app/Livewire/PatronPortal.php

namespace App\Livewire;

use App\Models\Accession;
use App\Models\Acquisition;
use App\Models\AssetType;
use App\Models\Catalog;
use App\Models\Patron;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class PatronPortal extends Component
{
    use WithPagination;

    public string $activeTab = 'catalog'; // catalog | records | profile
    public string $recordFilter = 'all'; // all | borrowed | overdue | returned
    public string $recordSearch = '';

    public string $search = '';
    public string $assetTypeFilter = 'all';
    public string $assetTypeSearch = '';

    public function setTab(string $tab): void
    {
        $allowed = ['catalog', 'records', 'profile'];
        if (! in_array($tab, $allowed, true)) {
            $tab = 'catalog';
        }

        if (($tab === 'records' || $tab === 'profile') && ! $this->resolveCurrentPatron()) {
            $this->activeTab = 'catalog';
            $this->dispatch('toast', type: 'error', message: 'Please sign in as a patron to view your records and profile.');
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function setRecordFilter(string $filter): void
    {
        $allowed = ['all', 'borrowed', 'overdue', 'returned'];
        $this->recordFilter = in_array($filter, $allowed, true) ? $filter : 'all';
        $this->resetPage();
    }

    public function updatingRecordSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAssetTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssetTypeSearch(string $value): void
    {
        try {
            $clean = strtoupper(trim(strip_tags($value)));
            $this->assetTypeSearch = $clean;

            if ($clean === '' || $clean === 'ALL ASSET TYPES') {
                $this->assetTypeFilter = 'all';
                $this->resetPage();
                return;
            }

            $matched = AssetType::where('name', $this->likeOperator(), $clean)->first();
            if ($matched) {
                $this->assetTypeFilter = (string) $matched->id;
                $this->resetPage();
            }
        } catch (Throwable $e) {
            Log::error('Failed filtering asset type search in PatronPortal: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->dispatch('toast', type: 'error', message: 'Unable to filter asset types right now.');
        }
    }

    public function selectAssetType(string $value): void
    {
        try {
            if ($value === 'all') {
                $this->assetTypeFilter = 'all';
                $this->assetTypeSearch = 'ALL ASSET TYPES';
                $this->resetPage();
                return;
            }

            $type = AssetType::find($value);
            if ($type) {
                $this->assetTypeFilter = (string) $type->id;
                $this->assetTypeSearch = strtoupper((string) $type->name);
                $this->resetPage();
            }
        } catch (Throwable $e) {
            Log::error('Failed selecting asset type in PatronPortal: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->dispatch('toast', type: 'error', message: 'Unable to apply asset type selection.');
        }
    }

    public function logoutPatron(): void
    {
        try {
            Session::forget(['patron_session_id', 'patron_id']);
            if (Auth::guard('patron')->check()) {
                Auth::guard('patron')->logout();
            }
            Session::regenerateToken();

            $this->activeTab = 'catalog';
            $this->dispatch('toast', type: 'success', message: 'Patron session ended.');
            $this->redirect(route('patron.login'), navigate: true);
        } catch (Throwable $e) {
            Log::error('Failed ending patron session in PatronPortal::logoutPatron: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->dispatch('toast', type: 'error', message: 'Unable to end patron session right now.');
        }
    }

    private function likeOperator(): string
    {
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }

    private function patronRelations(): array
    {
        $relations = ['patronType'];

        foreach (['gradeLevel', 'section', 'department', 'strand', 'adviser'] as $relation) {
            if (method_exists(Patron::class, $relation)) {
                $relations[] = $relation;
            }
        }

        return $relations;
    }

    private function resolveCurrentPatron(): ?Patron
    {
        try {
            $relations = $this->patronRelations();

            $sessionPatronId = Session::get('patron_session_id') ?? Session::get('patron_id');
            if ($sessionPatronId) {
                $patron = Patron::with($relations)->find($sessionPatronId);
                if ($patron) {
                    return $patron;
                }
            }

            if (Auth::guard('patron')->check()) {
                $guardUser = Auth::guard('patron')->user();
                if ($guardUser instanceof Patron) {
                    return $guardUser->loadMissing($relations);
                }
                if ($guardUser?->id) {
                    return Patron::with($relations)->find($guardUser->id);
                }
            }

            $authUser = Auth::user();
            if ($authUser?->email) {
                return Patron::with($relations)
                    ->where('email', $this->likeOperator(), $authUser->email)
                    ->first();
            }
        } catch (Throwable $e) {
            Log::error('Failed resolving patron in PatronPortal: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }

        return null;
    }

    private function baseAccessionsForCatalog(string $tableAlias = 'acc')
    {
        return Accession::from('accessions as ' . $tableAlias)
            ->join('acquisitions as acq', $tableAlias . '.acquisition_id', '=', 'acq.id')
            ->whereColumn('acq.catalog_id', 'catalogs.id');
    }

    private function unaccessionedSubquery()
    {
        $hasAcqStatus = Schema::hasColumn('acquisitions', 'status');
        $hasQty = Schema::hasColumn('acquisitions', 'quantity');

        $query = Acquisition::from('acquisitions as acq_unacc')
            ->whereColumn('acq_unacc.catalog_id', 'catalogs.id');

        if ($hasAcqStatus) {
            $query->where(function ($sub) {
                $sub->whereRaw('LOWER(acq_unacc.status) IN (?, ?)', ['unaccession', 'unaccessioned'])
                    ->orWhereNotExists(function ($accExists) {
                        $accExists->selectRaw('1')
                            ->from('accessions as acc_chk')
                            ->whereColumn('acc_chk.acquisition_id', 'acq_unacc.id');
                    });
            });
        } else {
            $query->whereNotExists(function ($accExists) {
                $accExists->selectRaw('1')
                    ->from('accessions as acc_chk')
                    ->whereColumn('acc_chk.acquisition_id', 'acq_unacc.id');
            });
        }

        return $hasQty
            ? $query->selectRaw('COALESCE(SUM(acq_unacc.quantity), COUNT(*))')
            : $query->selectRaw('COUNT(*)');
    }

    private function getOpacCatalogsQuery()
    {
        $op = $this->likeOperator();
        $cleanSearch = trim(strip_tags($this->search));
        $castYearExpr = config('database.default') === 'pgsql'
            ? 'CAST(catalogs.publication_year AS TEXT)'
            : 'CAST(catalogs.publication_year AS CHAR)';

        return Catalog::with(['author', 'assetType', 'publisher'])
            ->select('catalogs.*')
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_total')->selectRaw('count(*)'),
                'total_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_avail')
                    ->whereRaw('LOWER(acc_avail.status) = ?', ['available'])
                    ->selectRaw('count(*)'),
                'available_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_stored')
                    ->whereRaw('LOWER(acc_stored.status) = ?', ['stored'])
                    ->selectRaw('count(*)'),
                'stored_copies'
            )
            ->selectSub(
                $this->unaccessionedSubquery(),
                'unaccessioned_copies'
            )
            ->when($this->assetTypeFilter !== 'all', function ($q) {
                $q->where('catalogs.asset_type_id', $this->assetTypeFilter);
            })
            ->when($cleanSearch !== '', function ($q) use ($op, $cleanSearch, $castYearExpr) {
                $term = "%{$cleanSearch}%";
                $q->where(function ($sub) use ($op, $term, $castYearExpr) {
                    $sub->where('catalogs.title', $op, $term)
                        ->orWhere('catalogs.isbn_issn', $op, $term)
                        ->orWhere('catalogs.call_number', $op, $term)
                        ->orWhereRaw("{$castYearExpr} {$op} ?", [$term])
                        ->orWhereHas('author', fn ($a) => $a->where('name', $op, $term))
                        ->orWhereHas('publisher', fn ($p) => $p->where('name', $op, $term))
                        ->orWhereHas('acquisitions.accessions', function ($acc) use ($op, $term) {
                            $acc->where('call_number', $op, $term)
                                ->orWhere('accession_number', $op, $term);
                        });
                });
            })
            ->latest('catalogs.created_at');
    }

    private function buildPatronCirculationsBaseQuery(int $patronId)
    {
        if (! Schema::hasTable('circulations') || ! Schema::hasColumn('circulations', 'patron_id')) {
            return null;
        }

        return DB::table('circulations as cir')
            ->leftJoin('accessions as acc', 'cir.accession_id', '=', 'acc.id')
            ->leftJoin('acquisitions as acq', 'acc.acquisition_id', '=', 'acq.id')
            ->leftJoin('catalogs as cat', 'acq.catalog_id', '=', 'cat.id')
            ->leftJoin('authors as aut', 'cat.author_id', '=', 'aut.id')
            ->where('cir.patron_id', $patronId);
    }

    private function getPatronCirculationStats(int $patronId): array
    {
        $stats = [
            'borrowed' => 0,
            'overdue'  => 0,
            'returned' => 0,
        ];

        try {
            $base = $this->buildPatronCirculationsBaseQuery($patronId);
            if (! $base) {
                return $stats;
            }

            $hasStatus = Schema::hasColumn('circulations', 'status');
            $hasReturnedAt = Schema::hasColumn('circulations', 'returned_at');
            $hasDueDate = Schema::hasColumn('circulations', 'due_date');
            $now = Carbon::now();

            $rows = (clone $base)
                ->select([
                    'cir.id',
                    $hasStatus ? DB::raw('LOWER(COALESCE(cir.status, \'\')) as cir_status') : DB::raw('\'\' as cir_status'),
                    $hasReturnedAt ? 'cir.returned_at' : DB::raw('NULL as returned_at'),
                    $hasDueDate ? 'cir.due_date' : DB::raw('NULL as due_date'),
                ])
                ->get();

            foreach ($rows as $row) {
                $isReturned = ! empty($row->returned_at) || in_array($row->cir_status, ['returned', 'completed', 'checked_in'], true);
                if ($isReturned) {
                    $stats['returned']++;
                    continue;
                }

                $isOverdue = $row->cir_status === 'overdue'
                    || (! empty($row->due_date) && Carbon::parse($row->due_date)->lt($now));

                if ($isOverdue) {
                    $stats['overdue']++;
                } else {
                    $stats['borrowed']++;
                }
            }
        } catch (Throwable $e) {
            Log::error('Failed loading patron circulation stats in PatronPortal: ' . $e->getMessage(), [
                'patron_id' => $patronId,
                'exception' => $e,
            ]);
        }

        return $stats;
    }

    private function getPatronRecordsPaginator(int $patronId): LengthAwarePaginator
    {
        try {
            $base = $this->buildPatronCirculationsBaseQuery($patronId);
            if (! $base) {
                return new LengthAwarePaginator([], 0, 10);
            }

            $op = $this->likeOperator();
            $cleanSearch = trim(strip_tags($this->recordSearch));
            $hasStatus = Schema::hasColumn('circulations', 'status');
            $hasReturnedAt = Schema::hasColumn('circulations', 'returned_at');
            $hasDueDate = Schema::hasColumn('circulations', 'due_date');
            $hasBorrowedAt = Schema::hasColumn('circulations', 'borrowed_at');
            $hasAccNum = Schema::hasColumn('accessions', 'accession_number');
            $hasCallNum = Schema::hasColumn('accessions', 'call_number');
            $now = Carbon::now();

            $query = $base->select([
                'cir.id',
                $hasStatus ? DB::raw('LOWER(COALESCE(cir.status, \'\')) as cir_status') : DB::raw('\'\' as cir_status'),
                $hasBorrowedAt ? 'cir.borrowed_at' : DB::raw('cir.created_at as borrowed_at'),
                $hasDueDate ? 'cir.due_date' : DB::raw('NULL as due_date'),
                $hasReturnedAt ? 'cir.returned_at' : DB::raw('NULL as returned_at'),
                $hasAccNum ? 'acc.accession_number' : DB::raw('NULL as accession_number'),
                $hasCallNum ? 'acc.call_number' : DB::raw('cat.call_number as call_number'),
                DB::raw('COALESCE(cat.title, \'UNTITLED ITEM\') as catalog_title'),
                DB::raw('COALESCE(aut.name, \'UNKNOWN AUTHOR\') as author_name'),
            ]);

            if ($this->recordFilter === 'returned') {
                $query->where(function ($q) use ($hasReturnedAt, $hasStatus) {
                    if ($hasReturnedAt) {
                        $q->whereNotNull('cir.returned_at');
                    }
                    if ($hasStatus) {
                        $method = $hasReturnedAt ? 'orWhereIn' : 'whereIn';
                        $q->{$method}(DB::raw('LOWER(cir.status)'), ['returned', 'completed', 'checked_in']);
                    }
                });
            } elseif ($this->recordFilter === 'overdue') {
                $query->where(function ($q) use ($hasReturnedAt, $hasStatus, $hasDueDate, $now) {
                    if ($hasReturnedAt) {
                        $q->whereNull('cir.returned_at');
                    }
                    if ($hasStatus) {
                        $q->whereNotIn(DB::raw('LOWER(cir.status)'), ['returned', 'completed', 'checked_in']);
                    }
                    $q->where(function ($dueSub) use ($hasStatus, $hasDueDate, $now) {
                        if ($hasStatus) {
                            $dueSub->whereRaw('LOWER(cir.status) = ?', ['overdue']);
                        }
                        if ($hasDueDate) {
                            $dueMethod = $hasStatus ? 'orWhere' : 'where';
                            $dueSub->{$dueMethod}('cir.due_date', '<', $now);
                        }
                    });
                });
            } elseif ($this->recordFilter === 'borrowed') {
                $query->where(function ($q) use ($hasReturnedAt, $hasStatus, $hasDueDate, $now) {
                    if ($hasReturnedAt) {
                        $q->whereNull('cir.returned_at');
                    }
                    if ($hasStatus) {
                        $q->whereNotIn(DB::raw('LOWER(cir.status)'), ['returned', 'completed', 'checked_in', 'overdue']);
                    }
                    if ($hasDueDate) {
                        $q->where(function ($notOverdue) use ($now) {
                            $notOverdue->whereNull('cir.due_date')
                                ->orWhere('cir.due_date', '>=', $now);
                        });
                    }
                });
            }

            if ($cleanSearch !== '') {
                $term = "%{$cleanSearch}%";
                $query->where(function ($searchSub) use ($op, $term, $hasAccNum, $hasCallNum) {
                    $searchSub->where('cat.title', $op, $term)
                        ->orWhere('aut.name', $op, $term);
                    if ($hasAccNum) {
                        $searchSub->orWhere('acc.accession_number', $op, $term);
                    }
                    if ($hasCallNum) {
                        $searchSub->orWhere('acc.call_number', $op, $term);
                    }
                    $searchSub->orWhere('cat.call_number', $op, $term);
                });
            }

            $paginator = $query->orderByDesc('cir.id')->paginate(10);

            $paginator->getCollection()->transform(function ($item) use ($now) {
                $status = strtolower((string) ($item->cir_status ?? ''));
                $isReturned = ! empty($item->returned_at) || in_array($status, ['returned', 'completed', 'checked_in'], true);
                $isOverdue = ! $isReturned && ($status === 'overdue' || (! empty($item->due_date) && Carbon::parse($item->due_date)->lt($now)));

                $item->computed_state = $isReturned ? 'returned' : ($isOverdue ? 'overdue' : 'borrowed');
                $item->borrowed_label = ! empty($item->borrowed_at) ? Carbon::parse($item->borrowed_at)->format('M d, Y') : 'N/A';
                $item->due_label = ! empty($item->due_date) ? Carbon::parse($item->due_date)->format('M d, Y') : 'N/A';
                $item->returned_label = ! empty($item->returned_at) ? Carbon::parse($item->returned_at)->format('M d, Y') : '—';

                return $item;
            });

            return $paginator;
        } catch (Throwable $e) {
            Log::error('Failed loading patron records in PatronPortal: ' . $e->getMessage(), [
                'patron_id' => $patronId,
                'filter'    => $this->recordFilter,
                'exception' => $e,
            ]);
            $this->dispatch('toast', type: 'error', message: 'Unable to load your borrowing records right now.');

            return new LengthAwarePaginator([], 0, 10);
        }
    }

    private function extractPatronField(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if ($raw instanceof Model) {
            $raw = $raw->toArray();
        } elseif (is_object($raw)) {
            $raw = (array) $raw;
        } elseif (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed === '') {
                return null;
            }
            if (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $raw = $decoded;
                } else {
                    return $trimmed;
                }
            } else {
                return $trimmed;
            }
        }

        if (is_array($raw)) {
            $name = null;
            $code = null;

            foreach ([
                'name', 'NAME',
                'title', 'TITLE',
                'label', 'LABEL',
                'value', 'VALUE',
                'full_name', 'adviser_name', 'adviser', 'teacher_name',
            ] as $key) {
                if (! empty($raw[$key]) && is_scalar($raw[$key])) {
                    $name = trim((string) $raw[$key]);
                    break;
                }
            }

            if (! $name && (! empty($raw['first_name']) || ! empty($raw['last_name']))) {
                $name = trim(implode(' ', array_filter([
                    $raw['first_name'] ?? null,
                    $raw['middle_name'] ?? null,
                    $raw['last_name'] ?? null,
                    $raw['suffix'] ?? null,
                ])));
            }

            foreach (['code', 'CODE'] as $codeKey) {
                if (! empty($raw[$codeKey]) && is_scalar($raw[$codeKey])) {
                    $code = trim((string) $raw[$codeKey]);
                    break;
                }
            }

            if ($name && $code && strcasecmp($name, $code) !== 0) {
                return "{$name} ({$code})";
            }

            return $name ?: $code;
        }

        return is_scalar($raw) ? trim((string) $raw) : null;
    }

    private function resolveRelationOrFallback(Patron $patron, string $relation, string $fkColumn, ?string $fallbackTable = null): string
    {
        try {
            if ($patron->relationLoaded($relation)) {
                $relValue = $this->extractPatronField($patron->getRelation($relation));
                if ($relValue) {
                    return $relValue;
                }
            }

            $attrs = $patron->getAttributes();
            $fkId = $attrs[$fkColumn] ?? $patron->{$fkColumn} ?? null;
            if ($fkId && $fallbackTable && Schema::hasTable($fallbackTable)) {
                $row = DB::table($fallbackTable)->where('id', $fkId)->first();
                $rowValue = $this->extractPatronField($row);
                if ($rowValue) {
                    return $rowValue;
                }
            }

            $directAttr = $this->extractPatronField($attrs[str_replace('_id', '', $fkColumn)] ?? null);
            if ($directAttr) {
                return $directAttr;
            }
        } catch (Throwable $e) {
            Log::error("Failed resolving {$relation} for patron {$patron->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }

        return 'N/A';
    }

    private function resolveAdviserDisplay(Patron $patron): string
    {
        $directAdviser = $this->resolveRelationOrFallback($patron, 'adviser', 'adviser_id', 'advisers');
        if ($directAdviser !== 'N/A') {
            return $directAdviser;
        }

        try {
            $attrs = $patron->getAttributes();
            $sectionId = $attrs['section_id'] ?? $patron->section_id ?? null;

            if ($sectionId && Schema::hasTable('sections')) {
                $sectionRow = DB::table('sections')->where('id', $sectionId)->first();
                if ($sectionRow) {
                    $sectionArr = (array) $sectionRow;
                    if (! empty($sectionArr['adviser_id']) && Schema::hasTable('advisers')) {
                        $adviserRow = DB::table('advisers')->where('id', $sectionArr['adviser_id'])->first();
                        $adviserName = $this->extractPatronField($adviserRow);
                        if ($adviserName) {
                            return $adviserName;
                        }
                    }

                    $inlineAdviser = $this->extractPatronField($sectionArr['adviser'] ?? $sectionArr['adviser_name'] ?? null);
                    if ($inlineAdviser) {
                        return $inlineAdviser;
                    }
                }
            }
        } catch (Throwable $e) {
            Log::error("Failed resolving section adviser fallback for patron {$patron->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }

        return 'N/A';
    }

    #[Layout('components.layouts.kiosk')]
    #[Title('Patron Portal & OPAC Catalog')]
    public function render()
    {
        $patron = $this->resolveCurrentPatron();
        if (! $patron && in_array($this->activeTab, ['records', 'profile'], true)) {
            $this->activeTab = 'catalog';
        }

        $op = $this->likeOperator();
        $typeTerm = trim($this->assetTypeSearch);

        try {
            $assetTypeOptions = AssetType::query()
                ->when(
                    $typeTerm !== '' && strtoupper($typeTerm) !== 'ALL ASSET TYPES',
                    fn ($q) => $q->where('name', $op, "%{$typeTerm}%")
                )
                ->orderBy('name')
                ->limit(25)
                ->get();

            $catalogs = $this->activeTab === 'catalog'
                ? $this->getOpacCatalogsQuery()->paginate(12)
                : Catalog::whereRaw('1 = 0')->paginate(12);

            $circulationStats = $patron
                ? $this->getPatronCirculationStats((int) $patron->id)
                : ['borrowed' => 0, 'overdue' => 0, 'returned' => 0];

            $patronRecords = ($patron && $this->activeTab === 'records')
                ? $this->getPatronRecordsPaginator((int) $patron->id)
                : new LengthAwarePaginator([], 0, 10);

            $isTeacher = false;
            $isShs = false;
            $departmentDisplay = 'N/A';
            $strandDisplay = 'N/A';
            $gradeLevelDisplay = 'N/A';
            $sectionDisplay = 'N/A';
            $adviserDisplay = 'N/A';

            if ($patron) {
                $patronTypeName = strtoupper(trim((string) ($patron->patronType?->name ?? '')));
                $isTeacher = str_contains($patronTypeName, 'TEACHER') || str_contains($patronTypeName, 'FACULTY');

                $departmentDisplay = $this->resolveRelationOrFallback($patron, 'department', 'department_id', 'departments');
                $deptUpper = strtoupper($departmentDisplay);
                $isShs = str_contains($deptUpper, 'SHS') || str_contains($deptUpper, 'SENIOR HIGH');

                if ($isShs) {
                    $strandDisplay = $this->resolveRelationOrFallback($patron, 'strand', 'strand_id', 'strands');
                }

                if (! $isTeacher) {
                    $gradeLevelDisplay = $this->resolveRelationOrFallback($patron, 'gradeLevel', 'grade_level_id', 'grade_levels');
                    $sectionDisplay = $this->resolveRelationOrFallback($patron, 'section', 'section_id', 'sections');
                    $adviserDisplay = $this->resolveAdviserDisplay($patron);
                }
            }

            return view('livewire.patron-portal', [
                'patron'            => $patron,
                'isTeacher'         => $isTeacher,
                'isShs'             => $isShs,
                'departmentDisplay' => $departmentDisplay,
                'strandDisplay'     => $strandDisplay,
                'gradeLevelDisplay' => $gradeLevelDisplay,
                'sectionDisplay'    => $sectionDisplay,
                'adviserDisplay'    => $adviserDisplay,
                'catalogs'          => $catalogs,
                'assetTypeOptions'  => $assetTypeOptions,
                'circulationStats'  => $circulationStats,
                'patronRecords'     => $patronRecords,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to render PatronPortal::render', [
                'tab'        => $this->activeTab,
                'search'     => $this->search,
                'asset_type' => $this->assetTypeFilter,
                'error'      => $e->getMessage(),
                'exception'  => $e,
            ]);
            $this->dispatch('toast', type: 'error', message: 'Unable to load portal data right now.');

            return view('livewire.patron-portal', [
                'patron'            => $patron,
                'isTeacher'         => false,
                'isShs'             => false,
                'departmentDisplay' => 'N/A',
                'strandDisplay'     => 'N/A',
                'gradeLevelDisplay' => 'N/A',
                'sectionDisplay'    => 'N/A',
                'adviserDisplay'    => 'N/A',
                'catalogs'          => Catalog::whereRaw('1 = 0')->paginate(12),
                'assetTypeOptions'  => collect(),
                'circulationStats'  => ['borrowed' => 0, 'overdue' => 0, 'returned' => 0],
                'patronRecords'     => new LengthAwarePaginator([], 0, 10),
            ]);
        }
    }
}
