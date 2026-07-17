@props(['value', 'for' => null])

{{-- El atributo 'for' se declara explícitamente (y no vía $attributes) para que
     la asociación label→control sea visible al análisis estático. --}}
<label for="{{ $for }}" {{ $attributes->merge(['class' => 'block font-medium text-sm text-cocoa-800']) }}>
    {{ $value ?? $slot }}
</label>
