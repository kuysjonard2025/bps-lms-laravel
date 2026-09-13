<x-mail::message>
# Hello {{ ucwords($circulation->patron->first_name) }},

You have successfully borrowed an item from the library. Here are your loan details:

- **Accession Number:** {{ strtoupper($circulation->accession->accession_number ?? 'N/A') }}
- **Book Title:** {{ ucwords($circulation->accession->catalog->title ?? 'N/A') }}
- **Borrowed Date:** {{ $circulation->borrowed_at->format('M d, Y') }}
- **Due Date:** {{ \Carbon\Carbon::parse($circulation->due_at)->format('M d, Y') }}
- **Processed By:** {{ auth()->user()->getFullNameAttribute() ?? 'System User' }}
- **Role:** {{ ucwords(auth()->user()->role) ?? 'System' }}

Please make sure to return the item on or before the due date to avoid penalties.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
