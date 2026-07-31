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

    <header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-4 flex items-center justify-between shadow-xs sticky top-0 z-30">
        <div class="flex items-center gap-3">
            {{-- Mobile/Tablet Sidebar Toggle Button --}}
            <button
                @click="sidebarOpen = !sidebarOpen"
                type="button"
                class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none cursor-pointer"
                aria-label="Toggle Navigation"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Brand & Logo Link --}}
            <a wire:navigate href="{{ route('dashboard') }}" class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition-opacity">
                <img src="{{ asset('images/bps-logo.png') }}" alt="BPS Logo" class="w-10 h-10 lg:w-12 lg:h-12 object-contain shrink-0">
                <div class="flex flex-col">
                    <span class="text-base lg:text-lg font-bold tracking-tight text-blue-600 leading-tight">Bicutan Parochial School, Inc.</span>
                    <p class="text-xs text-gray-500 font-medium">Library Management System</p>
                </div>
            </a>
        </div>

        <div class="flex items-center gap-2 lg:gap-4">
            <button
                wire:click="openProfileModal('profile')"
                wire:loading.attr="disabled"
                wire:target="openProfileModal"
                type="button"
                class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                    {{ auth()->user()->initials() }}
                </span>
                <span class="hidden sm:inline whitespace-nowrap">
                    - {{ auth()->user()->first_name }} {{ auth()->user()->last_name }} -
                    <span class="capitalize bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs">
                        {{ str(auth()->user()->role)->replace('_', ' ') }}
                    </span>
                </span>
            </button>

            <button
                wire:click="logout"
                wire:loading.attr="disabled"
                wire:target="logout"
                type="button"
                class="text-xs text-red-600 hover:text-red-800 font-semibold px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition-colors cursor-pointer whitespace-nowrap disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="logout">Log Out</span>
                <span wire:loading wire:target="logout">Logging out...</span>
            </button>
        </div>
    </header>

    {{-- Toast Notification Container --}}
    <div class="fixed top-5 right-5 z-60 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="true"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-[-10px]"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-[-10px]"
                class="pointer-events-auto p-4 rounded-lg shadow-lg text-sm font-medium flex items-center justify-between gap-3 text-white"
                :class="toast.type === 'error' ? 'bg-red-600' : 'bg-emerald-600'"
            >
                <span x-text="toast.message"></span>
                <button @click="remove(toast.id)" class="text-white/80 hover:text-white text-lg font-bold cursor-pointer">&times;</button>
            </div>
        </template>
    </div>

    {{-- Profile Modal Container --}}
    @if ($showProfileModal)
        <div
            @keydown.escape.window="$wire.closeProfileModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
        >
            <div
                wire:click="closeProfileModal"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"
            ></div>

            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10 overflow-hidden my-8">

                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div class="flex gap-4">
                        <button
                            wire:click="$set('activeTab', 'profile')"
                            type="button"
                            class="text-sm font-semibold pb-1 border-b-2 transition-colors cursor-pointer {{ $activeTab === 'profile' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                        >
                            Profile Details
                        </button>
                        <button
                            wire:click="$set('activeTab', 'password')"
                            type="button"
                            class="text-sm font-semibold pb-1 border-b-2 transition-colors cursor-pointer {{ $activeTab === 'password' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                        >
                            Change Password
                        </button>
                    </div>
                    <button wire:click="closeProfileModal" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                {{-- TAB 1: Edit Profile --}}
                @if ($activeTab === 'profile')
                    <form wire:submit="updateProfile" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Suffix</label>
                                <input type="text" wire:model="suffix" placeholder="e.g. Jr., III" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                                @error('suffix') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-700">First Name *</label>
                                <input type="text" wire:model="first_name" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                                @error('first_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Middle Name *</label>
                                <input type="text" wire:model="middle_name" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                                @error('middle_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Last Name *</label>
                                <input type="text" wire:model="last_name" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                                @error('last_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Username *</label>
                                <input type="text" wire:model="username" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                                @error('username') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">User Role</label>
                                <input type="text" wire:model="role" disabled class="mt-1 w-full text-sm rounded-md border-gray-200 bg-gray-100 text-gray-500 shadow-xs border p-2 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Contact Number *</label>
                                <input type="text" wire:model="contact_number" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                                @error('contact_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Email Address *</label>
                            <input type="email" wire:model="email" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                            @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Address *</label>
                            <input type="text" wire:model="address" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                            @error('address') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <hr class="my-4 border-gray-200">

                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                            <label class="block text-xs font-medium text-blue-900">Current Password (To confirm changes) *</label>
                            <input type="password" wire:model="current_password_for_profile" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                            @error('current_password_for_profile') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-5 flex justify-end gap-3">
                            <button wire:click="closeProfileModal" type="button" class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="updateProfile"
                                class="px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 cursor-pointer disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="updateProfile">Save Changes</span>
                                <span wire:loading wire:target="updateProfile">Saving...</span>
                            </button>
                        </div>
                    </form>
                @endif

                {{-- TAB 2: Change Password --}}
                @if ($activeTab === 'password')
                    <form wire:submit="updatePassword" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Current Password *</label>
                            <input type="password" wire:model="current_password" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                            @error('current_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">New Password *</label>
                            <input type="password" wire:model="new_password" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                            @error('new_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Confirm New Password *</label>
                            <input type="password" wire:model="new_password_confirmation" class="mt-1 w-full text-sm rounded-md border-gray-300 shadow-xs border p-2">
                        </div>

                        <div class="mt-5 flex justify-end gap-3">
                            <button wire:click="closeProfileModal" type="button" class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="updatePassword"
                                class="px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 cursor-pointer disabled:opacity-50"
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
