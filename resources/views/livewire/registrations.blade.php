<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users &amp; Patrons Registration</h1>
            <p class="text-sm text-gray-500">Manage Library Assistant Staff And Patron Registrations.</p>
        </div>
        <div class="flex items-center gap-3">
            @if($activeTab === 'users')
                <button type="button" wire:click="openCreateUserModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Register Assistant
                </button>
            @else
                <button type="button" wire:click="openCreatePatronModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Register Patron
                </button>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs & Search Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <!-- Tabs -->
        <div class="flex items-center space-x-1 bg-gray-200/70 p-1 rounded-xl w-fit">
            <button type="button" wire:click="$set('activeTab', 'users')" class="px-4 py-2 text-sm font-medium rounded-lg transition cursor-pointer {{ $activeTab === 'users' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                Assistant Users
            </button>
            <button type="button" wire:click="$set('activeTab', 'patrons')" class="px-4 py-2 text-sm font-medium rounded-lg transition cursor-pointer {{ $activeTab === 'patrons' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                Patrons
            </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full md:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search {{ $activeTab === 'users' ? 'Users...' : 'Patrons, RFID, ID, Dept...' }}" class="w-full pl-9 pr-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    <!-- TAB 1: USERS TABLE -->
    @if($activeTab === 'users')
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500">
                        <tr>
                            <th class="px-6 py-3 whitespace-nowrap">FULL NAME</th>
                            <th class="px-6 py-3 whitespace-nowrap">USERNAME</th>
                            <th class="px-6 py-3 whitespace-nowrap">ROLE</th>
                            <th class="px-6 py-3 whitespace-nowrap">CONTACT #</th>
                            <th class="px-6 py-3 whitespace-nowrap">EMAIL</th>
                            <th class="px-6 py-3 whitespace-nowrap">ADDRESS</th>
                            <th class="px-6 py-3 whitespace-nowrap">IS VERIFIED</th>
                            <th class="px-6 py-3 text-right whitespace-nowrap">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50 transition whitespace-nowrap">
                                <td class="px-6 py-4 font-medium text-gray-900 capitalize whitespace-nowrap">
                                    {{ \Illuminate\Support\Str::title(trim("{$user->last_name}, {$user->first_name} {$user->middle_name} {$user->suffix}")) }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-indigo-600 font-medium whitespace-nowrap">
                                    {{ $user->username }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 uppercase">
                                        {{ strtoupper($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 font-medium">{{ $user->contact_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 font-medium">{{ $user->email ?: 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-700 max-w-xs capitalize whitespace-nowrap" title="{{ \Illuminate\Support\Str::title($user->address) }}">
                                    {{ $user->address ? \Illuminate\Support\Str::title($user->address) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 uppercase">
                                            VERIFIED
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 uppercase">
                                            UNVERIFIED
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <button type="button" wire:click="openEditUserModal({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs cursor-pointer">Edit</button>
                                    @if(Auth::id() !== $user->id)
                                        <button type="button" wire:click="confirmDelete('user', {{ $user->id }})" class="text-rose-600 hover:text-rose-900 font-medium text-xs cursor-pointer">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">No Assistant Users Found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 2: PATRONS TABLE -->
    @if($activeTab === 'patrons')
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500">
                        <tr>
                            <th class="px-6 py-3 whitespace-nowrap">STUDENT/EMPLOYEE #</th>
                            <th class="px-6 py-3 whitespace-nowrap">RFID TAG</th>
                            <th class="px-6 py-3 whitespace-nowrap">FULL NAME</th>
                            <th class="px-6 py-3 whitespace-nowrap">TYPE</th>
                            <th class="px-6 py-3 whitespace-nowrap">DEPARTMENT</th>
                            <th class="px-6 py-3 whitespace-nowrap">GRADE</th>
                            <th class="px-6 py-3 whitespace-nowrap">SECTION</th>
                            <th class="px-6 py-3 whitespace-nowrap">ADVISER</th>
                            <th class="px-6 py-3 whitespace-nowrap">STRAND</th>
                            <th class="px-6 py-3 whitespace-nowrap">CONTACT #</th>
                            <th class="px-6 py-3 whitespace-nowrap">EMAIL</th>
                            <th class="px-6 py-3 whitespace-nowrap">ADDRESS</th>
                            <th class="px-6 py-3 whitespace-nowrap">STATUS</th>
                            <th class="px-6 py-3 text-right whitespace-nowrap">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($patrons as $patron)
                            <tr wire:key="patron-{{ $patron->id }}" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-900 whitespace-nowrap uppercase">
                                    {{ strtoupper($patron->school_id) }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-indigo-600 whitespace-nowrap uppercase">
                                    <span class="inline-flex items-center gap-1.5 bg-indigo-50 px-2 py-1 rounded border border-indigo-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        {{ strtoupper($patron->rfid_tag) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 capitalize whitespace-nowrap">
                                    {{ \Illuminate\Support\Str::title(trim("{$patron->last_name}, {$patron->first_name} {$patron->middle_name} {$patron->suffix}")) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-xs text-gray-900 uppercase">{{ $patron->patronType?->name ? strtoupper($patron->patronType->name) : 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-800 uppercase">{{ $patron->department?->name ? strtoupper($patron->department->name) : '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-700 capitalize">{{ $patron->gradeLevel?->name ? \Illuminate\Support\Str::title($patron->gradeLevel->name) : '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-700 capitalize">{{ $patron->section?->name ? \Illuminate\Support\Str::title($patron->section->name) : '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $adviserName = $patron->adviser?->full_name
                                            ?? $patron->adviser?->name
                                            ?? trim(($patron->adviser?->last_name ? $patron->adviser->last_name . ', ' : '') . ($patron->adviser?->first_name ?? '') . ' ' . ($patron->adviser?->middle_name ?? '') . ' ' . ($patron->adviser?->suffix ?? ''));
                                    @endphp
                                    <div class="text-xs text-gray-700 capitalize">{{ $adviserName ? \Illuminate\Support\Str::title($adviserName) : '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($patron->strand)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase">
                                            {{ strtoupper($patron->strand->code) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 font-medium">{{ $patron->contact_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-900 font-medium">{{ $patron->email ?: 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-700 max-w-xs capitalize whitespace-nowrap" title="{{ \Illuminate\Support\Str::title($patron->address) }}">
                                    {{ $patron->address ? \Illuminate\Support\Str::title($patron->address) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase
                                        {{ $patron->status === 'active' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $patron->status === 'inactive' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $patron->status === 'suspended' ? 'bg-rose-100 text-rose-800' : '' }}">
                                        {{ strtoupper($patron->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <button type="button" wire:click="openEditPatronModal({{ $patron->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs cursor-pointer">Edit</button>
                                    <button type="button" wire:click="confirmDelete('patron', {{ $patron->id }})" class="text-rose-600 hover:text-rose-900 font-medium text-xs cursor-pointer">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="px-6 py-8 text-center text-gray-500">No Registered Patrons Found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200">
                {{ $patrons->links() }}
            </div>
        </div>
    @endif

    <!-- MODAL 1: SYSTEM USER FORM -->
    @if($showUserModal)
        <div x-data x-on:keydown.escape.window="$wire.set('showUserModal', false)" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 border border-gray-200 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-bold text-gray-900">{{ $userIdBeingEdited ? 'Edit System User' : 'Register New System User' }}</h3>
                    <button type="button" wire:click="$set('showUserModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveUser" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">First Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="u_first_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_first_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Middle Name</label>
                            <input type="text" wire:model="u_middle_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_middle_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Last Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="u_last_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_last_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Suffix</label>
                            <input type="text" wire:model="u_suffix" placeholder="Jr., III" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_suffix') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Username <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="u_username" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_username') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Password {{ $userIdBeingEdited ? '(Leave Blank To Keep)' : '*' }}</label>
                            <input type="password" wire:model="u_password" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_password') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="u_email" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Contact Number <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="u_contact_number" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_contact_number') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Home Address <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="u_address" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                        @error('u_address') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="$set('showUserModal', false)" class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium flex items-center gap-2 cursor-pointer">
                            <span wire:loading wire:target="saveUser" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                            Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: PATRON FORM -->
    @if($showPatronModal)
        <div x-data x-on:keydown.escape.window="$wire.set('showPatronModal', false)" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full p-6 border border-gray-200 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-bold text-gray-900">{{ $patronIdBeingEdited ? 'Edit Patron Record' : 'Register New Patron' }}</h3>
                    <button type="button" wire:click="$set('showPatronModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="savePatron" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-indigo-50/50 p-3 rounded-lg border border-indigo-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Student/Employee # <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="p_school_id" wire:keydown.enter.prevent placeholder="Enter Student/Employee Number" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 uppercase">
                            @error('p_school_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">RFID Tag UID <span class="text-rose-500">* (Scan Card Now)</span></label>
                            <div class="relative">
                                <input type="text" wire:model="p_rfid_tag" wire:keydown.enter.prevent placeholder="Tap RFID Card On Scanner..." autofocus class="w-full pl-9 pr-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-mono text-gray-900 uppercase focus:ring-2 focus:ring-indigo-500">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </span>
                            </div>
                            @error('p_rfid_tag') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">First Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="p_first_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('p_first_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Middle Name</label>
                            <input type="text" wire:model="p_middle_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('p_middle_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Last Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="p_last_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('p_last_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Suffix</label>
                            <input type="text" wire:model="p_suffix" placeholder="Jr., III" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('p_suffix') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 {{ $isStudentType ? 'md:grid-cols-4' : 'md:grid-cols-2' }} gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Patron Type <span class="text-rose-500">*</span></label>
                            <select wire:model.live="p_patron_type_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 uppercase focus:bg-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">SELECT PATRON TYPE</option>
                                @foreach($patronTypes as $type)
                                    <option value="{{ $type->id }}">{{ strtoupper($type->name) }}</option>
                                @endforeach
                            </select>
                            @error('p_patron_type_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- INLINE SEARCHABLE DEPARTMENT -->
                        @php
                            $deptOptions = $allDepartments->map(fn($d) => ['id' => $d->id, 'label' => strtoupper($d->name)])->values()->all();
                        @endphp
                        <div
                            wire:key="inline-dept-{{ $patronIdBeingEdited ?? 'new' }}"
                            x-data="{
                                open: false,
                                search: '',
                                selected: @entangle('p_department_id'),
                                options: @js($deptOptions),
                                get filtered() {
                                    const q = this.search.trim().toLowerCase();
                                    return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
                                },
                                get label() {
                                    const found = this.options.find(o => String(o.id) === String(this.selected));
                                    return found ? found.label : '';
                                },
                                pick(id) {
                                    this.selected = id;
                                    this.open = false;
                                    this.search = '';
                                    $wire.set('p_department_id', id);
                                }
                            }"
                            x-on:click.outside="open = false"
                            class="relative"
                        >
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Department <span class="text-rose-500">*</span></label>
                            <button
                                type="button"
                                x-on:click="open = !open; if (open) { search = ''; $nextTick(() => $refs.deptSearch?.focus()); }"
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm flex items-center justify-between gap-2 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <span x-text="label || 'Select Department'" :class="label ? 'text-gray-900 font-medium uppercase truncate' : 'text-gray-400 truncate'"></span>
                                <svg :class="open ? 'rotate-180 text-indigo-600' : 'text-gray-400'" class="w-4 h-4 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" style="display: none;" class="absolute z-40 mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-lg p-2.5 space-y-2">
                                <input x-ref="deptSearch" type="text" x-model="search" placeholder="Type to search department..." class="w-full px-3 py-2 text-xs text-gray-900 bg-white border-2 border-indigo-500 rounded-lg focus:outline-none">
                                <div class="max-h-44 overflow-y-auto divide-y divide-gray-50">
                                    <template x-for="opt in filtered" :key="opt.id">
                                        <button type="button" x-on:click="pick(opt.id)" :class="String(selected) === String(opt.id) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'" class="w-full text-left px-3 py-2 text-xs uppercase rounded-lg cursor-pointer flex items-center justify-between">
                                            <span x-text="opt.label"></span>
                                            <svg x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-gray-400">No departments found.</div>
                                </div>
                            </div>
                            @error('p_department_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if($isStudentType)
                            <!-- INLINE SEARCHABLE GRADE LEVEL -->
                            @php
                                $gradeOptions = $availableGradeLevels->map(fn($g) => ['id' => $g->id, 'label' => \Illuminate\Support\Str::title($g->name)])->values()->all();
                            @endphp
                            <div
                                wire:key="inline-grade-{{ $p_department_id ?? 'none' }}"
                                x-data="{
                                    open: false,
                                    search: '',
                                    selected: @entangle('p_grade_level_id'),
                                    options: @js($gradeOptions),
                                    disabled: @js(! (bool) $p_department_id),
                                    get filtered() {
                                        const q = this.search.trim().toLowerCase();
                                        return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
                                    },
                                    get label() {
                                        const found = this.options.find(o => String(o.id) === String(this.selected));
                                        return found ? found.label : '';
                                    },
                                    pick(id) {
                                        if (this.disabled) return;
                                        this.selected = id;
                                        this.open = false;
                                        this.search = '';
                                        $wire.set('p_grade_level_id', id);
                                    }
                                }"
                                x-on:click.outside="open = false"
                                class="relative"
                            >
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Grade Level <span class="text-rose-500">*</span></label>
                                <button
                                    type="button"
                                    :disabled="disabled"
                                    x-on:click="if (!disabled) { open = !open; if (open) { search = ''; $nextTick(() => $refs.gradeSearch?.focus()); } }"
                                    :class="disabled ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-900 border-gray-300 cursor-pointer focus:ring-2 focus:ring-indigo-500'"
                                    class="w-full px-3 py-2 border rounded-lg text-sm flex items-center justify-between gap-2 text-left focus:outline-none"
                                >
                                    <span x-text="label || 'Select Grade Level'" :class="disabled ? 'text-gray-400 truncate' : (label ? 'text-gray-900 font-medium capitalize truncate' : 'text-gray-400 truncate')"></span>
                                    <svg :class="open ? 'rotate-180 text-indigo-600' : 'text-gray-400'" class="w-4 h-4 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open && !disabled" style="display: none;" class="absolute z-40 mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-lg p-2.5 space-y-2">
                                    <input x-ref="gradeSearch" type="text" x-model="search" placeholder="Type to search grade level..." class="w-full px-3 py-2 text-xs text-gray-900 bg-white border-2 border-indigo-500 rounded-lg focus:outline-none">
                                    <div class="max-h-44 overflow-y-auto divide-y divide-gray-50">
                                        <template x-for="opt in filtered" :key="opt.id">
                                            <button type="button" x-on:click="pick(opt.id)" :class="String(selected) === String(opt.id) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'" class="w-full text-left px-3 py-2 text-xs capitalize rounded-lg cursor-pointer flex items-center justify-between">
                                                <span x-text="opt.label"></span>
                                                <svg x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-gray-400">No grade levels found.</div>
                                    </div>
                                </div>
                                @error('p_grade_level_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- INLINE SEARCHABLE SECTION -->
                            @php
                                $sectionOptions = $availableSections->map(fn($s) => ['id' => $s->id, 'label' => \Illuminate\Support\Str::title($s->name)])->values()->all();
                            @endphp
                            <div
                                wire:key="inline-section-{{ $p_grade_level_id ?? 'none' }}"
                                x-data="{
                                    open: false,
                                    search: '',
                                    selected: @entangle('p_section_id'),
                                    options: @js($sectionOptions),
                                    disabled: @js(! (bool) $p_grade_level_id),
                                    get filtered() {
                                        const q = this.search.trim().toLowerCase();
                                        return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
                                    },
                                    get label() {
                                        const found = this.options.find(o => String(o.id) === String(this.selected));
                                        return found ? found.label : '';
                                    },
                                    pick(id) {
                                        if (this.disabled) return;
                                        this.selected = id;
                                        this.open = false;
                                        this.search = '';
                                        $wire.set('p_section_id', id);
                                    }
                                }"
                                x-on:click.outside="open = false"
                                class="relative"
                            >
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Section <span class="text-rose-500">*</span></label>
                                <button
                                    type="button"
                                    :disabled="disabled"
                                    x-on:click="if (!disabled) { open = !open; if (open) { search = ''; $nextTick(() => $refs.sectionSearch?.focus()); } }"
                                    :class="disabled ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-900 border-gray-300 cursor-pointer focus:ring-2 focus:ring-indigo-500'"
                                    class="w-full px-3 py-2 border rounded-lg text-sm flex items-center justify-between gap-2 text-left focus:outline-none"
                                >
                                    <span x-text="label || 'Select Section'" :class="disabled ? 'text-gray-400 truncate' : (label ? 'text-gray-900 font-medium capitalize truncate' : 'text-gray-400 truncate')"></span>
                                    <svg :class="open ? 'rotate-180 text-indigo-600' : 'text-gray-400'" class="w-4 h-4 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open && !disabled" style="display: none;" class="absolute z-40 mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-lg p-2.5 space-y-2">
                                    <input x-ref="sectionSearch" type="text" x-model="search" placeholder="Type to search section..." class="w-full px-3 py-2 text-xs text-gray-900 bg-white border-2 border-indigo-500 rounded-lg focus:outline-none">
                                    <div class="max-h-44 overflow-y-auto divide-y divide-gray-50">
                                        <template x-for="opt in filtered" :key="opt.id">
                                            <button type="button" x-on:click="pick(opt.id)" :class="String(selected) === String(opt.id) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'" class="w-full text-left px-3 py-2 text-xs capitalize rounded-lg cursor-pointer flex items-center justify-between">
                                                <span x-text="opt.label"></span>
                                                <svg x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-gray-400">No sections found.</div>
                                    </div>
                                </div>
                                @error('p_section_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    @if($isStudentType)
                        <!-- INLINE SEARCHABLE ADVISER (REQUIRED FOR STUDENT) & SHS STRAND (REQUIRED WHEN SHS) -->
                        @php
                            $adviserCollection = $availableAdvisers ?? collect();
                            $adviserOptions = $adviserCollection->map(function ($adv) {
                                $rawName = $adv->full_name
                                    ?? $adv->name
                                    ?? trim(($adv->last_name ? $adv->last_name . ', ' : '') . ($adv->first_name ?? '') . ' ' . ($adv->middle_name ?? '') . ' ' . ($adv->suffix ?? ''));
                                return [
                                    'id' => $adv->id,
                                    'label' => \Illuminate\Support\Str::title($rawName ?: ('Adviser #' . $adv->id)),
                                ];
                            })->values()->all();
                        @endphp
                        <div class="grid grid-cols-1 {{ ($isSeniorHighType ?? false) ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-4 bg-indigo-50/40 p-3 rounded-lg border border-indigo-100">
                            <div
                                wire:key="inline-adviser-{{ $p_department_id ?? 'none' }}"
                                x-data="{
                                    open: false,
                                    search: '',
                                    selected: @entangle('p_adviser_id'),
                                    options: @js($adviserOptions),
                                    disabled: @js(! (bool) $p_department_id),
                                    get filtered() {
                                        const q = this.search.trim().toLowerCase();
                                        return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
                                    },
                                    get label() {
                                        const found = this.options.find(o => String(o.id) === String(this.selected));
                                        return found ? found.label : '';
                                    },
                                    pick(id) {
                                        if (this.disabled) return;
                                        this.selected = id;
                                        this.open = false;
                                        this.search = '';
                                        $wire.set('p_adviser_id', id);
                                    }
                                }"
                                x-on:click.outside="open = false"
                                class="relative"
                            >
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Adviser <span class="text-rose-500">*</span></label>
                                <button
                                    type="button"
                                    :disabled="disabled"
                                    x-on:click="if (!disabled) { open = !open; if (open) { search = ''; $nextTick(() => $refs.adviserSearch?.focus()); } }"
                                    :class="disabled ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-900 border-gray-300 cursor-pointer focus:ring-2 focus:ring-indigo-500'"
                                    class="w-full px-3 py-2 border rounded-lg text-sm flex items-center justify-between gap-2 text-left focus:outline-none"
                                >
                                    <span x-text="label || 'Select Adviser'" :class="disabled ? 'text-gray-400 truncate' : (label ? 'text-gray-900 font-medium capitalize truncate' : 'text-gray-400 truncate')"></span>
                                    <svg :class="open ? 'rotate-180 text-indigo-600' : 'text-gray-400'" class="w-4 h-4 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open && !disabled" style="display: none;" class="absolute z-40 mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-lg p-2.5 space-y-2">
                                    <input x-ref="adviserSearch" type="text" x-model="search" placeholder="Type to search adviser..." class="w-full px-3 py-2 text-xs text-gray-900 bg-white border-2 border-indigo-500 rounded-lg focus:outline-none">
                                    <div class="max-h-44 overflow-y-auto divide-y divide-gray-50">
                                        <template x-for="opt in filtered" :key="opt.id">
                                            <button type="button" x-on:click="pick(opt.id)" :class="String(selected) === String(opt.id) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'" class="w-full text-left px-3 py-2 text-xs capitalize rounded-lg cursor-pointer flex items-center justify-between">
                                                <span x-text="opt.label"></span>
                                                <svg x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-gray-400">No advisers found.</div>
                                    </div>
                                </div>
                                @error('p_adviser_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            @if($isSeniorHighType ?? false)
                                <!-- INLINE SEARCHABLE STRAND (SHS DEPARTMENT ONLY) -->
                                @php
                                    $strandOptions = $allStrands->map(fn($st) => ['id' => $st->id, 'label' => strtoupper($st->code) . ' - ' . \Illuminate\Support\Str::title($st->name)])->values()->all();
                                @endphp
                                <div
                                    wire:key="inline-strand-{{ $p_department_id ?? 'none' }}"
                                    x-data="{
                                        open: false,
                                        search: '',
                                        selected: @entangle('p_strand_id'),
                                        options: @js($strandOptions),
                                        get filtered() {
                                            const q = this.search.trim().toLowerCase();
                                            return q ? this.options.filter(o => o.label.toLowerCase().includes(q)) : this.options;
                                        },
                                        get label() {
                                            const found = this.options.find(o => String(o.id) === String(this.selected));
                                            return found ? found.label : '';
                                        },
                                        pick(id) {
                                            this.selected = id;
                                            this.open = false;
                                            this.search = '';
                                            $wire.set('p_strand_id', id);
                                        }
                                    }"
                                    x-on:click.outside="open = false"
                                    class="relative"
                                >
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Strand (SHS Department Only) <span class="text-rose-500">*</span></label>
                                    <button
                                        type="button"
                                        x-on:click="open = !open; if (open) { search = ''; $nextTick(() => $refs.strandSearch?.focus()); }"
                                        class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm flex items-center justify-between gap-2 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <span x-text="label || 'Select Strand'" :class="label ? 'text-gray-900 font-medium truncate' : 'text-gray-400 truncate'"></span>
                                        <svg :class="open ? 'rotate-180 text-indigo-600' : 'text-gray-400'" class="w-4 h-4 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="open" style="display: none;" class="absolute z-40 mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-lg p-2.5 space-y-2">
                                        <input x-ref="strandSearch" type="text" x-model="search" placeholder="Type to search strand..." class="w-full px-3 py-2 text-xs text-gray-900 bg-white border-2 border-indigo-500 rounded-lg focus:outline-none">
                                        <div class="max-h-44 overflow-y-auto divide-y divide-gray-50">
                                            <template x-for="opt in filtered" :key="opt.id">
                                                <button type="button" x-on:click="pick(opt.id)" :class="String(selected) === String(opt.id) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50'" class="w-full text-left px-3 py-2 text-xs rounded-lg cursor-pointer flex items-center justify-between">
                                                    <span x-text="opt.label"></span>
                                                    <svg x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </template>
                                            <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-gray-400">No strands found.</div>
                                        </div>
                                    </div>
                                    @error('p_strand_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>

                        @if($isSeniorHighType ?? false)
                            <p class="text-[11px] text-indigo-800">
                                Strands Apply Only When The Selected Department Is SHS.
                            </p>
                        @endif
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="p_email" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('p_email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Contact Number <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="p_contact_number" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('p_contact_number') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Account Status <span class="text-rose-500">*</span></label>
                            <select wire:model="p_status" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 uppercase focus:bg-white focus:ring-2 focus:ring-indigo-500">
                                <option value="active">ACTIVE</option>
                                <option value="inactive">INACTIVE</option>
                                <option value="suspended">SUSPENDED</option>
                            </select>
                            @error('p_status') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Complete Address <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="p_address" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 capitalize focus:bg-white focus:ring-2 focus:ring-indigo-500">
                        @error('p_address') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="$set('showPatronModal', false)" class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium flex items-center gap-2 cursor-pointer">
                            <span wire:loading wire:target="savePatron" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                            Save Patron
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 3: DELETE CONFIRMATION -->
    @if($showDeleteModal)
        <div x-data x-on:keydown.escape.window="$wire.set('showDeleteModal', false)" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-200 space-y-4">
                <div class="text-center space-y-2">
                    <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Confirm Deletion</h3>
                    <p class="text-sm text-gray-500">
                        Are You Sure You Want To Delete This {{ $deleteType === 'user' ? 'Assistant User' : 'Patron' }} Record? This Action Cannot Be Undone.
                    </p>
                </div>

                <div class="flex justify-center gap-3 pt-4 border-t border-gray-200">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteRecord" wire:loading.attr="disabled" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-medium flex items-center gap-2 cursor-pointer">
                        <span wire:loading wire:target="deleteRecord" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                        Delete Record
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
