<div class="space-y-6">
    {{-- Header Container --}}
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900">Student Circulation Policy</h2>
            <p class="text-xs text-gray-500">Configure multiple borrowing policy rules for Student borrower types.</p>
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

    {{-- Filter & Search Bar --}}
    <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-xs">
        <div class="relative w-full sm:w-80">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search policy name or asset type..."
                class="w-full pl-9 pr-8 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            >
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    {{-- Table Matrix --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-xs bg-white">
        <table class="w-full text-left text-xs text-gray-700">
            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Policy Rule Name</th>
                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Borrower Type</th>
                    <th scope="col" class="px-4 py-3 whitespace-nowrap">Asset Type</th>
                    <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Max Borrow Limit</th>
                    <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Loan Duration</th>
                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Daily Fine</th>
                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Max Fine Cap</th>
                    <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($policies as $policy)
                    <tr wire:key="policy-row-{{ $policy->id }}" class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-semibold text-gray-900 capitalize whitespace-nowrap">
                            {{ $policy->name }}
                        </td>
                        <td class="px-4 py-3 capitalize whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $policy->patronType?->name ?? 'Student' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 capitalize text-gray-600 whitespace-nowrap">
                            {{ $policy->assetType?->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap">
                            {{ $policy->max_borrow_limit }} item(s)
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 whitespace-nowrap">
                            {{ $policy->loan_duration_days }} day(s)
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
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto" role="dialog" aria-modal="true">
            <div wire:click.self="closeModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-xl z-10 overflow-hidden my-auto flex flex-col max-h-[90vh]">
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center shrink-0">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900">
                        {{ $isEditing ? 'Edit Student Policy Rule' : 'Create Student Policy Rule' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit.prevent="save" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                    {{-- Policy Rule Name --}}
                    <div>
                        <label for="name" class="block text-xs font-medium text-gray-700">Policy Name / Description <span class="text-red-500">*</span></label>
                        <input id="name" type="text" wire:model="name" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs" placeholder="e.g. Standard Book Loan, Overnight Reference Book">
                        @error('name') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Target Attributes --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Borrower Type</label>
                            <div class="mt-1 w-full text-xs rounded-md border border-gray-200 bg-gray-100 p-2 text-gray-600 font-semibold flex items-center justify-between cursor-not-allowed">
                                <span>{{ $studentTypeName }}</span>
                                <span class="text-[10px] bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded font-normal">Fixed</span>
                            </div>
                            <input type="hidden" wire:model="patron_type_id" />
                        </div>

                        <div>
                            <label for="asset_type_id" class="block text-xs font-medium text-gray-700">Asset Type <span class="text-red-500">*</span></label>
                            <select id="asset_type_id" wire:model.number="asset_type_id" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs bg-white capitalize">
                                <option value="">Select Asset Type</option>
                                @foreach($assetTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('asset_type_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Circulation Limits --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="max_borrow_limit" class="block text-xs font-medium text-gray-700">Max Borrow Limit <span class="text-red-500">*</span></label>
                            <input id="max_borrow_limit" type="number" wire:model.number="max_borrow_limit" min="1" max="100" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs" placeholder="e.g. 3">
                            @error('max_borrow_limit') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="loan_duration_days" class="block text-xs font-medium text-gray-700">Loan Duration (Days) <span class="text-red-500">*</span></label>
                            <input id="loan_duration_days" type="number" wire:model.number="loan_duration_days" min="1" max="365" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs" placeholder="e.g. 7">
                            @error('loan_duration_days') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Overdue Fine Rules --}}
                    <div class="pt-2 border-t border-gray-100">
                        <div class="mb-2">
                            <h4 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Overdue Fine Rates</h4>
                            <p class="text-[11px] text-gray-500">Set the daily penalty fee charged when an item is returned past its due date.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="fine_per_day" class="block text-xs font-medium text-gray-700">Daily Overdue Fine Rate (₱) <span class="text-red-500">*</span></label>
                                <input id="fine_per_day" type="number" step="0.01" min="0" max="9999.99" wire:model.number="fine_per_day" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                                @error('fine_per_day') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="max_fine_amount" class="block text-xs font-medium text-gray-700">Maximum Cumulative Fine Limit (₱) <span class="text-red-500">*</span></label>
                                <input id="max_fine_amount" type="number" step="0.01" min="0" max="99999.99" wire:model.number="max_fine_amount" class="mt-1 w-full text-xs rounded-md border-gray-300 border p-2 shadow-xs">
                                @error('max_fine_amount') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1 border-t border-gray-100">
                        <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer">
                        <label for="is_active" class="text-xs font-medium text-gray-700 select-none cursor-pointer">Policy Active</label>
                    </div>

                    <div class="pt-3 border-t border-gray-200 flex justify-end gap-2">
                        <button type="button" wire:click="closeModal" class="px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-md transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-xs transition cursor-pointer">
                            {{ $isEditing ? 'Update Policy' : 'Save Policy' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
