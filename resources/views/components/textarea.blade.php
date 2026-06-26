@props([
    'name',
    'id' => null,
    'value' => '',
    'label' => null,
    'placeholder' => '',
    'required' => false,
    'rows' => 3,
    'error' => null,
    'class' => ''
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

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-control' . ($error ? ' is-invalid' : '')]) }}
    >{{ old($name, $value) }}</textarea>

    @if($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
