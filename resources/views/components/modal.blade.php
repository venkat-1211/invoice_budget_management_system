@props([
    'id',
    'title' => '',
    'size' => null,
    'centered' => true,
    'scrollable' => false,
    'static' => false
])

@php
$dialogClass = 'modal-dialog';
if ($size) $dialogClass .= ' modal-' . $size;
if ($centered) $dialogClass .= ' modal-dialog-centered';
if ($scrollable) $dialogClass .= ' modal-dialog-scrollable';
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true" {{ $static ? 'data-bs-backdrop="static"' : '' }}>
    <div class="{{ $dialogClass }}">
        <div class="modal-content">
            @if($title)
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
