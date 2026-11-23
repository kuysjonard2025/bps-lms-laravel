<div
    x-data="{ isDragging: false }"
    class="rounded-2xl border border-surface-border bg-surface-card p-5 sm:p-6 shadow-2xs space-y-5"
>
    <div>
        <h2 class="text-sm font-bold text-content-heading">Upload Accessions Spreadsheet</h2>
        <p class="text-xs text-content-body mt-0.5">
            Accepts <span class="font-semibold text-content-heading">.xlsx</span> files up to <span class="font-semibold text-content-heading">5 MB</span> matching the template columns.
        </p>
    </div>

    <form wire:submit="submitUpload" class="space-y-4">
        <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="
                isDragging = false;
                if ($event.dataTransfer.files.length > 0) {
                    $refs.accessionImportFileInput.files = $event.dataTransfer.files;
                    $refs.accessionImportFileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            "
            :class="isDragging ? 'border-[#4D9EFF] bg-[#4D9EFF]/10' : 'border-surface-border/80 bg-surface-canvas/70 hover:bg-surface-canvas'"
            class="rounded-2xl border border-dashed px-6 py-9 text-center transition flex flex-col items-center justify-center gap-2"
        >
            <input
                x-ref="accessionImportFileInput"
                id="accessions-import-file"
                type="file"
                accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                wire:model="file"
                class="sr-only"
            >

            <div class="w-10 h-10 rounded-xl bg-[#4D9EFF]/12 text-[#4D9EFF] flex items-center justify-center">
                <x-heroicon-o-arrow-up-tray class="w-5 h-5" aria-hidden="true" />
            </div>

            <div class="text-xs text-content-body">
                <label
                    for="accessions-import-file"
                    class="font-semibold text-[#4D9EFF] hover:underline cursor-pointer"
                >
                    Choose a file
                </label>
                <span>or drag and drop your <span class="font-mono text-content-heading">.xlsx</span> here</span>
            </div>

            <p class="text-[11px] text-content-muted">
                Maximum size: 5 MB (.xlsx only)
            </p>

            @if ($file || $stagedFileName)
                <div class="mt-2 inline-flex items-center gap-2 rounded-xl border border-surface-border bg-surface-card px-3 py-1.5 text-xs text-content-heading shadow-2xs">
                    <span class="font-mono truncate max-w-xs">{{ $stagedFileName ?? $file->getClientOriginalName() }}</span>
                    @if ($isVerified)
                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-[#4D9EFF]/15 text-[#4D9EFF]">
                            Ready
                        </span>
                    @endif
                    <button
                        type="button"
                        wire:click="clearFile"
                        class="text-content-muted hover:text-[#FF6B52] font-semibold cursor-pointer"
                        aria-label="Remove selected file"
                    >
                        ✕
                    </button>
                </div>
            @endif
        </div>

        @error('file')
            <div role="alert" class="rounded-xl border border-[#FF6B52]/30 bg-[#FF6B52]/10 px-3.5 py-2.5 text-xs font-medium text-[#FF6B52]">
                {{ $message }}
            </div>
        @enderror

        <div class="flex items-center justify-end pt-1">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="file,submitUpload"
                @class([
                    'px-4 py-2 text-xs font-semibold rounded-xl transition flex items-center gap-1.5 shadow-2xs',
                    'bg-[#4D9EFF] text-white hover:opacity-90 cursor-pointer' => $isVerified,
                    'bg-[#4D9EFF]/40 text-white/80 cursor-not-allowed' => ! $isVerified,
                ])
                :disabled="!@js($isVerified)"
            >
                <span wire:loading.remove wire:target="file,submitUpload">Stage File</span>
                <span wire:loading wire:target="file">Checking file...</span>
                <span wire:loading wire:target="submitUpload">Staging...</span>
            </button>
        </div>
    </form>
</div>
