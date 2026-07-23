<div class="max-w-lg mx-auto bg-white p-8 rounded-xl shadow-md border border-gray-200">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">BPS LMS</h1>
        <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
    </div>

    <!-- Alert Banner for Authentication Warning Error -->
    @error('warning')
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg flex items-center space-x-2">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0 text-red-500" />
            <span>{{ $message }}</span>
        </div>
    @enderror

    <!-- FIX: Use wire:submit="login" without .prevent -->
    <form wire:submit="login" class="space-y-4">
        <!-- Username/Email Address -->
        <div>
            <label for="umail" class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-user class="w-5 h-5 text-gray-400" />
                </div>
                <input
                    wire:model="umail"
                    type="text"
                    id="umail"
                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    placeholder="username or admin@example.com"
                    required
                    autofocus
                >
            </div>
            @error('umail')
                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-lock-closed class="w-5 h-5 text-gray-400" />
                </div>
                <input
                    wire:model="password"
                    type="password"
                    id="password"
                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    required
                >
            </div>
            @error('password')
                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label class="flex items-center text-sm text-gray-600 cursor-pointer">
                <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2">Remember me</span>
            </label>
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
            <span class="pointer-events-none flex items-center space-x-1">
                <span>Sign In</span>
                <x-heroicon-m-arrow-right-end-on-rectangle class="w-5 h-5 ml-1" />
            </span>
        </button>
    </form>
</div>
