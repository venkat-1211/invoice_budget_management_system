@props([
    'name',
    'id' => null,
    'label' => null,
    'options' => [],
    'value' => '',
    'required' => false,
    'error' => null,
    'class' => '',
    'placeholder' => 'Select...',
    'multiple' => false
])

@php
$id = $id ?? $name;
$selectedValue = old($name, $value);
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

    <select
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        id="{{ $id }}"
        {{ $required ? 'required' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes->merge(['class' => 'form-select' . ($error ? ' is-invalid' : '')]) }}
    >
        @if(!$multiple && $placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $key => $option)
            @php
                $optionValue = is_array($option) ? ($option['value'] ?? $key) : $key;
                $optionLabel = is_array($option) ? ($option['label'] ?? $option) : $option;
                $isSelected = $multiple
                    ? (is_array($selectedValue) && in_array($optionValue, $selectedValue))
                    : (string)$selectedValue === (string)$optionValue;
            @endphp
            <option value="{{ $optionValue }}" {{ $isSelected ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach

        {{ $slot }}
    </select>

    @if($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
