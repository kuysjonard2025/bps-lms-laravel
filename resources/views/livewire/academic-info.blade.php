@php
    $tabConfig = [
        'grade_levels' => ['label' => 'Grade Levels', 'singular' => 'Grade Level', 'placeholder' => 'Search grade level or code...'],
        'sections'     => ['label' => 'Sections',     'singular' => 'Section',     'placeholder' => 'Search section name...'],
        'departments'  => ['label' => 'Departments',  'singular' => 'Department',  'placeholder' => 'Search department or code...'],
        'strands'      => ['label' => 'Strands',      'singular' => 'Strand',      'placeholder' => 'Search strand or code...'],
        'advisers'     => ['label' => 'Advisers',     'singular' => 'Adviser',     'placeholder' => 'Search adviser name, email, dept...'],
    ];
    $currentTab = $tabConfig[$activeTab] ?? $tabConfig['grade_levels'];
    $modalSingular = $tabConfig[$modalType]['singular'] ?? 'Record';
@endphp

<div class="space-y-6">

    {{-- Header & Quick Actions --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Academic Information</h1>
            <p class="text-xs text-slate-500 mt-1">Manage grade levels, sections, academic departments, senior high strands, and faculty advisers.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    id="academic-search"
                    placeholder="{{ $currentTab['placeholder'] }}"
                    class="w-full pl-9 pr-9 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all"
                >
                <svg wire:loading.remove wire:target="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <svg wire:loading wire:target="search" class="animate-spin w-4 h-4 text-blue-600 absolute left-3 top-2.5 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <button
                wire:click="openCreate('{{ $activeTab }}')"
                type="button"
                class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-xl transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 touch-manipulation"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add {{ $currentTab['singular'] }}</span>
            </button>
        </div>
    </div>

    {{-- Tabs & Contextual Filter --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
            @foreach ($tabConfig as $key => $meta)
                <button
                    wire:click="$set('activeTab', '{{ $key }}')"
                    type="button"
                    class="px-3.5 py-2 text-xs font-semibold rounded-xl transition cursor-pointer whitespace-nowrap {{ $activeTab === $key
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'bg-white text-slate-600 border border-slate-200/80 hover:bg-slate-50 hover:text-slate-900' }}"
                >
                    {{ $meta['label'] }}
                </button>
            @endforeach
        </div>

        @if ($activeTab === 'sections')
            <select wire:model.live="sectionGradeFilter" aria-label="Filter sections by grade level" class="w-full sm:w-60 text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all">
                <option value="">All Grade Levels</option>
                @foreach ($allGradeLevels as $gl)
                    <option value="{{ $gl->id }}">{{ ucwords($gl->name) }}</option>
                @endforeach
            </select>
        @elseif ($activeTab === 'advisers')
            <select wire:model.live="adviserDepartmentFilter" aria-label="Filter advisers by department" class="w-full sm:w-60 text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all">
                <option value="">All Departments</option>
                @foreach ($allDepartments as $dept)
                    <option value="{{ $dept->id }}">{{ ucwords($dept->name) }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Unified Data Table --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs text-slate-600">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                        @if (in_array($activeTab, ['grade_levels', 'departments', 'strands'], true))
                            <th scope="col" class="p-3 pl-4 whitespace-nowrap">Code</th>
                            <th scope="col" class="p-3 whitespace-nowrap">{{ $currentTab['singular'] }} Name</th>
                            @if ($activeTab === 'grade_levels')
                                <th scope="col" class="p-3 whitespace-nowrap">Sections</th>
                            @elseif ($activeTab === 'departments')
                                <th scope="col" class="p-3 whitespace-nowrap">Advisers</th>
                            @endif
                        @elseif ($activeTab === 'sections')
                            <th scope="col" class="p-3 pl-4 whitespace-nowrap">Grade Level</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Section Name</th>
                        @elseif ($activeTab === 'advisers')
                            <th scope="col" class="p-3 pl-4 whitespace-nowrap">Adviser Name</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Department</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Contact Number</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Email</th>
                        @endif
                        <th scope="col" class="p-3 pr-4 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $row)
                        <tr wire:key="{{ $activeTab }}-row-{{ $row->id }}" class="hover:bg-slate-50/60 transition-colors">
                            @if (in_array($activeTab, ['grade_levels', 'departments', 'strands'], true))
                                <td class="p-3 pl-4 font-mono font-semibold text-slate-700 whitespace-nowrap">{{ strtoupper($row->code) }}</td>
                                <td class="p-3 font-bold text-slate-900 whitespace-nowrap">{{ ucwords($row->name) }}</td>
                                @if ($activeTab === 'grade_levels')
                                    <td class="p-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                            {{ $row->sections_count }} {{ Str::plural('Section', $row->sections_count) }}
                                        </span>
                                    </td>
                                @elseif ($activeTab === 'departments')
                                    <td class="p-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                            {{ $row->advisers_count }} {{ Str::plural('Adviser', $row->advisers_count) }}
                                        </span>
                                    </td>
                                @endif
                            @elseif ($activeTab === 'sections')
                                <td class="p-3 pl-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                        {{ isset($row->gradeLevel->name) ? ucwords($row->gradeLevel->name) : '—' }}
                                    </span>
                                </td>
                                <td class="p-3 font-bold text-slate-900 whitespace-nowrap">{{ ucwords($row->name) }}</td>
                            @elseif ($activeTab === 'advisers')
                                <td class="p-3 pl-4 font-bold text-slate-900 whitespace-nowrap">{{ ucwords($row->full_name) }}</td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                        {{ isset($row->department->name) ? ucwords($row->department->name) : '—' }}
                                    </span>
                                </td>
                                <td class="p-3 font-mono text-[11px] text-slate-600 whitespace-nowrap">{{ $row->contact_number ?: 'N/A' }}</td>
                                <td class="p-3 text-slate-600 whitespace-nowrap">{{ $row->email ?: 'N/A' }}</td>
                            @endif

                            <td class="p-3 pr-4 text-right whitespace-nowrap space-x-1">
                                @if ($activeTab === 'grade_levels')
                                    <button wire:click="openCreate('sections', {{ $row->id }})" type="button" class="text-emerald-600 hover:text-emerald-800 font-bold px-2 py-1 rounded-lg hover:bg-emerald-50 transition cursor-pointer">+ Section</button>
                                @elseif ($activeTab === 'departments')
                                    <button wire:click="openCreate('advisers', {{ $row->id }})" type="button" class="text-emerald-600 hover:text-emerald-800 font-bold px-2 py-1 rounded-lg hover:bg-emerald-50 transition cursor-pointer">+ Adviser</button>
                                @endif
                                <button wire:click="openEdit('{{ $activeTab }}', {{ $row->id }})" type="button" class="text-blue-600 hover:text-blue-800 font-bold px-2 py-1 rounded-lg hover:bg-blue-50 transition cursor-pointer">Edit</button>
                                <button wire:click="confirmDelete('{{ $activeTab }}', {{ $row->id }})" type="button" class="text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 transition cursor-pointer">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">
                                No {{ strtolower($currentTab['label']) }} found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (method_exists($rows, 'links'))
        <div class="pt-2">{{ $rows->links() }}</div>
    @endif

    {{-- Unified Create / Edit Modal --}}
    @if ($showModal)
        <div
            x-data
            @keydown.escape.window="$wire.closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div wire:click="closeModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full {{ $modalType === 'advisers' ? 'max-w-lg' : 'max-w-md' }} z-10 max-h-[90vh] flex flex-col overflow-visible border border-slate-100">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200/80 flex justify-between items-center shrink-0 rounded-t-2xl">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $editingId ? "Edit {$modalSingular}" : "Add {$modalSingular}" }}
                    </h3>
                    <button wire:click="closeModal" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer p-1">&times;</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4 overflow-y-auto">
                    @if (in_array($modalType, ['grade_levels', 'departments', 'strands'], true))
                        <div>
                            <label for="entity-code" class="block text-xs font-semibold text-slate-700">Code *</label>
                            <input
                                id="entity-code"
                                type="text"
                                wire:model="code"
                                maxlength="30"
                                placeholder="{{ match($modalType) {
                                    'grade_levels' => 'e.g. G7, G11',
                                    'departments'  => 'e.g. JHS, SHS',
                                    'strands'      => 'e.g. STEM, ABM',
                                    default        => 'Enter code...',
                                } }}"
                                class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 font-mono uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all"
                            >
                            @error('code') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="entity-name" class="block text-xs font-semibold text-slate-700">{{ $modalSingular }} Name *</label>
                            <input
                                id="entity-name"
                                type="text"
                                wire:model="name"
                                maxlength="120"
                                placeholder="{{ match($modalType) {
                                    'grade_levels' => 'e.g. Grade 7, Grade 11',
                                    'departments'  => 'e.g. Senior High School',
                                    'strands'      => 'e.g. Science, Technology, Engineering, and Mathematics',
                                    default        => 'Enter name...',
                                } }}"
                                class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all"
                            >
                            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @elseif ($modalType === 'sections')
                        @php $comboboxItems = $allGradeLevels; $comboboxLabel = 'Grade Level'; @endphp
                        @include('livewire.partials.academic-parent-combobox')

                        <div>
                            <label for="section-name" class="block text-xs font-semibold text-slate-700">Section Name *</label>
                            <input id="section-name" type="text" wire:model="name" maxlength="100" placeholder="e.g. St. Matthew, Einstein" class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all">
                            @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @elseif ($modalType === 'advisers')
                        @php $comboboxItems = $allDepartments; $comboboxLabel = 'Department'; @endphp
                        @include('livewire.partials.academic-parent-combobox')

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="adv-first" class="block text-xs font-semibold text-slate-700">First Name *</label>
                                <input id="adv-first" type="text" wire:model="first_name" maxlength="100" class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all">
                                @error('first_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="adv-middle" class="block text-xs font-semibold text-slate-700">Middle Name</label>
                                <input id="adv-middle" type="text" wire:model="middle_name" maxlength="100" class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all">
                                @error('middle_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="adv-last" class="block text-xs font-semibold text-slate-700">Last Name *</label>
                                <input id="adv-last" type="text" wire:model="last_name" maxlength="100" class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all">
                                @error('last_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label for="adv-phone" class="block text-xs font-semibold text-slate-700">Contact Number</label>
                                <input id="adv-phone" type="text" wire:model="contact_number" maxlength="20" placeholder="e.g. 09171234567" class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 font-mono focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all">
                                @error('contact_number') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="adv-email" class="block text-xs font-semibold text-slate-700">Email Address</label>
                                <input id="adv-email" type="email" wire:model="email" maxlength="100" placeholder="adviser@school.edu.ph" class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-hidden transition-all">
                                @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-100">
                        <button wire:click="closeModal" type="button" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 cursor-pointer transition">
                            Cancel
                        </button>

                        @if (! $editingId)
                            <button
                                wire:click="save(true)"
                                wire:loading.attr="disabled"
                                type="button"
                                class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition"
                            >
                                <span wire:loading wire:target="save(true)" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-blue-700"></span>
                                <span>Create &amp; Add More</span>
                            </button>
                        @endif

                        <button type="submit" wire:loading.attr="disabled" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition shadow-xs">
                            <span wire:loading wire:target="save" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                            <span>{{ $editingId ? 'Save Changes' : "Create {$modalSingular}" }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div
            x-data
            @keydown.escape.window="$wire.closeDeleteModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-modal-title"
        >
            <div wire:click="closeDeleteModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm z-10 p-6 text-center space-y-4 border border-slate-100">
                <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto shrink-0 border border-rose-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <div>
                    <h3 id="delete-modal-title" class="text-base font-bold text-slate-900">
                        Delete {{ str($deleteType)->singular()->replace('_', ' ')->title() }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Are you sure you want to delete this record? This action cannot be undone.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button wire:click="closeDeleteModal" type="button" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 cursor-pointer transition">
                        Cancel
                    </button>
                    <button wire:click="deleteItem" wire:loading.attr="disabled" type="button" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition shadow-xs">
                        <span wire:loading wire:target="deleteItem" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
