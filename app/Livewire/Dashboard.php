<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Layout('components.layouts.app')]
    #[Title('Dashboard')]
    public function render()
    {
        /*
        |--------------------------------------------------------------------------
        | Real Database Queries (Uncomment when ready)
        |--------------------------------------------------------------------------
        | $totalBooks = Book::sum('quantity');
        | $activeBorrows = Borrow::where('status', 'borrowed')->count();
        | $overdueBooks = Borrow::where('status', 'borrowed')
        |     ->where('due_date', '<', now())
        |     ->count();
        | $totalPatrons = User::whereIn('role', ['student', 'faculty', 'staff'])->count();
        |
        | $overdueAlerts = Borrow::with(['user', 'book'])
        |     ->where('status', 'borrowed')
        |     ->where('due_date', '<', now())
        |     ->orderBy('due_date', 'asc')
        |     ->take(5)
        |     ->get();
        |
        | $recentTransactions = Borrow::with(['user', 'book'])
        |     ->latest('updated_at')
        |     ->paginate(10);
        */

        // 1. Mock Key Metrics
        $totalBooks = 1245;
        $activeBorrows = 48;
        $overdueBooks = 5;
        $totalPatrons = 312;

        // 2. Mock Overdue Alerts
        $overdueAlerts = collect([
            (object)[
                'id' => 1,
                'user' => (object)['first_name' => 'Juan', 'last_name' => 'Dela Cruz'],
                'book' => (object)['title' => 'Introduction to Algorithms (4th Ed.)'],
                'due_date' => now()->subDays(4)->toDateTimeString(),
            ],
            (object)[
                'id' => 2,
                'user' => (object)['first_name' => 'Maria', 'last_name' => 'Santos'],
                'book' => (object)['title' => 'Clean Code: A Handbook of Agile Software Craftsmanship'],
                'due_date' => now()->subDays(2)->toDateTimeString(),
            ],
            (object)[
                'id' => 3,
                'user' => (object)['first_name' => 'Alex', 'last_name' => 'Reyes'],
                'book' => (object)['title' => 'Design Patterns: Elements of Reusable Object-Oriented Software'],
                'due_date' => now()->subDay()->toDateTimeString(),
            ],
        ]);

        // 3. Mock Recent Transactions
        $allTransactions = collect([
            (object)[
                'id' => 101,
                'user' => (object)['first_name' => 'Juan', 'last_name' => 'Dela Cruz'],
                'book' => (object)['title' => 'Introduction to Algorithms'],
                'status' => 'borrowed',
                'due_date' => now()->subDays(4)->toDateTimeString(),
                'updated_at' => now()->subHours(2),
            ],
            (object)[
                'id' => 102,
                'user' => (object)['first_name' => 'Ana', 'last_name' => 'Gomez'],
                'book' => (object)['title' => 'Modern Operating Systems'],
                'status' => 'returned',
                'due_date' => now()->addDays(3)->toDateTimeString(),
                'updated_at' => now()->subHours(5),
            ],
            (object)[
                'id' => 103,
                'user' => (object)['first_name' => 'Mark', 'last_name' => 'Bautista'],
                'book' => (object)['title' => 'Database System Concepts'],
                'status' => 'borrowed',
                'due_date' => now()->addDays(5)->toDateTimeString(),
                'updated_at' => now()->subDay(),
            ],
            (object)[
                'id' => 104,
                'user' => (object)['first_name' => 'Maria', 'last_name' => 'Santos'],
                'book' => (object)['title' => 'Clean Code'],
                'status' => 'borrowed',
                'due_date' => now()->subDays(2)->toDateTimeString(),
                'updated_at' => now()->subDays(2),
            ],
            (object)[
                'id' => 105,
                'user' => (object)['first_name' => 'Carlos', 'last_name' => 'Mendoza'],
                'book' => (object)['title' => 'Computer Networking: A Top-Down Approach'],
                'status' => 'returned',
                'due_date' => now()->addDays(10)->toDateTimeString(),
                'updated_at' => now()->subDays(3),
            ],
        ]);

        // Filter mock transactions based on search input
        if (!empty($this->search)) {
            $allTransactions = $allTransactions->filter(function ($item) {
                $fullName = strtolower($item->user->first_name . ' ' . $item->user->last_name);
                $title = strtolower($item->book->title);
                $query = strtolower($this->search);

                return str_contains($fullName, $query) || str_contains($title, $query);
            });
        }

        return view('livewire.dashboard', [
            'totalBooks'         => $totalBooks,
            'activeBorrows'      => $activeBorrows,
            'overdueBooks'       => $overdueBooks,
            'totalPatrons'       => $totalPatrons,
            'overdueAlerts'      => $overdueAlerts,
            'recentTransactions' => $allTransactions,
        ]);
    }
}
