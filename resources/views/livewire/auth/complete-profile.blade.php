<div class="max-w-lg mx-auto my-10 bg-white p-8 rounded-xl shadow-md border border-gray-200">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Setup Required</h1>
        <p class="text-sm text-gray-500 mt-1">Please update your account details to continue.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-blue-600 bg-blue-50 p-3 rounded-lg border border-blue-200">
            {{ session('status') }}
        </div>
    @endif

    @error('warning')
        <div class="mb-4 py-3 px-4 text-sm font-bold text-yellow-700 bg-amber-100 rounded-lg border border-amber-300">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="updateProfile" class="space-y-4">
        <!-- Name Group -->
        <div class="grid grid-cols-3 gap-2">
            <div class="col-span-2">
                <label for="first_name" class="block text-xs font-medium text-gray-700 mb-1">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="first_name"
                    type="text"
                    id="first_name"
                    placeholder="John"
                    class="w-full p-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_name') border-red-500 ring-1 ring-red-500 @else border-gray-300 @enderror"
                >
                @error('first_name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="suffix" class="block text-xs font-medium text-gray-700 mb-1">
                    Suffix <span class="text-gray-400 font-normal">(Optional)</span>
                </label>
                <input
                    wire:model="suffix"
                    type="text"
                    id="suffix"
                    placeholder="Jr., Sr."
                    class="w-full p-2 border border-gray-300 rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('suffix') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label for="middle_name" class="block text-xs font-medium text-gray-700 mb-1">
                    Middle Name <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="middle_name"
                    type="text"
                    id="middle_name"
                    placeholder="D."
                    class="w-full p-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 @error('middle_name') border-red-500 ring-1 ring-red-500 @else border-gray-300 @enderror"
                >
                @error('middle_name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="last_name" class="block text-xs font-medium text-gray-700 mb-1">
                    Last Name <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="last_name"
                    type="text"
                    id="last_name"
                    placeholder="Doe"
                    class="w-full p-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 @error('last_name') border-red-500 ring-1 ring-red-500 @else border-gray-300 @enderror"
                >
                @error('last_name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Contact & Address -->
        <div>
            <label for="address" class="block text-xs font-medium text-gray-700 mb-1">
                Address <span class="text-red-500">*</span>
            </label>
            <input
                wire:model="address"
                type="text"
                id="address"
                placeholder="123 Street, City"
                class="w-full p-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 ring-1 ring-red-500 @else border-gray-300 @enderror"
            >
            @error('address') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="contact_number" class="block text-xs font-medium text-gray-700 mb-1">
                Contact Number <span class="text-red-500">*</span>
            </label>
            <input
                wire:model="contact_number"
                type="text"
                id="contact_number"
                placeholder="09123456789"
                class="w-full p-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 @error('contact_number') border-red-500 ring-1 ring-red-500 @else border-gray-300 @enderror"
            >
            @error('contact_number') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Credentials -->
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label for="email" class="block text-xs font-medium text-gray-700 mb-1">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="email"
                    type="email"
                    id="email"
                    placeholder="john@example.com"
                    class="w-full p-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 ring-1 ring-red-500 @else border-gray-300 @enderror"
                >
                @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="username" class="block text-xs font-medium text-gray-700 mb-1">
                    Username <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="username"
                    type="text"
                    id="username"
                    placeholder="johndoe"
                    class="w-full p-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 @error('username') border-red-500 ring-1 ring-red-500 @else border-gray-300 @enderror"
                >
                @error('username') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Password Update -->
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label for="password" class="block text-xs font-medium text-gray-700 mb-1">
                    New Password <span class="text-gray-400 font-normal">(Optional)</span>
                </label>
                <input
                    wire:model="password"
                    type="password"
                    id="password"
                    placeholder="••••••••"
                    class="w-full p-2 border rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 ring-1 ring-red-500 @else border-gray-300 @enderror"
                >
                @error('password') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">
                    Confirm Password
                </label>
                <input
                    wire:model="password_confirmation"
                    type="password"
                    id="password_confirmation"
                    placeholder="••••••••"
                    class="w-full p-2 border border-gray-300 rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            class="w-full bg-blue-600 text-white font-medium py-2 rounded-lg hover:bg-blue-700 transition mt-4 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
            <span wire:loading.remove wire:target="updateProfile">Save & Continue</span>
            <span wire:loading wire:target="updateProfile">Processing...</span>
        </button>
    </form>
</div>
