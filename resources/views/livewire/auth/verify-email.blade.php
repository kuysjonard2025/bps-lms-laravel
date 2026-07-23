<div class="max-w-md mx-auto my-10 bg-white p-6 rounded-xl shadow-md border border-gray-200 text-center">
    <h2 class="text-xl font-bold text-gray-800 mb-2">Verify Your Email Address</h2>

    <p class="text-sm text-gray-600 mb-6">
        Thanks for updating your profile! Before getting started, please check your email inbox for a verification link.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg">
            A new verification link has been sent to your email address.
        </div>
    @elseif (session('status'))
        <div class="mb-4 text-sm font-medium text-blue-600 bg-blue-50 p-3 rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    @error('warning')
        <h1 class="mb-4 py-3 px-4 text-sm text-yellow-300 border border-amber-200">{{ $message }}</h1>
    @enderror

    <div class="flex flex-col gap-3 mt-4">
        <!-- Resend Button -->
        <button
            wire:click="resendNotification"
            wire:loading.attr="disabled"
            class="w-full bg-blue-600 text-white font-medium py-2 rounded-lg hover:bg-blue-700 text-sm disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="resendNotification">Resend Verification Email</span>
            <span wire:loading wire:target="resendNotification">Sending...</span>
        </button>

        <!-- Logout Button -->
        <button
            wire:click="logout"
            type="button"
            class="text-xs text-gray-500 hover:underline"
        >
            Log Out
        </button>
    </div>
</div>
