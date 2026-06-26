@props([
    'type' => 'text',
    'name',
    'id' => null,
    'value' => '',
    'label' => null,
    'placeholder' => '',
    'required' => false,
    'error' => null,
    'class' => '',
    'help' => null
])

@php
$id = $id ?? $name;
@endphp

<div class="mb-3 {{ $class }}">
    @if($label)
        <label for="{{ $id }}" class="form-label fw-semibold">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-control' . ($error ? ' is-invalid' : '')]) }}
    >

    @if($help)
        <div class="form-text text-muted">{{ $help }}</div>
    @endif

    @if($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
