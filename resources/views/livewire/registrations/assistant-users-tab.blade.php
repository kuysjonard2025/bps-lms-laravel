<?php // resources/views/livewire/registrations/assistant-users-tab.blade.php ?>
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Auth;
@endphp

<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
            <label for="au-search" class="sr-only">Search assistant users</label>
            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
            <input
                id="au-search"
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search Users..."
                class="w-full pl-9 pr-8 py-2 bg-surface-card border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
            >
            @if($search !== '')
                <button
                    wire:click="$set('search', '')"
                    type="button"
                    class="absolute right-2.5 top-2 text-content-muted hover:text-content-heading text-xs cursor-pointer"
                    aria-label="Clear search"
                >&times;</button>
            @endif
        </div>

        <button
            type="button"
            wire:click="openCreateUserModal"
            wire:loading.attr="disabled"
            wire:target="openCreateUserModal"
            class="w-full sm:w-auto px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer shrink-0"
        >
            <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
            <span>Register Assistant</span>
        </button>
    </div>

    <div class="bg-surface-card shadow-2xs rounded-2xl border border-surface-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs text-content-body">
                <thead class="bg-surface-canvas border-b border-surface-border text-[11px] uppercase font-semibold tracking-wider text-content-muted">
                    <tr>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">FULL NAME</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">USERNAME</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">ROLE</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">CONTACT #</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">EMAIL</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">ADDRESS</th>
                        <th scope="col" class="px-4 py-3 whitespace-nowrap">IS VERIFIED</th>
                        <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border/70">
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-surface-canvas/70 transition-colors whitespace-nowrap">
                            <td class="px-4 py-3 font-semibold text-content-heading">
                                {{ Str::title(trim("{$user->last_name}, {$user->first_name} {$user->middle_name} {$user->suffix}")) }}
                            </td>
                            <td class="px-4 py-3 font-mono text-brand-primary font-semibold">{{ $user->username }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-tint-light text-brand-primary uppercase">
                                    {{ strtoupper($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-content-heading font-medium">{{ $user->contact_number }}</td>
                            <td class="px-4 py-3 text-content-heading font-medium">{{ $user->email ?: 'N/A' }}</td>
                            <td class="px-4 py-3 max-w-xs truncate" title="{{ $user->address ? Str::title($user->address) : 'N/A' }}">
                                {{ $user->address ? Str::title($user->address) : 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-tint-light text-brand-primary uppercase">VERIFIED</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-secondary/15 text-brand-secondary uppercase">UNVERIFIED</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <button
                                    type="button"
                                    wire:click="openEditUserModal({{ $user->id }})"
                                    class="text-brand-primary hover:bg-brand-tint-light font-semibold text-xs px-2.5 py-1.5 rounded-lg transition cursor-pointer"
                                >Edit</button>
                                @if(Auth::id() !== $user->id)
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $user->id }})"
                                        class="text-brand-secondary hover:bg-brand-secondary/10 font-semibold text-xs px-2.5 py-1.5 rounded-lg transition cursor-pointer"
                                    >Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center">
                                <p class="text-xs font-semibold text-content-heading">No Assistant Users Found</p>
                                <p class="text-[11px] text-content-muted mt-1">Try adjusting your search query or register a new assistant.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-t border-surface-border bg-surface-canvas">
            {{ $users->links() }}
        </div>
    </div>

    @if($showUserModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.set('showUserModal', false)"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="user-modal-title"
        >
            <div wire:click="$set('showUserModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl max-w-2xl w-full p-5 sm:p-6 border border-surface-border space-y-4 z-10 max-h-[90dvh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-surface-border pb-3">
                    <h2 id="user-modal-title" class="text-sm sm:text-base font-bold text-content-heading">
                        {{ $userIdBeingEdited ? 'Edit System User' : 'Register New System User' }}
                    </h2>
                    <button type="button" wire:click="$set('showUserModal', false)" aria-label="Close modal" class="text-content-muted hover:text-content-heading p-1 rounded-lg cursor-pointer">
                        <x-heroicon-o-x-mark class="w-5 h-5" aria-hidden="true" />
                    </button>
                </div>

                <form wire:submit="saveUser" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="u_first_name" class="block text-xs font-semibold text-content-heading mb-1">First Name <span class="text-brand-secondary">*</span></label>
                            <input id="u_first_name" type="text" wire:model="u_first_name" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('u_first_name') border-brand-secondary @else border-surface-border @enderror">
                            @error('u_first_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_middle_name" class="block text-xs font-semibold text-content-heading mb-1">Middle Name</label>
                            <input id="u_middle_name" type="text" wire:model="u_middle_name" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('u_middle_name') border-brand-secondary @else border-surface-border @enderror">
                            @error('u_middle_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_last_name" class="block text-xs font-semibold text-content-heading mb-1">Last Name <span class="text-brand-secondary">*</span></label>
                            <input id="u_last_name" type="text" wire:model="u_last_name" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('u_last_name') border-brand-secondary @else border-surface-border @enderror">
                            @error('u_last_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="u_suffix" class="block text-xs font-semibold text-content-heading mb-1">Suffix</label>
                            <input id="u_suffix" type="text" wire:model="u_suffix" placeholder="Jr., III" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('u_suffix') border-brand-secondary @else border-surface-border @enderror">
                            @error('u_suffix') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_username" class="block text-xs font-semibold text-content-heading mb-1">Username <span class="text-brand-secondary">*</span></label>
                            <input id="u_username" type="text" wire:model="u_username" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('u_username') border-brand-secondary @else border-surface-border @enderror">
                            @error('u_username') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_password" class="block text-xs font-semibold text-content-heading mb-1">Password {{ $userIdBeingEdited ? '(Leave Blank To Keep)' : '*' }}</label>
                            <input id="u_password" type="password" wire:model="u_password" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('u_password') border-brand-secondary @else border-surface-border @enderror">
                            @error('u_password') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="u_email" class="block text-xs font-semibold text-content-heading mb-1">Email Address <span class="text-brand-secondary">*</span></label>
                            <input id="u_email" type="email" wire:model="u_email" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('u_email') border-brand-secondary @else border-surface-border @enderror">
                            @error('u_email') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_contact_number" class="block text-xs font-semibold text-content-heading mb-1">Contact Number <span class="text-brand-secondary">*</span></label>
                            <input id="u_contact_number" type="text" wire:model="u_contact_number" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs font-mono text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('u_contact_number') border-brand-secondary @else border-surface-border @enderror">
                            @error('u_contact_number') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="u_address" class="block text-xs font-semibold text-content-heading mb-1">Home Address <span class="text-brand-secondary">*</span></label>
                        <input id="u_address" type="text" wire:model="u_address" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('u_address') border-brand-secondary @else border-surface-border @enderror">
                        @error('u_address') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-surface-border/70">
                        <button type="button" wire:click="$set('showUserModal', false)" class="w-full sm:w-auto px-4 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-semibold text-content-body hover:text-content-heading cursor-pointer">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveUser" class="w-full sm:w-auto px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <span wire:loading wire:target="saveUser" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                            <span>Save User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.set('showDeleteModal', false)"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="au-delete-modal-title"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card rounded-2xl shadow-xl max-w-sm w-full p-6 border border-surface-border space-y-4 z-10 text-center">
                <div class="w-12 h-12 bg-brand-secondary/15 text-brand-secondary rounded-full flex items-center justify-center mx-auto">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>
                <div>
                    <h2 id="au-delete-modal-title" class="text-sm sm:text-base font-bold text-content-heading">Confirm Deletion</h2>
                    <p class="text-xs text-content-body mt-1">Are You Sure You Want To Delete This Assistant User Record? This Action Cannot Be Undone.</p>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="w-full sm:w-auto px-4 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-semibold text-content-body hover:text-content-heading cursor-pointer">Cancel</button>
                    <button type="button" wire:click="deleteRecord" wire:loading.attr="disabled" wire:target="deleteRecord" class="w-full sm:w-auto px-4 py-2 bg-brand-secondary rounded-xl text-xs font-semibold text-white hover:opacity-90 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                        <span wire:loading wire:target="deleteRecord" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
