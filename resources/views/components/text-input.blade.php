@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-cocoa-200 focus:border-caramel-500 focus:ring-caramel-400 rounded-lg shadow-sm transition duration-150']) }}>
