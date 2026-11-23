<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-content-heading">Active Sessions</h1>
            <p class="text-xs text-content-body mt-0.5">
                View active signed-in sessions across accounts and log out other devices.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <div class="w-full sm:w-64">
                <label for="active-sessions-search" class="sr-only">Search active sessions</label>
                <input
                    id="active-sessions-search"
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search user, email, or IP..."
                    class="w-full rounded-xl border border-surface-border bg-surface-card px-3.5 py-2 text-xs text-content-heading placeholder:text-content-muted focus:border-brand-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/20"
                >
            </div>

            @if (collect($sessions)->where('is_current', false)->isNotEmpty())
                <button
                    type="button"
                    wire:click="openLogoutOthersModal"
                    class="px-3.5 py-2 text-xs font-semibold text-white bg-brand-secondary hover:opacity-90 rounded-xl shadow-2xs transition cursor-pointer shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary/30"
                >
                    Log out all other devices
                </button>
            @endif
        </div>
    </div>

    <div wire:loading.flex wire:target="search,revokeSession,logoutOtherDevices" class="items-center gap-2 text-xs font-semibold text-brand-primary">
        <span class="animate-spin h-3.5 w-3.5 border-2 border-brand-primary border-t-transparent rounded-full"></span>
        <span>Updating active sessions...</span>
    </div>

    <div class="rounded-2xl border border-surface-border bg-surface-card overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs text-content-body">
                <thead class="bg-surface-canvas text-content-muted uppercase tracking-wider text-[11px] border-b border-surface-border">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-semibold">User</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Device (Browser / OS)</th>
                        <th scope="col" class="px-4 py-3 font-semibold">IP Address</th>
                        <th scope="col" class="px-4 py-3 font-semibold">Last Active</th>
                        <th scope="col" class="px-4 py-3 font-semibold text-right">Session</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border/70 bg-surface-card">
                    @forelse ($sessions as $session)
                        <tr class="hover:bg-surface-canvas/60 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-semibold text-content-heading">
                                    {{ $session['user_name'] }}
                                </div>
                                <div class="text-[11px] text-content-muted">
                                    {{ $session['user_role'] }}@if ($session['user_email']) &bull; {{ $session['user_email'] }}@endif
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium text-content-heading whitespace-nowrap">
                                <span>{{ $session['browser'] }}</span>
                                <span class="text-content-muted">on</span>
                                <span>{{ $session['os'] }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-content-body whitespace-nowrap">
                                {{ $session['ip_address'] }}
                            </td>
                            <td class="px-4 py-3 text-content-body whitespace-nowrap">
                                {{ $session['last_activity_human'] }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if ($session['is_current'])
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-brand-tint-light text-brand-primary">
                                        This device
                                    </span>
                                @else
                                    <button
                                        type="button"
                                        wire:click="revokeSession('{{ $session['id'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="revokeSession('{{ $session['id'] }}')"
                                        class="px-3 py-1.5 text-xs font-semibold text-white bg-brand-secondary hover:opacity-90 rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary/30"
                                    >
                                        Log out
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-content-muted">
                                No active sessions found matching your current view.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showLogoutOthersModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="logout-others-title"
        >
            <div class="w-full max-w-md rounded-2xl border border-surface-border bg-surface-card p-5 sm:p-6 shadow-lg space-y-4">
                <div>
                    <h2 id="logout-others-title" class="text-base font-semibold text-content-heading">
                        Log out all other devices
                    </h2>
                    <p class="text-xs text-content-body mt-1">
                        Enter your current password to end all other active sessions while keeping this session signed in.
                    </p>
                </div>

                <form wire:submit="logoutOtherDevices" class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-content-heading mb-1.5">
                            Current Password
                        </label>
                        <input
                            id="current_password"
                            type="password"
                            wire:model="currentPassword"
                            autocomplete="current-password"
                            class="w-full rounded-xl border border-surface-border bg-surface-canvas px-3.5 py-2 text-xs text-content-heading focus:border-brand-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/20"
                            placeholder="Enter current password"
                        >
                        @error('currentPassword')
                            <p class="mt-1.5 text-xs text-brand-secondary">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button
                            type="button"
                            wire:click="closeLogoutOthersModal"
                            class="px-3.5 py-2 text-xs font-medium rounded-xl border border-surface-border bg-surface-card hover:bg-surface-canvas text-content-heading transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="logoutOtherDevices"
                            class="px-3.5 py-2 text-xs font-semibold text-white bg-brand-secondary hover:opacity-90 rounded-xl shadow-2xs transition cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="logoutOtherDevices">Log out other devices</span>
                            <span wire:loading wire:target="logoutOtherDevices">Logging out...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
