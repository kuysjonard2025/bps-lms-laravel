<div class="p-6 bg-white rounded-lg shadow-sm border border-gray-100">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Circulation Policy</h2>
            <p class="text-sm text-gray-500 mt-1">Set borrowing rules, durations, and fine structures per patron & asset combination.</p>
        </div>
        <button wire:click="openCreateModal" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-medium rounded-md text-sm transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Policy Rule
        </button>
    </div>

    <!-- Notification Alert -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-md text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <!-- Search Input -->
    <div class="mb-4">
        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Filter by Patron or Asset Type..." class="w-full pl-9 pr-8 py-2 border border-gray-300 rounded-md text-sm text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition-colors" />
            @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    <!-- Table Matrix -->
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Patron Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Asset Type</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Max Limit</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Duration</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Renewals</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Grace Period</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Fine / Day</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Max Cap</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($policies as $policy)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $policy->patronType->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $policy->assetType->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $policy->max_borrow_limit }} item(s)</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $policy->loan_duration_days }} day(s)</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $policy->max_renewals }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $policy->grace_period_days }} day(s)</td>
                        <td class="px-4 py-3 text-right font-mono text-gray-900">₱{{ number_format($policy->fine_per_day, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-gray-900">₱{{ number_format($policy->max_fine_amount, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleStatus({{ $policy->id }})" class="px-2.5 py-0.5 text-xs font-semibold rounded-full border transition-colors {{ $policy->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}">
                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center space-x-3 whitespace-nowrap">
                            <button wire:click="editPolicy({{ $policy->id }})" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold transition-colors">Edit</button>
                            <button wire:click="deletePolicy({{ $policy->id }})" wire:confirm="Are you sure you want to delete this policy?" class="text-rose-600 hover:text-rose-900 text-xs font-semibold transition-colors">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            No circulation policy rules found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $policies->links() }}
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 bg-gray-600/50 backdrop-blur-sm flex items-center justify-center p-4" x-data @keydown.escape.window="$wire.closeModal()">
            <div class="bg-white rounded-lg shadow-xl max-w-xl w-full p-6 border border-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $isEditing ? 'Edit Circulation Policy' : 'Create Circulation Policy' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 rounded-lg text-sm p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Patron Type</label>
                            <select wire:model="patron_type_id" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-900 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                                <option value="">Select Patron Type</option>
                                @foreach($patronTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('patron_type_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Asset Type</label>
                            <select wire:model="asset_type_id" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-900 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none">
                                <option value="">Select Asset Type</option>
                                @foreach($assetTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('asset_type_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Max Limit</label>
                            <input type="number" wire:model="max_borrow_limit" min="1" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none" />
                            @error('max_borrow_limit') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Loan Days</label>
                            <input type="number" wire:model="loan_duration_days" min="1" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none" />
                            @error('loan_duration_days') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Max Renewals</label>
                            <input type="number" wire:model="max_renewals" min="0" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none" />
                            @error('max_renewals') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Grace Days</label>
                            <input type="number" wire:model="grace_period_days" min="0" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none" />
                            @error('grace_period_days') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Fine Per Day (₱)</label>
                            <input type="number" step="0.01" wire:model="fine_per_day" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none" />
                            @error('fine_per_day') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Max Fine Cap (₱)</label>
                            <input type="number" step="0.01" wire:model="max_fine_amount" class="w-full border border-gray-300 rounded-md p-2 text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none" />
                            @error('max_fine_amount') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 cursor-pointer" />
                        <label for="is_active" class="text-sm font-medium text-gray-700 select-none cursor-pointer">Policy active</label>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 mt-6">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-300 text-gray-700 bg-white rounded-md text-sm font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-medium rounded-md text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <span wire:loading.remove>{{ $isEditing ? 'Update Policy' : 'Save Policy' }}</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
