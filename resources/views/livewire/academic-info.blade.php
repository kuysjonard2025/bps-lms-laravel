<div class="space-y-6" x-data="{ closeOnEsc(e) { if (e.key === 'Escape') { $wire.showGradeLevelModal = false; $wire.showSectionModal = false; $wire.showDeleteModal = false; } } }" @keydown.window="closeOnEsc">

    {{-- Header & Tab Switcher --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Academic Setup</h1>
            <p class="text-xs text-slate-500 mt-1">Manage grade levels and section assignments across academic tracks.</p>
        </div>

        {{-- Tab Switcher & Action Button --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <div class="inline-flex rounded-xl border border-slate-200/80 bg-slate-100/80 p-1 shadow-xs">
                <button
                    wire:click="$set('activeTab', 'grade_levels')"
                    type="button"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg transition cursor-pointer {{ $activeTab === 'grade_levels' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-500 hover:text-slate-900' }}"
                >
                    Grade Levels
                </button>
                <button
                    wire:click="$set('activeTab', 'sections')"
                    type="button"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg transition cursor-pointer {{ $activeTab === 'sections' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-500 hover:text-slate-900' }}"
                >
                    Sections
                </button>
            </div>

            @if ($activeTab === 'grade_levels')
                <button
                    wire:click="openCreateGradeLevelModal"
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-xl transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Grade Level</span>
                </button>
            @else
                <button
                    wire:click="openCreateSectionModal"
                    type="button"
                    class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-xl transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Section</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-72 lg:w-80">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search {{ $activeTab === 'grade_levels' ? 'grade levels or codes...' : 'sections...' }}"
                class="w-full pl-9 pr-4 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-slate-900 placeholder:text-slate-400"
            >
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        @if ($activeTab === 'sections')
            <div class="w-full sm:w-auto flex items-center gap-2">
                <label for="sectionGradeFilter" class="text-xs font-semibold text-slate-500 shrink-0">Grade Level:</label>
                <select
                    id="sectionGradeFilter"
                    wire:model.live="sectionGradeFilter"
                    class="w-full sm:w-56 px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer text-slate-700"
                >
                    <option value="">All Grade Levels</option>
                    @foreach($allGradeLevels as $gl)
                        <option value="{{ $gl->id }}">{{ $gl->name }} ({{ $gl->code }})</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    {{-- TAB 1: GRADE LEVELS TABLE --}}
    @if ($activeTab === 'grade_levels')
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs text-slate-600">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                            <th scope="col" class="p-3 pl-4 whitespace-nowrap">Code</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Grade Level Name</th>
                            <th scope="col" class="p-3 text-center whitespace-nowrap">Sections Count</th>
                            <th scope="col" class="p-3 pr-4 text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($gradeLevels as $gl)
                            <tr wire:key="gl-row-{{ $gl->id }}" class="hover:bg-slate-50/60 transition-colors">
                                <td class="p-3 pl-4 font-mono font-bold text-blue-600 whitespace-nowrap">{{ $gl->code }}</td>
                                <td class="p-3 font-semibold text-slate-900 whitespace-nowrap">{{ $gl->name }}</td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200/80">
                                        {{ $gl->sections_count }} {{ Str::plural('section', $gl->sections_count) }}
                                    </span>
                                </td>
                                <td class="p-3 pr-4 text-right whitespace-nowrap space-x-1">
                                    <button
                                        wire:click="openCreateSectionModal({{ $gl->id }})"
                                        title="Add Section to {{ $gl->name }}"
                                        type="button"
                                        class="text-emerald-600 hover:text-emerald-800 font-bold px-2 py-1 rounded-lg hover:bg-emerald-50 transition cursor-pointer"
                                    >+ Add Sec</button>
                                    <button
                                        wire:click="openEditGradeLevelModal({{ $gl->id }})"
                                        type="button"
                                        class="text-blue-600 hover:text-blue-800 font-bold px-2 py-1 rounded-lg hover:bg-blue-50 transition cursor-pointer"
                                    >Edit</button>
                                    <button
                                        wire:click="confirmDelete('grade_level', {{ $gl->id }})"
                                        type="button"
                                        class="text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                    >Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-10 text-slate-400">
                                    No grade levels found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($gradeLevels, 'links'))
            <div class="pt-2">
                {{ $gradeLevels->links() }}
            </div>
        @endif
    @endif

    {{-- TAB 2: SECTIONS TABLE --}}
    @if ($activeTab === 'sections')
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs text-slate-600">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 font-bold uppercase text-[11px] tracking-wider">
                            <th scope="col" class="p-3 pl-4 whitespace-nowrap">Section Name</th>
                            <th scope="col" class="p-3 whitespace-nowrap">Grade Level</th>
                            <th scope="col" class="p-3 pr-4 text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sections as $sec)
                            <tr wire:key="sec-row-{{ $sec->id }}" class="hover:bg-slate-50/60 transition-colors">
                                <td class="p-3 pl-4 font-semibold text-slate-900 whitespace-nowrap">{{ $sec->name }}</td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="font-bold text-blue-600 font-mono">{{ $sec->gradeLevel->code ?? 'N/A' }}</span>
                                    <span class="text-slate-500 text-[11px] ml-1">({{ $sec->gradeLevel->name ?? 'Unassigned' }})</span>
                                </td>
                                <td class="p-3 pr-4 text-right whitespace-nowrap space-x-1">
                                    <button
                                        wire:click="openEditSectionModal({{ $sec->id }})"
                                        type="button"
                                        class="text-blue-600 hover:text-blue-800 font-bold px-2 py-1 rounded-lg hover:bg-blue-50 transition cursor-pointer"
                                    >Edit</button>
                                    <button
                                        wire:click="confirmDelete('section', {{ $sec->id }})"
                                        type="button"
                                        class="text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                    >Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-10 text-slate-400">
                                    No sections found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($sections, 'links'))
            <div class="pt-2">
                {{ $sections->links() }}
            </div>
        @endif
    @endif

    {{-- GRADE LEVEL MODAL --}}
    @if ($showGradeLevelModal)
        <div
            x-data
            @keydown.escape.window="$wire.set('showGradeLevelModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div wire:click="$set('showGradeLevelModal', false)" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md z-10 overflow-hidden my-auto flex flex-col border border-slate-100">
                <div class="bg-slate-50 px-5 sm:px-6 py-4 border-b border-slate-200/80 flex justify-between items-center shrink-0">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $gradeLevelIdBeingEdited ? 'Edit Grade Level' : 'Add Grade Level' }}
                    </h3>
                    <button wire:click="$set('showGradeLevelModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer p-1">&times;</button>
                </div>

                <form wire:submit="saveGradeLevel" class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label for="gl_name" class="block text-xs font-semibold text-slate-700">Grade Level Name *</label>
                        <input
                            id="gl_name"
                            type="text"
                            wire:model="gl_name"
                            autofocus
                            placeholder="e.g. Grade 11 - STEM"
                            class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-slate-900 placeholder:text-slate-400"
                        >
                        @error('gl_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="gl_code" class="block text-xs font-semibold text-slate-700">Code *</label>
                        <input
                            id="gl_code"
                            type="text"
                            wire:model="gl_code"
                            placeholder="e.g. G11-STEM"
                            class="mt-1 w-full text-xs font-mono uppercase rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-slate-900 placeholder:text-slate-400"
                        >
                        @error('gl_code') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-2 border-t border-slate-100">
                        <button
                            wire:click="$set('showGradeLevelModal', false)"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 cursor-pointer transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 cursor-pointer flex items-center justify-center gap-2 transition shadow-xs"
                        >
                            <span wire:loading wire:target="saveGradeLevel" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                            <span>{{ $gradeLevelIdBeingEdited ? 'Save Changes' : 'Create Grade Level' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- SECTION MODAL --}}
    @if ($showSectionModal)
        <div
            x-data
            @keydown.escape.window="$wire.set('showSectionModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div wire:click="$set('showSectionModal', false)" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md z-10 overflow-hidden my-auto flex flex-col border border-slate-100">
                <div class="bg-slate-50 px-5 sm:px-6 py-4 border-b border-slate-200/80 flex justify-between items-center shrink-0">
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $sectionIdBeingEdited ? 'Edit Section' : 'Add Section' }}
                    </h3>
                    <button wire:click="$set('showSectionModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold cursor-pointer p-1">&times;</button>
                </div>

                <form wire:submit="saveSection" class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label for="sec_grade_level_id" class="block text-xs font-semibold text-slate-700">Grade Level Assignment *</label>
                        <select
                            id="sec_grade_level_id"
                            wire:model="sec_grade_level_id"
                            autofocus
                            class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all bg-white text-slate-900"
                        >
                            <option value="">Select Grade Level</option>
                            @foreach($allGradeLevels as $gl)
                                <option value="{{ $gl->id }}">{{ $gl->name }} ({{ $gl->code }})</option>
                            @endforeach
                        </select>
                        @error('sec_grade_level_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="sec_name" class="block text-xs font-semibold text-slate-700">Section Name *</label>
                        <input
                            id="sec_name"
                            type="text"
                            wire:model="sec_name"
                            placeholder="e.g. St. Jude, Section A"
                            class="mt-1 w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-slate-900 placeholder:text-slate-400"
                        >
                        @error('sec_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-2 border-t border-slate-100">
                        <button
                            wire:click="$set('showSectionModal', false)"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 cursor-pointer transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 cursor-pointer flex items-center justify-center gap-2 transition shadow-xs"
                        >
                            <span wire:loading wire:target="saveSection" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                            <span>{{ $sectionIdBeingEdited ? 'Save Changes' : 'Create Section' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- DELETE CONFIRMATION MODAL --}}
    @if ($showDeleteModal)
        <div
            x-data
            @keydown.escape.window="$wire.set('showDeleteModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm z-10 p-5 sm:p-6 text-center space-y-4 border border-slate-100">
                <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto shrink-0 border border-rose-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900">Delete {{ $deleteType === 'grade_level' ? 'Grade Level' : 'Section' }}?</h3>
                    <p class="text-xs text-slate-500 mt-1">Are you sure you want to delete this record? This action cannot be undone.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button
                        wire:click="$set('showDeleteModal', false)"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 cursor-pointer transition"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteItem"
                        wire:loading.attr="disabled"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition shadow-xs"
                    >
                        <span wire:loading wire:target="deleteItem" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete Record</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
