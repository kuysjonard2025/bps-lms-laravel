<?php // resources/views/livewire/registrations.blade.php ?>
<div class="space-y-6">
    <section aria-label="Users and patrons registration header" class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-content-heading tracking-tight">Users &amp; Patrons Registration</h1>
            <p class="text-xs text-content-body mt-0.5">Manage Library Assistant Staff And Patron Registrations.</p>
        </div>

        <div role="tablist" aria-label="Registration categories" class="flex items-center space-x-1.5 bg-surface-canvas p-1 rounded-xl border border-surface-border w-fit">
            <button
                type="button"
                role="tab"
                aria-selected="{{ $activeTab === 'users' ? 'true' : 'false' }}"
                wire:click="$set('activeTab', 'users')"
                class="px-4 py-2 text-xs font-semibold rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'users' ? 'bg-brand-primary text-white shadow-2xs' : 'text-content-body hover:text-content-heading' }}"
            >
                Assistant Users
            </button>
            <button
                type="button"
                role="tab"
                aria-selected="{{ $activeTab === 'patrons' ? 'true' : 'false' }}"
                wire:click="$set('activeTab', 'patrons')"
                class="px-4 py-2 text-xs font-semibold rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'patrons' ? 'bg-brand-primary text-white shadow-2xs' : 'text-content-body hover:text-content-heading' }}"
            >
                Patrons
            </button>
        </div>
    </section>

    @if($activeTab === 'users')
        <livewire:registrations.assistant-users-tab wire:key="registrations-users-tab" />
    @else
        <livewire:registrations.patrons-tab wire:key="registrations-patrons-tab" />
    @endif
</div>
