<div class="p-4 sm:p-6 space-y-4 max-w-full">
    {{-- Header Container --}}
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Academic Setup</h2>
            <p class="text-xs text-gray-500">Manage grade levels and section assignments.</p>
        </div>

        {{-- Tab Switcher & Actions --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                <button
                    wire:click="$set('activeTab', 'grade_levels')"
                    type="button"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md transition cursor-pointer {{ $activeTab === 'grade_levels' ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}"
                >
                    Grade Levels
                </button>
                <button
                    wire:click="$set('activeTab', 'sections')"
                    type="button"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md transition cursor-pointer {{ $activeTab === 'sections' ? 'bg-white text-blue-600 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}"
                >
                    Sections
                </button>
            </div>

            @if ($activeTab === 'grade_levels')
                <button
                    wire:click="openCreateGradeLevelModal"
                    type="button"
                    class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Grade Level</span>
                </button>
            @else
                <button
                    wire:click="openCreateSectionModal"
                    type="button"
                    class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Section</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-72">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search {{ $activeTab === 'grade_levels' ? 'grade levels or codes...' : 'sections...' }}"
                class="w-full pl-9 pr-4 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        @if ($activeTab === 'sections')
            <div class="w-full sm:w-auto flex items-center gap-2">
                <label for="sectionGradeFilter" class="text-xs font-medium text-gray-500 shrink-0">Grade Level:</label>
                <select id="sectionGradeFilter" wire:model.live="sectionGradeFilter" class="w-full sm:w-48 px-3 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
            <table class="w-full text-left text-xs text-gray-700">
                <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-4 py-3">Code</th>
                        <th scope="col" class="px-4 py-3">Grade Level Name</th>
                        <th scope="col" class="px-4 py-3 text-center">Sections Count</th>
                        <th scope="col" class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($gradeLevels as $gl)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 font-mono font-bold text-blue-600">{{ $gl->code }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $gl->name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $gl->sections_count }} {{ Str::plural('section', $gl->sections_count) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <button
                                    wire:click="openCreateSectionModal({{ $gl->id }})"
                                    title="Add Section to {{ $gl->name }}"
                                    class="text-emerald-600 hover:text-emerald-800 font-semibold px-2 py-1 rounded hover:bg-emerald-50 transition cursor-pointer"
                                >+ Add Sec</button>
                                <button
                                    wire:click="openEditGradeLevelModal({{ $gl->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded hover:bg-blue-50 transition cursor-pointer"
                                >Edit</button>
                                <button
                                    wire:click="confirmDelete('grade_level', {{ $gl->id }})"
                                    class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded hover:bg-red-50 transition cursor-pointer"
                                >Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No grade levels found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $gradeLevels->links() }}</div>
    @endif

    {{-- TAB 2: SECTIONS TABLE --}}
    @if ($activeTab === 'sections')
        <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
            <table class="w-full text-left text-xs text-gray-700">
                <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-4 py-3">Section Name</th>
                        <th scope="col" class="px-4 py-3">Grade Level</th>
                        <th scope="col" class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($sections as $sec)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $sec->name }}</td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-blue-600 font-mono">{{ $sec->gradeLevel->code ?? 'N/A' }}</span>
                                <span class="text-gray-500 text-[11px] ml-1">({{ $sec->gradeLevel->name ?? 'Unassigned' }})</span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <button
                                    wire:click="openEditSectionModal({{ $sec->id }})"
                                    class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded hover:bg-blue-50 transition cursor-pointer"
                                >Edit</button>
                                <button
                                    wire:click="confirmDelete('section', {{ $sec->id }})"
                                    class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded hover:bg-red-50 transition cursor-pointer"
                                >Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">No sections found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $sections->links() }}</div>
    @endif

    {{-- GRADE LEVEL MODAL --}}
    @if ($showGradeLevelModal)
        <div
            wire:keydown.escape.window="$set('showGradeLevelModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        >
            <div wire:click="$set('showGradeLevelModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10 overflow-hidden my-auto flex flex-col">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900">{{ $gradeLevelIdBeingEdited ? 'Edit Grade Level' : 'Add Grade Level' }}</h3>
                    <button wire:click="$set('showGradeLevelModal', false)" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveGradeLevel" class="p-4 sm:p-6 space-y-4">
                    <div>
                        <label for="gl_name" class="block text-xs font-medium text-gray-700">Grade Level Name *</label>
                        <input id="gl_name" type="text" wire:model="gl_name" autofocus placeholder="e.g. Grade 11 - STEM" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('gl_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="gl_code" class="block text-xs font-medium text-gray-700">Code *</label>
                        <input id="gl_code" type="text" wire:model="gl_code" placeholder="e.g. G11-STEM" class="mt-1 w-full text-xs sm:text-sm font-mono uppercase rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('gl_code') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-4 flex justify-end gap-2.5 pt-3 border-t border-gray-100">
                        <button wire:click="$set('showGradeLevelModal', false)" type="button" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-3.5 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 cursor-pointer transition">
                            {{ $gradeLevelIdBeingEdited ? 'Save Changes' : 'Create Grade Level' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- SECTION MODAL --}}
    @if ($showSectionModal)
        <div
            wire:keydown.escape.window="$set('showSectionModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
        >
            <div wire:click="$set('showSectionModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10 overflow-hidden my-auto flex flex-col">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900">{{ $sectionIdBeingEdited ? 'Edit Section' : 'Add Section' }}</h3>
                    <button wire:click="$set('showSectionModal', false)" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit="saveSection" class="p-4 sm:p-6 space-y-4">
                    <div>
                        <label for="sec_grade_level_id" class="block text-xs font-medium text-gray-700">Grade Level Assignment *</label>
                        <select id="sec_grade_level_id" wire:model="sec_grade_level_id" autofocus class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-xs bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Grade Level</option>
                            @foreach($allGradeLevels as $gl)
                                <option value="{{ $gl->id }}">{{ $gl->name }} ({{ $gl->code }})</option>
                            @endforeach
                        </select>
                        @error('sec_grade_level_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="sec_name" class="block text-xs font-medium text-gray-700">Section Name *</label>
                        <input id="sec_name" type="text" wire:model="sec_name" placeholder="e.g. St. Jude, Section A" class="mt-1 w-full text-xs sm:text-sm rounded-md border-gray-300 border p-2 shadow-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('sec_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-4 flex justify-end gap-2.5 pt-3 border-t border-gray-100">
                        <button wire:click="$set('showSectionModal', false)" type="button" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 cursor-pointer">Cancel</button>
                        <button type="submit" class="px-3.5 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 cursor-pointer transition">
                            {{ $sectionIdBeingEdited ? 'Save Changes' : 'Create Section' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- DELETE CONFIRMATION MODAL --}}
    @if ($showDeleteModal)
        <div
            wire:keydown.escape.window="$set('showDeleteModal', false)"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10 p-6 space-y-4 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Delete {{ $deleteType === 'grade_level' ? 'Grade Level' : 'Section' }}?</h3>
                    <p class="text-xs text-gray-500 mt-1">Are you sure you want to delete this record? This action cannot be undone.</p>
                </div>
                <div class="flex justify-center gap-3 pt-2">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 cursor-pointer">Cancel</button>
                    <button wire:click="deleteItem" type="button" class="px-4 py-2 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 cursor-pointer shadow-xs">Delete Record</button>
                </div>
            </div>
        </div>
    @endif
</div>
