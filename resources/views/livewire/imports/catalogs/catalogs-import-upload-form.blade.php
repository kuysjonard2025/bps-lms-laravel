{{-- resources/views/livewire/imports/catalogs/catalogs-import-upload-form.blade.php --}}
<div
    x-data="{
        isDragging: false,
        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer?.files;
            if (! files || files.length === 0) {
                return;
            }
            const input = this.$refs.fileInput;
            if (! input) {
                return;
            }
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            input.files = dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }"
    class="rounded-2xl border border-surface-border bg-surface-card p-5 shadow-2xs space-y-4"
>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-content-heading">Upload Catalog Spreadsheet</h3>
            <p class="text-xs text-content-body mt-0.5">
                Accepts <span class="font-medium text-content-heading">.xlsx</span> or <span class="font-medium text-content-heading">.csv</span> files up to 10 MB matching the template columns.
            </p>
        </div>

        @if ($stagedFilePath)
            <button
                type="button"
                wire:click="resetUpload"
                wire:target="resetUpload"
                wire:loading.attr="disabled"
                class="px-3 py-1.5 text-xs font-medium rounded-xl border border-surface-border bg-surface-canvas text-content-body hover:text-content-heading transition cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
            >
                Clear file
            </button>
        @endif
    </div>

    <form wire:submit="prepareUpload" class="space-y-4">
        <div
            x-on:dragover.prevent="isDragging = true"
            x-on:dragleave.prevent="isDragging = false"
            x-on:drop.prevent="handleDrop($event)"
            :class="isDragging ? 'border-brand-primary bg-brand-tint-light' : 'border-surface-border bg-surface-canvas hover:border-brand-primary/60'"
            class="relative rounded-2xl border-2 border-dashed p-6 text-center transition"
        >
            <input
                x-ref="fileInput"
                id="catalog-import-file-input"
                type="file"
                wire:model.live.debounce.300ms="importFile"
                accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv"
                class="sr-only"
            />

            <div class="flex flex-col items-center justify-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-brand-tint flex items-center justify-center text-brand-primary">
                    <x-heroicon-o-document-arrow-up class="w-5 h-5" aria-hidden="true" />
                </div>

                <label
                    for="catalog-import-file-input"
                    class="text-xs font-semibold text-brand-primary hover:underline cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15 rounded"
                >
                    Choose a file
                </label>
                <span class="text-xs text-content-body">or drag and drop your .xlsx / .csv here</span>
                <span class="text-[11px] text-content-muted">Maximum size: 10 MB</span>
            </div>

            <div
                wire:loading
                wire:target="importFile"
                class="mt-3 inline-flex items-center gap-2 text-xs font-medium text-brand-primary"
            >
                <span class="animate-spin h-3.5 w-3.5 border-2 border-brand-primary border-t-transparent rounded-full"></span>
                <span>Uploading and staging spreadsheet...</span>
            </div>
        </div>

        @error('importFile')
            <p role="alert" class="text-xs font-medium text-brand-secondary">
                {{ $message }}
            </p>
        @enderror

        @if ($stagedFilePath && $stagedOriginalName)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-surface-border bg-brand-tint-light px-4 py-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-content-heading truncate">
                        {{ $stagedOriginalName }}
                    </p>
                    <p class="text-[11px] text-content-body">
                        Staged ({{ number_format(max(1, (int) round($stagedFileSize / 1024))) }} KB)
                    </p>
                </div>
                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full bg-brand-tint text-brand-primary">
                    Ready
                </span>
            </div>
        @endif

        <div class="flex items-center justify-end gap-2 pt-1">
            <button
                type="submit"
                wire:target="prepareUpload,importFile"
                wire:loading.attr="disabled"
                @if (! $stagedFilePath && ! $importFile) disabled @endif
                class="px-4 py-2 text-xs font-semibold rounded-xl bg-brand-primary hover:bg-brand-primary-hover text-white transition inline-flex items-center gap-1.5 shadow-2xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
            >
                <span wire:loading wire:target="prepareUpload" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                <span>Stage &amp; Validate File</span>
            </button>
        </div>
    </form>
</div>
