<nav class="bg-white border-b border-gray-200 shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Logo y navegación principal -->
            <div class="flex items-center space-x-8">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('images/navasan.png') }}" alt="Logo NAVASAN" class="h-10 w-auto">
                </a>

                <!-- Enlaces principales -->
                <div class="hidden sm:flex space-x-6">

                    @hasanyrole('preprensa|administrador')
                    <x-nav-link :href="route('ordenes.index')" :active="request()->routeIs('ordenes.*')">
                        {{ __('Órdenes') }}
                    </x-nav-link>
                    @endhasanyrole

                    @hasanyrole('administrador')
                    <x-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')">
                        {{ __('Clientes') }}
                    </x-nav-link>
                    @endhasanyrole
                    
                    @hasanyrole('almacen|administrador')
                    <x-nav-link :href="route('insumos.index')" :active="request()->routeIs('insumos.*')">
                        {{ __('Insumos') }}
                    </x-nav-link>
                    @endhasanyrole

                    @hasanyrole('impresion|preprensa|administrador')
                    <x-nav-link :href="route('impresiones.index')" :active="request()->routeIs('impresiones.*')">
                        {{ __('Impresión') }}
                    </x-nav-link>
                    @endhasanyrole

                    @hasanyrole('acabados|administrador')
                    <x-nav-link :href="route('acabados.index')" :active="request()->routeIs('acabados.*')">
                        {{ __('Acabados') }}
                    </x-nav-link>
                    @endhasanyrole

                    @hasanyrole('revision|administrador')
                    <x-nav-link :href="route('revisiones.index')" :active="request()->routeIs('revisiones.*')">
                        {{ __('Revisión') }}
                    </x-nav-link>
                    @endhasanyrole

                    @hasanyrole('logistica|administrador')
                    <x-nav-link :href="route('facturacion.index')" :active="request()->routeIs('facturacion.*')">
                        {{ __('Facturación') }}
                    </x-nav-link>
                    @endhasanyrole

                    @hasanyrole('almacen|administrador')
                    <x-nav-link :href="route('inventario-etiquetas.index')" :active="request()->routeIs('inventario-etiquetas.*')">
                        {{ __('Inventario') }}
                    </x-nav-link>
                    @endhasanyrole

                    @hasanyrole('administrador')
                    <x-nav-link :href="route('devoluciones.index')" :active="request()->routeIs('devoluciones.*')">
                        {{ __('Devoluciones') }}
                    </x-nav-link>
                    @endhasanyrole

                    @hasanyrole('preprensa|administrador')
                    <x-nav-link :href="route('reportes.revisado')" :active="request()->routeIs('reportes.revisado')">
                        {{ __('Reporte Revisado') }}
                    </x-nav-link>
                    @endhasanyrole

                </div>



                <!-- Usuario -->
                <div class="hidden sm:flex items-center space-x-3">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md bg-white text-sm text-gray-700 hover:bg-gray-100">
                                {{ Auth::user()->name }}
                                <svg class="ml-2 h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Perfil') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Cerrar sesión') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
</nav>