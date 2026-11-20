<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 tracking-tight">Circulation Policies</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Borrowing limits apply to <span class="font-semibold text-gray-700">{{ \Illuminate\Support\Str::headline($studentTypeName) }}</span> patrons; damage &amp; lost charges are handled in Circulations.
            </p>
        </div>

        <button
            wire:click="openCreateModal"
            type="button"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold text-white bg-[#4D9EFF] hover:bg-blue-600 rounded-xl shadow-xs transition-colors cursor-pointer shrink-0"
        >
            @if ($activeTab === 'limits')
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>{{ $limit ? 'Update ' . \Illuminate\Support\Str::headline($studentTypeName) . ' Limit' : 'Set ' . \Illuminate\Support\Str::headline($studentTypeName) . ' Limit' }}</span>
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Asset Rule</span>
            @endif
        </button>
    </div>

    {{-- Quick Summary Strip --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">LOAN LIMIT SCOPE</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">{{ \Illuminate\Support\Str::headline($studentTypeName) }} Only</p>
            </div>
            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700">{{ \Illuminate\Support\Str::headline($studentTypeName) }} Scope</span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ strtoupper($studentTypeName) }} MAX BORROW</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">
                    {{ $limit ? $limit->max_borrow_limit . ' ' . \Illuminate\Support\Str::plural('Item', $limit->max_borrow_limit) : 'Not Configured' }}
                </p>
            </div>
            <span class="text-xs font-medium text-gray-500">Per {{ \Illuminate\Support\Str::headline($studentTypeName) }}</span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ strtoupper($studentTypeName) }} LOAN PERIOD</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">
                    {{ $limit ? $limit->loan_duration_days . ' ' . \Illuminate\Support\Str::plural('Day', $limit->loan_duration_days) : 'Not Configured' }}
                </p>
            </div>
            <button
                wire:click="editLimit({{ $limit?->id }})"
                type="button"
                class="text-xs font-semibold text-[#4D9EFF] hover:underline cursor-pointer"
            >
                Edit
            </button>
        </div>
    </div>

    {{-- Tabs & Search Bar --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="px-4 sm:px-6 pt-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex gap-6">
                <button
                    wire:click="switchTab('circulation')"
                    type="button"
                    class="pb-3.5 text-xs sm:text-sm font-semibold border-b-2 transition-colors cursor-pointer {{ $activeTab === 'circulation' ? 'border-[#4D9EFF] text-[#4D9EFF]' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                >
                    Circulation Penalties
                </button>
                <button
                    wire:click="switchTab('limits')"
                    type="button"
                    class="pb-3.5 text-xs sm:text-sm font-semibold border-b-2 transition-colors cursor-pointer {{ $activeTab === 'limits' ? 'border-[#4D9EFF] text-[#4D9EFF]' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                >
                    Penalty Loan Limit
                </button>
            </div>

            @if ($activeTab === 'circulation')
                <div class="pb-3 md:pb-3.5 w-full md:w-72">
                    <div class="relative">
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            placeholder="Search Asset Type..."
                            class="w-full pl-9 pr-3 py-1.5 text-xs sm:text-sm rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#4D9EFF]/30 focus:border-[#4D9EFF]"
                        >
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 10.5a7.5 7.5 0 0013.15 6.15z"/>
                        </svg>
                    </div>
                </div>
            @endif
        </div>

        {{-- TAB 1: Circulation Penalties --}}
        @if ($activeTab === 'circulation')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50/80 text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 sm:px-6 py-3">ASSET TYPE</th>
                            <th class="px-4 sm:px-6 py-3">DAILY FINE FORMULA</th>
                            <th class="px-4 sm:px-6 py-3">LOST AFTER (OVERDUE)</th>
                            <th class="px-4 sm:px-6 py-3 text-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs sm:text-sm text-gray-700">
                        @forelse ($policies as $policy)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-4 sm:px-6 py-3.5 font-semibold text-gray-900">
                                    {{ $policy->assetType?->name ? \Illuminate\Support\Str::headline($policy->assetType->name) : 'N/A' }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 font-medium text-gray-700">
                                    Unit Price &divide; {{ $policy->lost_after_days }} {{ \Illuminate\Support\Str::plural('Day', $policy->lost_after_days) }}
                                </td>
                                <td class="px-4 sm:px-6 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800">
                                        After {{ $policy->lost_after_days }} {{ \Illuminate\Support\Str::plural('Day', $policy->lost_after_days) }} &rarr; Lost
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <button
                                            wire:click="editPolicy({{ $policy->id }})"
                                            type="button"
                                            class="px-2.5 py-1 text-xs font-semibold text-[#4D9EFF] bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            wire:click="deletePolicy({{ $policy->id }})"
                                            wire:confirm="Delete this asset penalty rule?"
                                            type="button"
                                            class="px-2.5 py-1 text-xs font-semibold text-[#FF6B52] bg-red-50 hover:bg-red-100 rounded-lg transition-colors cursor-pointer"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                    No Asset Penalty Rules Found. Click <span class="font-semibold text-gray-700">Add Asset Rule</span> to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($policies->hasPages())
                <div class="px-4 sm:px-6 py-3 border-t border-gray-200 bg-gray-50/50">
                    {{ $policies->links() }}
                </div>
            @endif
        @endif

        {{-- TAB 2: Penalty Loan Limit (Student Only) --}}
        @if ($activeTab === 'limits')
            <div class="p-4 sm:p-6 space-y-4">
                @if ($limit)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 border border-gray-200 rounded-2xl p-5">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Applies To</p>
                            <p class="text-base font-bold text-gray-900 mt-1">{{ \Illuminate\Support\Str::headline($limit->patronType?->name ?? $studentTypeName) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Maximum Borrow Limit</p>
                            <p class="text-base font-bold text-[#4D9EFF] mt-1">{{ $limit->max_borrow_limit }} {{ \Illuminate\Support\Str::plural('Item', $limit->max_borrow_limit) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Loan Duration</p>
                            <p class="text-base font-bold text-gray-900 mt-1">{{ $limit->loan_duration_days }} {{ \Illuminate\Support\Str::plural('Day', $limit->loan_duration_days) }}</p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-10 border border-dashed border-gray-300 rounded-2xl">
                        <p class="text-sm font-medium text-gray-700">No {{ \Illuminate\Support\Str::headline($studentTypeName) }} Loan Limit Configured Yet.</p>
                        <p class="text-xs text-gray-500 mt-1">Set the maximum borrow count and loan duration for {{ \Illuminate\Support\Str::headline($studentTypeName) }} patrons.</p>
                        <button
                            wire:click="editLimit()"
                            type="button"
                            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-[#4D9EFF] hover:bg-blue-600 rounded-xl cursor-pointer"
                        >
                            Set {{ \Illuminate\Support\Str::headline($studentTypeName) }} Loan Limit
                        </button>
                    </div>
                @endif

                <p class="text-xs text-gray-500">
                    Teachers are exempt from due-date loan limits; minor damage, severe damage, and lost items are handled directly in Circulations.
                </p>
            </div>
        @endif
    </div>

    {{-- Penalty Policy Modal --}}
    @if ($showModal)
        <div
            @keydown.escape.window="$wire.closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-800">
                        {{ $isEditing ? 'Edit Asset Rule' : 'Create Asset Rule' }}
                    </h2>
                    <button wire:click="closeModal" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Patron Type</label>
                            <input
                                type="text"
                                value="{{ \Illuminate\Support\Str::headline($studentTypeName) }}"
                                disabled
                                class="w-full text-sm rounded-xl border border-gray-200 bg-gray-100 text-gray-600 px-3 py-2 cursor-not-allowed"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Asset Type *</label>
                            <select
                                wire:model="asset_type_id"
                                class="w-full text-sm rounded-xl border border-gray-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-[#4D9EFF]/30 focus:border-[#4D9EFF]"
                            >
                                <option value="">Select Asset Type</option>
                                @foreach ($assetTypes as $type)
                                    <option value="{{ $type->id }}">{{ \Illuminate\Support\Str::headline($type->name) }}</option>
                                @endforeach
                            </select>
                            @error('asset_type_id') <span class="text-xs text-[#FF6B52] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Lost After (Overdue Days) *</label>
                        <input
                            wire:model="lost_after_days"
                            type="number"
                            min="1"
                            max="365"
                            class="w-full text-sm rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#4D9EFF]/30 focus:border-[#4D9EFF]"
                        >
                        <p class="text-[11px] text-gray-500 mt-1">
                            Daily fine equals <span class="font-medium text-gray-700">Item Unit Price &divide; Lost After Days</span>. Passing this overdue threshold marks the item as <span class="font-medium text-gray-700">Lost</span>.
                        </p>
                        @error('lost_after_days') <span class="text-xs text-[#FF6B52] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex justify-end gap-2.5">
                        <button
                            wire:click="closeModal"
                            type="button"
                            class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl cursor-pointer transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="px-4 py-2 text-xs font-semibold text-white bg-[#4D9EFF] hover:bg-blue-600 rounded-xl cursor-pointer disabled:opacity-50 transition-colors"
                        >
                            <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Rule' : 'Save Rule' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Student Loan Limit Modal --}}
    @if ($showLimitModal)
        <div
            @keydown.escape.window="$wire.closeLimitModal()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div wire:click="closeLimitModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-800">
                        {{ $isEditing ? 'Update ' . \Illuminate\Support\Str::headline($studentTypeName) . ' Loan Limit' : 'Set ' . \Illuminate\Support\Str::headline($studentTypeName) . ' Loan Limit' }}
                    </h2>
                    <button wire:click="closeLimitModal" type="button" class="text-gray-400 hover:text-gray-600 text-xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Patron Type</label>
                        <input
                            type="text"
                            value="{{ \Illuminate\Support\Str::headline($studentTypeName) }}"
                            disabled
                            class="w-full text-sm rounded-xl border border-gray-200 bg-gray-100 text-gray-600 px-3 py-2 cursor-not-allowed"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Max Borrow Limit *</label>
                            <input
                                wire:model="max_borrow_limit"
                                type="number"
                                min="1"
                                max="20"
                                class="w-full text-sm rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#4D9EFF]/30 focus:border-[#4D9EFF]"
                            >
                            @error('max_borrow_limit') <span class="text-xs text-[#FF6B52] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Loan Duration (Days) *</label>
                            <input
                                wire:model="loan_duration_days"
                                type="number"
                                min="1"
                                max="60"
                                class="w-full text-sm rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#4D9EFF]/30 focus:border-[#4D9EFF]"
                            >
                            @error('loan_duration_days') <span class="text-xs text-[#FF6B52] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex justify-end gap-2.5">
                        <button
                            wire:click="closeLimitModal"
                            type="button"
                            class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl cursor-pointer transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="px-4 py-2 text-xs font-semibold text-white bg-[#4D9EFF] hover:bg-blue-600 rounded-xl cursor-pointer disabled:opacity-50 transition-colors"
                        >
                            <span wire:loading.remove wire:target="save">Save {{ \Illuminate\Support\Str::headline($studentTypeName) }} Limit</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
