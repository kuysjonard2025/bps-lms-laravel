<div class="space-y-6 p-6 bg-slate-50 min-h-screen">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Registrations</h1>
            <p class="text-sm text-slate-500">Manage system user accounts and library borrowers.</p>
        </div>
        <div>
            @if ($activeTab === 'users')
                <button
                    wire:click="openCreateUserModal"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg shadow-xs transition duration-150 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Register Assistant
                </button>
            @else
                <button
                    wire:click="openCreatePatronModal"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg shadow-xs transition duration-150 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Register Borrower
                </button>
            @endif
        </div>
    </div>

    {{-- Tabs & Search Controls --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Navigation Tabs --}}
            <div class="flex space-x-1 bg-slate-100 p-1 rounded-lg w-fit">
                <button
                    wire:click="$set('activeTab', 'users')"
                    class="px-4 py-1.5 text-xs font-semibold rounded-md transition duration-150 {{ $activeTab === 'users' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    System Users
                </button>
                <button
                    wire:click="$set('activeTab', 'borrowers')"
                    class="px-4 py-1.5 text-xs font-semibold rounded-md transition duration-150 {{ $activeTab === 'borrowers' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Library Borrowers
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="w-full md:w-72">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search {{ $activeTab === 'users' ? 'users...' : 'borrowers...' }}"
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white"
                    />
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Display Section --}}
    @if ($activeTab === 'users')
        {{-- Users Table --}}
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            <th class="px-6 py-3.5">Name</th>
                            <th class="px-6 py-3.5">Username</th>
                            <th class="px-6 py-3.5">Role</th>
                            <th class="px-6 py-3.5">Contact Details</th>
                            <th class="px-6 py-3.5">Address</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-3.5 font-medium text-slate-900">
                                    {{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }} {{ $user->suffix }}
                                </td>
                                <td class="px-6 py-3.5 font-mono text-slate-600">{{ $user->username }}</td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : ($user->role === 'librarian' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="font-medium text-slate-800">{{ $user->email ?? 'N/A' }}</div>
                                    <div class="text-slate-500 text-xs">{{ $user->contact_number }}</div>
                                </td>
                                <td class="px-6 py-3.5 max-w-xs truncate text-slate-600" title="{{ $user->address }}">
                                    {{ $user->address ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-3.5 text-right space-x-2">
                                    <button wire:click="openEditUserModal({{ $user->id }})" class="text-blue-600 hover:text-blue-900 font-medium">Edit</button>
                                    @if ($user->role !== 'admin' && $user->role !== 'librarian' && $user->id !== auth()->id())
                                        <button wire:click="confirmDelete('user', {{ $user->id }})" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    @else
        {{-- Borrowers Table --}}
        <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            <th class="px-6 py-3.5">Borrower ID</th>
                            <th class="px-6 py-3.5">Name</th>
                            <th class="px-6 py-3.5">Type / Class</th>
                            <th class="px-6 py-3.5">Contact Details</th>
                            <th class="px-6 py-3.5">Address</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse ($patrons as $patron)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-3.5 font-mono font-medium text-slate-900">{{ $patron->patron_id }}</td>
                                <td class="px-6 py-3.5 font-medium text-slate-900">
                                    {{ $patron->first_name }} {{ $patron->middle_name }} {{ $patron->last_name }} {{ $patron->suffix }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="font-medium text-slate-800">{{ $patron->patronType->name ?? 'N/A' }}</div>
                                    @if ($patron->gradeLevel)
                                        <div class="text-slate-400 text-xs">{{ $patron->gradeLevel->name }} - {{ $patron->section->name ?? '' }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="font-medium text-slate-800">{{ $patron->email ?? 'N/A' }}</div>
                                    <div class="text-slate-500 text-xs">{{ $patron->contact_number }}</div>
                                </td>
                                <td class="px-6 py-3.5 max-w-xs truncate text-slate-600" title="{{ $patron->address }}">
                                    {{ $patron->address ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $patron->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($patron->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right space-x-2">
                                    <button wire:click="openEditPatronModal({{ $patron->id }})" class="text-blue-600 hover:text-blue-900 font-medium">Edit</button>
                                    <button wire:click="confirmDelete('borrower', {{ $patron->id }})" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-400">No borrowers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($patrons->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $patrons->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- USER MODAL --}}
    @if ($showUserModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800">
                        {{ $userIdBeingEdited ? 'Edit Assistant User' : 'Register New Assistant' }}
                    </h3>
                    <button wire:click="$set('showUserModal', false)" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <form wire:submit.prevent="saveUser" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_first_name" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('u_first_name') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Middle Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_middle_name" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('u_middle_name') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_last_name" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('u_last_name') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Suffix</label>
                            <input type="text" wire:model="u_suffix" placeholder="e.g. Jr." class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('u_suffix') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Username <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_username" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('u_username') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Role <span class="text-red-500">*</span></label>
                            <select wire:model="u_role" disabled class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 bg-slate-100 text-slate-500 cursor-not-allowed">
                                <option value="assistant">Librarian Assistant</option>
                            </select>
                            @error('u_role') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Email Address</label>
                            <input type="email" wire:model="u_email" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('u_email') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Contact Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_contact_number" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('u_contact_number') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Address <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="u_address" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                        @error('u_address') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">
                            Password {{ $userIdBeingEdited ? '(Leave blank to keep unchanged)' : '*' }}
                        </label>
                        <input type="password" wire:model="u_password" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                        @error('u_password') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex justify-end space-x-2 border-t border-slate-100">
                        <button type="button" wire:click="$set('showUserModal', false)" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- BORROWER MODAL --}}
    @if ($showPatronModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800">
                        {{ $patronIdBeingEdited ? 'Edit Borrower Record' : 'Register New Borrower' }}
                    </h3>
                    <button wire:click="$set('showPatronModal', false)" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>
                <form wire:submit.prevent="savePatron" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700">Borrower ID / Barcode <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="p_patron_id" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                        @error('p_patron_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_first_name" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('p_first_name') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Middle Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_middle_name" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('p_middle_name') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_last_name" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('p_last_name') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Suffix</label>
                            <input type="text" wire:model="p_suffix" placeholder="e.g. Jr." class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('p_suffix') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Borrower Type <span class="text-red-500">*</span></label>
                            <select wire:model.live="p_patron_type_id" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 bg-white focus:ring-2 focus:ring-blue-500/20">
                                <option value="">Select Type</option>
                                @foreach ($patronTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('p_patron_type_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Status <span class="text-red-500">*</span></label>
                            <select wire:model="p_status" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 bg-white focus:ring-2 focus:ring-blue-500/20">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            @error('p_status') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if ($isStudentType)
                        <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-lg border border-slate-200">
                            <div>
                                <label class="block text-xs font-medium text-slate-700">Grade Level <span class="text-red-500">*</span></label>
                                <select wire:model.live="p_grade_level_id" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 bg-white focus:ring-2 focus:ring-blue-500/20">
                                    <option value="">Select Grade</option>
                                    @foreach ($allGradeLevels as $gl)
                                        <option value="{{ $gl->id }}">{{ $gl->name }}</option>
                                    @endforeach
                                </select>
                                @error('p_grade_level_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700">Section <span class="text-red-500">*</span></label>
                                <select wire:model="p_section_id" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 bg-white focus:ring-2 focus:ring-blue-500/20" {{ !$p_grade_level_id ? 'disabled' : '' }}>
                                    <option value="">Select Section</option>
                                    @foreach ($availableSections as $sec)
                                        <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                    @endforeach
                                </select>
                                @error('p_section_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="p_email" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('p_email') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700">Contact Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_contact_number" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                            @error('p_contact_number') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700">Address <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="p_address" class="mt-1 w-full text-xs rounded-lg border-slate-300 border p-2 focus:ring-2 focus:ring-blue-500/20" />
                        @error('p_address') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex justify-end space-x-2 border-t border-slate-100">
                        <button type="button" wire:click="$set('showPatronModal', false)" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium">Save Borrower</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- DELETE CONFIRMATION MODAL --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden p-6 border border-slate-200 text-center">
                <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h3 class="text-base font-bold text-slate-800">Confirm Deletion</h3>
                <p class="text-xs text-slate-500 mt-1">Are you sure you want to delete this record? This action cannot be undone.</p>
                <div class="mt-6 flex justify-center space-x-3">
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium">Cancel</button>
                    <button wire:click="deleteRecord" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
