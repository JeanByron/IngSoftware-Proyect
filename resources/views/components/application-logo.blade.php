{{-- Logo del comercio (RNF-24: editable por .env vía config/comercio.php). --}}
<img src="{{ asset(config('comercio.logo')) }}" alt="{{ config('comercio.nombre') }}" {{ $attributes }}>
