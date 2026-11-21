{{-- resources/views/livewire/registrations.blade.php --}}
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Auth;
@endphp

<div class="space-y-6">
    <!-- Header Section -->
    <section aria-label="Users and patrons registration header" class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-content-heading tracking-tight">Users &amp; Patrons Registration</h1>
            <p class="text-xs text-content-body mt-0.5">Manage Library Assistant Staff And Patron Registrations.</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            @if($activeTab === 'users')
                <button
                    type="button"
                    wire:click="openCreateUserModal"
                    wire:loading.attr="disabled"
                    wire:target="openCreateUserModal"
                    class="w-full sm:w-auto px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20 cursor-pointer shrink-0"
                >
                    <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
                    <span>Register Assistant</span>
                </button>
            @else
                <button
                    type="button"
                    wire:click="openCreatePatronModal"
                    wire:loading.attr="disabled"
                    wire:target="openCreatePatronModal"
                    class="w-full sm:w-auto px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20 cursor-pointer shrink-0"
                >
                    <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
                    <span>Register Patron</span>
                </button>
            @endif
        </div>
    </section>

    <!-- Navigation Tabs & Search Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-surface-border pb-4">
        <!-- Tabs -->
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

        <!-- Search Bar -->
        <div class="relative w-full md:w-72">
            <label for="registration-search" class="sr-only">Search registrations</label>
            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
            <input
                id="registration-search"
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search {{ $activeTab === 'users' ? 'Users...' : 'Patrons, RFID, ID, Dept...' }}"
                class="w-full pl-9 pr-4 py-2 bg-surface-card border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
            >
        </div>
    </div>

    <!-- TAB 1: USERS TABLE -->
    @if($activeTab === 'users')
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
                                <td class="px-4 py-3 font-semibold text-content-heading whitespace-nowrap">
                                    {{ Str::title(trim("{$user->last_name}, {$user->first_name} {$user->middle_name} {$user->suffix}")) }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-brand-primary font-semibold whitespace-nowrap">
                                    {{ $user->username }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-tint-light text-brand-primary uppercase">
                                        {{ strtoupper($user->role) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs font-mono text-content-heading font-medium">{{ $user->contact_number }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-content-heading font-medium">{{ $user->email ?: 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-content-body max-w-xs truncate whitespace-nowrap" title="{{ $user->address ? Str::title($user->address) : 'N/A' }}">
                                    {{ $user->address ? Str::title($user->address) : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-tint-light text-brand-primary uppercase">
                                            VERIFIED
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-brand-secondary/15 text-brand-secondary uppercase">
                                            UNVERIFIED
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        wire:click="openEditUserModal({{ $user->id }})"
                                        class="text-brand-primary hover:bg-brand-tint-light font-semibold text-xs px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                    >Edit</button>
                                    @if(Auth::id() !== $user->id)
                                        <button
                                            type="button"
                                            wire:click="confirmDelete('user', {{ $user->id }})"
                                            class="text-brand-secondary hover:bg-brand-secondary/10 font-semibold text-xs px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
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
    @endif

    <!-- TAB 2: PATRONS TABLE -->
    @if($activeTab === 'patrons')
        <div class="bg-surface-card shadow-2xs rounded-2xl border border-surface-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-xs text-content-body">
                    <thead class="bg-surface-canvas border-b border-surface-border text-[11px] uppercase font-semibold tracking-wider text-content-muted">
                        <tr>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">STUDENT/EMPLOYEE #</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">RFID TAG</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">FULL NAME</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">TYPE</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">DEPARTMENT</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">GRADE</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">SECTION</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">ADVISER</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">STRAND</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">CONTACT #</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">EMAIL</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">ADDRESS</th>
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">STATUS</th>
                            <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border/70">
                        @forelse($patrons as $patron)
                            <tr wire:key="patron-{{ $patron->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-content-heading whitespace-nowrap uppercase">
                                    {{ strtoupper($patron->school_id) }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-brand-primary whitespace-nowrap uppercase">
                                    <span class="inline-flex items-center gap-1.5 bg-brand-tint-light px-2 py-1 rounded-lg border border-brand-primary/20">
                                        <x-heroicon-o-key class="w-3.5 h-3.5" aria-hidden="true" />
                                        <span>{{ strtoupper($patron->rfid_tag) }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-content-heading whitespace-nowrap">
                                    {{ Str::title(trim("{$patron->last_name}, {$patron->first_name} {$patron->middle_name} {$patron->suffix}")) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-xs text-content-heading uppercase">{{ $patron->patronType?->name ? strtoupper($patron->patronType->name) : 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs font-medium text-content-heading uppercase">{{ $patron->department?->name ? strtoupper($patron->department->name) : '—' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-content-body">{{ $patron->gradeLevel?->name ? Str::title($patron->gradeLevel->name) : '—' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-content-body">{{ $patron->section?->name ? Str::title($patron->section->name) : '—' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $adviserName = $patron->adviser?->full_name
                                            ?? $patron->adviser?->name
                                            ?? trim(($patron->adviser?->last_name ? $patron->adviser->last_name . ', ' : '') . ($patron->adviser?->first_name ?? '') . ' ' . ($patron->adviser?->middle_name ?? '') . ' ' . ($patron->adviser?->suffix ?? ''));
                                    @endphp
                                    <div class="text-xs text-content-body">{{ $adviserName ? Str::title($adviserName) : '—' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($patron->strand)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-semibold bg-brand-tint-light text-brand-primary border border-brand-primary/20 uppercase">
                                            {{ strtoupper($patron->strand->code) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-content-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs font-mono text-content-heading font-medium">{{ $patron->contact_number }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-content-heading font-medium">{{ $patron->email ?: 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-content-body max-w-xs truncate whitespace-nowrap" title="{{ $patron->address ? Str::title($patron->address) : 'N/A' }}">
                                    {{ $patron->address ? Str::title($patron->address) : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase {{ match($patron->status) {
                                        'active' => 'bg-brand-tint-light text-brand-primary',
                                        'inactive', 'suspended' => 'bg-brand-secondary/15 text-brand-secondary',
                                        default => 'bg-surface-canvas text-content-heading border border-surface-border'
                                    } }}">
                                        {{ strtoupper($patron->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        wire:click="openEditPatronModal({{ $patron->id }})"
                                        class="text-brand-primary hover:bg-brand-tint-light font-semibold text-xs px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                    >Edit</button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete('patron', {{ $patron->id }})"
                                        class="text-brand-secondary hover:bg-brand-secondary/10 font-semibold text-xs px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                                    >Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="px-6 py-10 text-center">
                                    <p class="text-xs font-semibold text-content-heading">No Registered Patrons Found</p>
                                    <p class="text-[11px] text-content-muted mt-1">Try adjusting your search query or register a new patron.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-surface-border bg-surface-canvas">
                {{ $patrons->links() }}
            </div>
        </div>
    @endif

    <!-- MODAL 1: SYSTEM USER FORM -->
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
                    <h3 id="user-modal-title" class="text-sm sm:text-base font-bold text-content-heading">
                        {{ $userIdBeingEdited ? 'Edit System User' : 'Register New System User' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="$set('showUserModal', false)"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading p-1 rounded-lg cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >
                        <x-heroicon-o-x-mark class="w-5 h-5" aria-hidden="true" />
                    </button>
                </div>

                <form wire:submit="saveUser" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="u_first_name" class="block text-xs font-semibold text-content-heading mb-1">
                                First Name <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="u_first_name"
                                type="text"
                                wire:model="u_first_name"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('u_first_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_middle_name" class="block text-xs font-semibold text-content-heading mb-1">Middle Name</label>
                            <input
                                id="u_middle_name"
                                type="text"
                                wire:model="u_middle_name"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('u_middle_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_last_name" class="block text-xs font-semibold text-content-heading mb-1">
                                Last Name <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="u_last_name"
                                type="text"
                                wire:model="u_last_name"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('u_last_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="u_suffix" class="block text-xs font-semibold text-content-heading mb-1">Suffix</label>
                            <input
                                id="u_suffix"
                                type="text"
                                wire:model="u_suffix"
                                placeholder="Jr., III"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('u_suffix') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_username" class="block text-xs font-semibold text-content-heading mb-1">
                                Username <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="u_username"
                                type="text"
                                wire:model="u_username"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('u_username') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_password" class="block text-xs font-semibold text-content-heading mb-1">
                                Password {{ $userIdBeingEdited ? '(Leave Blank To Keep)' : '*' }}
                            </label>
                            <input
                                id="u_password"
                                type="password"
                                wire:model="u_password"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('u_password') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="u_email" class="block text-xs font-semibold text-content-heading mb-1">
                                Email Address <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="u_email"
                                type="email"
                                wire:model="u_email"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('u_email') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="u_contact_number" class="block text-xs font-semibold text-content-heading mb-1">
                                Contact Number <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="u_contact_number"
                                type="text"
                                wire:model="u_contact_number"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-mono text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('u_contact_number') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="u_address" class="block text-xs font-semibold text-content-heading mb-1">
                            Home Address <span class="text-brand-secondary">*</span>
                        </label>
                        <input
                            id="u_address"
                            type="text"
                            wire:model="u_address"
                            class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                        >
                        @error('u_address') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-surface-border/70">
                        <button
                            type="button"
                            wire:click="$set('showUserModal', false)"
                            class="w-full sm:w-auto px-4 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-semibold text-content-body hover:text-content-heading cursor-pointer transition-colors"
                        >Cancel</button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveUser"
                            class="w-full sm:w-auto px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer transition-colors shadow-2xs disabled:opacity-50"
                        >
                            <span wire:loading wire:target="saveUser" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                            <span>Save User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: PATRON FORM -->
    @if($showPatronModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.set('showPatronModal', false)"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="patron-modal-title"
        >
            <div wire:click="$set('showPatronModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl max-w-3xl w-full p-5 sm:p-6 border border-surface-border space-y-4 z-10 max-h-[90dvh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-surface-border pb-3">
                    <h3 id="patron-modal-title" class="text-sm sm:text-base font-bold text-content-heading">
                        {{ $patronIdBeingEdited ? 'Edit Patron Record' : 'Register New Patron' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="$set('showPatronModal', false)"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading p-1 rounded-lg cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >
                        <x-heroicon-o-x-mark class="w-5 h-5" aria-hidden="true" />
                    </button>
                </div>

                <form wire:submit="savePatron" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-brand-tint-light/60 p-3.5 rounded-xl border border-brand-primary/20">
                        <div>
                            <label for="p_school_id" class="block text-xs font-semibold text-content-heading mb-1">
                                Student/Employee # <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="p_school_id"
                                type="text"
                                wire:model="p_school_id"
                                wire:keydown.enter.prevent
                                placeholder="Enter Student/Employee Number"
                                class="w-full px-3 py-2 bg-surface-card border border-surface-border rounded-xl text-xs font-mono text-content-heading placeholder:text-content-muted uppercase focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('p_school_id') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_rfid_tag" class="block text-xs font-semibold text-content-heading mb-1">
                                RFID Tag UID <span class="text-brand-secondary">* (Scan Card Now)</span>
                            </label>
                            <div class="relative">
                                <input
                                    id="p_rfid_tag"
                                    type="text"
                                    wire:model="p_rfid_tag"
                                    wire:keydown.enter.prevent
                                    placeholder="Tap RFID Card On Scanner..."
                                    autofocus
                                    class="w-full pl-9 pr-3 py-2 bg-surface-card border border-surface-border rounded-xl text-xs font-mono text-content-heading placeholder:text-content-muted uppercase focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                                >
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-content-muted pointer-events-none">
                                    <x-heroicon-o-key class="w-4 h-4" aria-hidden="true" />
                                </span>
                            </div>
                            @error('p_rfid_tag') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="p_first_name" class="block text-xs font-semibold text-content-heading mb-1">
                                First Name <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="p_first_name"
                                type="text"
                                wire:model="p_first_name"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('p_first_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_middle_name" class="block text-xs font-semibold text-content-heading mb-1">Middle Name</label>
                            <input
                                id="p_middle_name"
                                type="text"
                                wire:model="p_middle_name"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('p_middle_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_last_name" class="block text-xs font-semibold text-content-heading mb-1">
                                Last Name <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="p_last_name"
                                type="text"
                                wire:model="p_last_name"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('p_last_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_suffix" class="block text-xs font-semibold text-content-heading mb-1">Suffix</label>
                            <input
                                id="p_suffix"
                                type="text"
                                wire:model="p_suffix"
                                placeholder="Jr., III"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('p_suffix') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 {{ $isStudentType ? 'md:grid-cols-4' : 'md:grid-cols-2' }} gap-4">
                        <div>
                            <label for="p_patron_type_id" class="block text-xs font-semibold text-content-heading mb-1">
                                Patron Type <span class="text-brand-secondary">*</span>
                            </label>
                            <select
                                id="p_patron_type_id"
                                wire:model.live="p_patron_type_id"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading uppercase focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                                <option value="">SELECT PATRON TYPE</option>
                                @foreach($patronTypes as $type)
                                    <option value="{{ $type->id }}">{{ strtoupper($type->name) }}</option>
                                @endforeach
                            </select>
                            @error('p_patron_type_id') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
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
                            <label class="block text-xs font-semibold text-content-heading mb-1">
                                Department <span class="text-brand-secondary">*</span>
                            </label>
                            <button
                                type="button"
                                x-on:click="open = !open; if (open) { search = ''; $nextTick(() => $refs.deptSearch?.focus()); }"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs flex items-center justify-between gap-2 text-left cursor-pointer focus-visible:outline-none focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                            >
                                <span x-text="label || 'Select Department'" :class="label ? 'text-content-heading font-semibold uppercase truncate' : 'text-content-muted truncate'"></span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0 transition-transform" ::class="open ? 'rotate-180 text-brand-primary' : 'text-content-muted'" aria-hidden="true" />
                            </button>
                            <div x-show="open" x-cloak class="absolute z-40 mt-1.5 w-full bg-surface-card rounded-xl border border-surface-border shadow-lg p-2.5 space-y-2">
                                <input
                                    x-ref="deptSearch"
                                    type="text"
                                    x-model="search"
                                    placeholder="Type to search department..."
                                    class="w-full px-3 py-2 text-xs text-content-heading placeholder:text-content-muted bg-surface-canvas border border-brand-primary rounded-lg focus:bg-surface-card focus:outline-none"
                                >
                                <div class="max-h-44 overflow-y-auto divide-y divide-surface-border/60">
                                    <template x-for="opt in filtered" :key="opt.id">
                                        <button
                                            type="button"
                                            x-on:click="pick(opt.id)"
                                            :class="String(selected) === String(opt.id) ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                                            class="w-full text-left px-3 py-2 text-xs uppercase rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                                        >
                                            <span x-text="opt.label"></span>
                                            <x-heroicon-o-check x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-content-muted">No departments found.</div>
                                </div>
                            </div>
                            @error('p_department_id') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        @if($isStudentType)
                            <!-- INLINE SEARCHABLE GRADE LEVEL -->
                            @php
                                $gradeOptions = $availableGradeLevels->map(fn($g) => ['id' => $g->id, 'label' => Str::title($g->name)])->values()->all();
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
                                <label class="block text-xs font-semibold text-content-heading mb-1">
                                    Grade Level <span class="text-brand-secondary">*</span>
                                </label>
                                <button
                                    type="button"
                                    :disabled="disabled"
                                    x-on:click="if (!disabled) { open = !open; if (open) { search = ''; $nextTick(() => $refs.gradeSearch?.focus()); } }"
                                    :class="disabled ? 'bg-surface-canvas/70 text-content-muted border-surface-border cursor-not-allowed opacity-70' : 'bg-surface-canvas text-content-heading border-surface-border cursor-pointer focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15'"
                                    class="w-full px-3 py-2 border rounded-xl text-xs flex items-center justify-between gap-2 text-left focus-visible:outline-none transition"
                                >
                                    <span x-text="label || 'Select Grade Level'" :class="disabled ? 'text-content-muted truncate' : (label ? 'text-content-heading font-semibold truncate' : 'text-content-muted truncate')"></span>
                                    <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0 transition-transform" ::class="open ? 'rotate-180 text-brand-primary' : 'text-content-muted'" aria-hidden="true" />
                                </button>
                                <div x-show="open && !disabled" x-cloak class="absolute z-40 mt-1.5 w-full bg-surface-card rounded-xl border border-surface-border shadow-lg p-2.5 space-y-2">
                                    <input
                                        x-ref="gradeSearch"
                                        type="text"
                                        x-model="search"
                                        placeholder="Type to search grade level..."
                                        class="w-full px-3 py-2 text-xs text-content-heading placeholder:text-content-muted bg-surface-canvas border border-brand-primary rounded-lg focus:bg-surface-card focus:outline-none"
                                    >
                                    <div class="max-h-44 overflow-y-auto divide-y divide-surface-border/60">
                                        <template x-for="opt in filtered" :key="opt.id">
                                            <button
                                                type="button"
                                                x-on:click="pick(opt.id)"
                                                :class="String(selected) === String(opt.id) ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                                                class="w-full text-left px-3 py-2 text-xs rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                                            >
                                                <span x-text="opt.label"></span>
                                                <x-heroicon-o-check x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                                            </button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-content-muted">No grade levels found.</div>
                                    </div>
                                </div>
                                @error('p_grade_level_id') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- INLINE SEARCHABLE SECTION -->
                            @php
                                $sectionOptions = $availableSections->map(fn($s) => ['id' => $s->id, 'label' => Str::title($s->name)])->values()->all();
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
                                <label class="block text-xs font-semibold text-content-heading mb-1">
                                    Section <span class="text-brand-secondary">*</span>
                                </label>
                                <button
                                    type="button"
                                    :disabled="disabled"
                                    x-on:click="if (!disabled) { open = !open; if (open) { search = ''; $nextTick(() => $refs.sectionSearch?.focus()); } }"
                                    :class="disabled ? 'bg-surface-canvas/70 text-content-muted border-surface-border cursor-not-allowed opacity-70' : 'bg-surface-canvas text-content-heading border-surface-border cursor-pointer focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15'"
                                    class="w-full px-3 py-2 border rounded-xl text-xs flex items-center justify-between gap-2 text-left focus-visible:outline-none transition"
                                >
                                    <span x-text="label || 'Select Section'" :class="disabled ? 'text-content-muted truncate' : (label ? 'text-content-heading font-semibold truncate' : 'text-content-muted truncate')"></span>
                                    <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0 transition-transform" ::class="open ? 'rotate-180 text-brand-primary' : 'text-content-muted'" aria-hidden="true" />
                                </button>
                                <div x-show="open && !disabled" x-cloak class="absolute z-40 mt-1.5 w-full bg-surface-card rounded-xl border border-surface-border shadow-lg p-2.5 space-y-2">
                                    <input
                                        x-ref="sectionSearch"
                                        type="text"
                                        x-model="search"
                                        placeholder="Type to search section..."
                                        class="w-full px-3 py-2 text-xs text-content-heading placeholder:text-content-muted bg-surface-canvas border border-brand-primary rounded-lg focus:bg-surface-card focus:outline-none"
                                    >
                                    <div class="max-h-44 overflow-y-auto divide-y divide-surface-border/60">
                                        <template x-for="opt in filtered" :key="opt.id">
                                            <button
                                                type="button"
                                                x-on:click="pick(opt.id)"
                                                :class="String(selected) === String(opt.id) ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                                                class="w-full text-left px-3 py-2 text-xs rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                                            >
                                                <span x-text="opt.label"></span>
                                                <x-heroicon-o-check x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                                            </button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-content-muted">No sections found.</div>
                                    </div>
                                </div>
                                @error('p_section_id') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
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
                                    'label' => Str::title($rawName ?: ('Adviser #' . $adv->id)),
                                ];
                            })->values()->all();
                        @endphp
                        <div class="grid grid-cols-1 {{ ($isSeniorHighType ?? false) ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-4 bg-brand-tint-light/50 p-3.5 rounded-xl border border-brand-primary/20">
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
                                <label class="block text-xs font-semibold text-content-heading mb-1">
                                    Adviser <span class="text-brand-secondary">*</span>
                                </label>
                                <button
                                    type="button"
                                    :disabled="disabled"
                                    x-on:click="if (!disabled) { open = !open; if (open) { search = ''; $nextTick(() => $refs.adviserSearch?.focus()); } }"
                                    :class="disabled ? 'bg-surface-canvas/70 text-content-muted border-surface-border cursor-not-allowed opacity-70' : 'bg-surface-card text-content-heading border-surface-border cursor-pointer focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15'"
                                    class="w-full px-3 py-2 border rounded-xl text-xs flex items-center justify-between gap-2 text-left focus-visible:outline-none transition"
                                >
                                    <span x-text="label || 'Select Adviser'" :class="disabled ? 'text-content-muted truncate' : (label ? 'text-content-heading font-semibold truncate' : 'text-content-muted truncate')"></span>
                                    <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0 transition-transform" ::class="open ? 'rotate-180 text-brand-primary' : 'text-content-muted'" aria-hidden="true" />
                                </button>
                                <div x-show="open && !disabled" x-cloak class="absolute z-40 mt-1.5 w-full bg-surface-card rounded-xl border border-surface-border shadow-lg p-2.5 space-y-2">
                                    <input
                                        x-ref="adviserSearch"
                                        type="text"
                                        x-model="search"
                                        placeholder="Type to search adviser..."
                                        class="w-full px-3 py-2 text-xs text-content-heading placeholder:text-content-muted bg-surface-canvas border border-brand-primary rounded-lg focus:bg-surface-card focus:outline-none"
                                    >
                                    <div class="max-h-44 overflow-y-auto divide-y divide-surface-border/60">
                                        <template x-for="opt in filtered" :key="opt.id">
                                            <button
                                                type="button"
                                                x-on:click="pick(opt.id)"
                                                :class="String(selected) === String(opt.id) ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                                                class="w-full text-left px-3 py-2 text-xs rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                                            >
                                                <span x-text="opt.label"></span>
                                                <x-heroicon-o-check x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                                            </button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-content-muted">No advisers found.</div>
                                    </div>
                                </div>
                                @error('p_adviser_id') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            @if($isSeniorHighType ?? false)
                                <!-- INLINE SEARCHABLE STRAND (SHS DEPARTMENT ONLY) -->
                                @php
                                    $strandOptions = $allStrands->map(fn($st) => ['id' => $st->id, 'label' => strtoupper($st->code) . ' - ' . Str::title($st->name)])->values()->all();
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
                                    <label class="block text-xs font-semibold text-content-heading mb-1">
                                        Strand (SHS Department Only) <span class="text-brand-secondary">*</span>
                                    </label>
                                    <button
                                        type="button"
                                        x-on:click="open = !open; if (open) { search = ''; $nextTick(() => $refs.strandSearch?.focus()); }"
                                        class="w-full px-3 py-2 bg-surface-card border border-surface-border rounded-xl text-xs flex items-center justify-between gap-2 text-left cursor-pointer focus-visible:outline-none focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 transition"
                                    >
                                        <span x-text="label || 'Select Strand'" :class="label ? 'text-content-heading font-semibold truncate' : 'text-content-muted truncate'"></span>
                                        <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0 transition-transform" ::class="open ? 'rotate-180 text-brand-primary' : 'text-content-muted'" aria-hidden="true" />
                                    </button>
                                    <div x-show="open" x-cloak class="absolute z-40 mt-1.5 w-full bg-surface-card rounded-xl border border-surface-border shadow-lg p-2.5 space-y-2">
                                        <input
                                            x-ref="strandSearch"
                                            type="text"
                                            x-model="search"
                                            placeholder="Type to search strand..."
                                            class="w-full px-3 py-2 text-xs text-content-heading placeholder:text-content-muted bg-surface-canvas border border-brand-primary rounded-lg focus:bg-surface-card focus:outline-none"
                                        >
                                        <div class="max-h-44 overflow-y-auto divide-y divide-surface-border/60">
                                            <template x-for="opt in filtered" :key="opt.id">
                                                <button
                                                    type="button"
                                                    x-on:click="pick(opt.id)"
                                                    :class="String(selected) === String(opt.id) ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading'"
                                                    class="w-full text-left px-3 py-2 text-xs rounded-lg cursor-pointer flex items-center justify-between transition-colors"
                                                >
                                                    <span x-text="opt.label"></span>
                                                    <x-heroicon-o-check x-show="String(selected) === String(opt.id)" class="w-4 h-4 text-brand-primary shrink-0" aria-hidden="true" />
                                                </button>
                                            </template>
                                            <div x-show="filtered.length === 0" class="py-3 px-3 text-center text-xs italic text-content-muted">No strands found.</div>
                                        </div>
                                    </div>
                                    @error('p_strand_id') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>

                        @if($isSeniorHighType ?? false)
                            <p class="text-[11px] text-brand-primary font-medium">
                                Strands Apply Only When The Selected Department Is SHS.
                            </p>
                        @endif
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="p_email" class="block text-xs font-semibold text-content-heading mb-1">
                                Email Address <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="p_email"
                                type="email"
                                wire:model="p_email"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('p_email') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_contact_number" class="block text-xs font-semibold text-content-heading mb-1">
                                Contact Number <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="p_contact_number"
                                type="text"
                                wire:model="p_contact_number"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-mono text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                            @error('p_contact_number') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_status" class="block text-xs font-semibold text-content-heading mb-1">
                                Account Status <span class="text-brand-secondary">*</span>
                            </label>
                            <select
                                id="p_status"
                                wire:model="p_status"
                                class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading uppercase focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                            >
                                <option value="active">ACTIVE</option>
                                <option value="inactive">INACTIVE</option>
                                <option value="suspended">SUSPENDED</option>
                            </select>
                            @error('p_status') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="p_address" class="block text-xs font-semibold text-content-heading mb-1">
                            Complete Address <span class="text-brand-secondary">*</span>
                        </label>
                        <input
                            id="p_address"
                            type="text"
                            wire:model="p_address"
                            class="w-full px-3 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                        >
                        @error('p_address') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-surface-border/70">
                        <button
                            type="button"
                            wire:click="$set('showPatronModal', false)"
                            class="w-full sm:w-auto px-4 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-semibold text-content-body hover:text-content-heading cursor-pointer transition-colors"
                        >Cancel</button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="savePatron"
                            class="w-full sm:w-auto px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer transition-colors shadow-2xs disabled:opacity-50"
                        >
                            <span wire:loading wire:target="savePatron" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                            <span>Save Patron</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 3: DELETE CONFIRMATION -->
    @if($showDeleteModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.set('showDeleteModal', false)"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reg-delete-modal-title"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl max-w-sm w-full p-6 border border-surface-border space-y-4 z-10 text-center">
                <div class="w-12 h-12 bg-brand-secondary/15 text-brand-secondary rounded-full flex items-center justify-center mx-auto shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>

                <div>
                    <h3 id="reg-delete-modal-title" class="text-sm sm:text-base font-bold text-content-heading">Confirm Deletion</h3>
                    <p class="text-xs text-content-body mt-1">
                        Are You Sure You Want To Delete This {{ $deleteType === 'user' ? 'Assistant User' : 'Patron' }} Record? This Action Cannot Be Undone.
                    </p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="$set('showDeleteModal', false)"
                        class="w-full sm:w-auto px-4 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-semibold text-content-body hover:text-content-heading cursor-pointer transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="deleteRecord"
                        wire:loading.attr="disabled"
                        wire:target="deleteRecord"
                        class="w-full sm:w-auto px-4 py-2 bg-brand-secondary rounded-xl text-xs font-semibold text-white hover:opacity-90 flex items-center justify-center gap-2 cursor-pointer transition-opacity shadow-2xs disabled:opacity-50"
                    >
                        <span wire:loading wire:target="deleteRecord" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
