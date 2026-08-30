<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Registrations & Accounts</h1>
            <p class="text-sm text-gray-500">Manage library assistant staff and borrower registrations.</p>
        </div>
        <div class="flex items-center gap-3">
            @if($activeTab === 'users')
                <button wire:click="openCreateUserModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Register Assistant
                </button>
            @else
                <button wire:click="openCreateBorrowerModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Register Borrower
                </button>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs & Search Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <!-- Tabs -->
        <div class="flex items-center space-x-1 bg-gray-200/70 p-1 rounded-xl w-fit">
            <button wire:click="$set('activeTab', 'users')" class="px-4 py-2 text-sm font-medium rounded-lg transition {{ $activeTab === 'users' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                Assistant Users
            </button>
            <button wire:click="$set('activeTab', 'borrowers')" class="px-4 py-2 text-sm font-medium rounded-lg transition {{ $activeTab === 'borrowers' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                Borrowers
            </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full md:w-72">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search {{ $activeTab === 'users' ? 'users' : 'borrowers, RFID, ID...' }}..." class="w-full pl-9 pr-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    <!-- TAB 1: USERS TABLE -->
    @if($activeTab === 'users')
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Full Name</th>
                            <th class="px-6 py-3">Username</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Contact Details</th>
                            <th class="px-6 py-3">Address</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $user->last_name }}, {{ $user->first_name }} {{ $user->middle_name }} {{ $user->suffix }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-indigo-600 font-medium">
                                    {{ $user->username }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-900 font-medium">{{ $user->email ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->contact_number }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-700 max-w-xs truncate" title="{{ $user->address }}">
                                    {{ $user->address ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <button wire:click="openEditUserModal({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Edit</button>
                                    @if(Auth::id() !== $user->id)
                                        <button wire:click="confirmDelete('user', {{ $user->id }})" class="text-rose-600 hover:text-rose-900 font-medium text-xs">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No assistant users found.</td>
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

    <!-- TAB 2: BORROWERS TABLE -->
    @if($activeTab === 'borrowers')
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-semibold text-gray-500">
                        <tr>
                            <th class="px-6 py-3">School ID</th>
                            <th class="px-6 py-3">RFID Tag</th>
                            <th class="px-6 py-3">Full Name</th>
                            <th class="px-6 py-3">Type / Grade & Sec</th>
                            <th class="px-6 py-3">Contact Details</th>
                            <th class="px-6 py-3">Address</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($borrowers as $borrower)
                            <tr wire:key="borrower-{{ $borrower->id }}" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-900">
                                    {{ $borrower->school_id }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-indigo-600">
                                    <span class="inline-flex items-center gap-1.5 bg-indigo-50 px-2 py-1 rounded border border-indigo-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        {{ $borrower->rfid_tag }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $borrower->last_name }}, {{ $borrower->first_name }} {{ $borrower->middle_name }} {{ $borrower->suffix }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-xs text-gray-900">{{ $borrower->borrowerType->name ?? 'N/A' }}</div>
                                    @if($borrower->gradeLevel)
                                        <div class="text-xs text-gray-500">{{ $borrower->gradeLevel->name }} - {{ $borrower->section->name ?? '' }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-900 font-medium">{{ $borrower->email ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $borrower->contact_number }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-700 max-w-xs truncate" title="{{ $borrower->address }}">
                                    {{ $borrower->address ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $borrower->status === 'active' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $borrower->status === 'inactive' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $borrower->status === 'suspended' ? 'bg-rose-100 text-rose-800' : '' }}">
                                        {{ ucfirst($borrower->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <button wire:click="openEditBorrowerModal({{ $borrower->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Edit</button>
                                    <button wire:click="confirmDelete('borrower', {{ $borrower->id }})" class="text-rose-600 hover:text-rose-900 font-medium text-xs">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">No registered borrowers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200">
                {{ $borrowers->links() }}
            </div>
        </div>
    @endif

    <!-- MODAL 1: ASSISTANT USER FORM -->
    @if($showUserModal)
        <div x-data x-on:keydown.escape.window="$wire.set('showUserModal', false)" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 border border-gray-200 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-bold text-gray-900">{{ $userIdBeingEdited ? 'Edit Assistant User' : 'Register New Assistant User' }}</h3>
                    <button wire:click="$set('showUserModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveUser" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">First Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="u_first_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_first_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Middle Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="u_middle_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_middle_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Last Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="u_last_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_last_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Suffix</label>
                            <input type="text" wire:model="u_suffix" placeholder="Jr., III" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_suffix') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Username <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="u_username" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_username') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Password {{ $userIdBeingEdited ? '(Leave blank to keep)' : '*' }}</label>
                            <input type="password" wire:model="u_password" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('u_password') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
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
                        <input type="text" wire:model="u_address" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                        @error('u_address') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="$set('showUserModal', false)" class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium flex items-center gap-2">
                            <span wire:loading wire:target="saveUser" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                            Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: BORROWER FORM -->
    @if($showBorrowerModal)
        <div x-data x-on:keydown.escape.window="$wire.set('showBorrowerModal', false)" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full p-6 border border-gray-200 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <h3 class="text-lg font-bold text-gray-900">{{ $borrowerIdBeingEdited ? 'Edit Borrower Record' : 'Register New Borrower' }}</h3>
                    <button wire:click="$set('showBorrowerModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveBorrower" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-indigo-50/50 p-3 rounded-lg border border-indigo-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">School / Student ID <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="b_school_id" placeholder="e.g. 2026-00123" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-900">
                            @error('b_school_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">RFID Tag UID <span class="text-rose-500">* (Scan Card Now)</span></label>
                            <div class="relative">
                                <input type="text" wire:model="b_rfid_tag" wire:keydown.enter.prevent placeholder="Tap RFID card on scanner..." autofocus class="w-full pl-9 pr-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-mono text-gray-900 focus:ring-2 focus:ring-indigo-500">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </span>
                            </div>
                            @error('b_rfid_tag') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">First Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="b_first_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('b_first_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Middle Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="b_middle_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('b_middle_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Last Name <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="b_last_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('b_last_name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Suffix</label>
                            <input type="text" wire:model="b_suffix" placeholder="Jr., III" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('b_suffix') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Borrower Type <span class="text-rose-500">*</span></label>
                            <select wire:model.live="b_borrower_type_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select Borrower Type</option>
                                @foreach($borrowerTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('b_borrower_type_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Grade Level {{ $isStudentType ? '*' : '' }}</label>
                            <select wire:model.live="b_grade_level_id" {{ !$isStudentType ? 'disabled' : '' }} class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                                <option value="">Select Grade Level</option>
                                @foreach($allGradeLevels as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                            @error('b_grade_level_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Section {{ $isStudentType ? '*' : '' }}</label>
                            <select wire:model="b_section_id" {{ !$isStudentType || !$b_grade_level_id ? 'disabled' : '' }} class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                                <option value="">Select Section</option>
                                @foreach($availableSections as $sec)
                                    <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                @endforeach
                            </select>
                            @error('b_section_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="b_email" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('b_email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Contact Number <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="b_contact_number" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            @error('b_contact_number') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Account Status <span class="text-rose-500">*</span></label>
                            <select wire:model="b_status" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            @error('b_status') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Complete Address <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="b_address" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                        @error('b_address') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="$set('showBorrowerModal', false)" class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium flex items-center gap-2">
                            <span wire:loading wire:target="saveBorrower" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                            Save Borrower
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
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Confirm Deletion</h3>
                    <p class="text-sm text-gray-500">Are you sure you want to delete this {{ $deleteType === 'user' ? 'assistant user account' : 'borrower record' }}? This action cannot be undone.</p>
                </div>

                <div class="flex justify-center gap-3 pt-2">
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button wire:click="deleteRecord" wire:loading.attr="disabled" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-medium flex items-center gap-2">
                        <span wire:loading wire:target="deleteRecord" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                        Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
