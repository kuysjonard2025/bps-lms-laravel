<?php // resources/views/livewire/registrations/patrons-tab.blade.php ?>
@php
    use Illuminate\Support\Str;
@endphp

<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
            <label for="pt-search" class="sr-only">Search patrons</label>
            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-2.5 pointer-events-none" aria-hidden="true" />
            <input
                id="pt-search"
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search Patrons, RFID, ID, Dept..."
                class="w-full pl-9 pr-8 py-2 bg-surface-card border border-surface-border rounded-xl text-xs text-content-heading placeholder:text-content-muted focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none transition"
            >
            @if($search !== '')
                <button
                    wire:click="$set('search', '')"
                    type="button"
                    class="absolute right-2.5 top-2 text-content-muted hover:text-content-heading text-xs cursor-pointer"
                    aria-label="Clear search"
                >&times;</button>
            @endif
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button
                type="button"
                wire:click="exportExcel"
                wire:target="exportExcel"
                wire:loading.attr="disabled"
                class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-semibold rounded-xl border border-surface-border bg-surface-card hover:bg-brand-tint-light hover:text-brand-primary text-content-heading transition cursor-pointer disabled:opacity-50 whitespace-nowrap focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
            >
                <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                <span wire:loading wire:target="exportExcel">Exporting...</span>
            </button>
            <button
                type="button"
                wire:click="exportPdf"
                wire:target="exportPdf"
                wire:loading.attr="disabled"
                class="flex-1 sm:flex-none px-3.5 py-2 text-xs font-semibold rounded-xl border border-surface-border bg-surface-card hover:bg-brand-tint-light hover:text-brand-primary text-content-heading transition cursor-pointer disabled:opacity-50 whitespace-nowrap focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/15"
            >
                <span wire:loading.remove wire:target="exportPdf">Export PDF</span>
                <span wire:loading wire:target="exportPdf">Exporting...</span>
            </button>

            <button
                type="button"
                wire:click="openCreatePatronModal"
                wire:loading.attr="disabled"
                wire:target="openCreatePatronModal"
                class="flex-1 sm:flex-none px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer disabled:opacity-50"
            >
                <x-heroicon-o-plus class="w-4 h-4" aria-hidden="true" />
                <span>Register Patron</span>
            </button>
        </div>
    </div>

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
                        @php
                            $adviserName = $patron->adviser?->full_name
                                ?? $patron->adviser?->name
                                ?? trim(($patron->adviser?->last_name ? $patron->adviser->last_name . ', ' : '') . ($patron->adviser?->first_name ?? '') . ' ' . ($patron->adviser?->middle_name ?? '') . ' ' . ($patron->adviser?->suffix ?? ''));
                        @endphp
                        <tr wire:key="patron-{{ $patron->id }}" class="hover:bg-surface-canvas/70 transition-colors">
                            <td class="px-4 py-3 font-mono font-semibold text-content-heading whitespace-nowrap uppercase">{{ strtoupper($patron->school_id) }}</td>
                            <td class="px-4 py-3 font-mono text-brand-primary whitespace-nowrap uppercase">
                                <span class="inline-flex items-center gap-1.5 bg-brand-tint-light px-2 py-1 rounded-lg border border-brand-primary/20">
                                    <x-heroicon-o-key class="w-3.5 h-3.5" aria-hidden="true" />
                                    <span>{{ strtoupper($patron->rfid_tag) }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-content-heading whitespace-nowrap">
                                {{ Str::title(trim("{$patron->last_name}, {$patron->first_name} {$patron->middle_name} {$patron->suffix}")) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-content-heading uppercase">{{ $patron->patronType?->name ? strtoupper($patron->patronType->name) : 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-content-heading uppercase">{{ $patron->department?->name ? strtoupper($patron->department->name) : '—' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $patron->gradeLevel?->name ? Str::title($patron->gradeLevel->name) : '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $patron->section?->name ? Str::title($patron->section->name) : '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $adviserName ? Str::title($adviserName) : '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($patron->strand)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono font-semibold bg-brand-tint-light text-brand-primary border border-brand-primary/20 uppercase">
                                        {{ strtoupper($patron->strand->code) }}
                                    </span>
                                @else
                                    <span class="text-content-muted">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-content-heading font-medium whitespace-nowrap">{{ $patron->contact_number }}</td>
                            <td class="px-4 py-3 text-content-heading font-medium whitespace-nowrap">{{ $patron->email ?: 'N/A' }}</td>
                            <td class="px-4 py-3 max-w-xs truncate whitespace-nowrap" title="{{ $patron->address ? Str::title($patron->address) : 'N/A' }}">
                                {{ $patron->address ? Str::title($patron->address) : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase {{ match($patron->status) {
                                    'active' => 'bg-brand-tint-light text-brand-primary',
                                    'inactive', 'suspended' => 'bg-brand-secondary/15 text-brand-secondary',
                                    default => 'bg-surface-canvas text-content-heading border border-surface-border'
                                } }}">{{ strtoupper($patron->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                <button type="button" wire:click="openEditPatronModal({{ $patron->id }})" class="text-brand-primary hover:bg-brand-tint-light font-semibold text-xs px-2.5 py-1.5 rounded-lg transition cursor-pointer">Edit</button>
                                <button type="button" wire:click="confirmDelete({{ $patron->id }})" class="text-brand-secondary hover:bg-brand-secondary/10 font-semibold text-xs px-2.5 py-1.5 rounded-lg transition cursor-pointer">Delete</button>
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

    @if($showPatronModal)
        @php
            $deptOpts = $allDepartments->map(fn($d) => ['id' => $d->id, 'label' => strtoupper($d->name)])->values()->all();
            $gradeOpts = $availableGradeLevels->map(fn($g) => ['id' => $g->id, 'label' => Str::title($g->name)])->values()->all();
            $sectionOpts = $availableSections->map(fn($s) => ['id' => $s->id, 'label' => Str::title($s->name)])->values()->all();
            $adviserOpts = $availableAdvisers->map(function ($adv) {
                $raw = $adv->full_name ?? $adv->name ?? trim(($adv->last_name ? $adv->last_name . ', ' : '') . ($adv->first_name ?? '') . ' ' . ($adv->middle_name ?? '') . ' ' . ($adv->suffix ?? ''));
                return ['id' => $adv->id, 'label' => Str::title($raw ?: ('Adviser #' . $adv->id))];
            })->values()->all();
            $strandOpts = $allStrands->map(fn($st) => ['id' => $st->id, 'label' => strtoupper($st->code) . ' - ' . Str::title($st->name)])->values()->all();
        @endphp

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
                    <h2 id="patron-modal-title" class="text-sm sm:text-base font-bold text-content-heading">
                        {{ $patronIdBeingEdited ? 'Edit Patron Record' : 'Register New Patron' }}
                    </h2>
                    <button type="button" wire:click="$set('showPatronModal', false)" aria-label="Close modal" class="text-content-muted hover:text-content-heading p-1 rounded-lg cursor-pointer">
                        <x-heroicon-o-x-mark class="w-5 h-5" aria-hidden="true" />
                    </button>
                </div>

                <form wire:submit="savePatron" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-brand-tint-light/60 p-3.5 rounded-xl border border-brand-primary/20">
                        <div>
                            <label for="p_school_id" class="block text-xs font-semibold text-content-heading mb-1">Student/Employee # <span class="text-brand-secondary">*</span></label>
                            <input id="p_school_id" type="text" wire:model="p_school_id" wire:keydown.enter.prevent placeholder="Enter Student/Employee Number" class="w-full px-3 py-2 bg-surface-card border rounded-xl text-xs font-mono text-content-heading uppercase focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_school_id') border-brand-secondary @else border-surface-border @enderror">
                            @error('p_school_id') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_rfid_tag" class="block text-xs font-semibold text-content-heading mb-1">RFID Tag UID <span class="text-brand-secondary">* (Scan Card Now)</span></label>
                            <input id="p_rfid_tag" type="text" wire:model="p_rfid_tag" wire:keydown.enter.prevent placeholder="Tap RFID Card On Scanner..." class="w-full px-3 py-2 bg-surface-card border rounded-xl text-xs font-mono text-content-heading uppercase focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_rfid_tag') border-brand-secondary @else border-surface-border @enderror">
                            @error('p_rfid_tag') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="p_first_name" class="block text-xs font-semibold text-content-heading mb-1">First Name <span class="text-brand-secondary">*</span></label>
                            <input id="p_first_name" type="text" wire:model="p_first_name" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_first_name') border-brand-secondary @else border-surface-border @enderror">
                            @error('p_first_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_middle_name" class="block text-xs font-semibold text-content-heading mb-1">Middle Name</label>
                            <input id="p_middle_name" type="text" wire:model="p_middle_name" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_middle_name') border-brand-secondary @else border-surface-border @enderror">
                            @error('p_middle_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_last_name" class="block text-xs font-semibold text-content-heading mb-1">Last Name <span class="text-brand-secondary">*</span></label>
                            <input id="p_last_name" type="text" wire:model="p_last_name" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_last_name') border-brand-secondary @else border-surface-border @enderror">
                            @error('p_last_name') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_suffix" class="block text-xs font-semibold text-content-heading mb-1">Suffix</label>
                            <input id="p_suffix" type="text" wire:model="p_suffix" placeholder="Jr., III" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_suffix') border-brand-secondary @else border-surface-border @enderror">
                            @error('p_suffix') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 {{ $isStudentType ? 'md:grid-cols-4' : 'md:grid-cols-2' }} gap-4">
                        <div>
                            <label for="p_patron_type_id" class="block text-xs font-semibold text-content-heading mb-1">Patron Type <span class="text-brand-secondary">*</span></label>
                            <select id="p_patron_type_id" wire:model.live="p_patron_type_id" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading uppercase focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_patron_type_id') border-brand-secondary @else border-surface-border @enderror">
                                <option value="">SELECT PATRON TYPE</option>
                                @foreach($patronTypes as $type)
                                    <option value="{{ $type->id }}">{{ strtoupper($type->name) }}</option>
                                @endforeach
                            </select>
                            @error('p_patron_type_id') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <livewire:registrations.searchable-combobox
                            wire:model.live="p_department_id"
                            wire:key="pt-combo-dept-{{ $patronIdBeingEdited ?? 'new' }}"
                            :options="$deptOpts"
                            label="Department"
                            placeholder="Select Department"
                            search-placeholder="Type to search department..."
                            empty-text="No departments found."
                            :required="true"
                            :uppercase="true"
                            error-key="p_department_id"
                        />

                        @if($isStudentType)
                            <livewire:registrations.searchable-combobox
                                wire:model.live="p_grade_level_id"
                                wire:key="pt-combo-grade-{{ $p_department_id ?? 'none' }}"
                                :options="$gradeOpts"
                                label="Grade Level"
                                placeholder="Select Grade Level"
                                search-placeholder="Type to search grade level..."
                                empty-text="No grade levels found."
                                :required="true"
                                :disabled="! $p_department_id"
                                error-key="p_grade_level_id"
                            />

                            <livewire:registrations.searchable-combobox
                                wire:model="p_section_id"
                                wire:key="pt-combo-section-{{ $p_grade_level_id ?? 'none' }}"
                                :options="$sectionOpts"
                                label="Section"
                                placeholder="Select Section"
                                search-placeholder="Type to search section..."
                                empty-text="No sections found."
                                :required="true"
                                :disabled="! $p_grade_level_id"
                                error-key="p_section_id"
                            />
                        @endif
                    </div>

                    @if($isStudentType)
                        <div class="grid grid-cols-1 {{ $isSeniorHighType ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-4 bg-brand-tint-light/50 p-3.5 rounded-xl border border-brand-primary/20">
                            <livewire:registrations.searchable-combobox
                                wire:model="p_adviser_id"
                                wire:key="pt-combo-adviser-{{ $p_department_id ?? 'none' }}"
                                :options="$adviserOpts"
                                label="Adviser"
                                placeholder="Select Adviser"
                                search-placeholder="Type to search adviser..."
                                empty-text="No advisers found."
                                :required="true"
                                :disabled="! $p_department_id"
                                error-key="p_adviser_id"
                            />

                            @if($isSeniorHighType)
                                <livewire:registrations.searchable-combobox
                                    wire:model="p_strand_id"
                                    wire:key="pt-combo-strand-{{ $p_department_id ?? 'none' }}"
                                    :options="$strandOpts"
                                    label="Strand (SHS Department Only)"
                                    placeholder="Select Strand"
                                    search-placeholder="Type to search strand..."
                                    empty-text="No strands found."
                                    :required="true"
                                    error-key="p_strand_id"
                                />
                            @endif
                        </div>

                        @if($isSeniorHighType)
                            <p class="text-[11px] text-brand-primary font-medium">
                                Strands Apply Only When The Selected Department Is SHS.
                            </p>
                        @endif
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="p_email" class="block text-xs font-semibold text-content-heading mb-1">Email Address <span class="text-brand-secondary">*</span></label>
                            <input id="p_email" type="email" wire:model="p_email" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_email') border-brand-secondary @else border-surface-border @enderror">
                            @error('p_email') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_contact_number" class="block text-xs font-semibold text-content-heading mb-1">Contact Number <span class="text-brand-secondary">*</span></label>
                            <input id="p_contact_number" type="text" wire:model="p_contact_number" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs font-mono text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_contact_number') border-brand-secondary @else border-surface-border @enderror">
                            @error('p_contact_number') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="p_status" class="block text-xs font-semibold text-content-heading mb-1">Account Status <span class="text-brand-secondary">*</span></label>
                            <select id="p_status" wire:model="p_status" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading uppercase focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_status') border-brand-secondary @else border-surface-border @enderror">
                                <option value="active">ACTIVE</option>
                                <option value="inactive">INACTIVE</option>
                                <option value="suspended">SUSPENDED</option>
                            </select>
                            @error('p_status') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="p_address" class="block text-xs font-semibold text-content-heading mb-1">Complete Address <span class="text-brand-secondary">*</span></label>
                        <input id="p_address" type="text" wire:model="p_address" class="w-full px-3 py-2 bg-surface-canvas border rounded-xl text-xs text-content-heading focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('p_address') border-brand-secondary @else border-surface-border @enderror">
                        @error('p_address') <span class="text-brand-secondary text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-surface-border/70">
                        <button type="button" wire:click="$set('showPatronModal', false)" class="w-full sm:w-auto px-4 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-semibold text-content-body hover:text-content-heading cursor-pointer">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="savePatron" class="w-full sm:w-auto px-4 py-2 bg-brand-primary hover:bg-brand-primary-hover text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <span wire:loading wire:target="savePatron" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                            <span>Save Patron</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.set('showDeleteModal', false)"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="pt-delete-modal-title"
        >
            <div wire:click="$set('showDeleteModal', false)" class="fixed inset-0 bg-content-heading/55 backdrop-blur-2xs"></div>
            <div class="relative bg-surface-card rounded-2xl shadow-xl max-w-sm w-full p-6 border border-surface-border space-y-4 z-10 text-center">
                <div class="w-12 h-12 bg-brand-secondary/15 text-brand-secondary rounded-full flex items-center justify-center mx-auto">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" aria-hidden="true" />
                </div>
                <div>
                    <h2 id="pt-delete-modal-title" class="text-sm sm:text-base font-bold text-content-heading">Confirm Deletion</h2>
                    <p class="text-xs text-content-body mt-1">Are You Sure You Want To Delete This Patron Record? This Action Cannot Be Undone.</p>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-center gap-2 sm:gap-3 pt-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="w-full sm:w-auto px-4 py-2 bg-surface-canvas border border-surface-border rounded-xl text-xs font-semibold text-content-body hover:text-content-heading cursor-pointer">Cancel</button>
                    <button type="button" wire:click="deleteRecord" wire:loading.attr="disabled" wire:target="deleteRecord" class="w-full sm:w-auto px-4 py-2 bg-brand-secondary rounded-xl text-xs font-semibold text-white hover:opacity-90 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                        <span wire:loading wire:target="deleteRecord" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
