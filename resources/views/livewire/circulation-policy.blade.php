<?php // resources/views/livewire/circulation-policies.blade.php ?>
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-6">
    {{-- Page Header --}}
    <section aria-label="Circulation policies header" class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-content-heading tracking-tight">Circulation Policies</h1>
            <p class="text-xs text-content-body mt-0.5">
                Borrowing limits apply to <span class="font-semibold text-content-heading">{{ Str::headline($studentTypeName) }}</span> patrons; damage &amp; lost charges are handled in Circulations.
            </p>
        </div>

        <button
            wire:click="openCreateModal"
            wire:loading.attr="disabled"
            wire:target="openCreateModal"
            type="button"
            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl shadow-2xs transition cursor-pointer shrink-0 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
        >
            @if ($activeTab === 'limits')
                <x-heroicon-o-pencil-square class="w-4 h-4" aria-hidden="true" />
                <span>{{ $limit ? 'Update ' . Str::headline($studentTypeName) . ' Limit' : 'Set ' . Str::headline($studentTypeName) . ' Limit' }}</span>
            @else
                <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
                <span>Add Asset Rule</span>
            @endif
        </button>
    </section>

    {{-- Quick Summary Strip --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-surface-card rounded-xl border border-surface-border p-4 flex items-center justify-between shadow-2xs">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-content-muted">LOAN LIMIT SCOPE</p>
                <p class="text-sm font-bold text-content-heading mt-0.5">{{ Str::headline($studentTypeName) }} Only</p>
            </div>
            <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full bg-brand-tint-light text-brand-primary">{{ Str::headline($studentTypeName) }} Scope</span>
        </div>

        <div class="bg-surface-card rounded-xl border border-surface-border p-4 flex items-center justify-between shadow-2xs">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-content-muted">{{ strtoupper($studentTypeName) }} MAX BORROW</p>
                <p class="text-sm font-bold text-content-heading mt-0.5">
                    {{ $limit ? $limit->max_borrow_limit . ' ' . Str::plural('Item', $limit->max_borrow_limit) : 'Not Configured' }}
                </p>
            </div>
            <span class="text-xs font-medium text-content-muted">Per {{ Str::headline($studentTypeName) }}</span>
        </div>

        <div class="bg-surface-card rounded-xl border border-surface-border p-4 flex items-center justify-between shadow-2xs">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-content-muted">{{ strtoupper($studentTypeName) }} LOAN PERIOD</p>
                <p class="text-sm font-bold text-content-heading mt-0.5">
                    {{ $limit ? $limit->loan_duration_days . ' ' . Str::plural('Day', $limit->loan_duration_days) : 'Not Configured' }}
                </p>
            </div>
            <button
                wire:click="editLimit({{ $limit?->id }})"
                type="button"
                class="text-xs font-semibold text-brand-primary hover:underline cursor-pointer rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
            >
                Edit
            </button>
        </div>
    </div>

    {{-- Tabs & Search Bar --}}
    <div class="bg-surface-card rounded-2xl border border-surface-border shadow-2xs overflow-hidden">
        <div class="px-4 sm:px-6 pt-4 border-b border-surface-border flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div role="tablist" aria-label="Circulation policy categories" class="flex gap-6">
                <button
                    wire:click="switchTab('circulation')"
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === 'circulation' ? 'true' : 'false' }}"
                    class="pb-3.5 text-xs font-semibold border-b-2 transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'circulation' ? 'border-brand-primary text-brand-primary' : 'border-transparent text-content-muted hover:text-content-heading' }}"
                >
                    Circulation Penalties
                </button>
                <button
                    wire:click="switchTab('limits')"
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === 'limits' ? 'true' : 'false' }}"
                    class="pb-3.5 text-xs font-semibold border-b-2 transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'limits' ? 'border-brand-primary text-brand-primary' : 'border-transparent text-content-muted hover:text-content-heading' }}"
                >
                    Penalty Loan Limit
                </button>
            </div>

            @if ($activeTab === 'circulation')
                <div class="pb-3 md:pb-3.5 w-full md:w-72">
                    <label for="policy-search" class="sr-only">Search asset type</label>
                    <div class="relative">
                        <input
                            id="policy-search"
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            placeholder="Search Asset Type..."
                            class="w-full pl-9 pr-8 py-1.5 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted rounded-xl border border-surface-border focus:bg-surface-card focus:outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition"
                        >
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-2 pointer-events-none" aria-hidden="true" />
                        @if ($search !== '')
                            <button
                                wire:click="$set('search', '')"
                                type="button"
                                aria-label="Clear asset type search"
                                class="absolute right-2.5 top-1.5 text-content-muted hover:text-content-heading text-xs cursor-pointer"
                            >&times;</button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- TAB 1: Circulation Penalties --}}
        @if ($activeTab === 'circulation')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-surface-border text-left">
                    <thead class="bg-surface-canvas text-[11px] font-semibold uppercase tracking-wider text-content-muted">
                        <tr>
                            <th scope="col" class="px-4 sm:px-6 py-3 whitespace-nowrap">ASSET TYPE</th>
                            <th scope="col" class="px-4 sm:px-6 py-3 whitespace-nowrap">DAILY FINE FORMULA</th>
                            <th scope="col" class="px-4 sm:px-6 py-3 whitespace-nowrap">LOST AFTER (OVERDUE)</th>
                            <th scope="col" class="px-4 sm:px-6 py-3 text-right whitespace-nowrap">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border/70 text-xs text-content-body">
                        @forelse ($policies as $policy)
                            <tr wire:key="policy-row-{{ $policy->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                                <td class="px-4 sm:px-6 py-3.5 font-semibold text-content-heading whitespace-nowrap">
                                    {{ $policy->assetType?->name ? Str::headline($policy->assetType->name) : 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 font-medium text-content-heading whitespace-nowrap">
                                    Unit Price &divide; {{ $policy->lost_after_days }} {{ Str::plural('Day', $policy->lost_after_days) }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-brand-secondary/15 text-brand-secondary">
                                        After {{ $policy->lost_after_days }} {{ Str::plural('Day', $policy->lost_after_days) }} &rarr; Lost
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            wire:click="editPolicy({{ $policy->id }})"
                                            type="button"
                                            class="px-2.5 py-1 text-xs font-semibold text-brand-primary hover:bg-brand-tint-light rounded-lg transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            wire:click="deletePolicy({{ $policy->id }})"
                                            wire:confirm="Delete this asset penalty rule?"
                                            type="button"
                                            class="px-2.5 py-1 text-xs font-semibold text-brand-secondary hover:bg-brand-secondary/10 rounded-lg transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center">
                                    <p class="text-xs font-semibold text-content-heading">No Asset Penalty Rules Found</p>
                                    <p class="text-[11px] text-content-muted mt-1">Click <span class="font-semibold text-content-body">Add Asset Rule</span> to create one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($policies->hasPages())
                <div class="px-4 sm:px-6 py-3 border-t border-surface-border bg-surface-canvas">
                    {{ $policies->links() }}
                </div>
            @endif
        @endif

        {{-- TAB 2: Penalty Loan Limit (Student Only) --}}
        @if ($activeTab === 'limits')
            <div class="p-4 sm:p-6 space-y-4">
                @if ($limit)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-surface-canvas border border-surface-border rounded-2xl p-5">
                        <div>
                            <p class="text-xs font-medium text-content-muted">Applies To</p>
                            <p class="text-base font-bold text-content-heading mt-1">{{ Str::headline($limit->patronType?->name ?? $studentTypeName) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-content-muted">Maximum Borrow Limit</p>
                            <p class="text-base font-bold text-brand-primary mt-1">{{ $limit->max_borrow_limit }} {{ Str::plural('Item', $limit->max_borrow_limit) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-content-muted">Loan Duration</p>
                            <p class="text-base font-bold text-content-heading mt-1">{{ $limit->loan_duration_days }} {{ Str::plural('Day', $limit->loan_duration_days) }}</p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-10 border border-dashed border-surface-border rounded-2xl bg-surface-canvas/50">
                        <p class="text-xs sm:text-sm font-semibold text-content-heading">No {{ Str::headline($studentTypeName) }} Loan Limit Configured Yet.</p>
                        <p class="text-xs text-content-muted mt-1">Set the maximum borrow count and loan duration for {{ Str::headline($studentTypeName) }} patrons.</p>
                        <button
                            wire:click="editLimit()"
                            type="button"
                            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl cursor-pointer shadow-2xs transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
                        >
                            Set {{ Str::headline($studentTypeName) }} Loan Limit
                        </button>
                    </div>
                @endif

                <p class="text-xs text-content-muted">
                    Teachers are exempt from due-date loan limits; minor damage, severe damage, and lost items are handled directly in Circulations.
                </p>
            </div>
        @endif
    </div>

    {{-- Penalty Policy Modal --}}
    @if ($showModal)
        <div
            @keydown.escape.window="$wire.closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="policy-modal-title"
        >
            <div wire:click="closeModal" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-lg z-10 overflow-hidden border border-surface-border">
                <div class="px-6 py-4 bg-surface-canvas border-b border-surface-border flex items-center justify-between">
                    <h2 id="policy-modal-title" class="text-sm font-bold text-content-heading">
                        {{ $isEditing ? 'Edit Asset Rule' : 'Create Asset Rule' }}
                    </h2>
                    <button
                        wire:click="closeModal"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-xl font-bold leading-none cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="policy-patron-type" class="block text-xs font-semibold text-content-heading mb-1">Patron Type</label>
                            <input
                                id="policy-patron-type"
                                type="text"
                                value="{{ Str::headline($studentTypeName) }}"
                                disabled
                                class="w-full text-xs rounded-xl border border-surface-border bg-surface-canvas text-content-muted px-3 py-2 cursor-not-allowed"
                            >
                        </div>

                        <div>
                            <label for="policy-asset-type" class="block text-xs font-semibold text-content-heading mb-1">
                                Asset Type <span class="text-brand-secondary">*</span>
                            </label>
                            <select
                                id="policy-asset-type"
                                wire:model="asset_type_id"
                                class="w-full text-xs rounded-xl border border-surface-border px-3 py-2 bg-surface-canvas text-content-heading focus:bg-surface-card focus:outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition"
                            >
                                <option value="">Select Asset Type</option>
                                @foreach ($assetTypes as $type)
                                    <option value="{{ $type->id }}">{{ Str::headline($type->name) }}</option>
                                @endforeach
                            </select>
                            @error('asset_type_id') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="policy-lost-after-days" class="block text-xs font-semibold text-content-heading mb-1">
                            Lost After (Overdue Days) <span class="text-brand-secondary">*</span>
                        </label>
                        <input
                            id="policy-lost-after-days"
                            wire:model="lost_after_days"
                            type="number"
                            min="1"
                            max="365"
                            class="w-full text-xs font-mono rounded-xl border border-surface-border px-3 py-2 bg-surface-canvas text-content-heading focus:bg-surface-card focus:outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition"
                        >
                        <p class="text-[11px] text-content-muted mt-1">
                            Daily fine equals <span class="font-medium text-content-heading">Item Unit Price &divide; Lost After Days</span>. Passing this overdue threshold marks the item as <span class="font-medium text-content-heading">Lost</span>.
                        </p>
                        @error('lost_after_days') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-surface-border/70 flex flex-col-reverse sm:flex-row justify-end gap-2.5">
                        <button
                            wire:click="closeModal"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl cursor-pointer transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl cursor-pointer disabled:opacity-50 transition-colors shadow-2xs"
                        >
                            <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Rule' : 'Save Rule' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Student Loan Limit Modal --}}
    @if ($showLimitModal)
        <div
            @keydown.escape.window="$wire.closeLimitModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="limit-modal-title"
        >
            <div wire:click="closeLimitModal" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-md z-10 overflow-hidden border border-surface-border">
                <div class="px-6 py-4 bg-surface-canvas border-b border-surface-border flex items-center justify-between">
                    <h2 id="limit-modal-title" class="text-sm font-bold text-content-heading">
                        {{ $isEditing ? 'Update ' . Str::headline($studentTypeName) . ' Loan Limit' : 'Set ' . Str::headline($studentTypeName) . ' Loan Limit' }}
                    </h2>
                    <button
                        wire:click="closeLimitModal"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-xl font-bold leading-none cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label for="limit-patron-type" class="block text-xs font-semibold text-content-heading mb-1">Patron Type</label>
                        <input
                            id="limit-patron-type"
                            type="text"
                            value="{{ Str::headline($studentTypeName) }}"
                            disabled
                            class="w-full text-xs rounded-xl border border-surface-border bg-surface-canvas text-content-muted px-3 py-2 cursor-not-allowed"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="limit-max-borrow" class="block text-xs font-semibold text-content-heading mb-1">
                                Max Borrow Limit <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="limit-max-borrow"
                                wire:model="max_borrow_limit"
                                type="number"
                                min="1"
                                max="20"
                                class="w-full text-xs font-mono rounded-xl border border-surface-border px-3 py-2 bg-surface-canvas text-content-heading focus:bg-surface-card focus:outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition"
                            >
                            @error('max_borrow_limit') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="limit-loan-duration" class="block text-xs font-semibold text-content-heading mb-1">
                                Loan Duration (Days) <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="limit-loan-duration"
                                wire:model="loan_duration_days"
                                type="number"
                                min="1"
                                max="60"
                                class="w-full text-xs font-mono rounded-xl border border-surface-border px-3 py-2 bg-surface-canvas text-content-heading focus:bg-surface-card focus:outline-none focus:ring-4 focus:ring-brand-primary/15 focus:border-brand-primary transition"
                            >
                            @error('loan_duration_days') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-3 border-t border-surface-border/70 flex flex-col-reverse sm:flex-row justify-end gap-2.5">
                        <button
                            wire:click="closeLimitModal"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl cursor-pointer transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl cursor-pointer disabled:opacity-50 transition-colors shadow-2xs"
                        >
                            <span wire:loading.remove wire:target="save">Save {{ Str::headline($studentTypeName) }} Limit</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
