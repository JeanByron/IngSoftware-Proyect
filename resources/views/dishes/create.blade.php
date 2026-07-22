<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display font-semibold text-xl text-cocoa-900 tracking-tight leading-tight">
            {{ __('Nuevo plato') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="card-brand p-6">
                {{-- RF-01: crear plato --}}
                <form id="dish-create-form" method="POST" action="{{ route('dishes.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('dishes._form')
                </form>
            </div>
        </div>
    </div>

    {{-- RNF-15: guarda un borrador del formulario en localStorage mientras se
         escribe, para no perder lo tecleado si la página se recarga por error o
         falla el envío. Se limpia al enviar con éxito. (El fallo de validación
         del servidor ya repuebla con old(); esto cubre además la recarga.) --}}
    <script>
        (function () {
            const KEY  = 'mesaqr.dish.draft';
            const form = document.getElementById('dish-create-form');
            if (! form) return;

            // Restaurar: sólo rellena campos vacíos (no pisa lo que old() ya puso
            // tras un fallo de validación). No se restauran archivos.
            try {
                const draft = JSON.parse(localStorage.getItem(KEY) || '{}');
                for (const [name, value] of Object.entries(draft)) {
                    const el = form.elements[name];
                    if (el && el.type !== 'file' && ! el.value) el.value = value;
                }
            } catch (e) { /* dato corrupto: se ignora */ }

            // Guardar en cada cambio.
            form.addEventListener('input', function () {
                const draft = {};
                for (const el of form.elements) {
                    if (el.name && el.type !== 'file' && el.type !== 'checkbox') draft[el.name] = el.value;
                }
                try { localStorage.setItem(KEY, JSON.stringify(draft)); } catch (e) {}
            });

            // Limpiar al enviar (el plato ya se guardó).
            form.addEventListener('submit', function () {
                try { localStorage.removeItem(KEY); } catch (e) {}
            });
        })();
    </script>
</x-app-layout>
