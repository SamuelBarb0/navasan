<nav class="bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            {{-- LOGO --}}
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <img src="{{ asset('images/navasan.png') }}" alt="Logo NAVASAN" class="h-10 w-auto">
            </a>

            {{-- MENÚS DESPLEGABLES --}}
            <div class="hidden sm:flex space-x-6">
                <!-- Menú Genérico -->
                @php
    $menus = [
        'Producción' => [
            ['route' => 'ordenes.index', 'label' => 'Órdenes', 'roles' => ['preprensa', 'administrador']],
            ['route' => 'impresiones.index', 'label' => 'Impresión', 'roles' => ['impresion', 'preprensa', 'administrador']],
            ['route' => 'acabados.index', 'label' => 'Acabados', 'roles' => ['acabados', 'administrador']],
            ['route' => 'revisiones.index', 'label' => 'Revisión', 'roles' => ['revision', 'administrador']],
        ],
        'Administración' => [
            ['route' => 'clientes.index', 'label' => 'Clientes', 'roles' => ['administrador']],
            ['route' => 'productos.index', 'label' => 'Productos', 'roles' => ['administrador']],
            ['route' => 'devoluciones.index', 'label' => 'Devoluciones', 'roles' => ['administrador']],
            ['route' => 'usuarios.index', 'label' => 'Usuarios', 'roles' => ['administrador']],
        ],
        'Almacén' => [
            ['route' => 'insumos.index', 'label' => 'Insumos', 'roles' => ['almacen', 'administrador']],
            ['route' => 'inventario-etiquetas.index', 'label' => 'Inventario', 'roles' => ['almacen', 'administrador']],
        ],
        'Logística' => [
            ['route' => 'facturacion.index', 'label' => 'Facturación', 'roles' => ['logistica', 'administrador']],
        ],
        'Reportes' => [
            ['route' => 'reportes.revisado', 'label' => 'Reporte Revisado', 'roles' => ['preprensa', 'administrador']],
        ],
    ];
@endphp

                @foreach($menus as $titulo => $opciones)
                    <div class="relative group">
                        <button onclick="toggleDropdown('{{ Str::slug($titulo) }}')" class="text-[#16509D] font-semibold hover:text-[#0578BE] focus:outline-none transition duration-200">
                            {{ $titulo }}
                        </button>
                        <div id="{{ Str::slug($titulo) }}" class="dropdown-content hidden absolute left-1/2 -translate-x-1/2 mt-2 w-48 bg-white rounded-xl shadow-xl z-50 py-2 text-center">
                            @foreach($opciones as $item)
                                @hasanyrole(implode('|', $item['roles']))
                                    <a href="{{ route($item['route']) }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#f1f8ff] transition">
                                        {{ $item['label'] }}
                                    </a>
                                @endhasanyrole
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- USUARIO --}}
            <div class="hidden sm:flex items-center space-x-2">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 bg-white text-[#16509D] font-semibold rounded hover:bg-gray-100">
                            {{ Auth::user()->name }}
                            <svg class="ml-2 h-4 w-4 text-[#16509D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
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

{{-- JavaScript para los menús --}}
<script>
    function toggleDropdown(id) {
        // Oculta todos los menús
        document.querySelectorAll('.dropdown-content').forEach(el => el.classList.add('hidden'));
        // Muestra el que corresponde
        const dropdown = document.getElementById(id);
        if (dropdown) dropdown.classList.toggle('hidden');
    }

    // Cierra si se hace clic afuera
    window.addEventListener('click', function (e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('.dropdown-content').forEach(el => el.classList.add('hidden'));
        }
    });
</script>
