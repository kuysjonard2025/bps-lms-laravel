{{-- resources/views/livewire/imports/catalogs/catalogs-import-preview.blade.php --}}
<div>
    @if ($hasPreview)
        <div class="rounded-2xl border border-surface-border bg-surface-card p-5 shadow-2xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-content-heading">Import Validation &amp; Preview</h3>
                    <p class="text-xs text-content-body mt-0.5">
                        File: <span class="font-medium text-content-heading">{{ $originalName }}</span>
                        ({{ $totalRows }} {{ \Illuminate\Support\Str::plural('row', $totalRows) }} scanned)
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        wire:click="setFilter('all')"
                        class="px-3 py-1.5 text-xs font-medium rounded-xl border transition cursor-pointer {{ $filterStatus === 'all' ? 'border-brand-primary bg-brand-tint text-brand-primary' : 'border-surface-border bg-surface-canvas text-content-body hover:text-content-heading' }}"
                    >
                        All ({{ $totalRows }})
                    </button>
                    <button
                        type="button"
                        wire:click="setFilter('errors')"
                        class="px-3 py-1.5 text-xs font-medium rounded-xl border transition cursor-pointer {{ $filterStatus === 'errors' ? 'border-brand-secondary bg-brand-secondary/10 text-brand-secondary' : 'border-surface-border bg-surface-canvas text-content-body hover:text-content-heading' }}"
                    >
                        Errors ({{ $errorCount }})
                    </button>
                    <button
                        type="button"
                        wire:click="setFilter('valid')"
                        class="px-3 py-1.5 text-xs font-medium rounded-xl border transition cursor-pointer {{ $filterStatus === 'valid' ? 'border-brand-primary bg-brand-tint-light text-brand-primary' : 'border-surface-border bg-surface-canvas text-content-body hover:text-content-heading' }}"
                    >
                        Valid ({{ $validCount }})
                    </button>
                    @if ($errorCount > 0)
                        <button
                            type="button"
                            wire:click="exportErrorRows"
                            wire:target="exportErrorRows"
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 text-xs font-medium rounded-xl border border-brand-secondary/40 bg-brand-secondary/10 hover:bg-brand-secondary/15 text-brand-secondary transition inline-flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading wire:target="exportErrorRows" class="animate-spin h-3.5 w-3.5 border-2 border-brand-secondary border-t-transparent rounded-full"></span>
                            <span>Export Error Rows ({{ $errorCount }})</span>
                        </button>
                    @endif
                    <button
                        type="button"
                        wire:click="importValidRows"
                        wire:target="importValidRows"
                        wire:loading.attr="disabled"
                        @if (! $stagedPath || ! $headersValid || $validCount < 1) disabled @endif
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white transition inline-flex items-center gap-1.5 shadow-2xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading wire:target="importValidRows" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Import Valid Rows ({{ $validCount }})</span>
                    </button>
                    <button
                        type="button"
                        wire:click="clearPreview"
                        wire:target="clearPreview"
                        wire:loading.attr="disabled"
                        class="px-3 py-1.5 text-xs font-medium rounded-xl border border-surface-border bg-surface-canvas text-content-body hover:text-content-heading transition cursor-pointer disabled:opacity-50"
                    >
                        Reset
                    </button>
                </div>
            </div>

            @if ($importedCount > 0 || $skippedCount > 0 || ! empty($importMessages))
                <div class="rounded-xl border border-surface-border bg-brand-tint-light p-3.5 space-y-1">
                    <p class="text-xs font-semibold text-content-heading">
                        Import Summary: {{ $importedCount }} imported, {{ $skippedCount }} skipped
                    </p>
                    @foreach ($importMessages as $msg)
                        <p class="text-xs text-content-body">{{ $msg }}</p>
                    @endforeach
                </div>
            @endif

            @if (! $headersValid && ! empty($headerErrors))
                <div role="alert" class="rounded-xl border border-brand-secondary/40 bg-brand-secondary/10 p-3.5 space-y-1">
                    <p class="text-xs font-semibold text-brand-secondary">Spreadsheet Header Mismatch</p>
                    @foreach ($headerErrors as $headerError)
                        <p class="text-xs text-content-heading">{{ $headerError }}</p>
                    @endforeach
                </div>
            @endif

            @if (empty($filteredRows))
                <div class="rounded-xl border border-surface-border bg-surface-canvas p-6 text-center text-xs text-content-body">
                    No rows match the <span class="font-semibold text-content-heading">{{ $filterStatus }}</span> filter.
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-surface-border">
                    <table class="min-w-full divide-y divide-surface-border text-left text-xs">
                        <thead class="bg-surface-canvas text-content-heading font-semibold">
                            <tr>
                                <th class="px-3 py-2.5 whitespace-nowrap">Row</th>
                                <th class="px-3 py-2.5 whitespace-nowrap">Status</th>
                                @foreach ($headings as $heading)
                                    <th class="px-3 py-2.5 whitespace-nowrap">{{ $heading }}</th>
                                @endforeach
                                <th class="px-3 py-2.5 whitespace-nowrap">Validation Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-border bg-surface-card">
                            @foreach ($filteredRows as $row)
                                @php
                                    $rowErrors = $row['errors'] ?? [];
                                    $flatMessages = [];
                                    foreach ($rowErrors as $fieldMsgs) {
                                        foreach ($fieldMsgs as $msg) {
                                            $flatMessages[] = $msg;
                                        }
                                    }
                                @endphp
                                <tr class="{{ ! empty($row['is_valid']) ? '' : 'bg-brand-secondary/5' }}">
                                    <td class="px-3 py-2.5 font-mono text-content-body whitespace-nowrap">
                                        #{{ $row['row_number'] }}
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap">
                                        @if (! empty($row['is_valid']))
                                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-brand-tint text-brand-primary">
                                                Valid
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-brand-secondary/15 text-brand-secondary">
                                                Error
                                            </span>
                                        @endif
                                    </td>
                                    @foreach ($headings as $heading)
                                        @php
                                            $cellValue = $row['data'][$heading] ?? '';
                                            $hasFieldError = isset($rowErrors[$heading]);
                                        @endphp
                                        <td class="px-3 py-2.5 whitespace-nowrap {{ $hasFieldError ? 'text-brand-secondary font-semibold bg-brand-secondary/10' : 'text-content-heading' }}">
                                            {{ $cellValue !== '' ? $cellValue : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-2.5 text-content-body min-w-64">
                                        @if (empty($flatMessages))
                                            <span class="text-content-muted">Ready to import</span>
                                        @else
                                            <ul class="list-disc list-inside space-y-0.5 text-brand-secondary">
                                                @foreach ($flatMessages as $msg)
                                                    <li>{{ $msg }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
