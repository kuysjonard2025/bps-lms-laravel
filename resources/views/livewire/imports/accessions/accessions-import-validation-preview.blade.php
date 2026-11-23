<div>
    @if ($hasValidated)
        <div class="rounded-2xl border border-surface-border bg-surface-card p-5 sm:p-6 shadow-2xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-content-heading">
                        {{ $hasSaved ? 'Accessions Import Summary' : 'Accessions Validation & Preview' }}
                    </h2>
                    <p class="text-xs text-content-body mt-0.5">
                        File: <span class="font-mono text-content-heading">{{ $fileName }}</span>
                        @unless ($hasSaved)
                            <span class="text-content-muted">(Dry run — nothing saved yet)</span>
                        @endunless
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    @if ($hasSaved)
                        <span class="px-2.5 py-1 rounded-full bg-[#4D9EFF]/15 text-[#4D9EFF]">
                            {{ $createdCount }} Created
                        </span>
                        <span class="px-2.5 py-1 rounded-full bg-[#4D9EFF]/15 text-[#4D9EFF]">
                            {{ $updatedCount }} Updated
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full bg-[#4D9EFF]/15 text-[#4D9EFF]">
                            {{ $validRowsCount }} Valid
                        </span>
                    @endif

                    <span class="px-2.5 py-1 rounded-full bg-[#FF6B52]/15 text-[#FF6B52]">
                        {{ count($failures) }} Failed
                    </span>

                    @if (! $hasSaved && count($headingErrors) === 0 && $validRowsCount > 0)
                        <button
                            type="button"
                            wire:click="importValidRows"
                            wire:loading.attr="disabled"
                            wire:target="importValidRows"
                            class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#4D9EFF] hover:opacity-90 rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="importValidRows">Import Valid Rows</span>
                            <span wire:loading wire:target="importValidRows">Importing...</span>
                        </button>
                    @endif
                </div>
            </div>

            @foreach ($headingErrors as $headingError)
                <div role="alert" class="rounded-xl border border-[#FF6B52]/30 bg-[#FF6B52]/10 px-3.5 py-2.5 text-xs font-medium text-[#FF6B52]">
                    {{ $headingError }}
                </div>
            @endforeach

            @if (count($failures) > 0)
                <div class="overflow-x-auto border border-surface-border rounded-xl">
                    <table class="min-w-full text-left text-xs text-content-body">
                        <thead class="bg-surface-canvas text-content-muted uppercase tracking-wider text-[11px] border-b border-surface-border">
                            <tr>
                                <th scope="col" class="px-3.5 py-2.5 font-semibold whitespace-nowrap">Row #</th>
                                <th scope="col" class="px-3.5 py-2.5 font-semibold whitespace-nowrap">Column</th>
                                <th scope="col" class="px-3.5 py-2.5 font-semibold">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-border/70 bg-surface-card">
                            @foreach ($failures as $failure)
                                <tr>
                                    <td class="px-3.5 py-2.5 font-mono font-semibold text-content-heading whitespace-nowrap">{{ $failure['row'] }}</td>
                                    <td class="px-3.5 py-2.5 font-semibold text-content-heading whitespace-nowrap">{{ $failure['column'] }}</td>
                                    <td class="px-3.5 py-2.5 text-[#FF6B52]">{{ $failure['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif (! $hasSaved)
                <p class="text-xs text-content-body">
                    All non-empty data rows passed validation and are ready to import.
                </p>
            @else
                <p class="text-xs text-content-body">
                    All eligible rows were imported with no failed rows.
                </p>
            @endif
        </div>
    @endif
</div>
