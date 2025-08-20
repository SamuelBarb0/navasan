<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <x-toasts.impresiones-pendientes />
    <x-toasts.alertas-impresion />
    <x-toasts.revision-pendiente />
    <x-toasts.suaje-alerta /> {{-- suaje (global/info) --}}
    <x-toasts.suaje-desfase /> {{-- suaje (desfase) --}}

    <x-toasts.suaje-alerta tipo="laminado" /> {{-- laminado (global/info) --}}
    <x-toasts.suaje-desfase tipo="laminado" /> {{-- laminado (desfase) --}}

    <x-toasts.suaje-alerta tipo="empalmado" /> {{-- empalmado (global/info) --}}
    <x-toasts.suaje-desfase tipo="empalmado" /> {{-- empalmado (desfase) --}}


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Selecciona un cliente',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: () => "No se encontraron resultados",
                    searching: () => "Buscando...",
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#cliente_id').on('change', function() {
                const clienteId = $(this).val();

                if (clienteId) {
                    $.ajax({
                        url: `/productos-por-cliente/${clienteId}`,
                        type: 'GET',
                        success: function(productos) {
                            $('.select2-producto').each(function() {
                                const select = $(this);
                                const selected = select.val(); // mantener selección si aplica
                                const nombreInput = select.closest('.producto-item').find('.nombre-producto');

                                select.empty().append('<option value="">-- Selecciona código --</option>');

                                productos.forEach(p => {
                                    select.append(`<option value="${p.id}" data-nombre="${p.nombre}">${p.codigo}</option>`);
                                });

                                nombreInput.val('');
                            });
                        }
                    });
                } else {
                    $('.select2-producto').html('<option value="">-- Selecciona código --</option>');
                    $('.nombre-producto').val('');
                }
            });

            // Autocompletar nombre producto al seleccionar código
            $(document).on('change', '.select2-producto', function() {
                const selectedOption = $(this).find('option:selected');
                const nombre = selectedOption.data('nombre') || '';
                $(this).closest('.producto-item').find('.nombre-producto').val(nombre);
            });
        });
    </script>


    <script>
        $('#formCliente').submit(function(e) {
            e.preventDefault();

            const data = {
                nombre: $('#nombre_cliente').val(),
                nit: $('#nit_cliente').val(),
                telefono: $('#telefono_cliente').val(),
                _token: '{{ csrf_token() }}'
            };

            $.post("{{ route('clientes.ajaxStore') }}", data, function(response) {
                const option = new Option(response.nombre, response.id, true, true);
                $('#cliente_id').append(option).trigger('change');
                $('#modalCliente').modal('hide');
                $('#formCliente')[0].reset();
            }).fail(function(xhr) {
                alert('Error al crear cliente.');
            });
        });
    </script>

    <script>
        @isset($productos)
        window.productos = @json($productos);
        @else
        window.productos = [];
        @endisset
    </script>
    @if(request()->is('ordenes/create'))
    <script>
        $(document).ready(function() {
            console.log("🚀 Script cargado y listo");

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });

            $('#formNuevoProducto').on('submit', function(e) {
                e.preventDefault();
                console.log("📤 Enviando producto con imagen y precio...");

                const form = $(this)[0];
                const formData = new FormData(form);

                const $form = $(this);
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();

                $.ajax({
                    url: "{{ route('productos.store') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(producto) {
                        console.log("✅ Producto creado:", producto);
                        productos.push(producto);

                        $('.select2-producto').each(function() {
                            const select = $(this);
                            const option = new Option(producto.codigo, producto.id, false, false);
                            $(option).attr('data-nombre', producto.nombre);
                            select.append(option);
                        });

                        $('#modalProducto').modal('hide');
                        $form[0].reset();
                    },
                    error: function(xhr) {
                        console.warn("❌ Error al guardar producto:", xhr);
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let mensaje = 'Errores:\n';
                            for (const field in errors) {
                                mensaje += `- ${errors[field][0]}\n`;
                            }
                            alert(mensaje);
                        } else {
                            alert('Error inesperado. Revisa la consola.');
                        }
                    }
                });
            });
        });
    </script>
    @endif
    @push('scripts')
    <script>
        let index = 1;

        // Variable global editable para productos según el cliente seleccionado
        @isset($productos)
        var productos = @json($productos);
        @else
        var productos = [];
        @endisset

        function actualizarProductosSegunCliente() {
            const clienteId = $('#cliente_id').val();
            if (!clienteId) return;

            $.ajax({
                url: `/productos-por-cliente/${clienteId}`,
                type: 'GET',
                success: function(productosCliente) {
                    if (productosCliente.length === 0) {
                        console.warn("Cliente sin productos. Se mostrarán todos los disponibles.");
                    }

                    productos = productosCliente;

                    $('.select2-producto').each(function() {
                        const select = $(this);
                        const nombreInput = select.closest('.producto-item').find('.nombre-producto');

                        let options = '<option value="">-- Selecciona código --</option>';
                        productos.forEach(p => {
                            options += `<option value="${p.id}" data-nombre="${p.nombre}">${p.codigo}</option>`;
                        });

                        select.html(options).trigger('change');
                        nombreInput.val('');
                    });
                }
            });
        }

        function addItem() {
            const container = document.getElementById('items');
            const row = document.createElement('div');
            row.className = 'producto-item row g-3 mb-4';
            row.dataset.index = index;

            let options = `<option value="">-- Selecciona código --</option>`;
            productos.forEach(p => {
                options += `<option value="${p.id}" data-nombre="${p.nombre}">${p.codigo}</option>`;
            });

            row.innerHTML = `
            <div class="col-md-4">
                <label class="form-label">Código</label>
                <select name="items[${index}][producto_id]" class="form-select select2-producto" data-index="${index}" required>
                    ${options}
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control nombre-producto" name="items[${index}][nombre]" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Cantidad Total</label>
                <input type="number" class="form-control" name="items[${index}][cantidad]" required>
            </div>

            <div class="col-12 mt-2">
                <label class="form-label fw-semibold">Fechas de Entrega</label>
                <div class="entregas" data-item-index="${index}">
                    <div class="row entrega-row g-2 mb-2" data-entrega-index="0">
                        <div class="col-md-5">
                            <input type="date" class="form-control" name="items[${index}][entregas][0][fecha]" required>
                        </div>
                        <div class="col-md-5">
                            <input type="number" class="form-control" name="items[${index}][entregas][0][cantidad]" placeholder="Cantidad a entregar" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeEntrega(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="addEntrega(this)" style="border-color: #0578BE; color: #0578BE;">
                    <i class="bi bi-calendar-plus"></i> Añadir fecha
                </button>
            </div>
        `;

            container.appendChild(row);
            $(`.select2-producto[data-index="${index}"]`).select2();
            index++;
        }

        function addEntrega(button) {
            const entregasDiv = button.previousElementSibling;
            const itemIndex = entregasDiv.dataset.itemIndex;
            const entregaIndex = entregasDiv.querySelectorAll('.entrega-row').length;

            const entregaRow = document.createElement('div');
            entregaRow.className = 'row entrega-row g-2 mb-2';
            entregaRow.dataset.entregaIndex = entregaIndex;

            entregaRow.innerHTML = `
            <div class="col-md-5">
                <input type="date" class="form-control" name="items[${itemIndex}][entregas][${entregaIndex}][fecha]" required>
            </div>
            <div class="col-md-5">
                <input type="number" class="form-control" name="items[${itemIndex}][entregas][${entregaIndex}][cantidad]" placeholder="Cantidad a entregar" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeEntrega(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

            entregasDiv.appendChild(entregaRow);
        }

        function removeEntrega(button) {
            const row = button.closest('.entrega-row');
            row.remove();
        }

        $(document).on('change', '.select2-producto', function() {
            const selected = $(this).find('option:selected');
            const nombre = selected.data('nombre') || '';
            const idx = $(this).data('index');
            $(`div[data-index="${idx}"] .nombre-producto`).val(nombre);
        });

        $(document).ready(function() {
            $('.select2').select2();
            $('.select2-producto').select2();

            // Actualiza productos si ya hay un cliente seleccionado al cargar
            if ($('#cliente_id').val()) {
                actualizarProductosSegunCliente();
            }

            $('#cliente_id').on('change', function() {
                actualizarProductosSegunCliente();
            });
        });
    </script>

    <script>
        document.querySelectorAll('.estado-insumo').forEach(select => {
            select.addEventListener('change', function() {
                const id = this.dataset.id;
                const estado = this.value;

                fetch(`/insumos-orden/${id}/estado`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            estado
                        })
                    }).then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ Estado actualizado');
                        } else {
                            alert('⚠️ No se pudo actualizar');
                        }
                    });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.select-insumo').select2({
                width: '100%',
                placeholder: "Seleccione un insumo",
                allowClear: true
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Filtro superior (form principal)
            $('select[name="cliente_filtro"]').select2({
                placeholder: 'Seleccionar cliente',
                allowClear: true,
                width: '100%'
            });

            // Select del modal
            $('#producto_cliente').select2({
                dropdownParent: $('#modalProducto'),
                placeholder: 'Seleccionar cliente',
                allowClear: true,
                width: '100%'
            });
        });
    </script>

</body>

</html>