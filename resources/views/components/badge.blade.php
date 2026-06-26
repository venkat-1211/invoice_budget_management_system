@props([
    'variant' => 'primary',
    'pill' => false,
    'class' => ''
])

<span class="badge bg-{{ $variant }} {{ $pill ? 'rounded-pill' : '' }} {{ $class }}" {{ $attributes }}>
    {{ $slot }}
</span>
