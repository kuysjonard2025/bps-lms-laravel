<div class="p-6 bg-white dark:bg-gray-900 rounded-lg shadow">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Circulation Policy</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Set borrowing rules, durations, and fine structures per patron & asset combination.</p>
        </div>
        <button wire:click="openCreateModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md text-sm transition">
            + New Policy Rule
        </button>
    </div>

    <!-- Notification Alert -->
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-md text-sm">
            {{ session('message') }}
        </div>
    @endif

    <!-- Search input -->
    <div class="mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Filter by Patron Type or Asset Type..." class="w-full md:w-80 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
    </div>

    <!-- Table Matrix -->
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Patron Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Asset Type</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Max Limit</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Duration</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Renewals</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Grace Period</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Fine / Day</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Max Cap</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:bg-gray-900">
                @forelse($policies as $policy)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100">{{ $policy->patronType->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $policy->assetType->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-center">{{ $policy->max_borrow_limit }} item(s)</td>
                        <td class="px-4 py-3 text-center">{{ $policy->loan_duration_days }} day(s)</td>
                        <td class="px-4 py-3 text-center">{{ $policy->max_renewals }}</td>
                        <td class="px-4 py-3 text-center">{{ $policy->grace_period_days }} day(s)</td>
                        <td class="px-4 py-3 text-right font-mono">₱{{ number_format($policy->fine_per_day, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono">₱{{ number_format($policy->max_fine_amount, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleStatus({{ $policy->id }})" class="px-2 py-1 text-xs font-semibold rounded-full {{ $policy->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center space-x-2">
                            <button wire:click="editPolicy({{ $policy->id }})" class="text-indigo-600 hover:underline text-xs font-semibold">Edit</button>
                            <button wire:click="deletePolicy({{ $policy->id }})" wire:confirm="Are you sure you want to delete this policy?" class="text-red-600 hover:underline text-xs font-semibold">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-6 text-center text-gray-500">No circulation policy rules found.</td>
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
        <div class="fixed inset-0 z-50 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-xl w-full p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 border-b pb-3 mb-4">
                    {{ $isEditing ? 'Edit Circulation Policy' : 'Create Circulation Policy' }}
                </h3>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Patron Type</label>
                            <select wire:model="patron_type_id" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select Patron Type</option>
                                @foreach($patronTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('patron_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Asset Type</label>
                            <select wire:model="asset_type_id" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select Asset Type</option>
                                @foreach($assetTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('asset_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Max Limit</label>
                            <input type="number" wire:model="max_borrow_limit" min="1" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm" />
                            @error('max_borrow_limit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Loan Days</label>
                            <input type="number" wire:model="loan_duration_days" min="1" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm" />
                            @error('loan_duration_days') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Max Renewals</label>
                            <input type="number" wire:model="max_renewals" min="0" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm" />
                            @error('max_renewals') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Grace Days</label>
                            <input type="number" wire:model="grace_period_days" min="0" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm" />
                            @error('grace_period_days') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Fine Per Day (₱)</label>
                            <input type="number" step="0.01" wire:model="fine_per_day" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm" />
                            @error('fine_per_day') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Max Fine Cap (₱)</label>
                            <input type="number" step="0.01" wire:model="max_fine_amount" class="w-full mt-1 border border-gray-300 rounded p-2 text-sm" />
                            @error('max_fine_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="is_active" class="rounded text-indigo-600 focus:ring-indigo-500" />
                        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Policy active</label>
                    </div>

                    <div class="flex justify-end gap-2 border-t pt-4">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-300 text-gray-700 rounded text-sm hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded text-sm shadow">
                            {{ $isEditing ? 'Update Policy' : 'Save Policy' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
