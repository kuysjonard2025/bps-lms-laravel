{{-- resources/views/livewire/components/sidebar.blade.php --}}
@php
    $currentUser = auth()->user();
    $isLibrarian = false;

    if ($currentUser) {
        if (method_exists($currentUser, 'hasRole')) {
            $isLibrarian = $currentUser->hasRole('librarian');
        } else {
            $isLibrarian = mb_strtolower(trim((string) ($currentUser->role ?? ''))) === 'librarian';
        }
    }

    $canSeeRoute = function (array $routeItem) use ($isLibrarian): bool {
        $routeName = $routeItem['route'] ?? '';

        if ($routeName === 'active-sessions' && ! $isLibrarian) {
            return false;
        }

        if (! empty($routeItem['librarian_only']) && ! $isLibrarian) {
            return false;
        }

        if (! empty($routeItem['roles']) && is_array($routeItem['roles'])) {
            $allowedRoles = array_map(
                fn ($role) => mb_strtolower(trim((string) $role)),
                $routeItem['roles']
            );

            if (! $isLibrarian || ! in_array('librarian', $allowedRoles, true)) {
                return false;
            }
        }

        return true;
    };

    $visibleSections = [];
    foreach ($sections as $sectionTitle => $sectionRoutes) {
        if (! empty($sectionRoutes) && is_array($sectionRoutes)) {
            $filteredRoutes = array_values(array_filter(
                $sectionRoutes,
                fn ($item) => is_array($item) && $canSeeRoute($item)
            ));

            if ($filteredRoutes !== []) {
                $visibleSections[$sectionTitle] = $filteredRoutes;
            }
        }
    }

    $activeSectionKey = null;
    foreach ($visibleSections as $sectionTitle => $sectionRoutes) {
        foreach ($sectionRoutes as $routeItem) {
            if (request()->routeIs($routeItem['route'])) {
                $activeSectionKey = \Illuminate\Support\Str::slug($sectionTitle);
                break 2;
            }
        }
    }
    if (! $activeSectionKey) {
        foreach ($visibleSections as $sectionTitle => $sectionRoutes) {
            $activeSectionKey = \Illuminate\Support\Str::slug($sectionTitle);
            break;
        }
    }
@endphp

<div
    x-data="{
        sidebarCollapsed: false,
        isScrolling: false,
        scrollTimer: null,
        routeSection: @js($activeSectionKey),
        activeSection: @js($activeSectionKey),
        init() {
            try {
                const savedCollapsed = localStorage.getItem('bps_sidebar_collapsed');
                if (savedCollapsed !== null) {
                    this.sidebarCollapsed = savedCollapsed === 'true';
                }

                const savedSection = localStorage.getItem('bps_sidebar_active_section');
                if (this.routeSection) {
                    this.activeSection = this.routeSection;
                    localStorage.setItem('bps_sidebar_active_section', this.routeSection);
                } else if (savedSection) {
                    this.activeSection = savedSection;
                }
            } catch (e) {
                if (this.routeSection) {
                    this.activeSection = this.routeSection;
                }
            }

            this.$watch('sidebarCollapsed', (value) => {
                try {
                    localStorage.setItem('bps_sidebar_collapsed', String(value));
                } catch (e) {}
                window.dispatchEvent(new CustomEvent('sidebar-collapsed', {
                    detail: { collapsed: value }
                }));
            });
        },
        handleScroll() {
            this.isScrolling = true;
            clearTimeout(this.scrollTimer);
            this.scrollTimer = setTimeout(() => {
                this.isScrolling = false;
            }, 900);
        },
        toggleSection(key) {
            if (!key) return;
            this.activeSection = key;
            try {
                localStorage.setItem('bps_sidebar_active_section', this.activeSection);
            } catch (e) {}
        }
    }"
