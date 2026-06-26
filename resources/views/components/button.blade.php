@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => null,
    'href' => null,
    'icon' => null,
    'class' => '',
    'disabled' => false
])

@php
$baseClass = 'btn btn-' . $variant;
if ($size) $baseClass .= ' btn-' . $size;
@endphp

@if($href)
    <a href="{{ $href }}"
       class="{{ $baseClass }} {{ $class }}"
       {{ $disabled ? 'disabled' : '' }}
       {{ $attributes }}>
        @if($icon)
            <i class="bi bi-{{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
            class="{{ $baseClass }} {{ $class }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes }}>
        @if($icon)
            <i class="bi bi-{{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </button>
@endif
