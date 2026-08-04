<div x-data="{
    toasts: [],
    add(message, type = 'success') {
        const id = Date.now();
        this.toasts.push({ id, message, type });
        setTimeout(() => this.remove(id), 4000);
    },
    remove(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    }
}" @toast.window="add($event.detail.message, $event.detail.type)">

    <header class="bg-white border-b border-gray-200 px-3 sm:px-6 py-3 lg:py-4 flex items-center justify-between shadow-xs sticky top-0 z-30">
        {{-- Left Section: Navigation Toggle & Brand --}}
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            {{-- Mobile/Tablet Sidebar Toggle Button --}}
            <button
                @click="sidebarOpen = !sidebarOpen"
                type="button"
                class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer shrink-0"
                aria-label="Toggle Navigation"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Brand & Logo Link --}}
            <a wire:navigate href="{{ route('dashboard') }}" class="flex items-center gap-2.5 sm:gap-3 cursor-pointer hover:opacity-80 transition-opacity min-w-0">
                <img src="{{ asset('images/bps-logo.png') }}" alt="BPS Logo" class="w-9 h-9 sm:w-10 sm:h-10 lg:w-12 lg:h-12 object-contain shrink-0">
                <div class="flex flex-col min-w-0">
                    <span class="text-sm sm:text-base lg:text-lg font-bold tracking-tight text-blue-600 leading-tight truncate">
                        <span class="inline sm:hidden">BPS LMS</span>
                        <span class="hidden sm:inline">Bicutan Parochial School, Inc.</span>
                    </span>
                    <p class="text-[10px] sm:text-xs text-gray-500 font-medium truncate">Library Management System</p>
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
                class="flex items-center gap-2 p-1 rounded-lg text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0 ring-2 ring-blue-500/20">
                    {{ auth()->user()->initials() }}
                </span>

                {{-- Visible on larger screens --}}
                <div class="hidden md:flex items-center gap-2 whitespace-nowrap text-xs lg:text-sm">
                    <span class="font-semibold text-gray-800">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                    <span class="capitalize bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-[11px] font-semibold">
                        {{ str(auth()->user()->role)->replace('_', ' ') }}
                    </span>
                </div>
            </button>

            {{-- Logout Button --}}
            <button
                wire:click="logout"
                wire:loading.attr="disabled"
                wire:target="logout"
                type="button"
                class="text-xs text-red-600 hover:text-red-700 hover:bg-red-50 font-semibold px-2.5 sm:px-3 py-1.5 rounded-lg border border-red-200 transition-colors cursor-pointer whitespace-nowrap disabled:opacity-50 flex items-center gap-1"
            >
                <span wire:loading.remove wire:target="logout" class="flex items-center gap-1">
                    <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="hidden sm:inline">Log Out</span>
                </span>
                <span wire:loading wire:target="logout">Logging out...</span>
            </button>
        </div>
    </header>

    {{-- Toast Notification Container --}}
    <div class="fixed top-4 inset-x-4 sm:inset-x-auto sm:right-5 z-60 flex flex-col gap-2 max-w-sm w-full pointer-events-none mx-auto sm:mx-0">
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="true"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="pointer-events-auto p-3.5 sm:p-4 rounded-xl shadow-xl text-xs sm:text-sm font-medium flex items-center justify-between gap-3 text-white border border-white/10"
                :class="toast.type === 'error' ? 'bg-red-600' : 'bg-emerald-600'"
            >
                <span x-text="toast.message" class="leading-snug"></span>
                <button @click="remove(toast.id)" class="text-white/80 hover:text-white text-lg font-bold p-1 cursor-pointer shrink-0 leading-none">&times;</button>
            </div>
        </template>
    </div>

    {{-- Profile Modal Container --}}
    @if ($showProfileModal)
        <div
            @keydown.escape.window="$wire.closeProfileModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        >
            {{-- Backdrop --}}
            <div
                wire:click="closeProfileModal"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
            ></div>

            {{-- Modal Body --}}
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden my-auto max-h-[90dvh] flex flex-col">

                {{-- Modal Header --}}
                <div class="bg-gray-50 px-4 sm:px-6 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <div class="flex gap-4">
                        <button
                            wire:click="$set('activeTab', 'profile')"
                            type="button"
                            class="text-xs sm:text-sm font-semibold pb-1 border-b-2 transition-colors cursor-pointer {{ $activeTab === 'profile' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                        >
                            Profile Details
                        </button>
                        <button
                            wire:click="$set('activeTab', 'password')"
                            type="button"
                            class="text-xs sm:text-sm font-semibold pb-1 border-b-2 transition-colors cursor-pointer {{ $activeTab === 'password' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                        >
                            Change Password
                        </button>
                    </div>
                    <button wire:click="closeProfileModal" type="button" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none p-1 cursor-pointer">&times;</button>
                </div>

                {{-- TAB 1: Edit Profile --}}
                @if ($activeTab === 'profile')
                    <form wire:submit="updateProfile" class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Suffix</label>
                                <input type="text" wire:model="suffix" placeholder="e.g. Jr., III" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('suffix') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700">First Name *</label>
                                <input type="text" wire:model="first_name" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('first_name') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Middle Name *</label>
                                <input type="text" wire:model="middle_name" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('middle_name') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Last Name *</label>
                                <input type="text" wire:model="last_name" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('last_name') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Username *</label>
                                <input type="text" wire:model="username" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('username') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">User Role</label>
                                <input type="text" wire:model="role" disabled class="mt-1 w-full text-sm rounded-lg border-gray-200 bg-gray-100 text-gray-500 shadow-xs border p-2 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Contact Number *</label>
                                <input type="text" wire:model="contact_number" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                @error('contact_number') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Email Address *</label>
                            <input type="email" wire:model="email" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('email') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Address *</label>
                            <input type="text" wire:model="address" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('address') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <hr class="my-3 border-gray-200">

                        <div class="bg-blue-50 p-3 rounded-xl border border-blue-200">
                            <label class="block text-xs font-medium text-blue-900">Current Password (To confirm changes) *</label>
                            <input type="password" wire:model="current_password_for_profile" class="mt-1 w-full text-sm rounded-lg border-gray-300 bg-white shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('current_password_for_profile') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2 flex justify-end gap-2.5">
                            <button wire:click="closeProfileModal" type="button" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 cursor-pointer transition-colors">Cancel</button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="updateProfile"
                                class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 cursor-pointer disabled:opacity-50 transition-colors"
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
                            <label class="block text-xs font-medium text-gray-700">Current Password *</label>
                            <input type="password" wire:model="current_password" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('current_password') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">New Password *</label>
                            <input type="password" wire:model="new_password" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            @error('new_password') <span class="text-[11px] text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Confirm New Password *</label>
                            <input type="password" wire:model="new_password_confirmation" class="mt-1 w-full text-sm rounded-lg border-gray-300 shadow-xs border p-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>

                        <div class="pt-2 flex justify-end gap-2.5">
                            <button wire:click="closeProfileModal" type="button" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 cursor-pointer transition-colors">Cancel</button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="updatePassword"
                                class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 cursor-pointer disabled:opacity-50 transition-colors"
                            >
                                <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                                <span wire:loading wire:target="updatePassword">Updating...</span>
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>
    @endif
</div>