>
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

    {{-- Responsive Sidebar Drawer & Collapsible Rail (Mobile, Tablet & Desktop) --}}
    <aside
        aria-label="Sidebar navigation"
        @scroll.passive="handleScroll()"
        @keydown.escape.window="sidebarOpen = false"
        :class="[
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarCollapsed ? 'w-20 px-2.5' : 'w-64 px-3.5',
            isScrolling ? 'is-scrolling' : ''
        ]"
        class="sidebar-scroll-autohide fixed top-[var(--header-h,4rem)] bottom-0 left-0 z-40 bg-surface-card border-r border-surface-border py-4 flex flex-col justify-between transition-all duration-200 ease-out shrink-0 overflow-y-auto select-none"
    >
        <div class="space-y-4">
            {{-- Mobile/Tablet Drawer Close Row --}}
            <div
                class="flex items-center pb-2 border-b border-surface-border/70 lg:hidden"
                :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-between px-1'"
            >
                <span
                    x-show="!sidebarCollapsed"
                    x-cloak
                    class="text-[11px] font-semibold text-content-muted uppercase tracking-wider truncate"
                >
                    Navigation Menu
                </span>
                <button
                    @click="sidebarOpen = false"
                    type="button"
                    aria-label="Close navigation menu"
                    title="Close navigation menu"
                    class="text-content-muted hover:text-content-heading hover:bg-surface-canvas rounded-lg p-1 text-lg leading-none font-bold cursor-pointer transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                >
                    &times;
                </button>
            </div>

            {{-- Rail Width Toggle (Mobile, Tablet & Desktop) --}}
            <div class="flex items-center pb-1" :class="sidebarCollapsed ? 'justify-center' : 'justify-between px-1'">
                <span
                    x-show="!sidebarCollapsed"
                    x-cloak
                    class="text-[11px] font-semibold text-content-muted uppercase tracking-wider truncate"
                >
                    Navigation
                </span>
                <button
                    type="button"
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    :aria-expanded="(!sidebarCollapsed).toString()"
                    :aria-label="sidebarCollapsed ? 'Expand sidebar rail' : 'Collapse sidebar rail'"
                    :title="sidebarCollapsed ? 'Expand sidebar rail' : 'Collapse sidebar rail'"
                    class="inline-flex items-center justify-center rounded-xl border border-surface-border bg-surface-canvas p-1.5 text-content-body hover:bg-brand-tint-light hover:text-brand-primary cursor-pointer transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                >
                    <svg
                        class="w-4 h-4 transition-transform duration-200"
                        :class="sidebarCollapsed ? 'rotate-180' : ''"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.75"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
            </div>

            <nav aria-label="Primary navigation" class="space-y-3">
                @foreach($visibleSections as $title => $routes)
                    @php
                        $sectionKey = \Illuminate\Support\Str::slug($title);
                    @endphp
                    <div class="space-y-1">
                        {{-- Always-1-Active Collapsible Section Header --}}
                        <button
                            type="button"
                            x-show="!sidebarCollapsed"
                            x-cloak
                            @click="toggleSection('{{ $sectionKey }}')"
                            :aria-expanded="(activeSection === '{{ $sectionKey }}').toString()"
                            class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-[11px] font-semibold uppercase tracking-wider cursor-pointer transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
                            :class="activeSection === '{{ $sectionKey }}' ? 'text-content-heading bg-surface-canvas/80' : 'text-content-muted hover:text-content-heading hover:bg-surface-canvas/50'"
                        >
                            <span class="truncate">{{ $title }}</span>
                            <svg
                                class="w-3.5 h-3.5 shrink-0 transition-transform duration-150"
                                :class="activeSection === '{{ $sectionKey }}' ? 'rotate-0' : '-rotate-90'"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        {{-- Divider when Rail is Icon-Only Collapsed --}}
                        <div
                            x-show="sidebarCollapsed"
                            x-cloak
                            class="my-2 border-t border-surface-border/70"
                            aria-hidden="true"
                        ></div>

                        {{-- Nav Links inside Active Section (or all icons when Rail is Collapsed) --}}
                        <div
                            x-show="sidebarCollapsed || activeSection === '{{ $sectionKey }}'"
                            x-cloak
                            class="space-y-1"
                        >
                            @foreach($routes as $route)
                                @php
                                    $isActive = request()->routeIs($route['route']);
                                @endphp
                                <a
                                    href="{{ Route::has($route['route']) ? route($route['route']) : '#' }}"
                                    wire:navigate
                                    @click="sidebarOpen = false"
                                    title="{{ $route['name'] }}"
                                    aria-label="{{ $route['name'] }}"
                                    @if($isActive) aria-current="page" @endif
                                    :class="sidebarCollapsed ? 'justify-center px-2.5' : 'gap-3 px-3'"
                                    class="flex items-center py-2 rounded-xl text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary {{ $isActive ? 'bg-brand-tint-light text-brand-primary font-semibold' : 'text-content-body hover:bg-surface-canvas hover:text-content-heading' }}"
                                >
                                    <x-dynamic-component :component="'heroicon-o-' . $route['icon']" class="w-4 h-4 shrink-0" aria-hidden="true" />
                                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ $route['name'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </div>
    </aside>
</div>
