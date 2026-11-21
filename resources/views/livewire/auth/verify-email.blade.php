{{-- resources/views/livewire/auth/verify-email.blade.php --}}
<div class="w-full max-w-md mx-auto px-4 my-8 sm:my-10">
    <div class="bg-surface-card p-6 sm:p-8 rounded-2xl shadow-2xs border border-surface-border text-center">
        <div class="mx-auto w-12 h-12 rounded-2xl bg-brand-tint-light text-brand-primary flex items-center justify-center mb-3">
            <x-heroicon-o-envelope class="w-6 h-6" aria-hidden="true" />
        </div>

        <h1 class="text-lg sm:text-xl font-bold text-content-heading mb-1.5">Verify Your Email Address</h1>

        <p class="text-xs text-content-body mb-3">
            Thanks for updating your profile! We sent a verification email to:
        </p>

        <div class="inline-block px-3 py-1.5 mb-4 text-xs font-semibold font-mono text-brand-primary bg-brand-tint-light rounded-xl border border-brand-tint break-all">
            {{ $userEmail }}
        </div>

        <p class="text-xs text-content-muted mb-5">
            Please check your inbox and click the verification link inside to activate your account.
        </p>

        @if (session('status') === 'verification-link-sent')
            <div class="mb-4 text-xs font-medium text-brand-primary bg-brand-tint-light p-3 rounded-xl border border-brand-tint">
                A new verification link has been sent to your email address.
            </div>
        @elseif (session('status'))
            <div class="mb-4 text-xs font-medium text-brand-primary bg-brand-tint-light p-3 rounded-xl border border-brand-tint">
                {{ session('status') }}
            </div>
        @endif

        @error('warning')
            <div class="mb-4 p-3 text-xs font-semibold text-brand-secondary bg-brand-secondary/10 border border-brand-secondary/30 rounded-xl">
                {{ $message }}
            </div>
        @enderror

        <div class="flex flex-col gap-2.5 mt-4">
            <button
                wire:click="resendNotification"
                wire:loading.attr="disabled"
                wire:target="resendNotification"
                type="button"
                class="w-full bg-brand-primary hover:bg-brand-primary-hover text-white font-semibold py-2.5 px-4 rounded-xl text-xs disabled:opacity-50 transition cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
            >
                <span wire:loading.remove wire:target="resendNotification">Resend Verification Email</span>
                <span wire:loading wire:target="resendNotification">Sending...</span>
            </button>

            <button
                wire:click="changeEmail"
                wire:loading.attr="disabled"
                wire:target="changeEmail"
                type="button"
                class="w-full bg-surface-canvas text-content-heading hover:bg-brand-tint-light hover:text-brand-primary border border-surface-border font-semibold py-2.5 px-4 rounded-xl text-xs transition cursor-pointer flex items-center justify-center gap-1.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
            >
                <x-heroicon-o-pencil-square class="w-4 h-4 text-content-muted" aria-hidden="true" />
                <span>Wrong email address? Change it here</span>
            </button>

            <button
                wire:click="logout"
                type="button"
                class="text-xs font-medium text-content-body hover:text-brand-secondary mt-1.5 cursor-pointer transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary rounded-md py-1"
            >
                Log Out
            </button>
        </div>
    </div>
</div>
