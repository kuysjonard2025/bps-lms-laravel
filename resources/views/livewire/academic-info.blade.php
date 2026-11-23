{{-- resources/views/livewire/academic-info.blade.php --}}
@php
    use Illuminate\Support\Str;

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
    <section aria-label="Academic information header" class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-content-heading tracking-tight">Academic Information</h1>
            <p class="text-xs text-content-body mt-0.5">Manage grade levels, sections, academic departments, senior high strands, and faculty advisers.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-72">
                <label for="academic-search" class="sr-only">Search academic records</label>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    id="academic-search"
                    placeholder="{{ $currentTab['placeholder'] }}"
                    class="w-full pl-9 pr-9 py-2 text-xs bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                >
                <x-heroicon-o-magnifying-glass
                    wire:loading.remove
                    wire:target="search"
                    class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none"
                    aria-hidden="true"
                />
                <svg
                    wire:loading
                    wire:target="search"
                    class="animate-spin w-4 h-4 text-brand-primary absolute left-3 top-2.5 pointer-events-none"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                @if(!empty($search))
                    <button
                        wire:click="$set('search', '')"
                        type="button"
                        class="absolute right-2.5 top-2 text-content-muted hover:text-content-heading text-xs cursor-pointer rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                        aria-label="Clear search"
                    >
                        &times;
                    </button>
                @endif
            </div>

            <button
                wire:click="openCreate('{{ $activeTab }}')"
                wire:loading.attr="disabled"
                wire:target="openCreate"
                type="button"
                class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-primary-hover rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs shrink-0 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
            >
                <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
                <span>Add {{ $currentTab['singular'] }}</span>
            </button>
        </div>
    </section>

    {{-- Tabs & Contextual Filter --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-surface-border pb-3">
        <div role="tablist" aria-label="Academic information categories" class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
            @foreach ($tabConfig as $key => $meta)
                <button
                    wire:click="$set('activeTab', '{{ $key }}')"
                    type="button"
                    role="tab"
                    aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}"
                    class="px-3.5 py-2 text-xs font-semibold rounded-xl transition cursor-pointer whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $activeTab === $key
                        ? 'bg-brand-primary text-white shadow-2xs'
                        : 'bg-surface-card text-content-body border border-surface-border hover:bg-surface-canvas hover:text-content-heading' }}"
                >
                    {{ $meta['label'] }}
                </button>
            @endforeach
        </div>

        @if ($activeTab === 'sections')
            <div>
                <label for="section-grade-filter" class="sr-only">Filter sections by grade level</label>
                <select
                    id="section-grade-filter"
                    wire:model.live="sectionGradeFilter"
                    class="w-full sm:w-60 text-xs bg-surface-card text-content-heading border border-surface-border rounded-xl px-3 py-2 focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                >
                    <option value="">All Grade Levels</option>
                    @foreach ($allGradeLevels as $gl)
                        <option value="{{ $gl->id }}">{{ Str::title($gl->name) }}</option>
                    @endforeach
                </select>
            </div>
        @elseif ($activeTab === 'advisers')
            <div>
                <label for="adviser-department-filter" class="sr-only">Filter advisers by department</label>
                <select
                    id="adviser-department-filter"
                    wire:model.live="adviserDepartmentFilter"
                    class="w-full sm:w-60 text-xs bg-surface-card text-content-heading border border-surface-border rounded-xl px-3 py-2 focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
                >
                    <option value="">All Departments</option>
                    @foreach ($allDepartments as $dept)
                        <option value="{{ $dept->id }}">{{ Str::title($dept->name) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    {{-- Unified Data Table --}}
    <div class="bg-surface-card border border-surface-border rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left border-collapse text-xs text-content-body">
                <thead>
                    <tr class="bg-surface-canvas border-b border-surface-border text-content-muted font-semibold uppercase text-[11px] tracking-wider">
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
                <tbody class="divide-y divide-surface-border/70">
                    @forelse ($rows as $row)
                        <tr wire:key="{{ $activeTab }}-row-{{ $row->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                            @if (in_array($activeTab, ['grade_levels', 'departments', 'strands'], true))
                                <td class="p-3 pl-4 font-mono font-semibold text-content-heading whitespace-nowrap">{{ strtoupper($row->code) }}</td>
                                <td class="p-3 font-bold text-content-heading whitespace-nowrap">{{ Str::title($row->name) }}</td>
                                @if ($activeTab === 'grade_levels')
                                    <td class="p-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-brand-tint-light text-brand-primary">
                                            {{ $row->sections_count }} {{ Str::plural('Section', $row->sections_count) }}
                                        </span>
                                    </td>
                                @elseif ($activeTab === 'departments')
                                    <td class="p-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-brand-tint-light text-brand-primary">
                                            {{ $row->advisers_count }} {{ Str::plural('Adviser', $row->advisers_count) }}
                                        </span>
                                    </td>
                                @endif
                            @elseif ($activeTab === 'sections')
                                <td class="p-3 pl-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-surface-canvas text-content-heading border border-surface-border">
                                        {{ isset($row->gradeLevel->name) ? Str::title($row->gradeLevel->name) : '—' }}
                                    </span>
                                </td>
                                <td class="p-3 font-bold text-content-heading whitespace-nowrap">{{ Str::title($row->name) }}</td>
                            @elseif ($activeTab === 'advisers')
                                <td class="p-3 pl-4 font-bold text-content-heading whitespace-nowrap">{{ Str::title($row->full_name) }}</td>
                                <td class="p-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-surface-canvas text-content-heading border border-surface-border">
                                        {{ isset($row->department->name) ? Str::title($row->department->name) : '—' }}
                                    </span>
                                </td>
                                <td class="p-3 font-mono text-[11px] text-content-body whitespace-nowrap">{{ $row->contact_number ?: 'N/A' }}</td>
                                <td class="p-3 text-content-body whitespace-nowrap">{{ $row->email ?: 'N/A' }}</td>
                            @endif

                            <td class="p-3 pr-4 text-right whitespace-nowrap space-x-1">
                                @if ($activeTab === 'grade_levels')
                                    <button
                                        wire:click="openCreate('sections', {{ $row->id }})"
                                        type="button"
                                        class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                    >+ Section</button>
                                @elseif ($activeTab === 'departments')
                                    <button
                                        wire:click="openCreate('advisers', {{ $row->id }})"
                                        type="button"
                                        class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                    >+ Adviser</button>
                                @endif
                                <button
                                    wire:click="openEdit('{{ $activeTab }}', {{ $row->id }})"
                                    type="button"
                                    class="text-brand-primary hover:bg-brand-tint-light font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                                >Edit</button>
                                <button
                                    wire:click="confirmDelete('{{ $activeTab }}', {{ $row->id }})"
                                    type="button"
                                    class="text-brand-secondary hover:bg-brand-secondary/10 font-semibold px-2.5 py-1.5 rounded-lg transition cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary"
                                >Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <p class="text-xs font-semibold text-content-heading">No {{ strtolower($currentTab['label']) }} found</p>
                                <p class="text-[11px] text-content-muted mt-1">No {{ strtolower($currentTab['label']) }} found matching your criteria.</p>
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
            aria-labelledby="academic-modal-title"
        >
            <div wire:click="closeModal" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full {{ $modalType === 'advisers' ? 'max-w-lg' : 'max-w-md' }} z-10 max-h-[90dvh] flex flex-col overflow-visible border border-surface-border">
                <div class="bg-surface-canvas px-6 py-4 border-b border-surface-border flex justify-between items-center shrink-0 rounded-t-2xl">
                    <h3 id="academic-modal-title" class="text-sm font-bold text-content-heading">
                        {{ $editingId ? "Edit {$modalSingular}" : "Add {$modalSingular}" }}
                    </h3>
                    <button
                        wire:click="closeModal"
                        type="button"
                        aria-label="Close modal"
                        class="text-content-muted hover:text-content-heading text-lg font-bold cursor-pointer p-1 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                    >&times;</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4 overflow-y-auto">
                    @if (in_array($modalType, ['grade_levels', 'departments', 'strands'], true))
                        <div>
                            <label for="entity-code" class="block text-xs font-semibold text-content-heading mb-1">
                                Code <span class="text-brand-secondary">*</span>
                            </label>
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
                                class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 font-mono uppercase focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('code') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                            @error('code') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="entity-name" class="block text-xs font-semibold text-content-heading mb-1">
                                {{ $modalSingular }} Name <span class="text-brand-secondary">*</span>
                            </label>
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
                                class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('name') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                            @error('name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    @elseif ($modalType === 'sections')
                        @php $comboboxItems = $allGradeLevels; $comboboxLabel = 'Grade Level'; @endphp
                        @include('livewire.partials.academic-parent-combobox')

                        <div>
                            <label for="section-name" class="block text-xs font-semibold text-content-heading mb-1">
                                Section Name <span class="text-brand-secondary">*</span>
                            </label>
                            <input
                                id="section-name"
                                type="text"
                                wire:model="name"
                                maxlength="100"
                                placeholder="e.g. St. Matthew, Einstein"
                                class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('name') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                            >
                            @error('name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    @elseif ($modalType === 'advisers')
                        @php $comboboxItems = $allDepartments; $comboboxLabel = 'Department'; @endphp
                        @include('livewire.partials.academic-parent-combobox')

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="adv-first" class="block text-xs font-semibold text-content-heading mb-1">
                                    First Name <span class="text-brand-secondary">*</span>
                                </label>
                                <input
                                    id="adv-first"
                                    type="text"
                                    wire:model="first_name"
                                    maxlength="100"
                                    class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('first_name') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                                >
                                @error('first_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="adv-middle" class="block text-xs font-semibold text-content-heading mb-1">Middle Name</label>
                                <input
                                    id="adv-middle"
                                    type="text"
                                    wire:model="middle_name"
                                    maxlength="100"
                                    class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('middle_name') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                                >
                                @error('middle_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="adv-last" class="block text-xs font-semibold text-content-heading mb-1">
                                    Last Name <span class="text-brand-secondary">*</span>
                                </label>
                                <input
                                    id="adv-last"
                                    type="text"
                                    wire:model="last_name"
                                    maxlength="100"
                                    class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('last_name') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                                >
                                @error('last_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label for="adv-phone" class="block text-xs font-semibold text-content-heading mb-1">Contact Number</label>
                                <input
                                    id="adv-phone"
                                    type="text"
                                    wire:model="contact_number"
                                    maxlength="20"
                                    placeholder="e.g. 09171234567"
                                    class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 font-mono focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('contact_number') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                                >
                                @error('contact_number') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="adv-email" class="block text-xs font-semibold text-content-heading mb-1">Email Address</label>
                                <input
                                    id="adv-email"
                                    type="email"
                                    wire:model="email"
                                    maxlength="100"
                                    placeholder="adviser@school.edu.ph"
                                    class="w-full text-xs rounded-xl bg-surface-canvas text-content-heading placeholder:text-content-muted border p-2.5 focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition @error('email') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                                >
                                @error('email') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-surface-border/70">
                        <button
                            wire:click="closeModal"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl cursor-pointer transition-colors"
                        >
                            Cancel
                        </button>

                        @if (! $editingId)
                            <button
                                wire:click="save(true)"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                type="button"
                                class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-brand-primary bg-brand-tint-light border border-brand-primary/25 rounded-xl hover:bg-brand-primary/15 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition-colors"
                            >
                                <span wire:loading wire:target="save" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-brand-primary"></span>
                                <span>Create &amp; Add More</span>
                            </button>
                        @endif

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-primary rounded-xl hover:bg-brand-primary-hover cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition-colors shadow-2xs"
                        >
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
            <div wire:click="closeDeleteModal" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs transition-opacity"></div>

            <div class="relative bg-surface-card rounded-2xl shadow-xl w-full max-w-sm z-10 p-6 text-center space-y-4 border border-surface-border">
                <div class="w-12 h-12 rounded-full bg-brand-secondary/15 text-brand-secondary flex items-center justify-center mx-auto shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>

                <div>
                    <h3 id="delete-modal-title" class="text-base font-bold text-content-heading">
                        Delete {{ str($deleteType)->singular()->replace('_', ' ')->title() }}
                    </h3>
                    <p class="text-xs text-content-body mt-1">Are you sure you want to delete this record? This action cannot be undone.</p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button
                        wire:click="closeDeleteModal"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-content-body bg-surface-canvas border border-surface-border hover:text-content-heading rounded-xl cursor-pointer transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deleteItem"
                        wire:loading.attr="disabled"
                        wire:target="deleteItem"
                        type="button"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-white bg-brand-secondary rounded-xl hover:opacity-90 cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50 transition-opacity shadow-2xs"
                    >
                        <span wire:loading wire:target="deleteItem" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-white"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
