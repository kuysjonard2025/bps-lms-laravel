<div>
    {{-- Dark Backdrop overlay for mobile/tablet screen --}}
    <div
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        @keydown.escape.window="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-40 lg:hidden"
        x-cloak
    ></div>

    {{-- Responsive Sidebar Drawer --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed lg:sticky top-0 lg:top-[73px] inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 h-dvh lg:h-[calc(100vh-73px)] p-4 flex flex-col justify-between transition-transform duration-300 ease-in-out shrink-0 overflow-y-auto select-none"
    >
        <div class="space-y-4">
            {{-- Mobile/Tablet Sidebar Header / Close Button --}}
            <div class="flex items-center justify-between pb-2 border-b border-gray-100 lg:hidden">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Navigation Menu</span>
                <button
                    @click="sidebarOpen = false"
                    type="button"
                    class="text-gray-400 hover:text-gray-600 rounded-lg p-1 text-lg font-bold cursor-pointer"
                >
                    &times;
                </button>
            </div>

            <nav class="space-y-4">
                @foreach($sections as $title => $routes)
                    @if(!empty($routes))
                        <div>
                            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">
                                {{ $title }}
                            </h2>
                            @foreach($routes as $route)
                                <a
                                    href="{{ Route::has($route['route']) ? route($route['route']) : '#' }}"
                                    wire:navigate
                                    @click="sidebarOpen = false"
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs($route['route']) ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}"
                                >
                                    {{-- Dynamic Heroicon Component --}}
                                    <x-dynamic-component :component="'heroicon-o-' . $route['icon']" class="w-4.5 h-4.5 shrink-0" />
                                    <span class="whitespace-nowrap">{{ $route['name'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </nav>
        </div>
    </aside>
</div>
