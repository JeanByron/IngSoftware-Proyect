<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-ghost disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
