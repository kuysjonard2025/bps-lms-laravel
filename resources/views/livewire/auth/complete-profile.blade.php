{{-- resources/views/livewire/auth/complete-profile.blade.php --}}
<div class="w-full max-w-xl mx-auto px-4 my-8 sm:my-10">
    <div class="bg-surface-card p-6 sm:p-8 rounded-2xl shadow-2xs border border-surface-border">
        <div class="text-center mb-6 pb-4 border-b border-surface-border/70">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-brand-tint-light text-brand-primary mb-2">
                Account Onboarding
            </span>
            <h1 class="text-xl sm:text-2xl font-bold text-content-heading tracking-tight">Setup Required</h1>
            <p class="text-xs text-content-body mt-1">Please complete and verify your account details to continue.</p>
        </div>

        @error('warning')
            <div class="mb-4 py-3 px-4 text-xs font-semibold text-brand-secondary bg-brand-secondary/10 rounded-xl border border-brand-secondary/30 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0" aria-hidden="true" />
                <span>{{ $message }}</span>
            </div>
        @enderror

        <form wire:submit="updateProfile" class="space-y-4">
            {{-- First Name & Suffix --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label for="cp_first_name" class="block text-xs font-semibold text-content-heading mb-1">
                        First Name <span class="text-brand-secondary">*</span>
                    </label>
                    <input
                        wire:model="first_name"
                        type="text"
                        id="cp_first_name"
                        placeholder="John"
                        class="w-full p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('first_name') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                    @error('first_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="cp_suffix" class="block text-xs font-semibold text-content-heading mb-1">
                        Suffix <span class="text-content-muted font-normal">(Optional)</span>
                    </label>
                    <input
                        wire:model="suffix"
                        type="text"
                        id="cp_suffix"
                        placeholder="Jr., Sr."
                        class="w-full p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none"
                    >
                    @error('suffix') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Middle Name & Last Name --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="cp_middle_name" class="block text-xs font-semibold text-content-heading mb-1">
                        Middle Name <span class="text-content-muted font-normal">(Optional)</span>
                    </label>
                    <input
                        wire:model="middle_name"
                        type="text"
                        id="cp_middle_name"
                        placeholder="D."
                        class="w-full p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('middle_name') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                    @error('middle_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="cp_last_name" class="block text-xs font-semibold text-content-heading mb-1">
                        Last Name <span class="text-brand-secondary">*</span>
                    </label>
                    <input
                        wire:model="last_name"
                        type="text"
                        id="cp_last_name"
                        placeholder="Doe"
                        class="w-full p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('last_name') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                    @error('last_name') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Address & Contact Number --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="cp_address" class="block text-xs font-semibold text-content-heading mb-1">
                        Address <span class="text-brand-secondary">*</span>
                    </label>
                    <input
                        wire:model="address"
                        type="text"
                        id="cp_address"
                        placeholder="123 Street, City"
                        class="w-full p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('address') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                    @error('address') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="cp_contact_number" class="block text-xs font-semibold text-content-heading mb-1">
                        Contact Number <span class="text-brand-secondary">*</span>
                    </label>
                    <input
                        wire:model="contact_number"
                        type="text"
                        id="cp_contact_number"
                        placeholder="09123456789"
                        class="w-full p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('contact_number') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                    @error('contact_number') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Email & Username --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="cp_email" class="block text-xs font-semibold text-content-heading mb-1">
                        Email Address <span class="text-brand-secondary">*</span>
                    </label>
                    <input
                        wire:model="email"
                        type="email"
                        id="cp_email"
                        autocomplete="email"
                        placeholder="john@example.com"
                        class="w-full p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('email') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                    @error('email') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="cp_username" class="block text-xs font-semibold text-content-heading mb-1">
                        Username <span class="text-brand-secondary">*</span>
                    </label>
                    <input
                        wire:model="username"
                        type="text"
                        id="cp_username"
                        autocomplete="username"
                        placeholder="johndoe"
                        class="w-full p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('username') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                    >
                    @error('username') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Optional Password Update --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ showPassword: false }">
                <div>
                    <label for="cp_password" class="block text-xs font-semibold text-content-heading mb-1">
                        New Password <span class="text-content-muted font-normal">(Optional)</span>
                    </label>
                    <div class="relative">
                        <input
                            wire:model="password"
                            :type="showPassword ? 'text' : 'password'"
                            id="cp_password"
                            autocomplete="new-password"
                            placeholder="••••••••"
                            class="w-full p-2.5 pr-9 bg-surface-canvas text-content-heading placeholder:text-content-muted border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none @error('password') border-brand-secondary bg-brand-secondary/5 @else border-surface-border @enderror"
                        >
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Hide new password' : 'Show new password'"
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-content-muted hover:text-content-heading cursor-pointer"
                        >
                            <x-heroicon-o-eye x-show="!showPassword" class="w-4 h-4" aria-hidden="true" />
                            <x-heroicon-o-eye-slash x-show="showPassword" x-cloak class="w-4 h-4" aria-hidden="true" />
                        </button>
                    </div>
                    @error('password') <span class="text-[11px] text-brand-secondary mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="cp_password_confirmation" class="block text-xs font-semibold text-content-heading mb-1">
                        Confirm Password
                    </label>
                    <input
                        wire:model="password_confirmation"
                        :type="showPassword ? 'text' : 'password'"
                        id="cp_password_confirmation"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full p-2.5 bg-surface-canvas text-content-heading placeholder:text-content-muted border border-surface-border rounded-xl text-xs transition focus:bg-surface-card focus:border-brand-primary focus:ring-4 focus:ring-brand-primary/15 outline-none"
                    >
                </div>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="updateProfile"
                class="w-full bg-brand-primary hover:bg-brand-primary-hover text-white text-xs font-semibold py-2.5 px-4 rounded-xl shadow-2xs transition mt-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-primary/20"
            >
                <span wire:loading.remove wire:target="updateProfile">Save &amp; Continue</span>
                <span wire:loading wire:target="updateProfile">Processing...</span>
            </button>
        </form>
    </div>
</div>
