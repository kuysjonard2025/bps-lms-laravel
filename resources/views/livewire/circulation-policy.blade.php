<div class="space-y-6">
    {{-- Header Container --}}
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Student Circulation Policy</h2>
            <p class="text-xs text-gray-500">Configure borrowing limits, durations, and fines specifically for Student patron types.</p>
        </div>

        <button
            type="button"
            wire:click="openCreateModal"
            class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer flex items-center justify-center gap-2 shadow-xs shrink-0 whitespace-nowrap"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>New Student Policy</span>
        </button>
    </div>

    {{-- Flash Message Alert --}}
    @if (session()->has('message'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    {{-- Filter & Search Bar --}}
    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-xs">
        <div class="relative w-full sm:w-80">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Filter by Asset Type..."
                class="w-full pl-9 pr-8 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            >
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            @if(!empty($search))
                <button
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs cursor-pointer"
                    aria-label="Clear search"
                >
                    ✕
                </button>
            @endif
        </div>
    </div>

    {{-- Table Matrix --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
        <table class="w-full text-left text-xs text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Patron Type</th>
                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Asset Type</th>
                    <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Max Limit</th>
                    <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Duration</th>
                    <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Renewals</th>
                    <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Grace Period</th>
                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Fine / Day</th>
                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Max Cap</th>
                    <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($policies as $policy)
                    <tr wire:key="policy-row-{{ $policy->id }}" class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $policy->patronType?->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $policy->assetType?->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap">
                            {{ $policy->max_borrow_limit }} item(s)
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap">
                            {{ $policy->loan_duration_days }} day(s)
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap">
                            {{ $policy->max_renewals }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap">
                            {{ $policy->grace_period_days }} day(s)
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-900 whitespace-nowrap">
                            ₱{{ number_format($policy->fine_per_day, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-900 whitespace-nowrap">
                            ₱{{ number_format($policy->max_fine_amount, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <button
                                type="button"
                                wire:click="toggleStatus({{ $policy->id }})"
                                class="px-2.5 py-0.5 text-[10px] font-bold rounded-full border transition cursor-pointer {{ $policy->is_active ? 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' : 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200' }}"
                            >
                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                            <button
                                type="button"
                                wire:click="editPolicy({{ $policy->id }})"
                                class="text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 rounded hover:bg-blue-50 transition cursor-pointer"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                wire:click="deletePolicy({{ $policy->id }})"
                                wire:confirm="Are you sure you want to delete this policy?"
                                class="text-red-600 hover:text-red-800 font-semibold px-2 py-1 rounded hover:bg-red-50 transition cursor-pointer"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            No student circulation policy rules found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $policies->links() }}
    </div>

    {{-- CREATE/EDIT MODAL --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="policy-modal-title">
            <div wire:click.self="closeModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-xl z-10 overflow-hidden my-auto flex flex-col max-h-[90vh]">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 id="policy-modal-title" class="text-xs sm:text-sm font-bold text-gray-900">
                        {{ $isEditing ? 'Edit Student Policy Rule' : 'Create Student Policy Rule' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit.prevent="save" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="patron_type_id" class="block text-xs font-medium text-gray-700">Student Patron Type <span class="text-red-500">*</span></label>
                            <select id="patron_type_id" wire:model.number="patron_type_id" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="">Select Patron Type</option>
                                @foreach($patronTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('patron_type_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="asset_type_id" class="block text-xs font-medium text-gray-700">Asset Type <span class="text-red-500">*</span></label>
                            <select id="asset_type_id" wire:model.number="asset_type_id" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white">
                                <option value="">Select Asset Type</option>
                                @foreach($assetTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('asset_type_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label for="max_borrow_limit" class="block text-xs font-medium text-gray-700">Max Limit <span class="text-red-500">*</span></label>
                            <input id="max_borrow_limit" type="number" wire:model.number="max_borrow_limit" min="1" max="100" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs" placeholder="e.g. 3">
                            @error('max_borrow_limit') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="loan_duration_days" class="block text-xs font-medium text-gray-700">Loan Days <span class="text-red-500">*</span></label>
                            <input id="loan_duration_days" type="number" wire:model.number="loan_duration_days" min="1" max="365" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs" placeholder="e.g. 7">
                            @error('loan_duration_days') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="max_renewals" class="block text-xs font-medium text-gray-700">Max Renewals</label>
                            <input id="max_renewals" type="number" wire:model.number="max_renewals" min="0" max="10" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs" placeholder="e.g. 2">
                            @error('max_renewals') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="grace_period_days" class="block text-xs font-medium text-gray-700">Grace Days</label>
                            <input id="grace_period_days" type="number" wire:model.number="grace_period_days" min="0" max="30" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs" placeholder="e.g. 0">
                            @error('grace_period_days') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="fine_per_day" class="block text-xs font-medium text-gray-700">Fine Per Day (₱)</label>
                            <input id="fine_per_day" type="number" step="0.01" min="0" max="9999.99" wire:model.number="fine_per_day" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs" placeholder="0.00">
                            @error('fine_per_day') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="max_fine_amount" class="block text-xs font-medium text-gray-700">Max Fine Cap (₱)</label>
                            <input id="max_fine_amount" type="number" step="0.01" min="0" max="99999.99" wire:model.number="max_fine_amount" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs" placeholder="0.00">
                            @error('max_fine_amount') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer">
                        <label for="is_active" class="text-xs font-medium text-gray-700 select-none cursor-pointer">Policy Active</label>
                    </div>

                    <div class="pt-3 border-t border-gray-200 flex justify-end gap-2">
                        <button type="button" wire:click="closeModal" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-xs transition cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove>{{ $isEditing ? 'Update Policy' : 'Save Policy' }}</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
