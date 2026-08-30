<div class="max-w-md mx-auto my-10 bg-white p-6 rounded-xl shadow-md border border-gray-200 text-center">
    <h2 class="text-xl font-bold text-gray-800 mb-2">Verify Your Email Address</h2>

    <p class="text-sm text-gray-600 mb-2">
        Thanks for updating your profile! We sent a verification email to:
    </p>

    <!-- Displays current target email for quick typo identification -->
    <div class="inline-block px-3 py-1 mb-4 text-xs font-semibold font-mono text-blue-700 bg-blue-50 rounded-lg border border-blue-100">
        {{ $userEmail }}
    </div>

    <p class="text-xs text-gray-500 mb-6">
        Please check your inbox and click the link inside to activate your account.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
            A new verification link has been sent to your email address.
        </div>
    @elseif (session('status'))
        <div class="mb-4 text-sm font-medium text-blue-600 bg-blue-50 p-3 rounded-lg border border-blue-200">
            {{ session('status') }}
        </div>
    @endif

    @error('warning')
        <div class="mb-4 p-3 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg">
            {{ $message }}
        </div>
    @enderror

    <div class="flex flex-col gap-3 mt-4">
        <!-- Resend Button -->
        <button
            wire:click="resendNotification"
            wire:loading.attr="disabled"
            type="button"
            class="w-full bg-blue-600 text-white font-medium py-2 rounded-lg hover:bg-blue-700 text-sm disabled:opacity-50 transition cursor-pointer"
        >
            <span wire:loading.remove wire:target="resendNotification">Resend Verification Email</span>
            <span wire:loading wire:target="resendNotification">Sending...</span>
        </button>

        <!-- Redo / Change Email Button -->
        <button
            wire:click="changeEmail"
            type="button"
            class="w-full bg-slate-100 text-slate-700 hover:bg-slate-200 font-medium py-2 rounded-lg text-xs transition cursor-pointer flex items-center justify-center gap-1.5"
        >
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span>Wrong email address? Change it here</span>
        </button>

        <!-- Logout Button -->
        <button
            wire:click="logout"
            type="button"
            class="text-xs text-gray-500 hover:underline mt-1 cursor-pointer"
        >
            Log Out
        </button>
    </div>
</div>
