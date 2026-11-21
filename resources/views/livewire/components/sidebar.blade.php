{{-- resources/views/livewire/components/sidebar.blade.php --}}
<div>
    {{-- Backdrop overlay for drawer screens (< 1024px) --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-[var(--header-h,4rem)] inset-x-0 bottom-0 bg-content-heading/40 backdrop-blur-2xs z-30 lg:hidden"
    ></div>

    {{-- Responsive Sidebar Drawer --}}
    <aside
        aria-label="Sidebar navigation"
        @keydown.escape.window="sidebarOpen = false"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed top-[var(--header-h,4rem)] bottom-0 left-0 z-40 w-64 bg-surface-card border-r border-surface-border px-3.5 py-4 flex flex-col justify-between transition-transform duration-200 ease-out shrink-0 overflow-y-auto select-none"
    >
        <div class="space-y-4">
            {{-- Mobile/Drawer Sidebar Header / Close Button --}}
            <div class="flex items-center justify-between pb-2.5 px-1 border-b border-surface-border/70 lg:hidden">
                <span class="text-[11px] font-semibold text-content-muted uppercase tracking-wider">Navigation Menu</span>
                <button
                    @click="sidebarOpen = false"
                    type="button"
                    aria-label="Close navigation menu"
                    class="text-content-muted hover:text-content-heading hover:bg-surface-canvas rounded-lg p-1 text-lg font-bold cursor-pointer transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                >
                    &times;
                </button>
            </div>

            <nav aria-label="Primary navigation" class="space-y-5">
                @foreach($sections as $title => $routes)
                    @if(!empty($routes))
                        <div class="space-y-1">
                            <h2 class="text-[11px] font-semibold text-content-muted uppercase tracking-wider mb-1.5 px-3">
                                {{ $title }}
                            </h2>
                            @foreach($routes as $route)
                                @php
                                    $isActive = request()->routeIs($route['route']);
                                @endphp
                                <a
                                    href="{{ Route::has($route['route']) ? route($route['route']) : '#' }}"
                                    wire:navigate
                                    @click="sidebarOpen = false"
                                    @if($isActive) aria-current="page" @endif
                                    class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $isActive ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading' }}"
                                >
                                    <x-dynamic-component :component="'heroicon-o-' . $route['icon']" class="w-4 h-4 shrink-0" aria-hidden="true" />
                                    <span class="truncate">{{ $route['name'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </nav>
        </div>
    </aside>
</div>
