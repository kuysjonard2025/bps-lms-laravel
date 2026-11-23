{{-- resources/views/livewire/components/header.blade.php --}}
@php
    use Illuminate\Support\Str;

    $authUser = auth()->user();
    $displayFullName = $authUser
        ? Str::title(trim(($authUser->first_name ?? '') . ' ' . ($authUser->last_name ?? '')))
        : '';
@endphp

<div>
    <header class="bg-surface-card border-b border-surface-border px-3 sm:px-6 py-3 lg:py-3.5 flex items-center justify-between gap-4 shadow-2xs sticky top-0 z-30">
        {{-- Left Section: Navigation Toggle & Brand --}}
        <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
            {{-- Mobile/Tablet Sidebar Toggle Button --}}
            <button
                @click="sidebarOpen = !sidebarOpen"
                type="button"
                class="lg:hidden p-2 rounded-xl text-content-body hover:bg-surface-canvas hover:text-content-heading focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary cursor-pointer shrink-0 transition-colors"
                aria-label="Toggle Navigation"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Brand & Logo Link --}}
            <a wire:navigate href="{{ route('dashboard') }}" class="flex items-center gap-2.5 sm:gap-3 cursor-pointer hover:opacity-85 transition-opacity min-w-0">
                <img src="{{ asset('images/bps-logo.png') }}" alt="BPS Logo" class="w-9 h-9 sm:w-10 sm:h-10 lg:w-11 lg:h-11 object-contain shrink-0">
                <div class="flex flex-col min-w-0">
                    <span class="text-sm sm:text-base lg:text-lg font-bold tracking-tight text-brand-primary leading-tight whitespace-nowrap">
                        <span class="inline sm:hidden">BPS LMS</span>
                        <span class="hidden sm:inline">Bicutan Parochial School, Inc.</span>
                    </span>
                    <p class="text-[10px] sm:text-xs text-content-body font-medium truncate">Library Management System</p>
                </div>
            </a>
        </div>

        {{-- Right Section: User Profile & Actions --}}
        <div class="flex items-center gap-1.5 sm:gap-3 lg:gap-4 shrink-0">
            <button
                wire:click="openProfileModal('profile')"
                wire:loading.attr="disabled"
                wire:target="openProfileModal"
                type="button"
                class="flex items-center gap-2.5 px-2 py-1 rounded-xl text-sm font-medium text-content-body hover:text-content-heading hover:bg-surface-canvas border border-transparent hover:border-surface-border transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
            >
                <span class="w-8 h-8 rounded-full bg-brand-tint-light text-brand-primary flex items-center justify-center font-bold text-xs shrink-0 ring-2 ring-brand-primary/25">
                    {{ $authUser?->initials() ?? 'U' }}
                </span>

                {{-- Visible on larger screens --}}
                <div class="hidden md:flex items-center gap-2 whitespace-nowrap text-xs lg:text-sm">
                    <span class="font-semibold text-content-heading">{{ $displayFullName }}</span>
                    <span class="capitalize bg-brand-tint-light text-brand-primary px-2.5 py-0.5 rounded-full text-[11px] font-semibold">
                        {{ str($authUser?->role ?? 'user')->replace('_', ' ') }}
                    </span>
                </div>
            </button>

            {{-- Logout Button --}}
            <button
                wire:click="logout"
                wire:loading.attr="disabled"
                wire:target="logout"
                type="button"
                class="text-xs text-brand-secondary hover:bg-brand-secondary/10 font-semibold px-2.5 sm:px-3 py-1.5 rounded-xl border border-brand-secondary/30 transition-colors cursor-pointer whitespace-nowrap disabled:opacity-50 flex items-center gap-1.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
            >
                <span wire:loading.remove wire:target="logout" class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hidden sm:inline">Log Out</span>
                </span>
                <span wire:loading wire:target="logout">Logging out...</span>
            </button>
        </div>
    </header>

    {{-- Profile Modal Teleported to Body so Header & Sidebar Sit Behind the Backdrop --}}
    @if ($showProfileModal)
        <template x-teleport="body">
            <div
                role="dialog"
                aria-modal="true"
                aria-label="Account settings"
                @keydown.escape.window="$wire.closeProfileModal()"
                style="z-index: 999;"
                class="fixed inset-0 z-[999] flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            >
                {{-- Full-Screen Backdrop --}}
                <div
                    wire:click="closeProfileModal"
                    style="z-index: 0;"
                    class="fixed inset-0 z-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"
                ></div>

                {{-- Modal Body --}}
                <div style="z-index: 10;" class="relative bg-surface-card rounded-2xl border border-surface-border shadow-xl w-full max-w-lg z-10 overflow-hidden my-auto max-h-[90dvh] flex flex-col">

                    {{-- Modal Header --}}
                    <div class="bg-surface-canvas px-4 sm:px-6 py-3.5 border-b border-surface-border flex justify-between items-center gap-3 shrink-0">
                        <div role="tablist" aria-label="Profile sections" class="flex flex-wrap gap-4">
                            <button
                                wire:click="$set('activeTab', 'profile')"
                                type="button"
                                role="tab"
                                aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}"
                                class="text-xs sm:text-sm font-semibold pb-1 border-b-2 transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'profile' ? 'border-brand-primary text-brand-primary' : 'border-transparent text-content-body hover:text-content-heading' }}"
                            >
                                Profile Details
                            </button>
                            <button
                                wire:click="$set('activeTab', 'password')"
                                type="button"
                                role="tab"
                                aria-selected="{{ $activeTab === 'password' ? 'true' : 'false' }}"
                                class="text-xs sm:text-sm font-semibold pb-1 border-b-2 transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === 'password' ? 'border-brand-primary text-brand-primary' : 'border-transparent text-content-body hover:text-content-heading' }}"
                            >
                                Change Password
                            </button>
                        </div>
                        <button
                            wire:click="closeProfileModal"
                            type="button"
                            aria-label="Close modal"
                            class="text-content-muted hover:text-content-heading text-2xl font-bold leading-none p-1 cursor-pointer rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary shrink-0"
                        >&times;</button>
                    </div>

                    {{-- TAB 1: Edit Profile --}}
                    @if ($activeTab === 'profile')
                        <form wire:submit="updateProfile" class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">

                            {{-- Name Row: First, Middle, Last --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label for="profile_first_name" class="block text-xs font-medium text-content-heading">First Name *</label>
                                    <input id="profile_first_name" type="text" wire:model="first_name" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                    @error('first_name') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="profile_middle_name" class="block text-xs font-medium text-content-heading">Middle Name <span class="text-content-muted font-normal">(Optional)</span></label>
                                    <input id="profile_middle_name" type="text" wire:model="middle_name" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                    @error('middle_name') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="profile_last_name" class="block text-xs font-medium text-content-heading">Last Name *</label>
                                    <input id="profile_last_name" type="text" wire:model="last_name" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                    @error('last_name') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Suffix & Contact Number --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="profile_suffix" class="block text-xs font-medium text-content-heading">Suffix <span class="text-content-muted font-normal">(Optional)</span></label>
                                    <input id="profile_suffix" type="text" wire:model="suffix" placeholder="e.g. Jr., III" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                    @error('suffix') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="profile_contact_number" class="block text-xs font-medium text-content-heading">Contact Number *</label>
                                    <input id="profile_contact_number" type="text" wire:model="contact_number" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                    @error('contact_number') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Account & Role --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="profile_username" class="block text-xs font-medium text-content-heading">Username *</label>
                                    <input id="profile_username" type="text" wire:model="username" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                    @error('username') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="profile_role" class="block text-xs font-medium text-content-heading">User Role</label>
                                    <input id="profile_role" type="text" wire:model="role" disabled class="mt-1 w-full text-sm rounded-xl border border-surface-border bg-surface-canvas/70 text-content-muted px-3.5 py-2 cursor-not-allowed">
                                </div>
                            </div>

                            {{-- Email Address --}}
                            <div>
                                <label for="profile_email" class="block text-xs font-medium text-content-heading">Email Address *</label>
                                <input id="profile_email" type="email" wire:model="email" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                @error('email') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Address --}}
                            <div>
                                <label for="profile_address" class="block text-xs font-medium text-content-heading">Address *</label>
                                <input id="profile_address" type="text" wire:model="address" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                @error('address') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            <hr class="my-3 border-surface-border">

                            <div class="bg-brand-tint-light p-3.5 rounded-xl border border-brand-tint">
                                <label for="profile_current_password" class="block text-xs font-semibold text-content-heading">Current Password (To confirm changes) *</label>
                                <input id="profile_current_password" type="password" wire:model="current_password_for_profile" class="mt-1.5 w-full text-sm rounded-xl border border-surface-border bg-surface-card text-content-heading px-3.5 py-2 focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                @error('current_password_for_profile') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-2 flex justify-end gap-2.5">
                                <button wire:click="closeProfileModal" type="button" class="px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border rounded-xl hover:text-content-heading cursor-pointer transition-colors">Cancel</button>
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="updateProfile"
                                    class="px-4 py-2 text-xs font-semibold text-white bg-brand-primary rounded-xl hover:bg-brand-primary-hover cursor-pointer disabled:opacity-50 transition-colors"
                                >
                                    <span wire:loading.remove wire:target="updateProfile">Save Changes</span>
                                    <span wire:loading wire:target="updateProfile">Saving...</span>
                                </button>
                            </div>
                        </form>
                    @endif

                    {{-- TAB 2: Change Password --}}
                    @if ($activeTab === 'password')
                        <form wire:submit="updatePassword" class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                            <div>
                                <label for="pw_current_password" class="block text-xs font-medium text-content-heading">Current Password *</label>
                                <input id="pw_current_password" type="password" wire:model="current_password" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                @error('current_password') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="pw_new_password" class="block text-xs font-medium text-content-heading">New Password *</label>
                                <input id="pw_new_password" type="password" wire:model="new_password" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                                @error('new_password') <span class="text-[11px] text-brand-secondary mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="pw_new_password_confirmation" class="block text-xs font-medium text-content-heading">Confirm New Password *</label>
                                <input id="pw_new_password_confirmation" type="password" wire:model="new_password_confirmation" class="mt-1 w-full text-sm rounded-xl bg-surface-canvas text-content-heading border border-surface-border px-3.5 py-2 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition">
                            </div>

                            <div class="pt-2 flex justify-end gap-2.5">
                                <button wire:click="closeProfileModal" type="button" class="px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border rounded-xl hover:text-content-heading cursor-pointer transition-colors">Cancel</button>
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="updatePassword"
                                    class="px-4 py-2 text-xs font-semibold text-white bg-brand-primary rounded-xl hover:bg-brand-primary-hover cursor-pointer disabled:opacity-50 transition-colors"
                                >
                                    <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                                    <span wire:loading wire:target="updatePassword">Updating...</span>
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </template>
    @endif
</div>
