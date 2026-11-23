<div>
    @if ($hasResult)
        <div class="rounded-2xl border border-surface-border bg-surface-card p-5 sm:p-6 shadow-2xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-content-heading">Accessions Import Summary</h2>
                    <p class="text-xs text-content-body mt-0.5">
                        File: <span class="font-mono text-content-heading">{{ $fileName }}</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <span class="px-2.5 py-1 rounded-full bg-[#4D9EFF]/15 text-[#4D9EFF]">
                        {{ $createdCount }} Created
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-[#4D9EFF]/15 text-[#4D9EFF]">
                        {{ $updatedCount }} Updated
                    </span>
                    <span class="px-2.5 py-1 rounded-full bg-[#FF6B52]/15 text-[#FF6B52]">
                        {{ count($failures) }} Failed
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-xl border border-surface-border bg-surface-canvas/70 px-4 py-3">
                    <p class="text-[11px] font-medium text-content-muted uppercase tracking-wider">Created</p>
                    <p class="text-lg font-bold text-[#4D9EFF] mt-0.5">{{ $createdCount }}</p>
                </div>

                <div class="rounded-xl border border-surface-border bg-surface-canvas/70 px-4 py-3">
                    <p class="text-[11px] font-medium text-content-muted uppercase tracking-wider">Updated</p>
                    <p class="text-lg font-bold text-content-heading mt-0.5">{{ $updatedCount }}</p>
                </div>

                <div class="rounded-xl border border-surface-border bg-surface-canvas/70 px-4 py-3">
                    <p class="text-[11px] font-medium text-content-muted uppercase tracking-wider">Failed</p>
                    <p class="text-lg font-bold text-[#FF6B52] mt-0.5">{{ count($failures) }}</p>
                </div>
            </div>

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
            @else
                <p class="text-xs text-content-body">
                    All eligible rows were processed with zero row failures.
                </p>
            @endif
        </div>
    @endif
</div>
