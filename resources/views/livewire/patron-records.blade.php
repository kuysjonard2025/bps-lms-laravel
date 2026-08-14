<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Patron Management & History</h1>
            <p class="text-xs text-slate-500 mt-1">Search patrons, check borrowing history, and account status.</p>
        </div>
    </div>

    {{-- Main Split View --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT PANEL: Patron Directory (5 Columns) --}}
        <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="space-y-3">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Patron Directory</h2>

                {{-- Search & Filter --}}
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search Name or Patron ID..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none"
                        >
                        <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
                    </div>

                    <select wire:model.live="statusFilter" class="py-2 px-2.5 text-xs rounded-xl border border-slate-200 text-slate-700 outline-none">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            {{-- Patron Cards List --}}
            <div class="space-y-2 max-h-[600px] overflow-y-auto pr-1">
                @forelse ($patrons as $p)
                    <div
                        wire:click="selectPatron({{ $p->id }})"
                        class="p-3.5 rounded-xl border transition cursor-pointer flex items-center justify-between gap-3 {{ $selectedPatronId === $p->id ? 'border-blue-500 bg-blue-50/50 shadow-sm' : 'border-slate-100 hover:border-slate-200 hover:bg-slate-50' }}">

                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-sm shrink-0">
                                {{ strtoupper(substr($p->first_name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900">{{ $p->first_name }} {{ $p->last_name }}</h3>
                                <p class="text-[11px] text-slate-500 font-mono">ID: {{ $p->patron_id }}</p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold bg-slate-100 text-slate-600">
                                        {{ $p->patronType->name ?? 'Standard' }}
                                    </span>
                                    <span class="text-[10px] font-semibold {{ $p->status === 'active' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        • {{ ucfirst($p->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            @if ($p->active_loans_count > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    {{ $p->active_loans_count }} Active Loan{{ $p->active_loans_count > 1 ? 's' : '' }}
                                </span>
                            @else
                                <span class="text-[10px] text-slate-400">No active loans</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-slate-400 border border-dashed rounded-xl">
                        No patrons found matching your search.
                    </div>
                @endforelse
            </div>

            <div>
                {{ $patrons->links() }}
            </div>
        </div>

        {{-- RIGHT PANEL: Patron Transaction Details (7 Columns) --}}
        <div class="lg:col-span-7 space-y-6">
            @if ($selectedPatron)
                {{-- Patron Summary Card --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 border-slate-100">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white font-bold flex items-center justify-center text-xl shadow-md shadow-blue-500/20">
                                {{ strtoupper(substr($selectedPatron->first_name, 0, 1)) }}
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">{{ $selectedPatron->first_name }} {{ $selectedPatron->last_name }}</h2>
                                <p class="text-xs text-slate-500">
                                    Patron ID: <strong class="text-slate-700 font-mono">{{ $selectedPatron->patron_id }}</strong> •
                                    Email: <span class="text-slate-700">{{ $selectedPatron->email ?? 'N/A' }}</span>
                                </p>
                            </div>
                        </div>

                        <button
                            wire:click="togglePatronStatus({{ $selectedPatron->id }})"
                            class="px-3 py-1.5 text-xs font-semibold rounded-xl border transition cursor-pointer {{ $selectedPatron->status === 'active' ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                            {{ $selectedPatron->status === 'active' ? 'Deactivate Account' : 'Activate Account' }}
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-slate-400 text-[10px] block uppercase font-bold">Type</span>
                            <strong class="text-slate-800">{{ $selectedPatron->patronType->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-slate-400 text-[10px] block uppercase font-bold">Grade / Section</span>
                            <strong class="text-slate-800">
                                {{ $selectedPatron->gradeLevel->name ?? '—' }} {{ $selectedPatron->section?->name ? '('.$selectedPatron->section->name.')' : '' }}
                            </strong>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-slate-400 text-[10px] block uppercase font-bold">Account Status</span>
                            <span class="font-bold {{ $selectedPatron->status === 'active' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ ucfirst($selectedPatron->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Circulation History Table --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b pb-4 border-slate-100 gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Borrowing History Log</h3>
                            <p class="text-xs text-slate-500">Complete record of items borrowed by this patron.</p>
                        </div>

                        <select wire:model.live="loanFilter" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 outline-none">
                            <option value="all">All Records</option>
                            <option value="active">Active Borrowed</option>
                            <option value="overdue">Overdue</option>
                            <option value="returned">Returned</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto border border-slate-100 rounded-xl">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                                <tr>
                                    <th class="p-3 pl-4 whitespace-nowrap">Accession No.</th>
                                    <th class="p-3 whitespace-nowrap">Book Title</th>
                                    <th class="p-3 whitespace-nowrap">Author</th>
                                    <th class="p-3 whitespace-nowrap">ISBN</th>
                                    <th class="p-3 whitespace-nowrap">Borrowed Date</th>
                                    <th class="p-3 whitespace-nowrap">Due Date</th>
                                    <th class="p-3 whitespace-nowrap">Returned Date</th>
                                    <th class="p-3 whitespace-nowrap">Fine / Penalty</th>
                                    <th class="p-3 pr-4 text-right whitespace-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($patronLoans as $loan)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="p-3 pl-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                            {{ $loan->accession->accession_number ?? 'N/A' }}
                                        </td>
                                        <td class="p-3 font-bold text-slate-900 whitespace-nowrap">
                                            {{ $loan->accession->catalog->title ?? 'N/A' }}
                                        </td>
                                        <td class="p-3 text-slate-500 whitespace-nowrap">
                                            {{ $loan->accession->catalog->author->name ?? $loan->accession->catalog->author ?? '—' }}
                                        </td>
                                        <td class="p-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                            {{ $loan->accession->catalog->isbn_issn ?? '—' }}
                                        </td>
                                        <td class="p-3 text-slate-500 whitespace-nowrap">
                                            {{ $loan->borrowed_at ? \Carbon\Carbon::parse($loan->borrowed_at)->format('M d, Y') : '—' }}
                                        </td>
                                        <td class="p-3 text-slate-500 whitespace-nowrap">
                                            {{ $loan->due_at ? \Carbon\Carbon::parse($loan->due_at)->format('M d, Y') : '—' }}
                                        </td>
                                        <td class="p-3 text-slate-500 whitespace-nowrap">
                                            {{ $loan->returned_at ? \Carbon\Carbon::parse($loan->returned_at)->format('M d, Y') : '—' }}
                                        </td>
                                        <td class="p-3 font-semibold {{ ($loan->fine_amount ?? 0) > 0 ? 'text-rose-600' : 'text-slate-400' }} whitespace-nowrap">
                                            ₱{{ number_format($loan->fine_amount ?? 0, 2) }}
                                        </td>
                                        <td class="p-3 pr-4 text-right whitespace-nowrap">
                                            @if ($loan->status === 'returned')
                                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">RETURNED</span>
                                            @elseif ($loan->status === 'lost')
                                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">LOST</span>
                                            @elseif (($loan->due_at && \Carbon\Carbon::parse($loan->due_at)->isPast()) || $loan->status === 'overdue')
                                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">OVERDUE</span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">BORROWED</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-8 text-slate-400">
                                            No borrowing history found for this patron filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $patronLoans->links() }}
                    </div>
                </div>
            @else
                {{-- Empty State Placeholder --}}
                <div class="bg-white p-12 text-center rounded-2xl border border-slate-200/80 text-slate-400 space-y-3">
                    <div class="text-3xl">👤</div>
                    <h3 class="text-sm font-bold text-slate-700">No Patron Selected</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">
                        Select a patron from the directory list on the left to view their detailed library card, contact info, and circulation history.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
