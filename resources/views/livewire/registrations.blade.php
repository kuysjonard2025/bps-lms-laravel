<div class="p-4 sm:p-6 space-y-4 max-w-full">
    {{-- Header Container --}}
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Registration & Accounts</h2>
            <p class="text-xs text-gray-500">Manage system users and registered library patrons.</p>
        </div>

        {{-- Tab Switcher & Quick Add Actions --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1" role="tablist">
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === 'users' ? 'true' : 'false' }}"
                    wire:click="$set('activeTab', 'users')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md transition cursor-pointer {{ $activeTab === 'users' ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}"
                >
                    System Users
                </button>
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === 'patrons' ? 'true' : 'false' }}"
                    wire:click="$set('activeTab', 'patrons')"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md transition cursor-pointer {{ $activeTab === 'patrons' ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}"
                >
                    Library Patrons
                </button>
            </div>

            @if ($activeTab === 'users')
                <button
                    wire:click="openCreateUserModal"
                    type="button"
                    class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Register User</span>
                </button>
            @else
                <button
                    wire:click="openCreatePatronModal"
                    type="button"
                    class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Register Patron</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-xs">
        <div class="relative w-full sm:w-72">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search {{ $activeTab === 'users' ? 'username, name, email...' : 'patron ID, name, email...' }}"
                class="w-full pl-9 pr-8 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            >
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            @if($search)
                <button wire:click="$set('search', '')" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs cursor-pointer">
                    ✕
                </button>
            @endif
        </div>
    </div>

    {{-- TAB 1: USERS TABLE --}}
    @if ($activeTab === 'users')
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
            <table class="w-full text-left text-xs text-gray-700">
                <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-4 py-3">Username</th>
                        <th scope="col" class="px-4 py-3">Full Name</th>
                        <th scope="col" class="px-4 py-3">Contact / Email</th>
                        <th scope="col" class="px-4 py-3 text-center">Role</th>
                        <th scope="col" class="px-4 py-3 text-center">Status</th>
                        <th scope="col" class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($users as $user)
                        <tr wire:key="user-row-{{ $user->id }}" class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 font-mono font-bold text-blue-600">{{ $user->username }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                {{ implode(' ', array_filter([$user->first_name, $user->middle_name, $user->last_name, $user->suffix])) ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                @if($user->email) <div>{{ $user->email }}</div> @endif
                                @if($user->contact_number) <div class="text-[11px] text-gray-400">{{ $user->contact_number }}</div> @endif
                                @if(!$user->email && !$user->contact_number) <span class="text-gray-400">—</span> @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $roleBadgeStyles = [
                                        'admin' => 'bg-amber-100 text-amber-800',
                                        'librarian' => 'bg-blue-100 text-blue-800',
                                        'assistant' => 'bg-purple-100 text-purple-800',
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Admin',
                                        'librarian' => 'Librarian',
                                        'assistant' => 'Librarian Assistant',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider {{ $roleBadgeStyles[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $roleLabels[$user->role] ?? $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($user->email_verified_at)
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider bg-green-100 text-green-800">
                                        Verified
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider bg-gray-100 text-gray-800">
                                        Not Verified
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                <button
                                    type="button"
                                    wire:click="openEditUserModal({{ $user->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded hover:bg-blue-50 transition cursor-pointer"
                                >Edit</button>

                                @if($user->role !== 'admin')
                                    <button
                                        type="button"
                                        wire:click="confirmDelete('user', {{ $user->id }})"
                                        class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded hover:bg-red-50 transition cursor-pointer"
                                    >Delete</button>
                                @else
                                    <span class="text-gray-300 font-semibold px-2 py-1 select-none text-[11px]" title="Admin users cannot be deleted">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No user accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $users->links() }}</div>
    @endif

    {{-- TAB 2: PATRONS TABLE --}}
    @if ($activeTab === 'patrons')
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
            <table class="w-full text-left text-xs text-gray-700">
                <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-4 py-3">Patron ID</th>
                        <th scope="col" class="px-4 py-3">Full Name</th>
                        <th scope="col" class="px-4 py-3">Type / Academic Info</th>
                        <th scope="col" class="px-4 py-3">Address</th>
                        <th scope="col" class="px-4 py-3">Contact</th>
                        <th scope="col" class="px-4 py-3 text-center">Status</th>
                        <th scope="col" class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($patrons as $patron)
                        <tr wire:key="patron-row-{{ $patron->id }}" class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 font-mono font-bold text-blue-600">{{ $patron->patron_id }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                {{ implode(' ', array_filter([$patron->first_name, $patron->middle_name, $patron->last_name, $patron->suffix])) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="uppercase font-bold text-[10px] px-2 py-0.5 rounded bg-gray-100 text-gray-700 mr-1">
                                    {{ $patron->patronType->name ?? 'N/A' }}
                                </span>
                                @if($patron->gradeLevel)
                                    <span class="font-bold text-gray-800">{{ $patron->gradeLevel->code }}</span>
                                    @if($patron->section)
                                        <span class="text-gray-500 text-[11px]"> - {{ $patron->section->name }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $patron->address }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <div>{{ $patron->email }}</div>
                                <div class="text-[11px] text-gray-400">{{ $patron->contact_number }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-gray-100 text-gray-600',
                                        'suspended' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full capitalize {{ $statusColors[$patron->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $patron->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                <button
                                    type="button"
                                    wire:click="openEditPatronModal({{ $patron->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded hover:bg-blue-50 transition cursor-pointer"
                                >Edit</button>
                                <button
                                    type="button"
                                    wire:click="confirmDelete('patron', {{ $patron->id }})"
                                    class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded hover:bg-red-50 transition cursor-pointer"
                                >Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No patrons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $patrons->links() }}</div>
    @endif

    {{-- USER MODAL --}}
    @if ($showUserModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" role="dialog" aria-modal="true">
            <div wire:click.self="$set('showUserModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10 overflow-hidden my-auto flex flex-col max-h-[90vh]">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900">{{ $userIdBeingEdited ? 'Edit System User' : 'Register System User' }}</h3>
                    <button type="button" wire:click="$set('showUserModal', false)" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveUser" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_first_name" placeholder="John" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('u_first_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Middle Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_middle_name" placeholder="Doe" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('u_middle_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_last_name" placeholder="Smith" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('u_last_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Suffix</label>
                            <input type="text" wire:model="u_suffix" placeholder="Jr., Sr." class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Username <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_username" placeholder="johnsmith" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('u_username') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
                            <select
                                wire:model="u_role"
                                class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white {{ $isEditingAdmin ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}"
                                {{ $isEditingAdmin ? 'disabled' : '' }}
                            >
                                @if($isEditingAdmin)
                                    <option value="admin">Administrator</option>
                                @endif
                                <option value="librarian">Librarian</option>
                                <option value="assistant">Librarian Assistant</option>
                            </select>
                            @error('u_role') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Email Address</label>
                            <input type="email" wire:model="u_email" placeholder="john@example.com" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('u_email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Contact Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="u_contact_number" placeholder="09123456789" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('u_contact_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">Address <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="u_address" placeholder="123 Street Name, City" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                        @error('u_address') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">
                            Password {{ $userIdBeingEdited ? '(Leave blank to keep unchanged)' : '*' }}
                        </label>
                        <input type="password" wire:model="u_password" placeholder="••••••••" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                        @error('u_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-gray-200 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showUserModal', false)" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-xs transition cursor-pointer">
                            {{ $userIdBeingEdited ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- PATRON MODAL --}}
    @if ($showPatronModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" role="dialog" aria-modal="true">
            <div wire:click.self="$set('showPatronModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-xl z-10 overflow-hidden my-auto flex flex-col max-h-[90vh]">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900">{{ $patronIdBeingEdited ? 'Edit Library Patron' : 'Register Library Patron' }}</h3>
                    <button type="button" wire:click="$set('showPatronModal', false)" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="savePatron" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Patron ID <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_patron_id" placeholder="PAT-2026-0001" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('p_patron_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Patron Type <span class="text-red-500">*</span></label>
                            <select wire:model.live="p_patron_type_id" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="">Select Type</option>
                                @foreach($patronTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('p_patron_type_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_first_name" placeholder="John" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('p_first_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Middle Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_middle_name" placeholder="Doe" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('p_middle_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_last_name" placeholder="Smith" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('p_last_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Suffix</label>
                            <input type="text" wire:model="p_suffix" placeholder="Jr., Sr." class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="p_email" placeholder="patron@example.com" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('p_email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Contact Number <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_contact_number" placeholder="09123456789" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('p_contact_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if ($isStudentType)
                        <div class="p-3 bg-blue-50/50 rounded-lg border border-blue-100 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-blue-900">Grade Level</label>
                                <select wire:model.live="p_grade_level_id" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                    <option value="">Select Grade Level</option>
                                    @foreach($allGradeLevels as $level)
                                        <option value="{{ $level->id }}">{{ $level->name }} ({{ $level->code }})</option>
                                    @endforeach
                                </select>
                                @error('p_grade_level_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-blue-900">Section</label>
                                <select wire:model="p_section_id" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white {{ !$p_grade_level_id ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ !$p_grade_level_id ? 'disabled' : '' }}>
                                    <option value="">Select Section</option>
                                    @foreach($availableSections as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                                @error('p_section_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-700">Address <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="p_address" placeholder="123 Home Address" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                            @error('p_address') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Account Status <span class="text-red-500">*</span></label>
                            <select wire:model="p_status" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            @error('p_status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-200 flex justify-end gap-2">
                        <button type="button" wire:click="$set('showPatronModal', false)" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-xs transition cursor-pointer">
                            {{ $patronIdBeingEdited ? 'Update Patron' : 'Create Patron' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- DELETE CONFIRMATION MODAL --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" role="dialog" aria-modal="true">
            <div wire:click.self="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm z-10 p-5 text-center my-auto">
                <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900">Confirm Deletion</h3>
                <p class="text-xs text-gray-500 mt-1">Are you sure you want to delete this {{ $deleteType }}? This action cannot be undone.</p>

                <div class="mt-4 flex justify-center gap-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteItem" class="px-4 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-md shadow-xs transition cursor-pointer">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
